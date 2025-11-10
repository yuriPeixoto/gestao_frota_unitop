<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckList;
use App\Models\DepartamentoTransferencia;
use App\Models\Filial;
use App\Models\Departamento;
use App\Models\TelefoneTransferencia;
use App\Models\DevolucaoImobilizadoVeiculo;
use App\Models\Fornecedor;
use App\Models\HistoricoEventosSinistro;
use App\Models\LogsDevolucaoImobilizadoVeiculo;
use App\Models\Veiculo;
use App\Models\OrdemServico;
use App\Models\OrdemServicoPecas;
use App\Models\OrdemServicoServicos;
use App\Models\Sinistro;
use App\Models\TipoEquipamento;
use App\Models\TransferenciaVeiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\IntegracaoWhatssappCarvalimaService;
use App\Services\ChecklistService;
use App\Services\DevolucaoImobilizadoVeiculoService;

class DevolucaoImobilizadoVeiculoController extends Controller
{
    private ChecklistService $checklistService;
    private DevolucaoImobilizadoVeiculoService $devolucaoService;

    public function __construct(ChecklistService $checklistService, DevolucaoImobilizadoVeiculoService $devolucaoService)
    {
        $this->checklistService = $checklistService;
        $this->devolucaoService = $devolucaoService;
    }

    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = DevolucaoImobilizadoVeiculo::query()
            ->with('veiculo', 'sinistro');


        if ($request->filled('id_devolucao_imobilizado_veiculo')) {
            $query->where('id_devolucao_imobilizado_veiculo', $request->id_devolucao_imobilizado_veiculo);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
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

        $devolucaoImobilizadoVeiculo = $query->latest('id_devolucao_imobilizado_veiculo')
            ->paginate(8)
            ->appends($request->query());


        $this->processarStatusConfig($devolucaoImobilizadoVeiculo);

        $veiculos = $this->getIdVeiculo();

        $situacao = $this->getSituacao();

        $transferencias  = $this->getTransferencias();

        return view(
            'admin.devolucaoimobilizadoveiculo.index',
            compact(
                'devolucaoImobilizadoVeiculo',
                'veiculos',
                'situacao',
                'transferencias'
            )
        );
    }

