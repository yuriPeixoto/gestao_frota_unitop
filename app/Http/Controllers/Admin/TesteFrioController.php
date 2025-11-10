<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use App\Models\CertificadoVeiculos;
use App\Models\TipoCertificado;
use App\Models\Veiculo;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Traits\ExportableTrait;


class TesteFrioController extends Controller
{
    use ExportableTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = CertificadoVeiculos::query()
            ->with(['veiculo', 'tipocertificado', 'uf'])
            ->where('id_tipo_certificado', 1)
            ->distinct();

        if ($request->filled('searchCodigo')) {
            $query->where('id_certificado_veiculo', $request->searchCodigo);
        }

        if ($request->filled('searchCertificado')) {
            $query->where('numero_certificado', $request->searchCertificado);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }

        if ($request->filled('data_inicial')) {
            $query->whereDate('data_certificacao', '>=', $request->data_inicial);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_certificacao', '<=', $request->data_final);
        }

        if ($request->filled('vencimento_inicial')) {
            $query->whereDate('data_vencimento', '>=', $request->vencimento_inicial);
        }

        if ($request->filled('vencimento_final')) {
            $query->whereDate('data_vencimento', '<=', $request->vencimento_final);
        }

        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->ativos();
            } else {
                $query->inativos();
            }
        } else {
            $query->todos();
        }

        $testefrios = $query->orderBy('id_certificado_veiculo', 'desc')
            ->paginate(10)
            ->withQueryString();

        $testefriosData = $testefrios->map(function ($item) {
            return [
                'id' => $item->id_certificado_veiculo,
                'placa' => $item->veiculo->placa ?? 'SEM PLACA',
                'tipo_certificado' => $item->tipocertificado->descricao_certificado ?? 'NÃO INFORMADO',
                'data_vencimento' => $item->data_vencimento ? date('d/m/Y', strtotime($item->data_vencimento)) : 'N/A',
                'data_certificado' => $item->data_certificacao ? date('d/m/Y', strtotime($item->data_certificacao)) : 'N/A',
            ];
        })->toArray();


        $veiculosFrequentes = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->limit(20)
            ->get(['id_veiculo as value', 'placa as label']);

        $actionIcons = [
            "icon:pencil | tip:Editar | click:editTestefrios({id})",
            "icon:trash | tip:Excluir | color:red | click:destroyTestefrios({id}, '{placa}')",
        ];

        $column_aliases = [
            'id' => 'Código',
            'tipo_certificado' => 'Tipo Certificado',
            'placa' => 'Placa',
            'data_vencimento' => 'Data Vencimento',
            'data_certificado' => 'Data Certificação'
        ];

        $totalRegistros = $testefrios->total();
        $searchTerm = $request->input('search');

        return view('admin.testefrios.index', compact(
            'testefrios',
            'testefriosData',
            'actionIcons',
            'veiculosFrequentes',
            'column_aliases',
            'totalRegistros',
            'searchTerm'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $veiculosFrequentes = Cache::remember('veiculos_frequentes', now()->addHours(12), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->orderBy('chassi')
                ->get([
                    'id_veiculo as value',  // ID do veículo
                    'placa as label',       // Placa do veículo
                    'chassi as chassi',     // Chassi do veículo
                    'renavam'
                ])
                ->map(fn($veiculo) => [
                    'value' => $veiculo->value,
                    'label' => $veiculo->label,
                    'chassi' => $veiculo->chassi,
                    'renavam' => $veiculo->renavam,
                ]);
        });

        // Cria lista de tipos de certificado (fixando o tipo 1 para Teste de Frio)
        $tiposCertificados = TipoCertificado::where('id_tipo_certificado', 1)
            ->orderBy('descricao_certificado')
            ->get(['id_tipo_certificado as value', 'descricao_certificado as label']);

        // Carrega veículos ativos
        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->get(['id_veiculo as value', 'placa as label']);

        // Carrega estados
        $estados = Estado::orderBy('uf')
            ->get(['id_uf as value', 'uf as label']);

        return view('admin.testefrios.create', compact(
            'veiculosFrequentes',
            'tiposCertificados',
            'veiculos',
            'estados'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            if ($request->data_vencimento <= $request->data_certificacao) {
                return back()->with('notification', [
                    'type' => 'error', // Mudei para 'error' já que é um caso de erro
                    'title' => 'Erro de validação',
                    'message' => 'A data de vencimento não pode ser igual ou anterior à data de certificação',
                    'duration' => 5000,
                ])->withInput();
            }

            // Log para diagnóstico - dados recebidos
            Log::info('Dados recebidos para cadastro de Teste de Frio:', $request->all());

            // Validação dos dados com tratamento para casos específicos
            $validated = $request->validate([
                'id_tipo_certificado' => 'required|exists:tipocertificado,id_tipo_certificado',
                'id_veiculo' => 'required|exists:veiculo,id_veiculo',
                'id_uf' => 'nullable|exists:estado,id_uf',
                'data_vencimento' => 'required|date',
                'data_certificacao' => 'required|date',
                'numero_certificado' => 'required|string',
                'chassi' => 'nullable|string',
                'renavam' => 'nullable', // Removido type:string para permitir qualquer formato
                'arquivo' => 'nullable|file|max:10240',
            ]);

            // Log para diagnóstico - após validação
            Log::info('Dados validados para cadastro de Teste de Frio:', $validated);

            DB::beginTransaction();

            $valoresPermitidos = ['A vencer', 'Cancelado', 'Vencido'];
            $situacao = $request->situacao;
            if (!in_array($situacao, $valoresPermitidos)) {
                $situacao = 'A vencer'; // padrão
            }

            // Criar novo certificado
            $certificado = new CertificadoVeiculos();
            $certificado->id_tipo_certificado = $validated['id_tipo_certificado'];
            $certificado->id_veiculo = $validated['id_veiculo'];
            $certificado->data_vencimento = $validated['data_vencimento'];
            $certificado->data_certificacao = $validated['data_certificacao'];
            $certificado->numero_certificado = $validated['numero_certificado'];
            $certificado->valor_certificado  = $request->valor_certificado;
            $certificado->chassi = $validated['chassi'];
            $certificado->renavam = $validated['renavam'];
            $certificado->data_inclusao = now();
            $certificado->$situacao;

            // Processar UF
            if (!empty($validated['id_uf'])) {
                $estado = Estado::find($validated['id_uf']);
                $certificado->id_uf = $validated['id_uf'];
                $certificado->uf = $estado->uf;
            }

            // Processar arquivo
            if ($request->hasFile('arquivo') && $request->file('arquivo')->isValid()) {
                try {
                    $arquivo = $request->file('arquivo');
                    // Log do arquivo sendo processado
                    Log::info('Processando arquivo para Teste de Frio:', [
                        'original_name' => $arquivo->getClientOriginalName(),
                        'mime_type' => $arquivo->getMimeType(),
                        'size' => $arquivo->getSize(),
                    ]);

                    $path = $arquivo->store('laudos', 'public');
                    $certificado->caminho_arquivo = $path;

                    Log::info('Arquivo salvo com sucesso:', ['path' => $path]);
                } catch (\Exception $e) {
                    Log::error('Erro ao processar arquivo:', [
                        'erro' => $e->getMessage(),
                        'arquivo' => $e->getFile(),
                        'linha' => $e->getLine()
                    ]);
                    throw $e; // Relançar para tratamento pelo catch principal
                }
            }

            // Log do objeto antes de salvar
            Log::info('Objeto Teste de Frio antes de salvar:', [
                'id_veiculo' => $certificado->id_veiculo,
                'chassi' => $certificado->chassi,
                'renavam' => $certificado->renavam,
                'data_vencimento' => $certificado->data_vencimento,
                'arquivo' => $certificado->caminho_arquivo,
            ]);

            $certificado->save();

            // Log após salvar com sucesso
            Log::info('Teste de Frio cadastrado com sucesso: ID ' . $certificado->id_certificado_veiculo);

            DB::commit();

            return redirect()->route('admin.testefrios.index')
                ->with('success', 'Teste de Frio cadastrado com sucesso!')
                ->withNotification([
                    'title'   => 'Incluído com sucesso',
                    'type'    => 'success',
                    'message' => 'Teste de Frio cadastrado com sucesso'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log detalhado do erro
            Log::error('Erro ao gravar Teste de Frio: ' . $e->getMessage(), [
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return back()->withInput()->with('error', 'Erro ao cadastrar: ' . $e->getMessage())
                ->withNotification([
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $veiculosFrequentes = Cache::remember('veiculos_frequentes', now()->addHours(12), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->orderBy('chassi')
                ->get([
                    'id_veiculo as value',  // ID do veículo
                    'placa as label',       // Placa do veículo
                    'chassi as chassi',     // Chassi do veículo
                    'renavam'
                ])
                ->map(fn($veiculo) => [
                    'value' => $veiculo->value,
                    'label' => $veiculo->label,
                    'chassi' => $veiculo->chassi,
                    'renavam' => $veiculo->renavam,
                ]);
        });

        $testefrio = CertificadoVeiculos::findOrFail($id);

        // Obtém dados atuais do veículo
        $veiculo = $testefrio->veiculo;

        // Cria lista de tipos de certificado (fixando o tipo 1 para Teste de Frio)
        $tiposCertificados = TipoCertificado::where('id_tipo_certificado', 1)
            ->orderBy('descricao_certificado')
            ->get(['id_tipo_certificado as value', 'descricao_certificado as label']);

        // Carrega veículos ativos
        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->get(['id_veiculo as value', 'placa as label']);

        // Carrega estados
        $estados = Estado::orderBy('uf')
            ->get(['id_uf as value', 'uf as label']);

        return view('admin.testefrios.edit', compact(
            'veiculosFrequentes',
            'testefrio',
            'veiculo',
            'tiposCertificados',
            'veiculos',
            'estados'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            if ($request->data_vencimento <= $request->data_certificacao) {
                return back()->with('notification', [
                    'type' => 'error', // Mudei para 'error' já que é um caso de erro
                    'title' => 'Erro de validação',
                    'message' => 'A data de vencimento não pode ser igual ou anterior à data de certificação',
                    'duration' => 5000,
                ])->withInput();
            }

            // Log para diagnóstico
            Log::info('Dados recebidos para atualização de Teste de Frio ID ' . $id . ':', $request->all());

            // Validação dos dados com tratamento para casos específicos
            $validated = $request->validate([
                'id_tipo_certificado' => 'required|exists:tipocertificado,id_tipo_certificado',
                'id_veiculo' => 'required|exists:veiculo,id_veiculo',
                'id_uf' => 'nullable|exists:estado,id_uf',
                'data_vencimento' => 'required|date',
                'data_certificacao' => 'required|date',
                'numero_certificado' => 'required|string',
                'chassi' => 'nullable|string',
                'renavam' => 'nullable', // Removido type:string para permitir qualquer formato
                'arquivo' => 'nullable|file|max:10240',
            ]);

            // Log após validação
            Log::info('Dados validados para atualização de Teste de Frio:', $validated);

            DB::beginTransaction();

            // Buscar certificado existente
            $certificado = CertificadoVeiculos::findOrFail($id);

            // Log do registro antes da atualização
            Log::info('Teste de Frio antes da atualização:', [
                'id' => $certificado->id_certificado_veiculo,
                'veiculo' => $certificado->id_veiculo,
                'certificado' => $certificado->numero_certificado,
                'arquivo_atual' => $certificado->caminho_arquivo
            ]);

            $certificado->id_tipo_certificado = $validated['id_tipo_certificado'];
            $certificado->id_veiculo          = $validated['id_veiculo'];
            $certificado->data_vencimento     = $validated['data_vencimento'];
            $certificado->data_certificacao   = $validated['data_certificacao'];
            $certificado->numero_certificado  = $validated['numero_certificado'];
            $certificado->valor_certificado   = $request->valor_certificado;
            $certificado->chassi              = $validated['chassi'] ?? $certificado->chassi;
            $certificado->renavam             = $validated['renavam'] ?? $certificado->renavam;
            $certificado->data_alteracao      = now();

            // Processar UF
            if (!empty($validated['id_uf'])) {
                $estado = Estado::find($validated['id_uf']);
                $certificado->id_uf = $validated['id_uf'];
                $certificado->uf = $estado->uf;
            } else {
                $certificado->id_uf = null;
                $certificado->uf = null;
            }

            // Processar arquivo
            if ($request->hasFile('arquivo') && $request->file('arquivo')->isValid()) {
                try {
                    // Log do processamento do arquivo
                    $arquivo = $request->file('arquivo');
                    Log::info('Processando novo arquivo para Teste de Frio:', [
                        'original_name' => $arquivo->getClientOriginalName(),
                        'mime_type' => $arquivo->getMimeType(),
                        'size' => $arquivo->getSize(),
                    ]);

                    // Remover arquivo anterior se existir
                    if ($certificado->caminho_arquivo && Storage::disk('public')->exists($certificado->caminho_arquivo)) {
                        Storage::disk('public')->delete($certificado->caminho_arquivo);
                        Log::info('Arquivo anterior removido:', ['path' => $certificado->caminho_arquivo]);
                    }

                    $path = $arquivo->store('laudos', 'public');
                    $certificado->caminho_arquivo = $path;

                    Log::info('Novo arquivo salvo com sucesso:', ['path' => $path]);
                } catch (\Exception $e) {
                    Log::error('Erro ao processar novo arquivo:', [
                        'erro' => $e->getMessage(),
                        'arquivo' => $e->getFile(),
                        'linha' => $e->getLine()
                    ]);
                    throw $e; // Relançar para tratamento pelo catch principal
                }
            }

            // Log antes de salvar
            Log::info('Teste de Frio pronto para salvar:', [
                'id' => $certificado->id_certificado_veiculo,
                'veiculo' => $certificado->id_veiculo,
                'certificado' => $certificado->numero_certificado,
                'arquivo' => $certificado->caminho_arquivo
            ]);

            $certificado->save();

            // Log após salvar com sucesso
            Log::info('Teste de Frio atualizado com sucesso: ID ' . $certificado->id_certificado_veiculo);

            DB::commit();

            return redirect()->route('admin.testefrios.index')
                ->with('success', 'Teste de Frio atualizado com sucesso!')
                ->withNotification([
                    'title'   => 'Alterado com sucesso',
                    'type'    => 'success',
                    'message' => 'Teste de Frio atualizado com sucesso'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log detalhado do erro
            Log::error('Erro ao atualizar Teste de Frio: ' . $e->getMessage(), [
                'id' => $id,
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return back()->withInput()->with('error', 'Erro ao atualizar: ' . $e->getMessage())
                ->withNotification([
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $certificado = CertificadoVeiculos::findOrFail($id);

            // Log antes de excluir
            Log::info('Excluindo Teste de Frio:', [
                'id' => $certificado->id_certificado_veiculo,
                'veiculo' => $certificado->id_veiculo,
                'certificado' => $certificado->numero_certificado,
                'arquivo' => $certificado->caminho_arquivo
            ]);

            // Remover arquivo se existir
            if ($certificado->caminho_arquivo && Storage::disk('public')->exists($certificado->caminho_arquivo)) {
                Storage::disk('public')->delete($certificado->caminho_arquivo);
                Log::info('Arquivo removido:', ['path' => $certificado->caminho_arquivo]);
            }

            $certificado->delete();

            // Log após exclusão
            Log::info('Teste de Frio excluído com sucesso: ID ' . $id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Teste de Frio excluído com sucesso!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log detalhado do erro
            Log::error('Erro ao excluir Teste de Frio: ' . $e->getMessage(), [
                'id' => $id,
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtém os dados do veículo (chassi e renavam)
     */
    public function getDadosVeiculo(Request $request)
    {
        try {
            $idVeiculo = $request->id_veiculo;

            // Log da requisição
            Log::info('Requisição de dados do veículo:', ['id_veiculo' => $idVeiculo]);

            $veiculo = Veiculo::where('id_veiculo', $idVeiculo)
                ->with('filialVeiculo')
                ->first();

            if ($veiculo) {
                // Log dos dados encontrados
                Log::info('Dados do veículo encontrados:', [
                    'id' => $veiculo->id_veiculo,
                    'placa' => $veiculo->placa,
                    'chassi' => $veiculo->chassi,
                    'renavam' => $veiculo->renavam
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'chassi' => $veiculo->chassi ?? '',
                        'renavam' => $veiculo->renavam ?? ''
                    ]
                ]);
            }

            // Log quando veículo não for encontrado
            Log::warning('Veículo não encontrado:', ['id_veiculo' => $idVeiculo]);

            return response()->json(['success' => false, 'message' => 'Veículo não encontrado'], 404);
        } catch (\Exception $e) {
            // Log detalhado do erro
            Log::error('Erro ao buscar dados do veículo: ' . $e->getMessage(), [
                'id_veiculo' => $request->id_veiculo,
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtém os dados do veículo pelo ID
     * Método adicional para compatibilidade com o padrão de rota
     */
    public function getDadosVeiculoPorId($id)
    {
        try {
            // Log da requisição
            Log::info('Requisição de dados do veículo por ID:', ['id_veiculo' => $id]);

            $veiculo = Veiculo::findOrFail($id);

            // Log dos dados encontrados
            Log::info('Dados do veículo encontrados:', [
                'id' => $veiculo->id_veiculo,
                'placa' => $veiculo->placa,
                'chassi' => $veiculo->chassi,
                'renavam' => $veiculo->renavam
            ]);

            return response()->json([
                'chassi' => $veiculo->chassi ?? '',
                'renavam' => $veiculo->renavam ?? ''
            ]);
        } catch (\Exception $e) {
            // Log detalhado do erro
            Log::error('Erro ao buscar dados do veículo por ID: ' . $e->getMessage(), [
                'id' => $id,
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Veículo não encontrado',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function replicar($id)
    {
        $veiculosFrequentes = Cache::remember('veiculos_frequentes', now()->addHours(12), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->orderBy('chassi')
                ->get([
                    'id_veiculo as value',  // ID do veículo
                    'placa as label',       // Placa do veículo
                    'chassi as chassi',     // Chassi do veículo
                    'renavam'
                ])
                ->map(fn($veiculo) => [
                    'value' => $veiculo->value,
                    'label' => $veiculo->label,
                    'chassi' => $veiculo->chassi,
                    'renavam' => $veiculo->renavam,
                ]);
        });

        $testefrio = CertificadoVeiculos::findOrFail($id);

        $dataOriginal = Carbon::parse($testefrio->data_vencimento);
        $testefrio->data_vencimento = $dataOriginal->addYear();

        // Obtém dados atuais do veículo
        $veiculo = $testefrio->veiculo;

        // Cria lista de tipos de certificado (fixando o tipo 1 para Teste de Frio)
        $tiposCertificados = TipoCertificado::where('id_tipo_certificado', 1)
            ->orderBy('descricao_certificado')
            ->get(['id_tipo_certificado as value', 'descricao_certificado as label']);

        // Carrega veículos ativos
        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->get(['id_veiculo as value', 'placa as label']);

        // Carrega estados
        $estados = Estado::orderBy('uf')
            ->get(['id_uf as value', 'uf as label']);

        return view('admin.testefrios.replicar', compact(
            'veiculosFrequentes',
            'testefrio',
            'veiculo',
            'tiposCertificados',
            'veiculos',
            'estados'
        ));
    }

    public function replicarUpdate(Request $request)
    {
        try {
            // Log para diagnóstico - dados recebidos
            Log::info('Dados recebidos para cadastro de Teste de Frio:', $request->all());

            // Validação dos dados com tratamento para casos específicos
            $validated = $request->validate([
                'id_tipo_certificado' => 'required|exists:tipocertificado,id_tipo_certificado',
                'id_veiculo' => 'required|exists:veiculo,id_veiculo',
                'id_uf' => 'nullable|exists:estado,id_uf',
                'data_vencimento' => 'required|date',
                'data_certificacao' => 'required|date',
                'numero_certificado' => 'required|string',
                'chassi' => 'nullable|string',
                'renavam' => 'nullable', // Removido type:string para permitir qualquer formato
                'arquivo' => 'nullable|file|max:10240',
            ]);

            // Log para diagnóstico - após validação
            Log::info('Dados validados para cadastro de Teste de Frio:', $validated);

            DB::beginTransaction();

            // Criar novo certificado
            $certificado = new CertificadoVeiculos();
            $certificado->id_tipo_certificado = $validated['id_tipo_certificado'];
            $certificado->id_veiculo = $validated['id_veiculo'];
            $certificado->data_vencimento = $validated['data_vencimento'];
            $certificado->data_certificacao = $validated['data_certificacao'];
            $certificado->numero_certificado = $validated['numero_certificado'];
            $certificado->valor_certificado = $request->valor_certificado;
            $certificado->chassi = $validated['chassi'];
            $certificado->renavam = $validated['renavam'];
            $certificado->data_inclusao = now();

            // Processar UF
            if (!empty($validated['id_uf'])) {
                $estado = Estado::find($validated['id_uf']);
                $certificado->id_uf = $validated['id_uf'];
                $certificado->uf = $estado->uf;
            }

            // Processar arquivo
            if ($request->hasFile('arquivo') && $request->file('arquivo')->isValid()) {
                try {
                    $arquivo = $request->file('arquivo');
                    // Log do arquivo sendo processado
                    Log::info('Processando arquivo para Teste de Frio:', [
                        'original_name' => $arquivo->getClientOriginalName(),
                        'mime_type' => $arquivo->getMimeType(),
                        'size' => $arquivo->getSize(),
                    ]);

                    $path = $arquivo->store('laudos', 'public');
                    $certificado->caminho_arquivo = $path;

                    Log::info('Arquivo salvo com sucesso:', ['path' => $path]);
                } catch (\Exception $e) {
                    Log::error('Erro ao processar arquivo:', [
                        'erro' => $e->getMessage(),
                        'arquivo' => $e->getFile(),
                        'linha' => $e->getLine()
                    ]);
                    throw $e; // Relançar para tratamento pelo catch principal
                }
            }

            // Log do objeto antes de salvar
            Log::info('Objeto Teste de Frio antes de salvar:', [
                'id_veiculo' => $certificado->id_veiculo,
                'chassi' => $certificado->chassi,
                'renavam' => $certificado->renavam,
                'data_vencimento' => $certificado->data_vencimento,
                'arquivo' => $certificado->caminho_arquivo,
            ]);

            $certificado->save();

            // Log após salvar com sucesso
            Log::info('Teste de Frio replicado com sucesso: ID ' . $certificado->id_certificado_veiculo);

            DB::commit();

            return redirect()->route('admin.testefrios.index')
                ->with('success', 'Teste de Frio cadastrado com sucesso!')
                ->withNotification([
                    'title'   => 'Replicado com sucesso',
                    'type'    => 'success',
                    'message' => 'Teste de Frio replicado com sucesso'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log detalhado do erro
            Log::error('Erro ao gravar Teste de Frio: ' . $e->getMessage(), [
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return back()->withInput()->with('error', 'Erro ao cadastrar: ' . $e->getMessage())
                ->withNotification([
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);

            // Se a exportação direta pelo trait não funcionar, tente um método alternativo
            if (!$this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true
                ]);
            }

            if ($request->has('confirmed') || !$this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                // Configurar opções do PDF
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.testefrios.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('testeFrio_' . date('Y-m-d_His') . '.pdf');
            } else {
                // Confirmação para grande volume
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => "Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.",
                    'export_confirmation' => true,
                    'export_url' => $currentUrl
                ]);
            }
        } catch (\Exception $e) {
            // Log detalhado do erro
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true
            ]);
        }
    }

    public function buildExportQuery($request)
    {
        $query = CertificadoVeiculos::query()
            ->with(['veiculo', 'veiculo.filial', 'tipocertificado', 'uf'])
            ->where('id_tipo_certificado', 1);

        if ($request->filled('searchCodigo')) {
            $query->where('id_certificado_veiculo', $request->searchCodigo);
        }

        if ($request->filled('searchCertificado')) {
            $query->where('numero_certificado', $request->searchCertificado);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }

        if ($request->filled('data_inicial')) {
            $query->whereDate('data_certificacao', '>=', $request->data_inicial);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_certificacao', '<=', $request->data_final);
        }

        if ($request->filled('vencimento_inicial')) {
            $query->whereDate('data_vencimento', '>=', $request->vencimento_inicial);
        }

        if ($request->filled('vencimento_final')) {
            $query->whereDate('data_vencimento', '<=', $request->vencimento_final);
        }

        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->ativos();
            } else {
                $query->inativos();
            }
        } else {
            $query->todos();
        }


        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'id_certificado_veiculo',
            'numero_certificado',
            'id_veiculo',
            'data_certificacao',
            'data_vencimento',
            'status',
            'situacao'
        ];
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_certificado_veiculo' => 'Código',
            'numero_certificado' => 'Numero do Certificado',
            'veiculo.placa' => 'Placa',
            'veiculo.filial.name' => 'Filial do Veiculo',
            'tipocertificado.descricao_certificado' => 'Tipo Certificado',
            'situacao' => 'Situação',
            'data_certificacao' => 'Data Certificação',
            'data_vencimento' => 'Data Vencimento',
            'Status' => 'Status'
        ];

        return $this->exportToExcel($request, $query, $columns, 'testefrios', $this->getValidExportFilters());
    }

    /**
     * Exportar para CSV
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_certificado_veiculo' => 'Código',
            'numero_certificado' => 'Numero do Certificado',
            'veiculo.placa' => 'Placa',
            'veiculo.filial.name' => 'Filial do Veiculo',
            'tipocertificado.descricao_certificado' => 'Tipo Certificado',
            'situacao' => 'Situação',
            'data_certificacao' => 'Data Certificação',
            'data_vencimento' => 'Data Vencimento',
            'Status' => 'Status'
        ];

        return $this->exportToCsv($request, $query, $columns, 'testefrios', $this->getValidExportFilters());
    }

    /**
     * Exportar para XML
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'id' => 'id_certificado_veiculo',
            'Numero do Certificado' => 'numero_certificado',
            'Placa' => 'veiculo.placa',
            'Filial do Veiculo' => 'veiculo.filial.name',
            'Tipo Certificado' => 'tipocertificado.descricao_certificado',
            'Situação' => 'situacao',
            'Data Certificação' => 'data_certificacao',
            'Data Vencimento' => 'data_vencimento'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'testefrios',
            'testefrio',
            'testefrios',
            $this->getValidExportFilters()
        );
    }
}
