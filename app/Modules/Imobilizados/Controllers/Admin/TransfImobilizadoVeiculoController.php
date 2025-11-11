<?php

namespace App\Modules\Imobilizados\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TransfImobilizadoVeiculoService;
use App\Modules\Imobilizados\Models\LogsTransferenciaImobilizadoVeiculo;
use App\Modules\Imobilizados\Models\TransferenciaImobilizadoVeiculo;
use App\Models\TransferenciaVeiculo;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TransfImobilizadoVeiculoController extends Controller
{
    private TransfImobilizadoVeiculoService $transfImobilizadoVeiculoService;

    public function __construct(TransfImobilizadoVeiculoService $transfImobilizadoVeiculoService)
    {
        $this->transfImobilizadoVeiculoService = $transfImobilizadoVeiculoService;
    }

    public function index(Request $request)
    {
        $query = TransferenciaImobilizadoVeiculo::with('veiculo');


        if ($request->filled('id_transferencia_imobilizado_veiculo')) {
            $query->where('id_transferencia_imobilizado_veiculo', $request->id_transferencia_imobilizado_veiculo);
        }

        if ($request->filled('id_tipo_equipamento')) {
            $query->where('id_tipo_equipamento', $request->id_tipo_equipamento);
        }

        if ($request->filled('data_inclusao')) {
            $query->where('data_inclusao', $request->data_inclusao);
        }

        if ($request->filled('data_inicio')) {
            $query->where('data_inicio', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->where('data_fim', $request->data_fim);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transferenciaImobilizadoVeiculo = $query->latest('id_transferencia_imobilizado_veiculo')
            ->paginate(8)
            ->appends($request->query());


        $this->processarStatusConfig($transferenciaImobilizadoVeiculo);

        $id_tipo_equipamento = $this->transfImobilizadoVeiculoService->getIdTipoEquipamento();

        $situacao = $this->transfImobilizadoVeiculoService->getSituacao();

        $transferencias  = $this->getTransferencias();

        return view(
            'admin.transfimobilizadoveiculo.index',
            compact(
                'transferenciaImobilizadoVeiculo',
                'id_tipo_equipamento',
                'situacao',
                'transferencias',
            )
        );
    }

    public function create()
    {
        $tipoEquipamento = $this->transfImobilizadoVeiculoService->getTipoEquipamento();

        $fornecedor = $this->transfImobilizadoVeiculoService->getFornecedor();

        $filial = $this->transfImobilizadoVeiculoService->getFilial();

        $departamento = $this->transfImobilizadoVeiculoService->getDepartamento();

        return view(
            'admin.transfimobilizadoveiculo.create',
            compact(
                'tipoEquipamento',
                'filial',
                'fornecedor',
                'departamento'
            )
        );
    }

    public function store(Request $request)
    {
        // Validação mais robusta
        $validatedData = $request->validate([
            'id_filial_origem'    => 'nullable',
            'id_filial_destino'   => 'nullable',
            'id_fornecedor'       => 'nullable',
            'id_tipo_equipamento' => 'required',
            'tipo'                => 'required',
            'id_usuario'          => 'required',
            'id_departamento'     => 'nullable',
            'data_inicio'         => 'nullable',
            'data_fim'            => 'nullable',
            'observacao'          => 'required'
        ]);

        $tipo = $validatedData['tipo'];
        $filialOrigem = $validatedData['id_filial_origem'];
        $filialDestino = $validatedData['id_filial_destino'];

        if ($tipo == 'FILIAL') {
            if ($filialOrigem == $filialDestino) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Não é possivel transferir para a mesma filial. ');
            }
        }

        try {
            DB::beginTransaction();

            $status = $this->transfImobilizadoVeiculoService->situacaoImobilizado('TRÁFEGO');

            // Cria a Transferência de Imobilizado
            $transferenciaImobilizadoVeiculo = TransferenciaImobilizadoVeiculo::create([
                'status'              => $status,
                'id_tipo_equipamento' => $validatedData['id_tipo_equipamento'],
                'id_usuario'          => $validatedData['id_usuario'],
                'tipo'                => $tipo,
                'id_filial_origem'    => $filialOrigem,
                'id_filial_destino'   => $filialDestino,
                'id_fornecedor'       => $validatedData['id_fornecedor'],
                'id_departamento'     => $validatedData['id_departamento'],
                'observacao'          => $validatedData['observacao'],
                'data_inicio'         => $validatedData['data_inicio'],
                'data_fim'            => $validatedData['data_fim'],
                'data_inclusao'       => now(),
            ]);

            $id = $transferenciaImobilizadoVeiculo->id_transferencia_imobilizado_veiculo;

            // Cria o log de atividade
            LogsTransferenciaImobilizadoVeiculo::create([
                'data_solicitante' => now(),
                'usuario_solicitante' => $validatedData['id_usuario'],
                'status_solicitante' => true,
                'id_transferencia_imobilizado_veiculo' => $id
            ]);

            DB::commit();

            $this->transfImobilizadoVeiculoService->mensagemTransferencia($transferenciaImobilizadoVeiculo, $status, 'TRÁFEGO');

            return redirect()
                ->route('admin.transfimobilizadoveiculo.index')
                ->with('success', 'Transferência imobilizado cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar Transferência imobilizado: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Não foi possível cadastrar a Transferência imobilizado. ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $transferencia = TransferenciaImobilizadoVeiculo::with([
            'modeloVeiculo',
            'categoria',
            'veiculo',
            'tipoEquipamento',
            'departamento',
            'fornecedor',
            'user',
            'departamentoTransferencia',
            'log',
            'filialOrigem',
            'filialDestino',
            'checklist',
            'checklistDevo'
        ])->findOrFail($id);


        return view('admin.transfimobilizadoveiculo.show', compact('transferencia'));
    }

    public function edit(string $id_transferencia_imobilizado_veiculo)
    {
        $transferenciaImobilizadoVeiculo = TransferenciaImobilizadoVeiculo::findOrFail($id_transferencia_imobilizado_veiculo);

        $fornecedor = $this->transfImobilizadoVeiculoService->getFornecedor();

        $tipoEquipamento = $this->transfImobilizadoVeiculoService->getTipoEquipamento();

        $filial = $this->transfImobilizadoVeiculoService->getFilial();

        $departamento = $this->transfImobilizadoVeiculoService->getDepartamento();

        return view(
            'admin.transfimobilizadoveiculo.edit',
            compact(
                'transferenciaImobilizadoVeiculo',
                'tipoEquipamento',
                'filial',
                'fornecedor',
                'departamento'
            )
        );
    }

    public function update(Request $request, string $id)
    {

        // Validação mais robusta
        $validatedData = $request->validate([
            'id_filial_origem'    => 'nullable',
            'id_filial_destino'   => 'nullable',
            'id_fornecedor'       => 'nullable',
            'id_tipo_equipamento' => 'required',
            'tipo'                => 'required',
            'id_departamento'     => 'nullable',
            'id_usuario'          => 'required',
            'id_veiculo'          => 'nullable',
            'observacao'          => 'required_without:observacao_original', // Só obrigatório se não tem observação original
            'observacao_original' => 'nullable',
            'observacao_adicional' => 'required_with:observacao_original', // Obrigatório quando tem observação original
            'anexo_documento'     => 'nullable|file|mimes:pdf|max:2048',
            'data_inicio'         => 'nullable',
            'data_fim'            => 'nullable',
        ]);

        $tipo = $validatedData['tipo'];
        $filialOrigem = $validatedData['id_filial_origem'];
        $filialDestino = $validatedData['id_filial_destino'];

        if ($tipo == 'FILIAL') {
            if ($filialOrigem == $filialDestino) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Não é possivel transferir para a mesma filial. ');
            }
        }


        if (!empty($validatedData['data_inicio']) && !empty($validatedData['data_fim'])) {
            if ($validatedData['data_fim'] < $validatedData['data_inicio']) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Data fim deve ser maior que a data de inicio.');
            }
        }

        try {
            DB::beginTransaction();

            // se acaso mudar o tipo de filial para COMODATO, a filial deve ser nula
            if ($validatedData['tipo'] === 'COMODATO') {
                $filialOrigem = null;
                $filialDestino = null;
            }

            // se acaso mudar o tipo de COMODATO para FILIAL, o fornecedor deve ser nulo
            $fornecedor = $validatedData['id_fornecedor'];
            $departamento = $validatedData['id_departamento'];
            if ($validatedData['tipo'] === 'FILIAL') {
                $fornecedor = null;
                $departamento = null;
            }

            $transferenciaImobilizado = TransferenciaImobilizadoVeiculo::findOrFail($id);

            // Upload do arquivo
            if (empty($transferenciaImobilizado->anexo_documento)) {
                $anexo_documento = null;
            } else {
                $anexo_documento = $transferenciaImobilizado->anexo_documento;
            }
            if ($request->hasFile('anexo_documento') && $request->file('anexo_documento')->isValid()) {
                $anexo_documento = $request->file('anexo_documento')->store('laudos', 'public');
            }


            $data = $validatedData;

            // Se há observação original e nova observação adicional
            if ($request->has('observacao_original') && $request->filled('observacao_adicional')) {
                $observacaoOriginal = $request->input('observacao_original');
                $observacaoAdicional = $request->input('observacao_adicional');

                // Concatena com separador e timestamp
                $data['observacao'] = $observacaoOriginal . "\n" .
                    "--- Adicionado em " . now()->format('d/m/Y H:i') . " ---\n" .
                    $observacaoAdicional;

                // Remove os campos auxiliares
                unset($data['observacao_original'], $data['observacao_adicional']);
            }
            $transferenciaImobilizado->update([
                'id_tipo_equipamento' => $validatedData['id_tipo_equipamento'],
                'id_usuario'          => $validatedData['id_usuario'],
                'id_veiculo'          => $request->input('id_veiculo'),
                'tipo'                => $tipo,
                'id_filial_origem'    => $filialOrigem,
                'id_filial_destino'   => $filialDestino,
                'fornecedor'          => $fornecedor,
                'id_departamento'     => $departamento,
                'observacao'          => $data['observacao'], // Use $data em vez de $validatedData
                'anexo_documento'     => $anexo_documento,
                'data_inicio'         => $request->input('data_inicio'),
                'data_fim'            => $request->input('data_fim'),
                'data_alteracao'      => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.transfimobilizadoveiculo.index')
                ->with('success', 'Transferência imobilizado alterado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao alterar Transferência imobilizado: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Não foi possível alterar a Transferência imobilizado. ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $transferenciaImobilizado = TransferenciaImobilizadoVeiculo::findOrFail($id);
            $transferenciaImobilizado->delete();

            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Transferência Imobilizado excluída',
                    'type'    => 'success',
                    'message' => 'Transferência Imobilizado excluída com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao excluir Transferência imobilizado: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $e->getMessage()
                ]
            ]);
        }
    }

    public function onVerificarSituacao(Request $request)
    {
        $id_request = $request->input('id');
        $veiculo = $request->input('veiculo');
        $editar = $request->input('editar');
        $observacao = $request->input('observacao');
        $observacao_original = $request->input('observacao_original');
        $observacao_adicional = $request->input('observacao_adicional');

        $transferenciaImobilizadoVeiculo = TransferenciaImobilizadoVeiculo::findOrFail($id_request);
        if ($editar) {
            // Processa a observação adicional se fornecida
            if ($observacao_original && $observacao_adicional) {
                $observacaoFinal = $observacao_original . "\n" .
                    "--- Adicionado em " . now()->format('d/m/Y H:i') . " ---\n" .
                    $observacao_adicional;

                $transferenciaImobilizadoVeiculo->update([
                    'observacao' => $observacaoFinal
                ]);
            } elseif ($observacao && !$observacao_original) {
                // Se é uma observação nova (sem observação original)
                $transferenciaImobilizadoVeiculo->update([
                    'observacao' => $observacao
                ]);
            }

            $transferenciaImobilizadoVeiculo->update([
                'id_veiculo' => $veiculo
            ]);
        }
        $id = $transferenciaImobilizadoVeiculo->id_transferencia_imobilizado_veiculo;
        $status = $transferenciaImobilizadoVeiculo->status;
        $tipo = $transferenciaImobilizadoVeiculo->tipo;

        if (empty($transferenciaImobilizadoVeiculo->id_veiculo) && !$editar) {
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Veiculo não vinculado, impossivel alterar'
                ]
            ]);
        }

        // se estiver entrafego '2' e for COMODATO ele vai executar esse if
        if ($status === 2 && $tipo === 'COMODATO') {
            try {
                $this->onJuridico($id);

                if ($editar == true) {
                    return response()->json([
                        'notification' => [
                            'title' => 'Transferência Imobilizado veiculo alterada',
                            'type' => 'success',
                            'message' => 'Transferência Imobilizado veiculo em: Juridico '
                        ],
                        'redirect' => route('admin.transfimobilizadoveiculo.index')
                    ]);
                } else {
                    return response()->json([
                        'notification' => [
                            'title' => 'Transferência Imobilizado veiculo alterada',
                            'type' => 'success',
                            'message' => 'Transferência Imobilizado veiculo em: Juridico'
                        ]
                    ]);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao colocar em Juridico Transferência imobilizado: ' . $e->getMessage(), [
                    'exception' => $e
                ]);

                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => $e->getMessage()
                    ]
                ]);
            }
        }

        // se estiver entrafego '2' e for FILIAL ele vai executar esse if
        if ($status === 2 && $tipo === 'FILIAL') {
            try {
                $this->onFrota($id);

                if ($editar == true) {
                    return response()->json([
                        'notification' => [
                            'title'   => 'Transferência Imobilizado veiculo alterada',
                            'type'    => 'success',
                            'message' => 'Transferência Imobilizado veiculo em: Frota'
                        ],
                        'redirect' => route('admin.transfimobilizadoveiculo.index')
                    ]);
                } else {
                    return response()->json([
                        'notification' => [
                            'title' => 'Transferência Imobilizado veiculo alterada',
                            'type' => 'success',
                            'message' => 'Transferência Imobilizado veiculo em: Frota'
                        ]
                    ]);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao colocar em frota e é filial Transferência imobilizado: ' . $e->getMessage(), [
                    'exception' => $e
                ]);


                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => $e->getMessage()
                    ]
                ]);
            }
        }

        if ($status === 3 && $tipo === 'COMODATO') {
            if (empty($transferenciaImobilizadoVeiculo->data_inicio) || empty($transferenciaImobilizadoVeiculo->data_fim)) {
                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => 'Data de inicio e fim obrigatorio para COMODATO'
                    ]
                ]);
            }

            if (empty($transferenciaImobilizadoVeiculo->anexo_documento)) {
                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => 'Anexo obrigatorio para COMODATO'
                    ]
                ]);
            }

            try {
                $this->onFrota($id);

                return response()->json([
                    'notification' => [
                        'title'   => 'Transferência Imobilizado veiculo alterada',
                        'type'    => 'success',
                        'message' => 'Transferência Imobilizado veiculo em: Frota'
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao colocar em frota e é comodato Transferência imobilizado: ' . $e->getMessage(), [
                    'exception' => $e
                ]);

                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => $e->getMessage()
                    ]
                ]);
            }
        }

        if ($status === 4) {
            if ($transferenciaImobilizadoVeiculo->checklist->status != 'completed') {
                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => 'Checklist não concluído, impossivel transferir para Patrimônio'
                    ]
                ]);
            }

            try {
                $this->onPatrimonio($id);

                return response()->json([
                    'notification' => [
                        'title'   => 'Transferência Imobilizado veiculo alterada',
                        'type'    => 'success',
                        'message' => 'Transferência Imobilizado veiculo em: Patrimônio'
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao colocar em patrimônio Transferência imobilizado: ' . $e->getMessage(), [
                    'exception' => $e
                ]);

                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => $e->getMessage()
                    ]
                ]);
            }
        }

        if ($status === 5) {

            try {
                $this->onFilial($id);

                return response()->json([
                    'notification' => [
                        'title'   => 'Transferência Imobilizado veiculo alterada',
                        'type'    => 'success',
                        'message' => 'Transferência Imobilizado veiculo em: Filial'
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao concluir Transferência imobilizado: ' . $e->getMessage(), [
                    'exception' => $e
                ]);

                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => $e->getMessage()
                    ]
                ]);
            }
        }

        if ($status === 10) {
            if ($transferenciaImobilizadoVeiculo->checklistDevo->status != 'completed') {
                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => 'Checklist não concluído, impossivel transferir para Patrimônio'
                    ]
                ]);
            }

            try {
                $this->onConcluir($id);

                return response()->json([
                    'notification' => [
                        'title'   => 'Transferência Imobilizado veiculo alterada',
                        'type'    => 'success',
                        'message' => 'Transferência Imobilizado veiculo em: Concluída'
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao concluir Transferência imobilizado: ' . $e->getMessage(), [
                    'exception' => $e
                ]);

                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => $e->getMessage()
                    ]
                ]);
            }
        }
    }

    public function onJuridico($id)
    {
        DB::beginTransaction();

        // Cria a Transferência de Imobilizado
        $transferenciaImobilizadoVeiculo = TransferenciaImobilizadoVeiculo::findOrFail($id);
        $status = $this->transfImobilizadoVeiculoService->situacaoImobilizado('JURIDICO');

        $transferenciaImobilizadoVeiculo->update([
            'status'            => $status,
            'data_alteracao'    => now(),
        ]);

        $id = $transferenciaImobilizadoVeiculo->id_transferencia_imobilizado_veiculo;
        $user = Auth::user()->id;

        // Cria o log de atividade
        // é gravado a log de quem tirou do trafego
        $logTransferencia = LogsTransferenciaImobilizadoVeiculo::where('id_transferencia_imobilizado_veiculo', $id);
        $logTransferencia->update([
            'data_trafego' => now(),
            'usuario_trafego' => $user,
            'situacao_trafego' => true,
        ]);

        DB::commit();

        $this->transfImobilizadoVeiculoService->mensagemTransferencia($transferenciaImobilizadoVeiculo, $status, 'JURIDICO');
    }

    public function onFrota($id)
    {
        DB::beginTransaction();

        // Cria a Transferência de Imobilizado
        $transferenciaImobilizadoVeiculo = TransferenciaImobilizadoVeiculo::findOrFail($id);
        $id = $transferenciaImobilizadoVeiculo->id_transferencia_imobilizado_veiculo;
        $user = Auth::user()->id;

        $status = $this->transfImobilizadoVeiculoService->situacaoImobilizado('FROTA'); // FROTA

        $transferenciaImobilizadoVeiculo->update([
            'status'            => $status,
            'data_alteracao'    => now(),
        ]);


        if ($transferenciaImobilizadoVeiculo->tipo == 'COMODATO') {
            $logTransferencia = LogsTransferenciaImobilizadoVeiculo::where('id_transferencia_imobilizado_veiculo', $id);
            $logTransferencia->update([
                'data_juridico' => now(),
                'usuario_juridico' => $user,
                'situacao_juridico' => true,
            ]);
        }

        // Cria o log de atividade
        // é gravado a log de quem tirou do trafego
        $logTransferencia = LogsTransferenciaImobilizadoVeiculo::where('id_transferencia_imobilizado_veiculo', $id);
        $logTransferencia->update([
            'data_trafego' => now(),
            'usuario_trafego' => $user,
            'situacao_trafego' => true,
        ]);

        DB::commit();

        $this->transfImobilizadoVeiculoService->mensagemTransferencia($transferenciaImobilizadoVeiculo, $status, 'FROTA');

        // Criar checklist
        $this->transfImobilizadoVeiculoService->createChecklist($id);
    }

    public function onPatrimonio($id)
    {
        $transferenciaImobilizadoVeiculo = TransferenciaImobilizadoVeiculo::findOrFail($id);

        if (!$transferenciaImobilizadoVeiculo->checklist_id) {
            return response()->json(
                ['success' => false, 'message' => 'O checklist da transferência deve ser concluído antes de prosseguir.'],
                500
            );
        }

        if ($transferenciaImobilizadoVeiculo->checklist->status != 'completed') {
            return response()->json(
                ['success' => false, 'message' => 'O checklist da transferência deve ser concluído antes de prosseguir.'],
                500
            );
        }

        try {
            DB::beginTransaction();

            // Cria a Transferência de Imobilizado
            $status = $this->transfImobilizadoVeiculoService->situacaoImobilizado('PATRIMONIO');

            $transferenciaImobilizadoVeiculo->update([
                'status'            => $status,
                'data_alteracao'    => now(),
            ]);

            $id = $transferenciaImobilizadoVeiculo->id_transferencia_imobilizado_veiculo;
            $user = Auth::user()->id;

            // Cria o log de atividade
            // é gravado a log de quem tirou da frota
            $logTransferencia = LogsTransferenciaImobilizadoVeiculo::where('id_transferencia_imobilizado_veiculo', $id);
            $logTransferencia->update([
                'data_frota' => now(),
                'usuario_frota' => $user,
                'situacao_frota' => true,
            ]);

            DB::commit();

            $this->transfImobilizadoVeiculoService->mensagemTransferencia($transferenciaImobilizadoVeiculo, $status, 'PATRIMONIO');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao concluir Transferência imobilizado: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $e->getMessage()
                ]
            ]);
        }
    }

    public function onFilial($id)
    {
        DB::beginTransaction();

        // Cria a Transferência de Imobilizado
        $transferenciaImobilizadoVeiculo = TransferenciaImobilizadoVeiculo::findOrFail($id);

        $status = $this->transfImobilizadoVeiculoService->situacaoImobilizado('FILIAL'); // FROTA

        $transferenciaImobilizadoVeiculo->update([
            'status'            => $status,
            'data_alteracao'    => now(),
        ]);

        $id = $transferenciaImobilizadoVeiculo->id_transferencia_imobilizado_veiculo;
        $user = Auth::user()->id;

        // Cria o log de atividade
        // é gravado a log de quem tirou do patrimonio
        $logTransferencia = LogsTransferenciaImobilizadoVeiculo::where('id_transferencia_imobilizado_veiculo', $id);
        $logTransferencia->update([
            'data_patrimonio' => now(),
            'usuario_patrimonio' => $user,
            'situacao_patrimonio' => true,
        ]);

        DB::commit();

        $this->transfImobilizadoVeiculoService->mensagemTransferencia($transferenciaImobilizadoVeiculo, $status, 'FILIAL');

        // Criar checklist
        $this->transfImobilizadoVeiculoService->createChecklist($id);
    }

    public function onConcluir($id)
    {
        DB::beginTransaction();

        // Cria a Transferência de Imobilizado
        $transferenciaImobilizadoVeiculo = TransferenciaImobilizadoVeiculo::findOrFail($id);
        $veiculoId = $transferenciaImobilizadoVeiculo->id_veiculo;
        $filialOrigem = $transferenciaImobilizadoVeiculo->id_filial_origem;
        $filialDestino = $transferenciaImobilizadoVeiculo->id_filial_destino;
        $fornecedorId = $transferenciaImobilizadoVeiculo->id_fornecedor;
        $departamentoId = $transferenciaImobilizadoVeiculo->id_departamento;

        $status = $this->transfImobilizadoVeiculoService->situacaoImobilizado('CONCLUIDO');

        $transferenciaImobilizadoVeiculo->update([
            'status'            => $status,
            'data_alteracao'    => now(),
        ]);

        $id = $transferenciaImobilizadoVeiculo->id_transferencia_imobilizado_veiculo;
        $user = Auth::user()->id;

        // Cria o log de atividade
        // é gravado a log de quem tirou do pendente
        $logTransferencia = LogsTransferenciaImobilizadoVeiculo::where('id_transferencia_imobilizado_veiculo', $id);
        $logTransferencia->update([
            'data_pendente' => now(),
            'usuario_pendente' => $user,
            'situacao_pendente' => true,
        ]);

        $veiculo = Veiculo::findOrFail($veiculoId);
        $FilialOrigem = $filialOrigem;
        $FilialDestino = $filialDestino;

        if ($transferenciaImobilizadoVeiculo->tipo == 'COMODATO') {
            $veiculo->update([
                'is_terceiro' => true,
                'id_fornecedor' => $fornecedorId,
                'id_departamento' => $departamentoId,
            ]);
        } else {
            $checklistId = $transferenciaImobilizadoVeiculo->checklist_id;

            TransferenciaVeiculo::create([
                'data_inclusao'       => now(),
                'id_filial_origem'    => $FilialOrigem,
                'id_filial_destino'   => $FilialDestino,
                'data_transferencia'  => now(),
                'id_veiculo'          => $veiculo->id_veiculo,
                'checklist'           => $checklistId,
            ]);

            $veiculo->update([
                'id_filial' => $FilialDestino,
            ]);
        }

        DB::commit();

        $this->transfImobilizadoVeiculoService->mensagemTransferencia($transferenciaImobilizadoVeiculo, $status, 'CONCLUIDO');
    }

    public function onReprovar(Request $request)
    {
        $id = $request->input('id');
        $justificativa = $request->input('justificativa');

        if (empty($justificativa)) {
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Justificativa obrigatoria'
                ]
            ]);
        }

        try {
            DB::beginTransaction();

            // Cria a Transferência de Imobilizado
            $transferenciaImobilizadoVeiculo = TransferenciaImobilizadoVeiculo::findOrFail($id);
            $status = $this->transfImobilizadoVeiculoService->situacaoImobilizado('REPROVAR'); // FROTA

            $transferenciaImobilizadoVeiculo->update([
                'status'            => $status,
                'justificativa'     => $justificativa,
                'data_alteracao'    => now(),
            ]);

            $id = $transferenciaImobilizadoVeiculo->id_transferencia_imobilizado_veiculo;
            $user = Auth::user()->id;

            // Cria o log de atividade
            // é gravado a log de quem tirou do reprova
            $logTransferencia = LogsTransferenciaImobilizadoVeiculo::where('id_transferencia_imobilizado_veiculo', $id);
            $logTransferencia->update([
                'data_reprova' => now(),
                'usuario_reprova' => $user,
                'situacao_reprova' => true,
            ]);

            DB::commit();

            $this->transfImobilizadoVeiculoService->mensagemTransferencia($transferenciaImobilizadoVeiculo, $status, 'REPROVAR');

            return response()->json([
                'notification' => [
                    'title'   => 'Transferência Imobilizado veiculo reprovada',
                    'type'    => 'info',
                    'message' => 'Transferência Imobilizado veiculo reprovada'
                ],
                'redirect' => route('admin.transfimobilizadoveiculo.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao reprovar Transferência imobilizado: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $e->getMessage()
                ]
            ]);
        }
    }

    public function getVehicleData(Request $request)
    {
        $idTipoVeiculo = $request->input('tipoEquipamento');
        $idFilial = $request->input('filial');

        try {
            // Buscar todos os veículos do tipo equipamento selecionado
            $veiculos = Veiculo::select('id_veiculo as value', 'placa as label')
                ->where('id_tipo_equipamento', $idTipoVeiculo)
                ->where('id_filial', $idFilial)
                ->orderBy('placa')
                ->get();

            return response()->json([
                'veiculos' => $veiculos,
                'total' => $veiculos->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do veículo: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar dados do veículo'], 500);
        }
    }

    private function getTransferencias()
    {
        return  TransferenciaImobilizadoVeiculo::with(['log', 'filialOrigem', 'filialDestino'])
            ->get()
            ->mapWithKeys(function ($transferencia) {

                $log = $transferencia->log;

                // Função auxiliar para traduzir booleano para "APROVADO"
                $aprovadoSeTrue = fn($val) => $val === true || $val === 1 ? 'APROVADO' : $val;

                return [
                    $transferencia->id_transferencia_imobilizado_veiculo => [
                        'id_transferencia_imobilizado_veiculo' => $transferencia->id_transferencia_imobilizado_veiculo,
                        'data_inclusao' => $transferencia->data_inclusao,
                        'observacao' => $transferencia->observacao,
                        'tipo' => $transferencia->tipo,
                        'filial_origem' => $transferencia->filialOrigem->name ?? '',
                        'filial_destino' => $transferencia->filialDestino->name ?? '',
                        'fornecedor' => $transferencia->fornecedor->nome_fornecedor ?? '',
                        'departamento' => $transferencia->departamento->descricao_departamento ?? '',
                        'data_inicio' => $transferencia->data_inicio ?? '',
                        'data_fim' => $transferencia->data_fim ?? '',
                        'idVeiculo' => $transferencia->id_veiculo ?? '',
                        'veiculo' => $transferencia->veiculo->placa ?? '',
                        'log' => [
                            'data_solicitante' => optional($log)->data_solicitante,
                            'usuario_solicitante' => optional($log)->userSolicitante->name ?? '',
                            'data_trafego' => optional($log)->data_trafego,
                            'usuario_trafego' => optional($log)->userTrafego->name ?? '',
                            'situacao_trafego' => $aprovadoSeTrue(optional($log)->situacao_trafego),
                            'data_juridico' => optional($log)->data_juridico,
                            'usuario_juridico' => optional($log)->userJuridico->name ?? '',
                            'situacao_juridico' => $aprovadoSeTrue(optional($log)->situacao_juridico),
                            'data_frota' => optional($log)->data_frota,
                            'usuario_frota' => optional($log)->userFrota->name ?? '',
                            'situacao_frota' => $aprovadoSeTrue(optional($log)->situacao_frota),
                            'data_patrimonio' => optional($log)->data_patrimonio,
                            'usuario_patrimonio' => optional($log)->userPatrimonio->name ?? '',
                            'situacao_patrimonio' => $aprovadoSeTrue(optional($log)->situacao_patrimonio),
                            'data_reprova' => optional($log)->data_reprova,
                            'usuario_reprova' => optional($log)->userReprova->name ?? '',
                            'situacao_reprova' => $aprovadoSeTrue(optional($log)->situacao_reprova),
                            'justiticativa_reprova' => $transferencia->justificativa ?? '',
                            'data_pendente' => optional($log)->data_pendente,
                            'usuario_pendente' => optional($log)->userPendente->name ?? '',
                            'situacao_pendente' => $aprovadoSeTrue(optional($log)->situacao_pendente),
                            'anexo_documento' => $transferencia->anexo_documento ?? '',
                        ]
                    ]
                ];
            });
    }

    private function processarStatusConfig($collection)
    {
        $statusConfig = $this->getStatusConfig();

        $collection->getCollection()->transform(function ($item) use ($statusConfig) {
            $item->status_config = $this->determinarStatusConfig($item, $statusConfig);
            return $item;
        });
    }

    private function getStatusConfig()
    {
        return [
            '2' => [
                'processo' => 'onJuridicoVeiculo',
                'title' => 'Enviar para Juridico',
                'classes' => ' text-green-500 '
            ],
            '3' => [
                'processo' => 'JURIDICO',
                'title' => 'Enviar para Frota',
                'classes' => ' text-yellow-500 '
            ],
            '4' => [
                'processo' => 'FROTA',
                'title' => 'Enviar para Patrimônio',
                'classes' => ' text-orange-500 '
            ],
            '5' => [
                'processo' => 'PENDENTE',
                'title' => 'Colocar como pendente',
                'classes' => ' text-indigo-500 '
            ],
            '10' => [
                'processo' => 'CONCLUIR',
                'title' => 'Concluir',
                'classes' => ' text-indigo-500 '
            ],
        ];
    }

    private function determinarStatusConfig($item, $statusConfig)
    {
        // se a devolução tiver um sinistro aberto quer dizer que
        // o veiculo teve danos e assim ele não vai para o patriminio
        if ($item->status == 2 && $item->tipo == 'FILIAL') {
            $currentStatus = 3;
        } else {
            $currentStatus = $item->status ?? '';
        }

        // Configuração padrão
        $defaultConfig = [
            'processo' => '',
            'title' => '',
            'classes' => 'bg-gray-500 text-white hover:bg-gray-600 focus:ring-gray-500'
        ];

        return $statusConfig[$currentStatus] ?? $defaultConfig;
    }
}