    public function create()
    {
        $veiculos = $this->getVeiculo();

        $tipoEquipamento = $this->getTipoEquipamento();

        $fornecedor = $this->getFornecedor();

        $filial = $this->getFilial();

        $departamento = $this->getDepartamento();

        return view(
            'admin.devolucaoimobilizadoveiculo.create',
            compact(
                'veiculos',
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
            'id_filial_origem'    => 'required',
            'id_filial_destino'   => 'nullable',
            'id_fornecedor' => 'nullable',
            'id_departamento' => 'nullable',
            'tipo' => 'required',
            'id_veiculo' => 'required',
            'id_tipo_equipamento' => 'required',
            'id_usuario' => 'required',
            'data_inicio' => 'nullable',
            'data_fim' => 'nullable',
            'observacao' => 'required'
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
            $status = $this->devolucaoService->situacaoImobilizado('TRÁFEGO');

            DB::beginTransaction();

            // Cria a devolução de Imobilizado
            $devolucaoImobilizadoVeiculoVeiculo = DevolucaoImobilizadoVeiculo::create([
                'status'              => $status,
                'id_usuario'          => $validatedData['id_usuario'],
                'id_veiculo'          => $validatedData['id_veiculo'],
                'id_tipo_equipamento' => $validatedData['id_tipo_equipamento'],
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

            $id = $devolucaoImobilizadoVeiculoVeiculo->id_devolucao_imobilizado_veiculo;

            // Cria o log de atividade
            LogsDevolucaoImobilizadoVeiculo::create([
                'data_solicitante' => now(),
                'usuario_solicitante' => $validatedData['id_usuario'],
                'status_solicitante' => true,
                'id_devolucao_imobilizado_veiculo' => $id
            ]);

            DB::commit();

            $departamentoTransferencia = DepartamentoTransferencia::find(2);
            $id_departamento = $departamentoTransferencia->id_departamento_transferencia;
            $departamento = $departamentoTransferencia->departamento;

            $data = format_date($devolucaoImobilizadoVeiculoVeiculo->data_inclusao);

            // Busca os telefones que têm o departamento especifico ou 'ADMIN'
            $telefones = TelefoneTransferencia::select('telefone as label', 'nome as value', 'departamento')
                ->whereIn('departamento', [$id_departamento, 7])
                ->orderBy('telefone', 'desc')
                ->get()
                ->toArray();

            if (!empty($telefones)) {
                $this->devolucaoService->mensagemTransferencia($devolucaoImobilizadoVeiculoVeiculo, $status, 'TRÁFEGO');
            }

            return redirect()
                ->route('admin.devolucaoimobilizadoveiculo.index')
                ->with('success', 'devolução imobilizado cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar devolução imobilizado: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Não foi possível cadastrar a devolução imobilizado. ' . $e->getMessage());
        }
    }

    public function edit(string $id_devolucao_imobilizado_veiculo)
    {
        $devolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::findOrFail($id_devolucao_imobilizado_veiculo);

        $veiculos = $this->getIdVeiculo();

        $fornecedor = $this->getFornecedor();

        $tipoEquipamento = $this->getTipoEquipamento();

        $filial = $this->getFilial();

        $departamento = $this->getDepartamento();

        return view(
            'admin.devolucaoimobilizadoveiculo.edit',
            compact(
                'devolucaoImobilizadoVeiculo',
                'veiculos',
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
            'id_fornecedor' => 'nullable',
            'id_departamento' => 'nullable',
            'tipo' => 'required',
            'id_veiculo' => 'required',
            'id_tipo_equipamento' => 'required',
            'id_usuario' => 'required',
            'anexo_documento' => 'nullable|file|mimes:pdf|max:2048',
            'anexo_checklist' => 'nullable|file|mimes:pdf|max:2048',
            'data_inicio' => 'nullable',
            'data_fim' => 'nullable',
            'observacao'          => 'required_without:observacao_original', // Só obrigatório se não tem observação original
            'observacao_original' => 'nullable',
            'observacao_adicional' => 'required_with:observacao_original', // Obrigatório quando tem observação original
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

            $fornecedor = $validatedData['id_fornecedor'] ?? null;
            $departamento = $validatedData['id_departamento'] ?? null;
            // se acaso mudar o tipo de comodato para filial, o fornecedor deve ser nulo
            if ($validatedData['tipo'] === 'FILIAL') {
                $fornecedor = null;
                $departamento = null;
            }

            // Cria a devolução de Imobilizado
            $devolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::findOrFail($id);

            // Upload do arquivo
            if (empty($devolucaoImobilizadoVeiculo->anexo_documento)) {
                $anexo_documento = null;
            } else {
                $anexo_documento = $devolucaoImobilizadoVeiculo->anexo_documento;
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

            $devolucaoImobilizadoVeiculo->update([
                'id_usuario'          => $validatedData['id_usuario'],
                'id_veiculo'          => $validatedData['id_veiculo'],
                'id_tipo_equipamento' => $validatedData['id_tipo_equipamento'],
                'tipo'                => $tipo,
                'id_filial_origem'    => $filialOrigem,
                'id_filial_destino'   => $filialDestino,
                'id_fornecedor'       => $fornecedor,
                'id_departamento'     => $departamento,
                'observacao'          => $data['observacao'], // Use $data em vez de $validatedData
                'anexo_documento'     => $anexo_documento,
                'data_inicio'         => $validatedData['data_inicio'] ?? null,
                'data_fim'            => $validatedData['data_fim'] ?? null,
                'data_alteracao'      => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.devolucaoimobilizadoveiculo.index')
                ->with('success', 'devolução imobilizado alterado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao alterar devolução imobilizado: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Não foi possível alterar a devolução imobilizado. ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $logsDevolucaoImobilizadoVeiculo = LogsDevolucaoImobilizadoVeiculo::where('id_devolucao_imobilizado_veiculo', $id);
            $logsDevolucaoImobilizadoVeiculo->delete();

            $devolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::findOrFail($id);
            $devolucaoImobilizadoVeiculo->delete();

            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'devolução Imobilizado excluída',
                    'type'    => 'success',
                    'message' => 'devolução Imobilizado excluída com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao excluir devolução imobilizado: ' . $e->getMessage(), [
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


        $devolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::findOrFail($id_request);
        if ($editar) {
            // Processa a observação adicional se fornecida
            if ($observacao_original && $observacao_adicional) {
                $observacaoFinal = $observacao_original . "\n" .
                    "--- Adicionado em " . now()->format('d/m/Y H:i') . " ---\n" .
                    $observacao_adicional;

                $devolucaoImobilizadoVeiculo->update([
                    'observacao' => $observacaoFinal
                ]);
            } elseif ($observacao && !$observacao_original) {
                // Se é uma observação nova (sem observação original)
                $devolucaoImobilizadoVeiculo->update([
                    'observacao' => $observacao
                ]);
            }

            $devolucaoImobilizadoVeiculo->update([
                'id_veiculo' => $veiculo
            ]);
        }

        $id = $devolucaoImobilizadoVeiculo->id_devolucao_imobilizado_veiculo;
        $status = $devolucaoImobilizadoVeiculo->status;
        $tipo = $devolucaoImobilizadoVeiculo->tipo;

        Log::debug('Verificando situação da Devolução de Imobilizado Veículo: ' . $id_request . ' - Status: ' . $status . ' - Tipo: ' . $tipo);

        if (empty($devolucaoImobilizadoVeiculo->id_veiculo)) {
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Veiculo não vinculado, impossivel alterar'
                ]
            ]);
        }

        if ($status === 2 && $tipo == 'COMODATO') {
            try {
                $this->onJuridico($id);

                if ($editar == true) {
                    return response()->json([
                        'notification' => [
                            'title' => 'Transferência Imobilizado veiculo alterada',
                            'type' => 'success',
                            'message' => 'Transferência Imobilizado veiculo em: Juridico '
                        ],
                        'redirect' => route('admin.devolucaoimobilizadoveiculo.index')
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
                Log::error('Erro ao colocar em trânsito Devolução imobilizado: ' . $e->getMessage(), [
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

        if ($status === 2 && $tipo == 'FILIAL') {
            try {
                $this->onFrota($id);

                if ($editar == true) {
                    return response()->json([
                        'notification' => [
                            'title'   => 'Transferência Imobilizado veiculo alterada',
                            'type'    => 'success',
                            'message' => 'Transferência Imobilizado veiculo em: Frota'
                        ],
                        'redirect' => route('admin.devolucaoimobilizadoveiculo.index')
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
                Log::error('Erro ao colocar em trânsito Devolução imobilizado: ' . $e->getMessage(), [
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

        if ($status === 3 && $tipo == 'COMODATO') {
            if (empty($devolucaoImobilizadoVeiculo->data_inicio) || empty($devolucaoImobilizadoVeiculo->data_fim)) {
                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => 'Data de inicio e fim obrigatorio para COMODATO'
                    ]
                ]);
            }

            if (empty($devolucaoImobilizadoVeiculo->anexo_documento)) {
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
                        'title'   => 'Devolução Imobilizado veiculo alterada',
                        'type'    => 'success',
                        'message' => 'Devolução Imobilizado veiculo em: Frota'
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao colocar em trânsito Devolução imobilizado: ' . $e->getMessage(), [
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
            if (!$devolucaoImobilizadoVeiculo->checklist || $devolucaoImobilizadoVeiculo->checklist->status != 'completed') {
                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => 'Checklist não concluído, impossível transferir para Patrimônio'
                    ]
                ]);
            }

            $idOrdemServico = $devolucaoImobilizadoVeiculo->id_ordem_servico;
            $ordemServico = OrdemServico::find(id: $idOrdemServico);
            $statusOdemServico = $ordemServico->id_status_ordem_servico;
            Log::debug('Verificando situação da ordem de serviço: ' . $statusOdemServico);

            if (!in_array($statusOdemServico, [4, 6, 13])) {
                return response()->json([
                    'notification' => [
                        'title'   => 'Ordem de servico',
                        'type'    => 'error',
                        'message' => 'Ordem de Serviço não finalizada'
                    ]
                ]);
            }

            $ordemServicoPecas = OrdemServicoPecas::where('id_ordem_servico', $idOrdemServico)->count();
            $ordemServicoServicos = OrdemServicoServicos::where('id_ordem_servico', $idOrdemServico)->count();
            Log::debug('ordemServicoPecas ' . $ordemServicoPecas . ' ordemServicoServicos ' . $ordemServicoServicos);

            // se não tiver item na ordem de serviço é porque não houve danos e vai para o patrimio para verificar
            if ($ordemServicoServicos == 0 && $ordemServicoPecas == 0) {
                try {
                    $this->onPatrimonio($id);

                    return response()->json([
                        'notification' => [
                            'title'   => 'Devolução Imobilizado veiculo alterada',
                            'type'    => 'success',
                            'message' => 'Devolução Imobilizado veiculo em: Patrimônio'
                        ]
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erro ao colocar em trânsito Devolução imobilizado: ' . $e->getMessage(), [
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

            // se tiver item na ordem de serviço é porque houve dano
            if ($ordemServicoServicos > 0 || $ordemServicoPecas > 0) {
                $idSinistro = $devolucaoImobilizadoVeiculo->id_sinistro;
                $sinistro = Sinistro::find($idSinistro);

                if ($sinistro->status != 'Finalizada') {
                    return response()->json([
                        'notification' => [
                            'title'   => 'Sinistro',
                            'type'    => 'error',
                            'message' => 'Sinistro não finalizado'
                        ]
                    ]);
                }
                Log::debug('Ordem de serviço com pecas e serviços');
                try {
                    $this->onFilial($id);

                    return response()->json([
                        'notification' => [
                            'title'   => 'Devolução Imobilizado veiculo alterada',
                            'type'    => 'success',
                            'message' => 'Devolução Imobilizado veiculo em: Concluido'
                        ]
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erro ao colocar em trânsito Devolução imobilizado: ' . $e->getMessage(), [
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

        if ($status === 5) {
            if (!$devolucaoImobilizadoVeiculo->checklistDevo || $devolucaoImobilizadoVeiculo->checklistDevo->status != 'completed') {
                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => 'Checklist não concluído, impossível transferir para Patrimônio'
                    ]
                ]);
            }

            try {
                $this->onConcluirPatrimonio($id);

                return response()->json([
                    'notification' => [
                        'title'   => 'Devolução Imobilizado veiculo alterada',
                        'type'    => 'success',
                        'message' => 'Devolução Imobilizado veiculo em: Concluído'
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao colocar em trânsito Devolução imobilizado: ' . $e->getMessage(), [
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
            if ($devolucaoImobilizadoVeiculo->checklistDevo->status != 'completed') {
                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => 'Checklist não concluído, impossivel concluir a transferência'
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

        // Cria a Devolução de Imobilizado
        $devolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::findOrFail($id);
        $status = $this->devolucaoService->situacaoImobilizado('JURIDICO'); // JURIDICO

        $devolucaoImobilizadoVeiculo->update([
            'status'            => $status,
            'data_alteracao'    => now(),
        ]);

        $user = Auth::user()->id;

        // Cria o log de atividade
        // é gravado a log de quem tirou do trafego
        $logTransferencia = LogsDevolucaoImobilizadoVeiculo::where('id_devolucao_imobilizado_veiculo', $id);
        $logTransferencia->update([
            'data_trafego' => now(),
            'usuario_trafego' => $user,
            'situacao_trafego' => true,
        ]);

        DB::commit();

        $this->devolucaoService->mensagemTransferencia($devolucaoImobilizadoVeiculo, $status, 'JURIDICO');
    }

    public function onFrota($id)
    {

        DB::beginTransaction();

        // Cria a Devolução de Imobilizado
        $devolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::findOrFail($id);
        $status = $this->devolucaoService->situacaoImobilizado('FROTA'); // FROTA


        if ($devolucaoImobilizadoVeiculo->status === '3') {
            return response()->json([
                'notification' => [
                    'title'   => 'Devolução de Veiculo Imobilizado já está em Juridico',
                    'type'    => 'error',
                    'message' => 'Devolução de Veiculo Imobilizado em Juridico'
                ]
            ]);
        }

        $devolucaoImobilizadoVeiculo->update([
            'status'            => $status,
            'data_alteracao'    => now(),
        ]);

        $user = Auth::user()->id;

        if ($devolucaoImobilizadoVeiculo->tipo == 'COMODATO') {
            $logTransferencia = LogsDevolucaoImobilizadoVeiculo::where('id_devolucao_imobilizado_veiculo', $id);
            $logTransferencia->update([
                'data_juridico' => now(),
                'usuario_juridico' => $user,
                'situacao_juridico' => true,
            ]);
        }

        // Cria o log de atividade
        // é gravado a log de quem tirou do juridico
        $logTransferencia = LogsDevolucaoImobilizadoVeiculo::where('id_devolucao_imobilizado_veiculo', $id);
        $logTransferencia->update([
            'data_trafego' => now(),
            'usuario_trafego' => $user,
            'situacao_trafego' => true,
        ]);


        DB::commit();

        $this->devolucaoService->mensagemTransferencia($devolucaoImobilizadoVeiculo, $status, 'FROTA');

        // Criar checklist
        $this->devolucaoService->createChecklist($id);
    }

    public function onPatrimonio($id)
    {

        DB::beginTransaction();

        // Cria a Devolução de Imobilizado
        $devolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::findOrFail($id);
        $status = $this->devolucaoService->situacaoImobilizado('PATRIMONIO'); // PATRIMONIO

        $devolucaoImobilizadoVeiculo->update([
            'status'            => $status,
            'data_alteracao'    => now(),
        ]);

        $user = Auth::user()->id;

        // Cria o log de atividade
        // é gravado a log de quem tirou da frota
        $logTransferencia = LogsDevolucaoImobilizadoVeiculo::where('id_devolucao_imobilizado_veiculo', $id);
        $logTransferencia->update([
            'data_frota' => now(),
            'usuario_frota' => $user,
            'situacao_frota' => true,
        ]);


        DB::commit();

        $this->devolucaoService->mensagemTransferencia($devolucaoImobilizadoVeiculo, $status, 'PATRIMONIO');
    }

    public function onConcluirPatrimonio($id)
    {

        DB::beginTransaction();

        // Cria a Devolução de Imobilizado
        $devolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::findOrFail($id);
        $status = $this->devolucaoService->situacaoImobilizado('CONCLUIDO'); // CONCLUIDO

        $devolucaoImobilizadoVeiculo->update([
            'status'            => $status,
            'data_alteracao'    => now(),
        ]);

        $user = Auth::user()->id;

        // Cria o log de atividade
        // é gravado a log de quem tirou do patrimonio
        $logTransferencia = LogsDevolucaoImobilizadoVeiculo::where('id_devolucao_imobilizado_veiculo', $id);
        $logTransferencia->update([
            'data_patrimonio' => now(),
            'usuario_patrimonio' => $user,
            'situacao_patrimonio' => true,
        ]);

        $veiculo = Veiculo::findOrFail($devolucaoImobilizadoVeiculo->id_veiculo);
        $filialOrigem = $devolucaoImobilizadoVeiculo->id_filial_origem;
        $filialDestino = $devolucaoImobilizadoVeiculo->id_filial_destino;


        if ($devolucaoImobilizadoVeiculo->tipo == 'COMODATO') {
            $veiculo->update([
                'is_terceiro' => true,
                'id_fornecedor' => $devolucaoImobilizadoVeiculo->id_fornecedor,
                'id_departamento' => $devolucaoImobilizadoVeiculo->id_departamento,
            ]);
        } else {
            TransferenciaVeiculo::create([
                'data_inclusao'       => now(),
                'id_filial_origem'    => $filialOrigem,
                'id_filial_destino'   => $filialDestino,
                'data_transferencia'  => now(),
                'id_veiculo'          => $veiculo->id_veiculo,
            ]);

            $veiculo->update([
                'id_filial' => $filialDestino,
            ]);
        }

        DB::commit();

        $this->devolucaoService->mensagemTransferencia($devolucaoImobilizadoVeiculo, $status, 'CONCLUIDO');
    }

    public function onFilial($id)
    {
        DB::beginTransaction();

        // Cria a Transferência de Imobilizado
        $devolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::findOrFail($id);
        $status = $this->devolucaoService->situacaoImobilizado('PENDENTE'); // PENDENTE

        $devolucaoImobilizadoVeiculo->update([
            'status'            => $status,
            'data_alteracao'    => now(),
        ]);

        $id = $devolucaoImobilizadoVeiculo->id_devolucao_imobilizado_veiculo;
        $user = Auth::user()->id;

        // Cria o log de atividade
        // é gravado a log de quem tirou do patrimonio
        $logTransferencia = LogsDevolucaoImobilizadoVeiculo::where('id_devolucao_imobilizado_veiculo', $id);
        $logTransferencia->update([
            'data_patrimonio' => now(),
            'usuario_patrimonio' => $user,
            'situacao_patrimonio' => true,
        ]);



        DB::commit();

        $this->devolucaoService->mensagemTransferencia($devolucaoImobilizadoVeiculo, $status, 'PENDENTE');

        $this->createChecklist($id);
    }

    public function onConcluir($id)
    {

        DB::beginTransaction();

        // Cria a Devolução de Imobilizado
        $devolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::findOrFail($id);
        $veiculoId = $devolucaoImobilizadoVeiculo->id_veiculo;
        $filialOrigem = $devolucaoImobilizadoVeiculo->id_filial_origem;
        $filialDestino = $devolucaoImobilizadoVeiculo->id_filial_destino;
        $fornecedorId = $devolucaoImobilizadoVeiculo->id_fornecedor;
        $departamentoId = $devolucaoImobilizadoVeiculo->id_departamento;

        $status = $this->devolucaoService->situacaoImobilizado('CONCLUIDO'); // CONCLUIDO

        $devolucaoImobilizadoVeiculo->update([
            'status'            => $status,
            'data_alteracao'    => now(),
        ]);

        $user = Auth::user()->id;

        // Cria o log de atividade
        // é gravado a log de quem tirou do patrimonio
        $logTransferencia = LogsDevolucaoImobilizadoVeiculo::where('id_devolucao_imobilizado_veiculo', $id);
        $logTransferencia->update([
            'data_patrimonio' => now(),
            'usuario_patrimonio' => $user,
            'situacao_patrimonio' => true,
        ]);

        $veiculo = Veiculo::findOrFail($veiculoId);

        $checklist = CheckList::where('entity_id', $veiculoId);
        $checklistId = $checklist->id;

        if ($devolucaoImobilizadoVeiculo->tipo == 'COMODATO') {
            $veiculo->update([
                'is_terceiro' => true,
                'id_fornecedor' => $fornecedorId,
                'id_departamento' => $departamentoId,
            ]);
        } else {
            TransferenciaVeiculo::create([
                'data_inclusao'       => now(),
                'id_filial_origem'    => $filialOrigem,
                'id_filial_destino'   => $filialDestino,
                'data_transferencia'  => now(),
                'id_veiculo'          => $veiculoId,
                'checklist'           => $checklistId,
            ]);

            $veiculo->update([
                'id_filial' => $filialDestino,
            ]);
        }

        DB::commit();

        $this->devolucaoService->mensagemTransferencia($devolucaoImobilizadoVeiculo, $status, 'CONCLUIDO');
    }

    public function onEmitirOrdemServico(Request $request)
    {
        $id = $request->input('id');

        try {
            DB::beginTransaction();

            $devolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::findOrFail($id);
            $dataFim = $devolucaoImobilizadoVeiculo->data_fim;
            $filial = $devolucaoImobilizadoVeiculo->id_filial_origem;
            $id_veiculo = $devolucaoImobilizadoVeiculo->id_veiculo;
            $departamento = $devolucaoImobilizadoVeiculo->id_departamento;
            $observacao = $devolucaoImobilizadoVeiculo->observacao;


            // Inserir ordem de serviço corretiva
            $ordemServicoId = DB::connection('pgsql')->table('ordem_servico')->insertGetId([
                'data_inclusao'              => now(),
                'data_abertura'              => now(),
                'data_previsao_saida'        => $dataFim ?? null,
                'prioridade_os'              => 'Média' ?? null,
                'id_tipo_ordem_servico'      => 3 ?? null, // Devolução de Imobilizado / Ordem de Serviço Diagonóstico
                'id_status_ordem_servico'    => 1 ?? null,
                'local_manutencao'           => 'INTERNO' ?? null,
                'id_filial'                  => $filial ?? null,
                'id_filial_manutencao'       => $filial ?? null,
                'id_veiculo'                 => $id_veiculo ?? null,
                'id_departamento'            => $departamento ?? null,
                'observacao'                 => $observacao ?? null,
                'is_cancelada'               => false
            ], 'id_ordem_servico');

            Log::info('Ordem de Serviço criada com sucesso', [
                'ordem_servico_id' => $ordemServicoId,
                'devolucao_imobilizado_id' => $id
            ]);

            $devolucaoImobilizadoVeiculo->update([
                'id_ordem_servico'            => $ordemServicoId
            ]);

            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Ordem de Serviço',
                    'type'    => 'info',
                    'message' => 'Ordem de Serviço Gerada com sucesso!'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao gerar a ordem de serviço: ' . $e->getMessage(), [
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

    public function onEmitirSinistro(Request $request)
    {
        $id = $request->input('id');

        try {
            DB::beginTransaction();

            $devolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::with('user')->findOrFail($id);
            $dataInicio = $devolucaoImobilizadoVeiculo->data_inicio;
            $id_usuario = $devolucaoImobilizadoVeiculo->id_usuario;
            $usuario = $devolucaoImobilizadoVeiculo->user->name;
            $filial = $devolucaoImobilizadoVeiculo->id_filial_origem;
            $id_veiculo = $devolucaoImobilizadoVeiculo->id_veiculo;


            // Inserir ordem de serviço corretiva
            $sinistro = Sinistro::create([
                'data_inclusao'              => now(),
                'id_veiculo'                 => $id_veiculo,
                'id_filial'                  => $filial,
                'responsabilidade_sinistro'  => $usuario,
                'data_sinistro'              => $dataInicio,
                'status'                     => 'Em Andamento'
            ]);
            $sinistroId = $sinistro->id_sinistro;

            if (empty($dataInicio)) {
                return response()->json([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => 'É necessário definir a data de início para emitir sinistro.'
                    ]
                ], 422);
            }

            // Processar históricos
            HistoricoEventosSinistro::create([
                'data_inclusao'              => now(),
                'id_sinistro'                => $sinistroId,
                'data_evento'                => $dataInicio,
                'id_usuario'                 => $id_usuario,
                'descricao_situacao'         => 'Abertura processo sinistro',
            ]);



            Log::info('Ordem de Serviço criada com sucesso', [
                'Sinitro_id' => $sinistroId,
                'devolucao_imobilizado_id' => $id
            ]);

            $devolucaoImobilizadoVeiculo->update([
                'id_sinistro'            => $sinistroId
            ]);

            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Sinistro',
                    'type'    => 'info',
                    'message' => 'Sinistro Gerado com sucesso!'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao gerar a ordem de serviço: ' . $e->getMessage(), [
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

            // Cria a Devolução de Imobilizado
            $devolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::findOrFail($id);

            $status = $this->devolucaoService->situacaoImobilizado('REPROVADO');

            $departamentoTransferencia = DepartamentoTransferencia::where('departamento', 'REPROVADO')->first();
            $id_departamento = $departamentoTransferencia->id_departamento_transferencia;
            $departamento = $departamentoTransferencia->departamento;


            $devolucaoImobilizadoVeiculo->update([
                'status'            => $id_departamento,
                'justificativa'     => $justificativa,
                'data_alteracao'    => now(),
            ]);

            $id = $devolucaoImobilizadoVeiculo->id_devolucao_imobilizado_veiculo;
            $user = Auth::user()->id;

            // Cria o log de atividade
            // é gravado a log de quem tirou do reprova
            $logTransferencia = LogsDevolucaoImobilizadoVeiculo::where('id_devolucao_imobilizado_veiculo', $id);
            $logTransferencia->update([
                'data_reprova' => now(),
                'usuario_reprova' => $user,
                'situacao_reprova' => true,
            ]);

            DB::commit();

            $this->devolucaoService->mensagemTransferencia($devolucaoImobilizadoVeiculo, $status, $departamento);

            return response()->json([
                'notification' => [
                    'title'   => 'Devolução Imobilizado veiculo reprovada',
                    'type'    => 'info',
                    'message' => 'Devolução Imobilizado veiculo reprovada'
                ],
                'redirect' => route('admin.devolucaoimobilizadoveiculo.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao colocar em trânsito Devolução imobilizado: ' . $e->getMessage(), [
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

    private function getIdVeiculo()
    {
        return DevolucaoImobilizadoVeiculo::join('veiculo', 'devolucao_imobilizado_veiculo.id_veiculo', '=', 'veiculo.id_veiculo')
            ->select('veiculo.placa as label', 'devolucao_imobilizado_veiculo.id_veiculo as value')
            ->orderBy('veiculo.placa')
            ->distinct()
            ->get()
            ->toArray();
    }

    private function getVeiculo()
    {
        return Veiculo::select('placa as label', 'id_veiculo as value')
            ->where('placa', '!=', '')
            ->orderBy('placa', 'desc')
            ->limit(30)
            ->get()
            ->toArray();
    }

    public function getTipoEquipamento()
    {

        return TipoEquipamento::select('id_tipo_equipamento', 'descricao_tipo', 'numero_eixos')
            ->orderBy('descricao_tipo')
            ->get()
            ->map(function ($item) {
                if (!empty($item->numero_eixos)) {
                    $eixo = ' - eixos: ' . $item->numero_eixos;
                }

                return [
                    'value' => $item->id_tipo_equipamento,
                    'label' => $item->descricao_tipo . ($eixo ?? ''),
                ];
            });
    }


    private function getFornecedor()
    {
        return Fornecedor::select(
            'id_fornecedor as value',
            'nome_fornecedor as label'
        )
            ->orderBy('nome_fornecedor', 'asc')
            ->limit(30)
            ->get()
            ->toArray();
    }

    private function getDepartamento()
    {
        return  Departamento::select('descricao_departamento as label', 'id_departamento as value')
            ->orderBy('descricao_departamento')
            ->get()
            ->toArray();
    }

    public function getVehicleData(Request $request)
    {

        $idTipoVeiculo = $request->input('tipoEquipamento');
        $idFilial = $request->input('filial');

        try {
            // Buscar todos os veículos do tipo equipamento selecionado
            $query = Veiculo::select('id_veiculo as value', 'placa as label')
                ->where('id_tipo_equipamento', $idTipoVeiculo);

            if ($idFilial) {
                $query->where('id_filial', $idFilial);
            }

            $veiculos = $query->orderBy('placa')->get();

            Log::info('Veículos encontrados', [
                'total' => $veiculos->count(),
                'veiculos' => $veiculos->toArray()
            ]);

            return response()->json([
                'veiculos' => $veiculos,
                'total' => $veiculos->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do veículo: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar dados do veículo'], 500);
        }
    }
    private function getFilial()
    {
        return  Filial::select('name as label', 'id as value')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    private function getSituacao()
    {
        return  DepartamentoTransferencia::select('departamento as label', 'id_departamento_transferencia as value')
            ->where('departamento', '!=', 'ADMIN')
            ->orderBy('departamento')
            ->get()
            ->toArray();
    }

    private function getTransferencias()
    {
        return  DevolucaoImobilizadoVeiculo::with(['log', 'filialOrigem', 'filialDestino', 'veiculo'])
            ->get()
            ->mapWithKeys(function ($transferencia) {

                $log = $transferencia->log;

                // Função auxiliar para traduzir booleano para "APROVADO"
                $aprovadoSeTrue = fn($val) => $val === true || $val === 1 ? 'APROVADO' : $val;

                return [
                    $transferencia->id_devolucao_imobilizado_veiculo => [
                        'id_devolucao_imobilizado_veiculo' => $transferencia->id_devolucao_imobilizado_veiculo,
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
                'classes' => 'bg-green-500 text-white hover:bg-green-600 focus:ring-green-500'
            ],
            '3' => [
                'processo' => 'JURIDICO',
                'title' => 'Enviar para Frota',
                'classes' => 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500'
            ],
            '4' => [
                'processo' => 'FROTA',
                'title' => 'Enviar para Patrimônio',
                'classes' => 'bg-orange-500 text-white hover:bg-orange-600 focus:ring-orange-500'
            ],
            '5' => [
                'processo' => 'CONCLUIDO',
                'title' => 'Concluir',
                'classes' => 'bg-indigo-500 text-white hover:bg-indigo-600 focus:ring-indigo-500'
            ],
            '10' => [
                'processo' => 'Filial',
                'title' => 'Concluir',
                'classes' => 'bg-indigo-500 text-white hover:bg-indigo-600 focus:ring-indigo-500'
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
