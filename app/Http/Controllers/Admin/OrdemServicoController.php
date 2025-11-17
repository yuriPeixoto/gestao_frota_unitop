<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaPlanejamentoManutencao;
use App\Models\ContratoFornecedor;
use App\Models\Departamento;
use App\Models\DevolucaoProdutoOrdem;
use App\Models\Fornecedor;
use App\Models\GrupoResolvedor;
use App\Models\GrupoServico;
use App\Models\ItemSolicitacaoCompra;
use App\Models\Manutencao;
use App\Models\Municipio;
use App\Models\NfOrdemServico;
use App\Models\OrdemServico;
use App\Models\OrdemServicoMarcacao;
use App\Models\OrdemServicoPecas;
use App\Models\OrdemServicoServicos;
use App\Models\PedidoCompra;
use App\Modules\Pessoal\Models\Pessoal;
use App\Models\Produto;
use App\Models\Produtossolicitacoes;
use App\Models\RelacaoSolicitacaoPeca;
use App\Models\Servico;
use App\Models\StatusOrdemServico;
use App\Models\TipoOrdemServico;
use App\Models\SocorroOrdemServico;
use App\Models\PreOrdemServico;
use App\Models\ServicoFornecedor;
use App\Models\User;
use App\Modules\Veiculos\Models\Veiculo;
use App\Models\VFilial;
use App\Models\ProdutosPorFilial;
use App\Models\PlanejamentoManutencao;
use App\Models\SolicitacaoCompra;
use App\Traits\JasperServerIntegration;
use App\Models\PneusDeposito;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Traits\ExportableTrait;

use stdClass;
use DateTime;
use DateTimeZone;
use Exception;
use App\Traits\HasPneusParadosTrait;
use App\Traits\SmartSelectNormalizationTrait;

class OrdemServicoController extends Controller
{
    use ExportableTrait;
    use HasPneusParadosTrait;
    use SmartSelectNormalizationTrait;

    /**
     * Exibe a listagem das ordens de serviço com filtros
     */
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $this->normalizeSmartSelectParams($request);

        $query = OrdemServico::query()
            ->with([
                'pecas',
                'servicos',
                'servicos.fornecedor',
                'servicos.manutencao',
                'servicos.servicos',
                'pecas.fornecedor',
                'pecas.produto',
                'grupoResolvedor',
                'veiculo',
                'tipoOrdemServico',
                'statusOrdemServico',
                'usuario',
                'usuarioEncerramento'
            ])
            ->whereHas('veiculo');

        // Aplica filtros baseados na requisição
        $this->applyFilters($query, $request);

        // Usa cache para melhorar a performance
        $ordemServicos = $query->latest('id_ordem_servico')
            ->whereNotIn('id_veiculo', [11231]) // Exclui veículo específico
            ->paginate(40)
            ->appends($request->query());

        // Para requisições HTMX, retorna apenas a tabela
        if ($request->header('HX-Request')) {
            return view('admin.ordemservicos._table', compact('ordemServicos'));
        }

        // Carrega dados para os selects de filtro
        $veiculosFrequentes = $this->getVeiculosFrequentes();
        $filiais = $this->getFiliais();
        $tipoOrdemServico = $this->getTiposOrdemServico();
        $situacaoOrdemServico = $this->getSituacoesOrdemServico();
        $usuariosFrequentes = $this->getUsuariosFrequentes();
        $ordemServicoServicos = $this->getServicosRecentes();
        $tipoCorretiva = $this->getTiposCorretiva();
        $grupoResolvedor = $this->getGrupoResolvedor();

        return view('admin.ordemservicos.index', compact(
            'ordemServicos',
            'veiculosFrequentes',
            'filiais',
            'tipoOrdemServico',
            'situacaoOrdemServico',
            'usuariosFrequentes',
            'ordemServicoServicos',
            'tipoCorretiva',
            'grupoResolvedor'
        ));
    }

    public function indexDiagnostico(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $query = OrdemServico::query()
            ->with(['veiculo', 'tipoOrdemServico', 'statusOrdemServico', 'usuario', 'usuarioEncerramento'])
            ->whereHas('veiculo')
            ->where('id_tipo_ordem_servico', 3); // Adicione esta linha

        // Aplica filtros baseados na requisição
        $this->applyFilters($query, $request);

        // Usa cache para melhorar a performance
        $ordemServicos = $query->latest('id_ordem_servico')
            ->with([
                'pecas',
                'servicos',
                'servicos.fornecedor',
                'servicos.manutencao',
                'servicos.servicos',
                'pecas.fornecedor',
                'pecas.produto'
            ])
            ->where('is_cancelada', false)
            ->whereNotIn('id_veiculo', [11231]) // Exclui veículo específico
            ->paginate(40)
            ->appends($request->query());

        // Para requisições HTMX, retorna apenas a tabela
        if ($request->header('HX-Request')) {
            return view('admin.ordemservicos._table', compact('ordemServicos'));
        }

        // Carrega dados para os selects de filtro
        $veiculosFrequentes = $this->getVeiculosFrequentes();
        $filiais = $this->getFiliais();
        $tipoOrdemServico = $this->getTiposOrdemServico();
        $situacaoOrdemServico = $this->getSituacoesOrdemServico();
        $usuariosFrequentes = $this->getUsuariosFrequentes();
        $ordemServicoServicos = $this->getServicosRecentes();

        return view('admin.ordemservicos.index_diagnostico', compact(
            'ordemServicos',
            'veiculosFrequentes',
            'filiais',
            'tipoOrdemServico',
            'situacaoOrdemServico',
            'usuariosFrequentes',
            'ordemServicoServicos',
        ));
    }

    /**
     * Exibe o formulário para criar uma nova ordem de serviço corretiva
     */
    public function create(): View|\Illuminate\Http\RedirectResponse
    {
        // Bloqueio: não permite criar OS se existirem pneus parados no depósito por mais de 24 horas
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->route('admin.ordemservicos.index')
                ->with('notification', [
                    'type' => 'error',
                    'title' => 'Operação bloqueada',
                    'message' => 'Existem pneus parados no depósito há mais de 24 horas. Criação de Ordem de Serviço bloqueada.',
                    'duration' => 5000,
                ]);
        }
        $veiculosFrequentes = $this->getVeiculosFrequentesDetalhados();
        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();
        $produtosFrequentes = $this->getProdutosFrequentes();
        $motoristasFrequentes = $this->getMotoristasFrequentes();
        $servicosFrequentes = $this->getServicosFrequentes();
        $formOptions = $this->getFormOptions();
        $steps = [];


        return view('admin.ordemservicos.create', compact(
            'formOptions',
            'motoristasFrequentes',
            'produtosFrequentes',
            'fornecedoresFrequentes',
            'veiculosFrequentes',
            'servicosFrequentes',
            'steps',
        ));
    }

    /**
     * Exibe o formulário para criar uma nova ordem de serviço preventiva
     */
    public function create_preventiva(): View|\Illuminate\Http\RedirectResponse
    {
        // Bloqueio para preventiva
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->route('admin.ordemservicos.index')
                ->with('notification', [
                    'type' => 'error',
                    'title' => 'Operação bloqueada',
                    'message' => 'Existem pneus parados no depósito há mais de 24 horas. Criação de Ordem de Serviço bloqueada.',
                    'duration' => 5000,
                ]);
        }
        $veiculosFrequentes = $this->getVeiculosFrequentesDetalhados();
        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();
        $produtosFrequentes = $this->getProdutosFrequentes();
        $motoristasFrequentes = $this->getMotoristasFrequentes();
        $servicosFrequentes = $this->getServicosFrequentes();
        $formOptions = $this->getFormOptions();
        $steps = [];

        return view('admin.ordemservicos.create_preventiva', compact(
            'formOptions',
            'motoristasFrequentes',
            'produtosFrequentes',
            'fornecedoresFrequentes',
            'veiculosFrequentes',
            'servicosFrequentes',
            'steps'
        ));
    }

    public function create_diagnostico(): View|\Illuminate\Http\RedirectResponse
    {
        // Bloqueio para diagnóstico
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->route('admin.ordemservicos.index')
                ->with('notification', [
                    'type' => 'error',
                    'title' => 'Operação bloqueada',
                    'message' => 'Existem pneus parados no depósito há mais de 24 horas. Criação de Ordem de Serviço bloqueada.',
                    'duration' => 5000,
                ]);
        }
        $veiculosFrequentes = $this->getVeiculosFrequentesDetalhados();
        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();
        $produtosFrequentes = $this->getProdutosFrequentes();
        $motoristasFrequentes = $this->getMotoristasFrequentes();
        $servicosFrequentes = $this->getServicosFrequentes();
        $formOptions = $this->getFormOptions();

        return view('admin.ordemservicos.create_diagnostico', compact(
            'formOptions',
            'motoristasFrequentes',
            'produtosFrequentes',
            'fornecedoresFrequentes',
            'veiculosFrequentes',
            'servicosFrequentes',
            'steps',
        ));
    }

    /**
     * Armazena uma nova ordem de serviço corretiva
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Verificação server-side: bloquear store se houver pneus parados >24h
            if ($this->hasPneusParadosMais24Horas()) {
                return redirect()->back()->withInput()->with('notification', [
                    'type' => 'error',
                    'title' => 'Operação bloqueada',
                    'message' => 'Existem pneus parados no depósito há mais de 24 horas. Criação de Ordem de Serviço bloqueada.',
                    'duration' => 5000,
                ]);
            }
            if ($this->checkOS($request)) {
                return redirect()->back()
                    ->with('notification', [
                        'type' => 'error',
                        'title' => 'Atenção',
                        'message' => 'Já existe uma Ordem de Serviço Aberta para esse veículo.',
                        'duration' => 3000, // opcional (padrão: 5000ms)
                    ])->withInput();
            }

            $validated = $this->validateOrdemServico($request);

            if (!is_numeric($request->id_veiculo)) {
                $request['id_veiculo'] = Veiculo::where('placa', $request->id_veiculo)->first()->id_veiculo;
            }

            $validated['id_filial'] = $this->getFilialVeiculo($validated['id_veiculo']);

            $kmAbastecimento = $this->BuscarKmAbastecimentoStatico($validated['id_veiculo'], $validated['data_abertura']);

            if ($validated['km_atual'] < $kmAbastecimento) {
                return back()->with('notification', [
                    'type' => 'error',
                    'title' => 'Erro de validação',
                    'message' => 'O Km atual deve ser maior que o Km do ultimo Abastecimento.',
                    'duration' => 5000,
                ])->withInput();
            }

            if ($validated['data_previsao_saida'] < $validated['data_abertura']) {
                return back()->with('notification', [
                    'type' => 'error',
                    'title' => 'Data de saída inconsistente',
                    'message' => 'A Data de saída deve ser maior que a data de abertura.',
                    'duration' => 5000,
                ])->withInput();
            }
            DB::beginTransaction();

            // Inserir ordem de serviço corretiva
            $ordemServicoId = DB::connection('pgsql')->table('ordem_servico')->insertGetId([
                'data_inclusao'              => now(),
                'situacao_tipo_os_corretiva' => $validated['situacao_tipo_os_corretiva'] ?? null,
                'data_abertura'              => $validated['data_abertura'] ?? null,
                'data_previsao_saida'        => $validated['data_previsao_saida'] ?? null,
                'prioridade_os'              => $validated['prioridade_os'] ?? null,
                'id_tipo_ordem_servico'      => $validated['id_tipo_ordem_servico'] ?? null,
                'id_status_ordem_servico'    => $validated['id_status_ordem_servico'] ?? null,
                'local_manutencao'           => $validated['local_manutencao'] ?? null,
                'id_filial'                  => $validated['id_filial'] ?? null,
                'id_filial_manutencao'       => $validated['id_filial_manutencao'] ?? null,
                'id_motorista'               => $validated['id_motorista'] ?? null,
                'telefone_motorista'         => $validated['telefone_motorista'] ?? null,
                'tipo_corretiva'             => $validated['tipo_corretiva'] ?? null,
                'id_veiculo'                 => $validated['id_veiculo'] ?? null,
                'km_atual'                   => $validated['km_atual'] ?? null,
                'horas_manutencao_tk'        => $validated['horas_manutencao_tk'] ?? null,
                'id_departamento'            => $validated['id_departamento'] ?? null,
                'observacao'                 => $validated['observacao'] ?? null,
                'relato_problema'            => $validated['relato_problema'] ?? null,
                'id_recepcionista'           => Auth::id() ?? null,
                'is_cancelada'               => false
            ], 'id_ordem_servico');

            // Processar itens de serviços
            $this->processItemsServicos($validated, $ordemServicoId, 1);

            // Processar itens de peças
            $this->processItemsPecas($validated, $ordemServicoId);

            // Processar itens de socorro
            $this->processItemsSocorro($validated, $ordemServicoId);

            DB::commit();
            return redirect()->route('admin.ordemservicos.edit', ['ordemservicos' => $ordemServicoId])
                ->with('notification', [
                    'type' => 'success',
                    'title' => 'Operação concluída',
                    'message' => 'Os dados foram salvos com sucesso.',
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar OS: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erro ao cadastrar OS: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Armazena uma nova ordem de serviço preventiva
     */
    public function store_preventiva(Request $request): RedirectResponse
    {
        $this->normalizeSmartSelectParams($request);
        if ($this->checkOS($request)) {
            return redirect()->back()
                ->with('notification', [
                    'type' => 'error',
                    'title' => 'Atenção',
                    'message' => 'Já existe uma Ordem de Serviço Aberta para esse veículo.',
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ])->withInput();
        }

        $validated = $this->validateOrdemServico($request);

        $validated['id_filial'] = $this->getFilialVeiculo($validated['id_veiculo']);

        $kmAbastecimento = $this->BuscarKmAbastecimentoStatico($validated['id_veiculo'], $validated['data_abertura']);

        if ($validated['km_atual'] < $kmAbastecimento) {
            return back()->with('notification', [
                'type' => 'error',
                'title' => 'Erro de validação',
                'message' => 'O Km atual deve ser maior que o Km do ultimo Abastecimento.',
                'duration' => 5000,
            ])->withInput();
        }

        if ($validated['data_previsao_saida'] < $validated['data_abertura']) {
            return back()->with('notification', [
                'type' => 'error',
                'title' => 'Data de saída inconsistente',
                'message' => 'A Data de saída deve ser maior que a Data de abertura.',
                'duration' => 5000,
            ])->withInput();
        }

        try {
            DB::beginTransaction();

            // Inserir ordem de serviço preventiva
            $ordemServicoPreventivaId = DB::connection('pgsql')->table('ordem_servico')->insertGetId([
                'data_inclusao'              => now(),
                //'situacao_tipo_os_corretiva' => $validated['situacao_tipo_os_corretiva'] ?? null,
                'data_abertura'              => $validated['data_abertura'] ?? null,
                'data_previsao_saida'        => $validated['data_previsao_saida'] ?? null,
                'prioridade_os'              => $validated['prioridade_os'] ?? null,
                'id_tipo_ordem_servico'      => $validated['id_tipo_ordem_servico'] ?? null,
                'id_status_ordem_servico'    => $validated['id_status_ordem_servico'] ?? null,
                'local_manutencao'           => $validated['local_manutencao'] ?? null,
                'id_filial'                  => $validated['id_filial'] ?? null,
                'id_filial_manutencao'       => $validated['id_filial_manutencao'] ?? null,
                'id_motorista'               => $validated['id_motorista'] ?? null,
                'telefone_motorista'         => $validated['telefone_motorista'] ?? null,
                'tipo_corretiva'             => $validated['tipo_corretiva'] ?? null,
                'id_veiculo'                 => $validated['id_veiculo'] ?? null,
                'km_atual'                   => $validated['km_atual'] ?? null,
                'horas_manutencao_tk'        => $validated['horas_manutencao_tk'] ?? null,
                'id_departamento'            => $validated['id_departamento'] ?? null,
                'observacao'                 => $validated['observacao'] ?? null,
                'id_recepcionista'           => Auth::id() ?? null,
                'is_cancelada'               => false
            ], 'id_ordem_servico');

            // Processar itens de serviços
            if ($request->has('items_servicos')) {
                $this->processItemsServicos($validated, $ordemServicoPreventivaId, 2);
            }

            // Processar itens de peças
            if ($request->has('items_pecas')) {
                $this->processItemsPecas($validated, $ordemServicoPreventivaId);
            }

            DB::commit();

            return redirect()->route('admin.ordemservicos.edit_preventiva', ['ordemservicos' => $ordemServicoPreventivaId])
                ->with('notification', [
                    'type' => 'success',
                    'title' => 'Operação concluída',
                    'message' => 'Os dados foram salvos com sucesso.',
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar OS: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erro ao cadastrar OS: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function store_diagnostico(Request $request): RedirectResponse|JsonResponse
    {
        $this->normalizeSmartSelectParams($request);

        if ($this->checkOS($request)) {
            return redirect()->back()
                ->with('notification', [
                    'type' => 'error',
                    'title' => 'Atenção',
                    'message' => 'Já existe uma Ordem de Serviço Aberta para esse veículo.',
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ])->withInput();
        }


        $validated = $this->validateOrdemServico($request);

        $validated['id_filial'] = $this->getFilialVeiculo($validated['id_veiculo']);

        $kmAbastecimento = $this->BuscarKmAbastecimentoStatico($validated['id_veiculo'], $validated['data_abertura']);

        if ($validated['km_atual'] < $kmAbastecimento) {
            return back()->with('notification', [
                'type' => 'error',
                'title' => 'Erro de validação',
                'message' => 'O Km atual deve ser maior que o Km do ultimo Abastecimento.',
                'duration' => 5000,
            ])->withInput();
        }

        if ($validated['data_previsao_saida'] < $validated['data_abertura']) {
            return back()->with('notification', [
                'type' => 'error',
                'title' => 'Data de saída inconsistente',
                'message' => 'A Data de saída deve ser maior que a Data de abertura.',
                'duration' => 5000,
            ])->withInput();
        }

        try {
            DB::beginTransaction();

            // Inserir ordem de serviço preventiva
            $ordemServicoPreventivaId = DB::connection('pgsql')->table('ordem_servico')->insertGetId([
                'data_inclusao'              => now(),
                'situacao_tipo_os_corretiva' => $validated['situacao_tipo_os_corretiva'] ?? null,
                'data_abertura'              => $validated['data_abertura'] ?? null,
                'data_previsao_saida'        => $validated['data_previsao_saida'] ?? null,
                'prioridade_os'              => $validated['prioridade_os'] ?? null,
                'id_tipo_ordem_servico'      => $validated['id_tipo_ordem_servico'] ?? null,
                'id_status_ordem_servico'    => $validated['id_status_ordem_servico'] ?? null,
                'local_manutencao'           => $validated['local_manutencao'] ?? null,
                'id_filial'                  => $validated['id_filial'] ?? null,
                'id_filial_manutencao'       => $validated['id_filial_manutencao'] ?? null,
                'id_motorista'               => $validated['id_motorista'] ?? null,
                'telefone_motorista'         => $validated['telefone_motorista'] ?? null,
                'tipo_corretiva'             => $validated['tipo_corretiva'] ?? null,
                'id_veiculo'                 => $validated['id_veiculo'] ?? null,
                'km_atual'                   => $validated['km_atual'] ?? null,
                'horas_manutencao_tk'        => $validated['horas_manutencao_tk'] ?? null,
                'id_departamento'            => $validated['id_departamento'] ?? null,
                'observacao'                 => $validated['observacao'] ?? null,
                'id_recepcionista'           => Auth::id() ?? null,
                'is_cancelada'               => false
            ], 'id_ordem_servico');

            // Processar itens de serviços
            if ($request->has('items_servicos')) {
                $this->processItemsServicos($validated, $ordemServicoPreventivaId, 2);
            }

            // Processar itens de peças
            if ($request->has('items_pecas')) {
                $this->processItemsPecas($validated, $ordemServicoPreventivaId);
            }

            DB::commit();

            return redirect()->route('admin.ordemservicos.edit_diagnostico', ['ordemservicos' => $ordemServicoPreventivaId])
                ->with('notification', [
                    'type' => 'success',
                    'title' => 'Operação concluída',
                    'message' => 'Os dados foram salvos com sucesso.',
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar OS preventiva: ' . $e->getMessage());

            return redirect()->back()
                ->with('notification', [
                    'type' => 'error',
                    'title' => 'Operação falhou',
                    'message' => '$e->getMessage()',
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ]);
        }
    }

    /**
     * Exibe o formulário para editar uma ordem de serviço corretiva
     */
    public function edit(string $id): View|\Illuminate\Http\RedirectResponse
    {
        // Bloqueio: não permite editar OS se existirem pneus parados no depósito por mais de 24 horas
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->route('admin.ordemservicos.index')
                ->with('notification', [
                    'type' => 'error',
                    'title' => 'Operação bloqueada',
                    'message' => 'Existem pneus parados no depósito há mais de 24 horas. Edição de Ordem de Serviço bloqueada.',
                    'duration' => 5000,
                ]);
        }
        $svgPath = public_path('images/logo_carvalima.svg');
        $svgContent = file_get_contents($svgPath);
        $base64Svg = base64_encode($svgContent);

        $ordemServico = OrdemServico::where('id_ordem_servico', $id)
            ->with([
                'veiculo',
                'servicos',
                'servicos.fornecedor',
                'servicos.manutencao',
                'servicos.servicos',
                'pecas',
                'pecas.fornecedor',
                'pecas.produto.unidadeProduto'
            ])
            ->first();

        $tabelaSocorro = SocorroOrdemServico::where('id_ordem_servico', $id)
            ->with(['socorrista', 'veiculo', 'municipio'])
            ->get() ?? [];

        $tabelaReclamacoes = PreOrdemServico::where('id_pre_os', $ordemServico->id_pre_os ?? 0)
            ->with(['veiculo', 'pessoal'])
            ->get();

        $tabelaServicos = OrdemServicoServicos::where('id_ordem_servico', $id)
            ->with(['fornecedor', 'servicos'])
            ->get();

        $tabelaPecas = OrdemServicoPecas::where('id_ordem_servico', $id)
            ->with(['fornecedor', 'produto.unidadeProduto'])
            ->get();

        $veiculosFrequentes = $this->getVeiculosFrequentesDetalhados();
        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();
        $produtosFrequentes = $this->getProdutosFrequentes();
        $motoristasFrequentes = $this->getMotoristasFrequentes();
        $servicosFrequentes = $this->getServicosFrequentes();
        $formOptions = $this->getFormOptions();

        $ordem = OrdemServico::with('statusOrdemServico')->findOrFail($id);

        $marcacoes = OrdemServicoMarcacao::where('ordem_servico_marcacao.id_ordem_servico', $id)->get();

        $steps = [
            'PRÉ-O.S',
            'ABERTA',
            'AGUARDANDO ASSUMIR O.S',
            'AGUARDANDO PEÇA',
            'AGUARDANDO RETIRADA DA PEÇA',
            'EM EXECUÇÃO',
            'EM PROCESSO DE COMPRA',
            'PENDENTE LANÇAMENTO NF',
            'SERVIÇO INICIADO',
            'SERVIÇOS FINALIZADOS',

        ];

        // pega o status atual
        $statusAtual = strtoupper($ordem->statusOrdemServico->situacao_ordem_servico);

        // adiciona dinamicamente os finais só se forem o status atual
        if (in_array($statusAtual, ['CANCELADA', 'ENCERRADA', 'FINALIZADA'])) {
            $steps[] = $statusAtual;
        }

        $itemDevolucao = DevolucaoProdutoOrdem::where('id_ordem_servico', $ordemServico->id_ordem_servico)->exists();


        return view('admin.ordemservicos.edit', compact(
            'ordemServico',
            'formOptions',
            'motoristasFrequentes',
            'produtosFrequentes',
            'fornecedoresFrequentes',
            'veiculosFrequentes',
            'servicosFrequentes',
            'base64Svg',
            'tabelaSocorro',
            'tabelaReclamacoes',
            'tabelaServicos',
            'tabelaPecas',
            'steps',
            'ordem',
            'marcacoes',
            'itemDevolucao'
        ));
    }

    /**
     * Exibe o formulário para editar uma ordem de serviço preventiva
     */
    public function edit_preventiva(string $id): View|\Illuminate\Http\RedirectResponse
    {
        $tabelaReclamacoes = [];
        $svgPath = public_path('images/logo_carvalima.svg');
        $svgContent = file_get_contents($svgPath);
        $base64Svg = base64_encode($svgContent);

        $ordemServico = OrdemServico::where('id_ordem_servico', $id)
            ->with([
                'servicos',
                'servicos.fornecedor',
                'servicos.manutencao',
                'servicos.servicos',
                'pecas',
                'pecas.fornecedor',
                'pecas.produto'
            ])
            ->first();


        if (isset($ordemServico->id_pre_os)) {
            $tabelaReclamacoes = PreOrdemServico::where('id_pre_os', $ordemServico->id_pre_os)
                ->with(['veiculo', 'pessoal'])
                ->get();
        }

        $tabelaServicos = OrdemServicoServicos::where('id_ordem_servico', $id)
            ->with(['fornecedor', 'servicos', 'manutencao'])
            ->get();

        $tabelaPecas = OrdemServicoPecas::where('id_ordem_servico', $id)
            ->with(['fornecedor', 'produto.unidadeProduto'])
            ->get();

        $veiculosFrequentes = $this->getVeiculosFrequentesDetalhados();
        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();
        $produtosFrequentes = $this->getProdutosFrequentes();
        $motoristasFrequentes = $this->getMotoristasFrequentes();
        $servicosFrequentes = $this->getServicosFrequentes();
        $formOptions = $this->getFormOptions();

        $ordem = OrdemServico::with('statusOrdemServico')->findOrFail($id);

        $steps = [
            'PRÉ-O.S',
            'ABERTA',
            'AGUARDANDO ASSUMIR O.S',
            'AGUARDANDO PEÇA',
            'AGUARDANDO RETIRADA DA PEÇA',
            'EM EXECUÇÃO',
            'EM PROCESSO DE COMPRA',
            'PENDENTE LANÇAMENTO NF',
            'SERVIÇO INICIADO',
            'SERVIÇOS FINALIZADOS',

        ];

        // pega o status atual
        $statusAtual = strtoupper($ordem->statusOrdemServico->situacao_ordem_servico);


        // adiciona dinamicamente os finais só se forem o status atual
        if (in_array($statusAtual, ['CANCELADA', 'ENCERRADA', 'FINALIZADA'])) {
            $steps[] = $statusAtual;
        }
        $itemDevolucao = DevolucaoProdutoOrdem::where('id_ordem_servico', $ordemServico->id_ordem_servico)->exists();


        return view('admin.ordemservicos.edit_preventiva', compact(
            'ordemServico',
            'formOptions',
            'motoristasFrequentes',
            'produtosFrequentes',
            'fornecedoresFrequentes',
            'veiculosFrequentes',
            'servicosFrequentes',
            'base64Svg',
            'tabelaServicos',
            'tabelaPecas',
            'steps',
            'ordem',
            'itemDevolucao',
        ));
    }

    public function edit_diagnostico(string $id): View|\Illuminate\Http\RedirectResponse
    {
        $ordemServico = OrdemServico::where('id_ordem_servico', $id)
            ->with([
                'servicos',
                'servicos.fornecedor',
                'servicos.manutencao',
                'servicos.servicos',
                'pecas',
                'pecas.fornecedor',
                'pecas.produto'
            ])
            ->first();

        $tabelaReclamacoes = PreOrdemServico::where('id_pre_os', $ordemServico->id_pre_os)
            ->with(['veiculo', 'pessoal'])
            ->get();

        $veiculosFrequentes = $this->getVeiculosFrequentesDetalhados();
        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();
        $produtosFrequentes = $this->getProdutosFrequentes();
        $motoristasFrequentes = $this->getMotoristasFrequentes();
        $servicosFrequentes = $this->getServicosFrequentes();
        $formOptions = $this->getFormOptions();

        return view('admin.ordemservicos.edit_diagnostico', compact(
            'ordemServico',
            'formOptions',
            'motoristasFrequentes',
            'produtosFrequentes',
            'fornecedoresFrequentes',
            'veiculosFrequentes',
            'servicosFrequentes'
        ));
    }

    /**
     * Atualiza uma ordem de serviço corretiva existente
     */
    public function update(Request $request, string $id)
    {
        $this->normalizeSmartSelectParams($request);
        // Bloqueio server-side para updates
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->back()->withInput()->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Existem pneus parados no depósito há mais de 24 horas. Edição de Ordem de Serviço bloqueada.',
                'duration' => 5000,
            ]);
        }
        if (!is_numeric($request->id_veiculo)) {
            $request['id_veiculo'] = Veiculo::where('placa', $request->id_veiculo)->first()->id_veiculo;
        }



        try {
            $validated = $this->validateOrdemServico($request, true);

            $validated['id_filial'] = $this->getFilialVeiculo($validated['id_veiculo']);

            DB::beginTransaction();

            $ordemServico = OrdemServico::where('id_ordem_servico', $id)->first();

            $ordemServico->data_alteracao             = now();
            $ordemServico->data_abertura              = $validated['data_abertura'] ?? null;
            $ordemServico->data_previsao_saida        = $validated['data_previsao_saida'] ?? null;
            $ordemServico->id_tipo_ordem_servico      = $validated['id_tipo_ordem_servico'] ?? null;
            $ordemServico->id_status_ordem_servico    = $validated['id_status_ordem_servico'] ?? null;
            $ordemServico->id_user_alteracao          = Auth::id();
            if ($request->has('situacao_tipo_os_corretiva')) {
                $ordemServico->situacao_tipo_os_corretiva = $validated['situacao_tipo_os_corretiva'];
            }
            $ordemServico->local_manutencao           = $validated['local_manutencao'] ?? null;
            $ordemServico->id_filial                  = $validated['id_filial'] ?? null;
            $ordemServico->id_filial_manutencao       = $validated['id_filial_manutencao'] ?? null;
            $ordemServico->id_motorista               = $validated['id_motorista'] ?? null;
            $ordemServico->telefone_motorista         = $validated['telefone_motorista'] ?? null;
            $ordemServico->id_veiculo                 = $validated['id_veiculo'] ?? null;
            $ordemServico->km_atual                   = $validated['km_atual'] ?? null;
            $ordemServico->horas_manutencao_tk        = $validated['horas_manutencao_tk'] ?? null;
            $ordemServico->id_departamento            = $validated['id_departamento'] ?? null;
            $ordemServico->observacao                 = $validated['observacao'] ?? null;
            //$ordemServico->id_manutencao              = $validated['id_manutencao'];
            $ordemServico->is_cancelada               = false;



            $ordemServico->update();

            Log::info('Tipo de situacao selecionada = ' . $ordemServico->situacao_tipo_os_corretiva);
            $this->processItemsServicos($validated, $id, 1);

            $this->processItemsPecas($validated, $id);

            // Processar itens de socorro (a função irá decidir deletar/reinserir apenas
            // se o payload incluir 'tabelaSocorro'. Isso evita apagar registros existentes
            // quando o frontend não reenvia os dados de socorro em um update.)
            $this->processItemsSocorro($validated, $id);

            DB::commit();

            return redirect()->route('admin.ordemservicos.edit', ['ordemservicos' => $ordemServico->id_ordem_servico])
                ->with('notification', [
                    'type' => 'success',
                    'title' => 'Operação concluída',
                    'message' => 'Os dados foram atualizados com sucesso.',
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            LOG::ERROR('Erro ao atualizar ordem de serviço: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar ordem de serviço: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza uma ordem de serviço preventiva existente
     */
    public function update_preventiva(Request $request, string $id, $isUpdate = false): RedirectResponse
    {
        $this->normalizeSmartSelectParams($request);
        // Bloqueio para updates preventivas
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->back()->withInput()->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Existem pneus parados no depósito há mais de 24 horas. Edição de Ordem de Serviço bloqueada.',
                'duration' => 5000,
            ]);
        }
        if (!is_numeric($request->id_veiculo)) {
            $request['id_veiculo'] = Veiculo::where('placa', $request->id_veiculo)->first()->id_veiculo;
        }
        try {
            $validated = $request->validate([
                'data_abertura' => 'required|date', // Obrigatório conforme sistema legado
                'data_previsao_saida' => 'required|date', // Obrigatório conforme sistema legado
                'prioridade_os' => 'nullable',
                'id_tipo_ordem_servico' => 'required', // Obrigatório conforme sistema legado
                'id_status_ordem_servico' => 'required', // Obrigatório conforme sistema legado
                'local_manutencao' => 'required', // Obrigatório conforme sistema legado
                'id_filial_manutencao' => 'nullable', // Obrigatório conforme sistema legado
                'id_motorista' => 'nullable',
                'telefone_motorista' => 'nullable',
                'servico_garantia' => 'nullable',
                'id_veiculo' => 'required',
                'id_departamento' => 'required', // Obrigatório conforme sistema legado
                'observacao' => 'required', // Obrigatório conforme sistema legado
                'id_servicos' => 'nullable',
                'relato_problema' => 'nullable',
                'km_atual' => [
                    'nullable',
                    function ($attribute, $value, $fail) use ($request) {
                        // Verificar se o veículo tem tração (verificar em outro método)
                        $idVeiculo = $request->id_veiculo;
                        if ($idVeiculo && $this->veiculoTemTracao($idVeiculo) && (empty($value) || $value == 0)) {
                            $fail('O campo KM Atual não pode ser zero para veículos com tração.');
                        }

                        if ($idVeiculo && $this->veiculoTemTracao($idVeiculo) && (empty($value) || $value == 0)) {
                            $fail('O campo KM Atual não pode ser zero para veículos com tração.');
                        }
                    }
                ],
                'horas_manutencao_tk' => [
                    'nullable',
                    function ($attribute, $value, $fail) use ($request) {
                        // Verificar se o veículo é Thermo King
                        $idVeiculo = $request->id_veiculo;
                        if ($idVeiculo && $this->veiculoEhThermoKing($idVeiculo) && empty($value)) {
                            $fail('O campo Horas TK é obrigatório para veículos Thermo King.');
                        }
                    }
                ],
                'tabelaServicos' => [
                    'nullable',
                    'json',
                    function ($attribute, $value, $fail) {
                        $items = json_decode($value, true);

                        foreach ($items as $index => $item) {
                            // Usa ?? para definir null se a chave não existir
                            $valorServico = sanitizeToDouble($item['valorServico'] ?? null);
                            $valorTotDescServico = sanitizeToDouble($item['valorTotDescServico'] ?? null);

                            // Valida se a conversão foi bem-sucedida
                            if ($valorServico === null || !is_numeric($valorServico)) {
                                $fail("Item #" . ($index + 1) . ": 'valorServico' deve ser um número válido.");
                                return;
                            }

                            if ($valorTotDescServico === null || !is_numeric($valorTotDescServico)) {
                                $fail("Item #" . ($index + 1) . ": 'valorTotDescServico' deve ser um número válido.");
                                return;
                            }
                        }
                    }
                ],
                'tabelaPecas' => [
                    'nullable',
                    'json',
                    function ($attribute, $value, $fail) use ($isUpdate) {
                        if (!$isUpdate && empty($value)) {
                            // Só valida se não for update e estiver vazio
                            return;
                        }

                        $items = json_decode($value, true);

                        if (!is_array($items)) {
                            $fail("O formato dos itens de peça é inválido.");
                            return;
                        }

                        foreach ($items as $index => $item) {
                            $missingFields = [];

                            // Só valida campos obrigatórios se não for update OU se o campo foi enviado
                            if ((!$isUpdate || isset($item['idFornecedor'])) && !isset($item['idFornecedor'])) {
                                $missingFields[] = 'fornecedor';
                            }
                            if ((!$isUpdate || isset($item['idProduto'])) && !isset($item['idProduto'])) {
                                $missingFields[] = 'produto';
                            }
                            // ... resto dos campos com a mesma lógica

                            // if (!empty($missingFields)) {
                            //     $fail("A Peça #" . ($index + 1) . " está faltando os seguintes campos: " . implode(', ', $missingFields));
                            //     return;
                            // }
                        }
                        Log::info('Itens recebidos em tabelaPecas', $items);
                    }
                ],
                // Nota: Para remoção intencional de todos os socorros envie a flag
                // 'tabelaSocorro_remover' = true no payload. Ausência de 'tabelaSocorro'
                // preserva os registros existentes. Se 'tabelaSocorro' for enviado
                // como array vazio e sem a flag 'tabelaSocorro_remover', não será
                // executada remoção.
                'tabelaSocorro' => [
                    'nullable',
                    'json',
                    function ($attribute, $value, $fail) use ($isUpdate) {
                        // Se não informou o campo (update sem tabelaSocorro), não valida
                        if (!array_key_exists('tabelaSocorro', request()->all()) && $isUpdate) {
                            return;
                        }

                        // Se for create (não é update) e estiver vazio, não valida
                        if (!$isUpdate && empty($value)) {
                            return;
                        }

                        $items = json_decode($value, true);

                        // Aceitamos array vazio aqui (remoção só com flag explícita). Se não for array, falha
                        if (!is_array($items)) {
                            $fail("O formato dos itens de socorro é inválido.");
                            return;
                        }

                        foreach ($items as $index => $item) {
                            $missingFields = [];

                            // Validar apenas quando for create OU quando o campo foi enviado no payload (para update permitir campos omitidos)
                            if ((!$isUpdate || isset($item['idVeiculo'])) && !isset($item['idVeiculo'])) {
                                $missingFields[] = 'idVeiculo';
                            }
                            if ((!$isUpdate || isset($item['idSocorrista'])) && !isset($item['idSocorrista'])) {
                                $missingFields[] = 'idSocorrista';
                            }
                            if ((!$isUpdate || isset($item['idLocalSocorro'])) && !isset($item['idLocalSocorro'])) {
                                $missingFields[] = 'idLocalSocorro';
                            }

                            if (!empty($missingFields)) {
                                $fail("Registro de socorro #" . ($index + 1) . " está faltando os seguintes campos: " . implode(', ', $missingFields));
                                return;
                            }
                        }
                    }
                ],
                'descricao_servico' => [
                    'nullable',
                    'json'
                ],
            ]);

            $validated['id_filial'] = $this->getFilialVeiculo($validated['id_veiculo']);

            DB::beginTransaction();

            // Atualizar ordem de serviço preventiva
            $ordemServicoPreventiva = OrdemServico::where('id_ordem_servico', $id)->first();

            $ordemServicoPreventiva->data_alteracao          = now();
            $ordemServicoPreventiva->data_abertura           = $validated['data_abertura'];
            $ordemServicoPreventiva->data_previsao_saida     = $validated['data_previsao_saida'];
            $ordemServicoPreventiva->id_tipo_ordem_servico   = $validated['id_tipo_ordem_servico'];
            $ordemServicoPreventiva->id_status_ordem_servico = $validated['id_status_ordem_servico'];
            //$ordemServicoPreventiva->situacao_tipo_os_corretiva = $validated['situacao_tipo_os_corretiva'];
            $ordemServicoPreventiva->local_manutencao        = $validated['local_manutencao'];
            $ordemServicoPreventiva->id_filial               = $validated['id_filial'];
            $ordemServicoPreventiva->id_filial_manutencao    = $validated['id_filial_manutencao'];
            $ordemServicoPreventiva->id_motorista            = $validated['id_motorista'];
            $ordemServicoPreventiva->telefone_motorista      = $validated['telefone_motorista'];
            $ordemServicoPreventiva->id_veiculo              = $validated['id_veiculo'];
            $ordemServicoPreventiva->km_atual                = $validated['km_atual'];
            $ordemServicoPreventiva->horas_manutencao_tk     = $validated['horas_manutencao_tk'];
            $ordemServicoPreventiva->id_departamento         = $validated['id_departamento'];
            $ordemServicoPreventiva->observacao              = $validated['observacao'];

            $ordemServicoPreventiva->update();


            // Processar serviços
            $this->processItemsServicos($validated, $id, 2);

            // Processar peças
            $this->processItemsPecas($validated, $id);

            DB::commit();

            return redirect()->route('admin.ordemservicos.edit_preventiva', ['ordemservicos' => $ordemServicoPreventiva->id_ordem_servico])
                ->with('notification', [
                    'type' => 'success',
                    'title' => 'Operação concluída',
                    'message' => 'Os dados foram atualizados com sucesso.',
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar ordem de serviço: ' . $e->getMessage());
        }
    }

    public function update_diagnostico(Request $request, string $id): RedirectResponse
    {
        $this->normalizeSmartSelectParams($request);
        // Bloqueio para updates diagnóstico
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->back()->withInput()->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Existem pneus parados no depósito há mais de 24 horas. Edição de Ordem de Serviço bloqueada.',
                'duration' => 5000,
            ]);
        }
        if (!is_numeric($request->id_veiculo)) {
            $request['id_veiculo'] = Veiculo::where('placa', $request->id_veiculo)->first()->id_veiculo;
        }

        try {
            $validated = $this->validateOrdemServico($request, true);

            if (!isset($validated['km_atual']) || !is_int($validated['km_atual']) || $validated['km_atual'] < 0) {
                $validated['km_atual'] = 0;
            }

            if (isset($validated['km_atual']) && isset($validated['id_veiculo']) && isset($validated['data_abertura'])) {
                $this->onvalidarkmabastecimento($validated['km_atual'], $validated['id_veiculo'], $validated['data_abertura']);

                return redirect()->back()->with('notification', [
                    'type' => 'success',
                    'title' => 'Operação concluída',
                    'message' => 'Os dados foram salvos com sucesso.',
                    'duration' => 10000, // opcional (padrão: 5000ms)
                ]);
            }

            $validated['id_filial'] = $this->getFilialVeiculo($validated['id_veiculo']);

            DB::beginTransaction();

            // Atualizar ordem de serviço preventiva
            $ordemServicoPreventiva = OrdemServico::where('id_ordem_servico', $id)->first();

            $ordemServicoPreventiva->data_alteracao          = now();
            $ordemServicoPreventiva->data_abertura           = $validated['data_abertura'];
            $ordemServicoPreventiva->data_previsao_saida     = $validated['data_previsao_saida'];
            $ordemServicoPreventiva->id_tipo_ordem_servico   = $validated['id_tipo_ordem_servico'];
            $ordemServicoPreventiva->id_status_ordem_servico = $validated['id_status_ordem_servico'];
            $ordemServicoPreventiva->situacao_tipo_os_corretiva = $validated['situacao_tipo_os_corretiva'];
            $ordemServicoPreventiva->local_manutencao        = $validated['local_manutencao'];
            $ordemServicoPreventiva->id_filial               = $validated['id_filial_manutencao'];
            $ordemServicoPreventiva->id_filial_manutencao    = $validated['id_filial_manutencao'];
            $ordemServicoPreventiva->id_motorista            = $validated['id_motorista'];
            $ordemServicoPreventiva->telefone_motorista      = $validated['telefone_motorista'];
            $ordemServicoPreventiva->id_veiculo              = $validated['id_veiculo'];
            $ordemServicoPreventiva->km_atual                = $validated['km_atual'];
            $ordemServicoPreventiva->horas_manutencao_tk     = $validated['horas_manutencao_tk'];
            $ordemServicoPreventiva->id_departamento         = $validated['id_departamento'];
            $ordemServicoPreventiva->observacao              = $validated['observacao'];

            $ordemServicoPreventiva->update();


            // Processar serviços
            DB::connection('pgsql')->table('ordem_servico_servicos')->where('id_ordem_servico', $id)->delete();
            $this->processItemsServicos($validated, $id, 2);

            // Processar peças
            DB::connection('pgsql')->table('ordem_servico_pecas')->where('id_ordem_servico', $id)->delete();
            $this->processItemsPecas($validated, $id);

            DB::commit();

            return redirect()->route('admin.ordemservicos.edit_diagnostico', ['ordemservicos' => $ordemServicoPreventiva->id_ordem_servico])
                ->with('notification', [
                    'type' => 'success',
                    'title' => 'Operação concluída',
                    'message' => 'Os dados foram atualizados com sucesso.',
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar ordem de serviço: ' . $e->getMessage());
        }
    }

    /**
     * Remove uma ordem de serviço
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $ordem = OrdemServico::where('id_ordem_servico', $id)->first();

            if (!$ordem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ordem de serviço não encontrada'
                ], 404);
            }

            if ($ordem->id_status_ordem_servico === 4) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ordem de serviço não pode ser excluída pois está com status "FINALIZADA"'
                ], 400);
            }

            if ($ordem->id_status_ordem_servico === 7) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ordem de serviço não pode ser excluída pois existem Serviços Iniciados.'
                ], 400);
            }

            // Remover itens relacionados
            DB::table('ordem_servico_pecas')->where('id_ordem_servico', $id)->delete();
            DB::table('ordem_servico_servicos')->where('id_ordem_servico', $id)->delete();
            DB::table('socorro_ordem_servico')->where('id_ordem_servico', $id)->delete();

            // Remover devoluções relacionadas
            DB::table('devolucao_produto_ordem')->where('id_ordem_servico', $id)->delete();

            // Remover a ordem de serviço
            DB::connection('pgsql')->table('ordem_servico')->where('id_ordem_servico', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ordem de serviço excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Solicitar peças para uma ordem de serviço
     */
    public function onActionSolicitarPecas(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        try {
            $idordem = $request->id;
            $ordemServico = OrdemServico::where('id_ordem_servico', $idordem)
                ->with(['servicos.servicos', 'servicos.manutencao', 'servicos.fornecedor', 'pecas'])
                ->first();
            $idfilialmanutencao = $request->idFilialManutencao;
            $idstatus = $ordemServico->id_status_ordem_servico;
            $Status = array(4, 5, 6, 8, 9);
            $idusuario = Auth::user()->id;
            $idfilialveiculo = Veiculo::where('id_veiculo', $ordemServico->id_veiculo)->value('id_filial');
            $descstatus = StatusOrdemServico::where('id_status_ordem_servico', $idstatus)->first();
            // $idfilialFornecedor = isset($ordemServico->servicos) ? $ordemServico->servicos->first()->fornecedor->id_filial : $ordemServico->pecas[0]->fornecedor->id_filial;
            $tipoOS = $request->tipoOS;

            if (empty($idordem)) {
                return response()->json([
                    'error' => 'Atenção',
                    'message' => 'A ordem de serviço ainda não foi Gerada.'
                ], 400);
            }

            // nova condição para atender a solicitação de pneus
            if ($tipoOS == 3) {
                $jaSolicitada = OrdemServicoPecas::where('id_ordem_servico', $idordem)->first()->jasolicitada;

                if ($jaSolicitada == 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Peças já foram solicitadas ao estoque.'
                    ], 400);
                }

                $retorno = $this->onSolicitarPneus($idordem, $idusuario);

                if ($retorno) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Peças Solicitadas com sucesso.'
                    ], 200);
                } elseif ($retorno === 'Estoque insuficiente') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Estoque do Pneu insuficiente.'
                    ], 400);
                } elseif ($retorno === 'Produto não é um pneu') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Produto selecionado não é um pneu ou não há modelo cadastrado.'
                    ], 400);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Peças não foram solicitadas.'
                    ], 400);
                }
            }

            if (!in_array($idstatus, $Status)) {
                $Solicitar        = $this->InserirProdutoSolicitacao($idordem, $idusuario, $idfilialveiculo, $idfilialmanutencao);
                $SolicitarCompras = $this->InserirComprasProdutos($idordem, $idusuario, $idfilialveiculo, $idfilialmanutencao);
            } else {
                return response()->json([
                    'error' => 'Atenção',
                    'message' => "A Ordem de Serviço não está com status correto $descstatus->situacao_ordem_servico para solicitação da peça ao estoque."
                ], 400);
            }

            if ($Solicitar == false) {
                return response()->json([
                    'error' => 'Atenção',
                    'message' => "Peças foram solicitadas ao compras"
                ], 400);
            } elseif ($SolicitarCompras == 'filial_diferentes') {
                return response()->json([
                    'error' => 'Atention',
                    'message' => "O fornecedor não está vinculado com a filial de manutenção!"
                ], 400);
            } elseif ($Solicitar) {
                $this->ContabilizarSolicitacaoPecas($idordem);
                return response()->json([
                    'success' => 'Atenção',
                    'message' => "Solicitação Realizada ao estoque com sucesso"
                ], 200);
            } elseif (empty($Solicitar)) {
                return response()->json([
                    'error' => 'Atenção',
                    'message' => "Não Existem produtos para Solicitação"
                ], 400);
            } else {
                return response()->json([
                    'error' => 'Atenção',
                    'message' => "Não foi possível solicitar peças ao estoque devido ao fornecedor."
                ], 400);
            }
        } catch (\Exception $e) {
            LOG::ERROR('Erro durante o processo de solicitação de peças ao estoque:', [$e->getMessage()]);
        }
    }

    public function onActionEncerrar(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        Log::info('Iniciando processo de encerramento da O.S: ', ['request' => $request->all()]);

        try {
            $idos                 = $request->id;
            $idStatusOrdemServico = OrdemServico::where('id_ordem_servico', $idos)->value('id_status_ordem_servico');
            $tipoOS               = OrdemServico::where('id_ordem_servico', $idos)->value('id_tipo_ordem_servico');
            $km_atual             = OrdemServico::where('id_ordem_servico', $idos)->value('km_atual');
            $id_veiculo           = $request->idVeiculo;
            $eixosVazios          = $this->getTodosEixosVazios($id_veiculo);
            $veiculosTerceiro     = Veiculo::where('id_veiculo', $id_veiculo)->where('situacao_veiculo', true)->first();
            $validoSerPecas       = $this->getServicosePecasVazio($idos);
            $fornecedorNulo       = $this->ExisteServicoSemFornecedor($idos);
            $exisservico          = $this->existservicogerado($idos);
            $manutencaoVinc       = $this->ValidarManutencaoVinculada($idos);
            $this->finalizarServicosInternos($idos);

            if (!$this->ValidarBaixaPecasEstoqueStatico($idos)) {
                return response()->json([
                    'success' => false,
                    'message' => "Atenção: Você não pode finalizar, pois as peças ainda não foram baixadas no estoque."
                ], 400);
            }

            if ($exisservico == 1) {
                return response()->json([
                    'success' => false,
                    'message' => "Atenção: Você não pode encerrar está O.S, pois existem serviços com status SERVIÇO GERADO"
                ], 400);
            }

            if (!$validoSerPecas) {
                return response()->json([
                    'success' => false,
                    'message' => "Atenção: Não será possível encerrar esta O.S, pois é necessário o lançamento de Serviços."
                ], 400);
            }

            if (!$veiculosTerceiro && !empty($eixosVazios)) {
                $eixosVazios = implode('<br>', $eixosVazios);
                return response()->json([
                    'success' => false,
                    'message' => "Atenção: Não será possível encerrar esta O.S pois o veículo ainda não possui Pneus Aplicados nos seguintes Eixos:<b style = 'color: red';>" . $eixosVazios . "\n"
                ], 400);
            }


            if (!$fornecedorNulo) {

                if (!$this->RetornarExisteServicoInterno($idos) && !$this->RetornarExisteProdutoInterno($idos)) {

                    if ($idStatusOrdemServico != 4 && $manutencaoVinc && $request->confirma == 1) {

                        return $this->onYesEncerrar($idos, $id_veiculo, $km_atual, Auth::user()->id);
                    } elseif ($idStatusOrdemServico != 4 && $tipoOS == 2) {
                        return $this->onYesEncerrar($idos, $id_veiculo, $km_atual, Auth::user()->id);
                    } elseif ($idStatusOrdemServico != 4 && !$manutencaoVinc && $tipoOS == 1) {
                        return response()->json([
                            'success' => false,
                            'message' => "Atenção: Ordem de Serviço não pode ser encerrada sem uma manutenção vinculada."
                        ], 400);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => "Atenção: Ordem de Serviço já se encontra encerrada."
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "Atenção: Existem serviços sem solicitação ou finalização nessa ordem de serviço."
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Atenção: Existe serviços sem Fornecedor Vinculado. Por favor faça o vinculo antes do encerramento da O.S."
                ], 400);
            }
        } catch (\Exception $e) {
            LOG::info('Erro ao Encerrar O.S: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao encerrar ordem de serviço: ' . $e->getMessage()
            ], 500);
        }
    }

    public function onImprimir(string $id)
    {
        try {
            $svgPath = public_path('images/logo_carvalima.svg');
            $svgContent = file_get_contents($svgPath);
            $base64Svg = base64_encode($svgContent);

            if (!empty($id) || $id != null) {
                $parametros = array('P_id_ordem' => $id);

                $name       = 'impressao_os';
                $agora      = date('d-m-YH:i');
                $tipo       = '.pdf';
                $relatorio  = $name . $agora . $tipo;
                $barra      = '/';
                $partes     = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
                $host       = $partes['host'] . PHP_EOL;
                $pathrel    = (explode('.', $host));
                $dominio    = $pathrel[0];

                if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
                    $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                    $pastarelatorio = '/reports/homologacao/' . $name;
                    $imprime = 'homologacao';

                    Log::info('Usando servidor de homologação');
                } elseif ($dominio == 'lcarvalima') {
                    $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                    $input = '/reports/carvalima/' . $name;

                    // Verificar se o diretório existe antes de tentar chmod
                    if (is_dir($input)) {
                        chmod($input, 0777);
                        Log::info('Permissões do diretório alteradas: ' . $input);
                    } else {
                        Log::warning('Diretório não encontrado: ' . $input);
                    }

                    $pastarelatorio = $input;
                    $imprime = $dominio;

                    Log::info('Usando servidor de produção');
                } else {
                    $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                    $input = '/reports/' . $dominio . '/' . $name;

                    // Verificar se o diretório existe antes de tentar chmod
                    if (is_dir($input)) {
                        chmod($input, 0777);
                        Log::info('Permissões do diretório alteradas: ' . $input);
                    } else {
                        Log::warning('Diretório não encontrado: ' . $input);
                    }

                    $pastarelatorio = $input;
                    $imprime = $dominio;

                    Log::info('Usando servidor de produção');
                }

                $jsi = new jasperserverintegration(
                    $jasperserver,
                    $pastarelatorio, // Report Unit Path
                    'pdf',           // Tipo da exportação do relatório
                    'unitop',        // Usuário com acesso ao relatório
                    'unitop2022',    // Senha do usuário
                    $parametros      // Conteudo do Array
                );

                $data = $jsi->execute();

                try {
                    return response($data, 200)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'inline; filename="' . $relatorio . '"');
                } catch (\Exception $e) {
                    LOG::ERROR('error', $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            LOG::info('error', $e->getMessage());
        }
    }

    public function onImprimirServPec(string $id)
    {
        try {
            // Buscar a ordem de serviço com todos os relacionamentos necessários
            $data = OrdemServico::with([
                'veiculo:id_veiculo,placa', // Apenas os campos necessários
                'pecas' => function ($query) {
                    $query->select([
                        'id_ordem_servico_pecas',
                        'id_ordem_servico',
                        'id_produto',
                        'quantidade',
                        'aplicacao',
                        'valor_pecas'
                    ]);
                },
                'pecas.produto:id_produto,descricao_produto,codigo_produto',
                'servicos' => function ($query) {
                    $query->select([
                        'id_ordem_servico_serv',
                        'id_ordem_servico',
                        'id_servicos',
                        'quantidade_servico',
                        'valor_servico'
                    ]);
                },
                'servicos.servicos:id_servico,descricao_servico'
            ])->find($id);

            if (!$data) {
                Log::warning("Ordem de serviço não encontrada: ID {$id}");
                return response()->json([
                    'success' => false,
                    'message' => 'Ordem de serviço não encontrada'
                ], 404);
            }

            // Preparar o logo
            $base64Svg = $this->prepararLogo();

            // Log para debug (apenas em desenvolvimento)
            if (config('app.debug')) {
                Log::info("Gerando relatório OS {$id}: " .
                    "{$data->pecas->count()} peças, {$data->servicos->count()} serviços");
            }

            // Configurar e gerar PDF
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('admin.ordemservicos.rel_serv_pec_pdf', [
                'data' => $data,
                'base64Svg' => $base64Svg
            ]);

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial',
                'isRemoteEnabled' => false // Segurança
            ]);

            $filename = "rel_serv_pec_{$id}_" . date('Y-m-d_H-i-s') . ".pdf";

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório serviços/peças', [
                'ordem_servico_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    public function onFinalizar(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        try {
            $userId      = Auth::user()->id;
            $permission  = array(1, 17, 25, 41, 99, 100, 105, 109, 111, 127, 143, 169, 248, 289, 291, 296, 302, 304, 318, 319, 365, 399, 421, 433, 455, 516, 519);
            $idOrdem     = $request->id;
            $idStatus    = $request->StatusOS;
            $exisservico = $this->existservicogerado($idOrdem);
            $existePecas = $this->ExisteSerivcoPecas($idOrdem);

            if ($idStatus == 8) {
                $servicosVinculados = OrdemServicoServicos::where('id_ordem_servico', '=', $idOrdem)->count();
                if ($servicosVinculados == 0) {
                    // Exibe uma mensagem de erro e bloqueia a finalização
                    return response()->json([
                        'success' => false,
                        'message' => 'A finalização desta Ordem de Serviço está bloqueada pois esta pendente de lançamento de nota fiscal e não há serviços vinculados.'
                    ], 400);
                }
            }

            if ($exisservico == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Atenção: Você não pode encerrar está O.S pois existem serviços com status SERVIÇO GERADO'
                ], 400);
            }

            if ($existePecas) {
                return response()->json([
                    'success' => false,
                    'message' => "Atenção: Existe peças pendentes de finalização."
                ], 400);
            }

            if (!in_array($userId, $permission) && !Auth::user()->is_superuser) {
                return response()->json([
                    'success' => false,
                    'message' => "Atenção: Não será possível finalizar esta O.S, você não tem permissão para realizar esta ação.\n"
                ], 400);
            }

            if ((!$this->RetornarExisteNotaLancada($idOrdem))) {
                return response()->json([
                    'success' => false,
                    'message' => "Atenção: Não será possível finalizar esta O.S, existe serviços/peças sem lançamento de Notas Fiscal.\n"
                ], 400);
            } else {
                $objeto = OrdemServico::findOrFail($idOrdem);

                $objeto->id_status_ordem_servico = 4;
                $objeto->data_alteracao = now();

                $objeto->update();
                return response()->json([
                    'success' => true,
                    'message' => "Ordem de Serviço Finalizada com sucesso."
                ], 201);
            }
        } catch (\Exception $e) {
            LOG::info('Erro ao finalizar a ordem de serviço:   ' . $e->getMessage());
        }
    }

    /**
     * Cancelar uma ordem de serviço
     */
    public function onCancelarOS(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        try {
            $key = $request->id;
            if ($key) {
                if (!$this->ExisteSerivcoPecas($key)) {
                    if (isset($request->CancelarOS) && $request->CancelarOS == 1) {
                        $user   = Auth::user()->id;

                        $timezone = new DateTimeZone('America/Cuiaba');
                        $data = new DateTime('now', $timezone);
                        $dt_cba = $data->format('Y-m-d H:i:s');

                        if (!empty($key) && !empty($dt_cba) && !empty($user)) {
                            $objeto =  OrdemServico::findOrFail($key);

                            if (!empty($objeto->id_manutencao)) {
                                $objeto->id_status_ordem_servico = 4;
                                $objeto->is_cancelada = true;
                            } else {
                                $objeto->id_status_ordem_servico = 13;
                                $objeto->is_cancelada = true;
                            }

                            $objeto->data_alteracao = $dt_cba;
                            $objeto->data_hora_cancelamento = $dt_cba;
                            $objeto->user_cancelamento = $user;

                            $objeto->update();

                            return response()->json([
                                'success' => true,
                                'message' => 'Ordem Serviço cancelada com sucesso.'
                            ], 201);
                        } else {
                            return response()->json([
                                'error' => 'Atenção',
                                'message' => 'Não foi possível cancelar esta ordem de serviço.'
                            ], 400);
                        }
                    }
                } else {
                    return response()->json([
                        'error' => 'Atenção',
                        'message' => 'Atenção: Você não pode cancelar essa ordem de serviço, pois já existem serviços ou peças adicionadas.'
                    ], 400);
                }
            } else {
                return response()->json([
                    'error' => 'Atenção',
                    'message' => 'Atenção: É necessário salvar a ordem de serviço antes de efetuar o cancelamento.'
                ], 400);
            }
        } catch (\Exception $e) {
            LOG::info('error', $e->getMessage());
        }
    }

    public function onSolicitarServicos(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        try {
            $idfilialveiculo = $request->idFilialVeiculo;
            $idordem = $request->id;
            $idfilialmanutencao = $request->idFilialManutencao ?? Veiculo::where('id_veiculo', $request->idVeiculo)->first()->id_filial;
            $idusuario = Auth::user()->id;

            // Valida se a ordem existe
            if (empty($idordem)) {
                return response()->json([
                    'success' => false,
                    'message' => "Salve a Ordem de Serviço antes de solicitar os serviços."
                ], 400);
            }

            $solicitarCompras = $this->InserirComprasServicos($idordem, $idusuario, $idfilialveiculo, $idfilialmanutencao);


            // Monta mensagem final
            if ($solicitarCompras) {
                $message = "Serviço(s) processado(s) com sucesso.";

                $success = true;
                $statusCode = 200;
            } else {
                $message = "Nenhum serviço pôde ser solicitado.";

                $success = false;
                $statusCode = 400;
            }

            return response()->json([
                'success' => $success,
                'message' => $message
            ], $statusCode);
        } catch (\Exception $e) {
            Log::error('Erro ao solicitar os serviços: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao solicitar os serviços: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna os dados do veículo para o formulário via API
     */
    public function getDadosVeiculo(Request $request): JsonResponse
    {
        $this->normalizeSmartSelectParams($request);
        try {
            if (is_numeric($request->id_veiculo)) {
                $veiculo = Veiculo::where('id_veiculo', $request->id_veiculo)
                    ->with('filialVeiculo')
                    ->first();
            } else {
                $veiculo = Veiculo::where('placa', $request->id_veiculo)
                    ->with('filialVeiculo')
                    ->first();
            }
            Log::debug('Veículo encontrado:', ['veiculo' => $veiculo]);

            if ($veiculo) {
                return response()->json([
                    'success' => true,
                    'chassi' => $veiculo->chassi,
                    'id_filial' => $veiculo->filialVeiculo->name,
                    'id_departamento' => $veiculo->id_departamento
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Veículo não encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados do veículo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados do veículo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function inserirProdutoSolicitacao($idOrdem, $idUser, $filialVeiculo, $filialManutencao)
    {
        if (empty($idOrdem) || empty($idUser) || empty($filialVeiculo) || empty($filialManutencao)) {
            return false;
        }

        // if ($idfilialFornecedor != $filialManutencao) {
        //     return 'filial_diferentes';
        // }

        $produtos = OrdemServicoPecas::where('id_ordem_servico', '=', $idOrdem)->first();

        if (empty($produtos->id_ordem_servico_pecas)) {
            return false;
        }

        // Chamar função do banco de dados
        $result = DB::connection('pgsql')->select(
            "SELECT * FROM fc_inserir_produtos_solicitacao(?, ?, ?, ?)",
            [$idOrdem, $idUser, $filialVeiculo, $filialManutencao]
        );

        return $result[0]->fc_inserir_produtos_solicitacao == 1;
    }

    /**
     * Insere produtos nas compras
     */
    private function inserirComprasProdutos($idOrdem, $idUser, $filialVeiculo, $filialManutencao)
    {
        if (empty($idOrdem) && empty($idUser) && empty($filialVeiculo) && empty($filialManutencao)) {
            return false;
        }

        // Obter IDs de fornecedores da Carvalima
        $carvalimaDados = Fornecedor::where('nome_fornecedor', 'ILIKE', '%carvalima%')
            ->pluck('id_fornecedor')
            ->toArray();

        // Verificar se existem resultados excludindo fornecedores da Carvalima
        $resultados = OrdemServicoPecas::where('id_ordem_servico', $idOrdem)
            ->whereNotIn('id_fornecedor', $carvalimaDados)
            ->select('id_ordem_servico_pecas')
            ->get();

        $validador = null;
        foreach ($resultados as $item) {
            $validador = $item->id_ordem_servico_pecas;
        }

        if (empty($validador)) {
            return  "Não existem serviços a serem cotados.";
        }
        // Chamar função do banco de dados
        $objects = DB::connection('pgsql')->select(
            "SELECT * FROM fc_inserir_compras_produtos_(?, ?, ?, ?)",
            [$idOrdem, $idUser, $filialVeiculo, $filialManutencao]
        );


        $retorno = null;
        if ($objects) {
            foreach ($objects as $item) {
                $retorno = $item->fc_inserir_compras_produtos_;
            }
        }

        return $retorno == 1;
    }

    /**
     * Contabiliza a solicitação de peças
     */
    private function contabilizarSolicitacaoPecas($idOrdemServico)
    {
        if (!empty($idOrdemServico)) {
            // Atualizar o status da ordem de serviço para 3 (em solicitação)
            $ordemServico = OrdemServico::find($idOrdemServico);
            if ($ordemServico) {
                $ordemServico->id_status_ordem_servico = 3;
                $ordemServico->save();
            }
        }
    }

    /**
     * Verifica se existem eixos sem pneus aplicados no veículo
     */
    private function getTodosEixosVazios($idVeiculo)
    {
        if (empty($idVeiculo)) {
            return [];
        }

        // Consulta complexa para verificar eixos sem pneus
        $query = "WITH dados AS
            (
                SELECT
                    vxp.id_veiculo,
                    pa.localizacao,
                    pa.id_pneu
                FROM veiculo_x_pneu AS vxp
                JOIN pneus_aplicados AS pa ON pa.id_veiculo_x_pneu = vxp.id_veiculo_pneu
                WHERE vxp.situacao IS TRUE

                    UNION

                SELECT
                    v.id_veiculo,
                    eix.localizacao,
                    NULL
                FROM veiculo AS v
                JOIN tipoequipamento AS tpe ON v.id_tipo_equipamento = tpe.id_tipo_equipamento
                JOIN desenho_eixos AS dse ON dse.id_desenho_eixos = tpe.id_desenho_eixos
                JOIN eixos AS eix ON eix.id_desenho_eixos = dse.id_desenho_eixos
                WHERE eix.localizacao NOT IN (SELECT
                                                pa.localizacao
                                            FROM veiculo_x_pneu AS vxp
                                            JOIN pneus_aplicados AS pa ON pa.id_veiculo_x_pneu = vxp.id_veiculo_pneu
                                            WHERE vxp.situacao IS TRUE
                                            AND vxp.id_veiculo = v.id_veiculo
                )
            )
            SELECT
                d.localizacao
            FROM dados AS d
            WHERE d.id_veiculo = ?
            AND d.id_pneu IS NULL
            AND d.localizacao NOT IN ('E1','E2')
            ORDER BY
                d.localizacao";

        try {
            $eixosVazios = [];
            $localizacoes = DB::connection('pgsql')->select($query, [$idVeiculo]);

            if ($localizacoes) {
                foreach ($localizacoes as $object) {
                    $eixosVazios[] = $object->localizacao;
                }
            }

            return $eixosVazios;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar eixos vazios: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se a ordem de serviço tem serviços
     */
    private function getServicosePecasVazio($idOS)
    {
        if (empty($idOS)) {
            return false;
        }

        $auxiliar = OrdemServico::where('id_ordem_servico', $idOS)->first();

        if ($auxiliar && empty($auxiliar->id_lancamento_os_auxiliar)) {
            $servicos = OrdemServicoServicos::where('id_ordem_servico', '=', $idOS)->first();
            if (!empty($servicos)) {
                return true;
            } else {
                return false;
            }
        } else if ($auxiliar) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Verifica se existe serviço sem fornecedor vinculado
     */
    private function existeServicoSemFornecedor($idOrdem)
    {
        if (empty($idOrdem)) {
            return false;
        }

        $fornecedor = OrdemServicoServicos::where('id_ordem_servico', '=', $idOrdem)
            ->whereNull('id_fornecedor')
            ->first();

        return !empty($fornecedor);
    }

    /**
     * Verifica se existe serviço com status "SERVIÇO GERADO"
     */
    private function existeServicoGerado($idOrdem)
    {
        return OrdemServicoServicos::where('id_ordem_servico', $idOrdem)
            ->where('status_servico', 'SERVIÇO GERADO')
            ->where('id_fornecedor', '!=', 1)
            ->exists() ? 1 : 0;
    }

    /**
     * Finaliza serviços internos (da Carvalima)
     */
    private function finalizarServicosInternos($idOrdem)
    {
        if (empty($idOrdem)) {
            return false;
        }

        try {
            // Subconsulta para obter os IDs dos fornecedores com nome 'carvalima'
            $fornecedoresCarvalima = Fornecedor::whereRaw("nome_fornecedor ILIKE '%carvalima%'")
                ->select('id_fornecedor');

            // Atualização na tabela ordem_servico_servicos
            return OrdemServicoServicos::whereIn('id_fornecedor', $fornecedoresCarvalima)
                ->where('id_ordem_servico', $idOrdem)
                ->update([
                    'data_alteracao' => now(),
                    'finalizado' => true
                ]) > 0;
        } catch (\Exception $e) {
            Log::error('Erro ao finalizar serviços internos: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se as peças foram baixadas no estoque
     */
    private function validarBaixaPecasEstoqueStatico($idOS)
    {
        if (empty($idOS)) {
            return true;
        }

        $retorno = true;

        $requisicoes = RelacaoSolicitacaoPeca::where('id_orderm_servico', '=', $idOS)->get();

        foreach ($requisicoes as $requisicao) {
            $produtosSolicitacoes = Produtossolicitacoes::where('id_relacao_solicitacoes', '=', $requisicao->id_solicitacao_pecas)->get();

            if ($produtosSolicitacoes->count() > 0) {
                foreach ($produtosSolicitacoes as $produto) {
                    if ($retorno) {
                        if ((float)$produto->quantidade != (float)$produto->quantidade_baixa) {
                            $ordemServicoPecas = OrdemServicoPecas::where('id_produto', '=', $produto->id_protudos)
                                ->where('id_ordem_servico', '=', $idOS)
                                ->get();

                            foreach ($ordemServicoPecas as $item) {
                                if (($item->quantidade == $produto->quantidade) &&
                                    ($item->quantidade != $produto->quantidade_baixa)
                                ) {
                                    $retorno = false;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $retorno;
    }

    /**
     * Valida o KM atual em relação ao último abastecimento
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function validarKmAbastecimento(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        try {
            $kmAtual = $request->km_atual;
            $idVeiculo = $request->id_veiculo;
            $dataAbertura = $request->data_abertura;

            if (!$this->isVeiculoTerceiro($idVeiculo) && !empty($kmAtual)) {
                $kmAbastecimento = $this->buscarKmAbastecimento($idVeiculo, $dataAbertura);

                if ($kmAtual < $kmAbastecimento) {
                    // Resetar o campo KM Atual
                    $object = new \stdClass();
                    $object->km_atual = '';

                    return response()->json([
                        'error' => true,
                        'message' => "Atenção: O Km {$kmAtual} é menor que do último abastecimento registrado, por isso, não será permitido a abertura desta ordem de serviço. Verifique o Km.",
                        'data' => $object
                    ]);
                }

                // Verificar se há uma diferença muito grande no KM (mais de 5000)
                $retornoKm = $kmAtual - $kmAbastecimento;
                if ($retornoKm > 5000 && $this->veiculoTemTracao($idVeiculo)) {
                    return response()->json([
                        'error' => true,
                        'message' => "Atenção: O Sistema não permitirá a inclusão de km superior à 5.000 KM do último abastecimento. Notamos que há uma inconsistência no que diz respeito ao Km. Por gentileza, ajuste o km do veículo antes de incluir a O.S.",
                        'data' => null
                    ]);
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error('Erro ao validar KM: ' . $e->getMessage());

            return response()->json([
                'error' => true,
                'message' => 'Erro ao validar KM: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * Método para buscar o KM do último abastecimento
     *
     * @param int $idVeiculo
     * @param string $dataAbertura
     * @return int
     */
    private function buscarKmAbastecimento($idVeiculo, $dataAbertura)
    {
        // Converter data para formato padrão se necessário
        if (strpos($dataAbertura, '/') !== false) {
            $dataAbertura = \DateTime::createFromFormat('d/m/Y H:i', $dataAbertura)
                ->format('Y-m-d H:i:s');
        }

        // Buscar o KM do último abastecimento antes da data de abertura
        $kmAbastecimento = DB::connection('pgsql')->table('v_abastecimento_listar_todos AS lt')
            ->join('veiculo AS v', 'lt.placa', '=', 'v.placa')
            ->where('v.id_veiculo', $idVeiculo)
            ->where('lt.data_inicio', '<=', $dataAbertura)
            ->orderBy('lt.data_inicio', 'desc')
            ->value('lt.km_abastecimento');

        return $kmAbastecimento ?? 0;
    }

    /**
     * Verifica se o veículo é terceiro
     *
     * @param int $idVeiculo
     * @return bool
     */
    private function isVeiculoTerceiro($idVeiculo)
    {
        // Verificar se é um veículo de terceiro
        return DB::connection('pgsql')->table('veiculo')
            ->where('id_veiculo', $idVeiculo)
            ->where('terceiro', true)
            ->exists();
    }

    /**
     * Carrega dados do veículo incluindo KM, Chassi, etc.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function carregarDadosVeiculo(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        try {
            $idVeiculo = $request->id_veiculo;
            $dataAbertura = $request->data_abertura;

            // Converter data para formato padrão se necessário
            if (strpos($dataAbertura, '/') !== false) {
                $dataAbertura = DateTime::createFromFormat('d/m/Y H:i', $dataAbertura)
                    ->format('Y-m-d H:i:s');
            }

            // Buscar dados do veículo
            $veiculo = Veiculo::find($idVeiculo);

            if (!$veiculo) {
                return response()->json([
                    'error' => true,
                    'message' => 'Veículo não encontrado',
                    'data' => null
                ], 404);
            }

            // Buscar dados adicionais
            $idSascar = $veiculo->id_sascar;
            $filialVeiculo = $veiculo->id_filial;
            $chassis = $veiculo->chassi;
            $departamento = $veiculo->id_departamento;
            $placa = $veiculo->placa;
            $isCarreta = $this->isCarreta($idVeiculo);

            // Verificar inconsistências de abastecimento
            $inconsistencia = $this->verificarAbastecimentoInconsistencia($idVeiculo);

            if (!empty($inconsistencia)) {
                return response()->json([
                    'error' => true,
                    'message' => "Atenção: Não é possível prosseguir com a ação pois a placa {$placa} possui os abastecimentos '{$inconsistencia}' na inconsistência ATS.",
                    'data' => null
                ]);
            }

            // Verificar ordem de serviço já aberta
            $idOrdemAberta = $this->consultarOSAberta($idVeiculo);

            if (!empty($request->id_ordem_servico)) {
                if (!empty($idOrdemAberta) && $idOrdemAberta != $request->id_ordem_servico) {
                    return response()->json([
                        'error' => true,
                        'message' => "Atenção: A ordem de serviço corretiva n°:{$idOrdemAberta} já está aberta e registrada para esse veículo.",
                        'data' => null
                    ]);
                }
            } else {
                if (!empty($idOrdemAberta)) {
                    return response()->json([
                        'error' => true,
                        'message' => "Atenção: A ordem de serviço corretiva n°:{$idOrdemAberta} já está aberta e registrada para esse veículo.",
                        'data' => null
                    ]);
                }
            }

            // Verificar se é veículo terceiro
            if ($this->isVeiculoTerceiro($idVeiculo)) {
                // Informar que é veículo terceiro, mas prosseguir
                Log::info("Veículo informado é um veículo de Terceiro: {$placa}");
            }

            // Buscar KM atual por rastreador ou abastecimento
            $km = 0;
            if (!empty($idSascar)) {
                $kmRetroativo = $this->buscarKmRetroativo($idSascar, $dataAbertura);

                if (empty($kmRetroativo)) {
                    $kmAbastecimento = $this->buscarKmAbastecimento($idVeiculo, $dataAbertura);
                    $km = !empty($kmAbastecimento) ? $kmAbastecimento : 0;

                    // Informar que o KM não foi encontrado pelo rastreador
                    if ($km == 0) {
                        Log::info("KM do veículo {$placa} não encontrado, verificar o histórico de KM.");
                    }
                } else {
                    $km = $kmRetroativo;
                }
            }

            // Para carretas, usar KM = 1 se não encontrado
            if ($isCarreta && $km == 0) {
                $km = 1;
            }

            // Montar objeto de resposta
            $object = new stdClass();
            $object->veiculo_chassi = $chassis;
            $object->id_filial = $filialVeiculo;
            $object->km_atual = $km;
            $object->id_departamento = $departamento;

            return response()->json([
                'error' => false,
                'message' => 'Dados carregados com sucesso',
                'data' => $object
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao carregar dados do veículo: ' . $e->getMessage());

            return response()->json([
                'error' => true,
                'message' => 'Erro ao carregar dados do veículo: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * Verifica se o veículo é uma carreta
     *
     * @param int $idVeiculo
     * @return bool
     */
    private function isCarreta($idVeiculo)
    {
        // Verificar se o veículo é uma carreta
        return DB::connection('pgsql')->table('veiculo')
            ->join('tipoequipamento', 'veiculo.id_tipo_equipamento', '=', 'tipoequipamento.id_tipo_equipamento')
            ->where('veiculo.id_veiculo', $idVeiculo)
            ->where('tipoequipamento.descricao_tipo_equipamento', 'LIKE', '%CARRETA%')
            ->exists();
    }

    /**
     * Verifica abastecimentos em inconsistência
     *
     * @param int $idVeiculo
     * @return string|null
     */
    private function verificarAbastecimentoInconsistencia($idVeiculo)
    {
        return null; // Retorno genérico, implementar
    }

    /**
     * Consulta se já existe uma OS aberta para o veículo
     *
     * @param int $idVeiculo
     * @return int|null
     */
    private function consultarOSAberta($idVeiculo)
    {
        // Buscar ordens de serviço abertas para o veículo
        return OrdemServico::where('id_veiculo', $idVeiculo)
            ->whereNotIn('id_status_ordem_servico', [4, 6, 13]) // Status finalizados ou cancelados
            ->where('is_cancelada', false)
            ->value('id_ordem_servico');
    }

    /**
     * Verifica se existe serviço interno não finalizado
     */
    private function retornarExisteServicoInterno($idOrdem)
    {
        if (empty($idOrdem)) {
            return false;
        }

        // Obter fornecedores da Carvalima
        $fornecedoresCarvalima = Fornecedor::whereRaw("nome_fornecedor ILIKE '%carvalima%'")
            ->select('id_fornecedor');

        // Verificar serviços não finalizados que não são da Carvalima
        $servicos = OrdemServicoServicos::where('id_ordem_servico', $idOrdem)
            ->where('finalizado', '!=', true)
            ->whereNotIn('id_fornecedor', $fornecedoresCarvalima)
            ->count();

        return $servicos > 0;
    }

    /**
     * Verifica se existe nota fiscal lançada para todos os serviços/peças
     */
    private function retornarExisteNotaLancada($idOrdem)
    {
        if (empty($idOrdem)) {
            return false;
        }

        $retorno = [true];
        $filiais = [1, 4, 5, 1395, 5033]; // Filiais internas que não precisam de nota

        // Verificar produtos
        $produtos = OrdemServicoPecas::where('id_ordem_servico', '=', $idOrdem)
            ->whereNotIn('id_fornecedor', $filiais)
            ->get();

        if ($produtos->count() > 0) {
            foreach ($produtos as $produto) {
                if (!empty($produto->id_produto) && $produto->id_fornecedor != 1) {
                    $notaFiscal = NFOrdemServico::where('id_ordem_servico', '=', $idOrdem)
                        ->where('id_fornecedor', '=', $produto->id_fornecedor)
                        ->whereNotIn('id_fornecedor', $filiais)
                        ->first();

                    if (empty($notaFiscal)) {
                        $retorno[] = false;
                    }
                }
            }
        }

        // Verificar serviços
        $servicos = OrdemServicoServicos::where('id_ordem_servico', '=', $idOrdem)
            ->whereNotIn('id_fornecedor', $filiais)
            ->get();

        if ($servicos->count() > 0) {
            foreach ($servicos as $servico) {
                if (!empty($servico->id_servicos) && !in_array($servico->id_fornecedor, $filiais)) {
                    $notaFiscal = NFOrdemServico::where('id_ordem_servico', '=', $idOrdem)
                        ->where('id_fornecedor', '=', $servico->id_fornecedor)
                        ->whereNotIn('id_fornecedor', $filiais)
                        ->first();

                    if (empty($notaFiscal)) {
                        $retorno[] = false;
                    }
                }
            }
        }

        return !in_array(false, $retorno);
    }

    /**
     * Insere compras de serviços
     */
    private function inserirComprasServicos($idOrdem, $idUser, $filialVeiculo, $filialManutencao)
    {
        Log::debug('Iniciando inserção de compras de serviços', [
            'idOrdem' => $idOrdem,
            'idUser' => $idUser,
            'filialVeiculo' => $filialVeiculo,
            'filialManutencao' => $filialManutencao
        ]);
        if (empty($idOrdem) || empty($idUser) || empty($filialVeiculo) || empty($filialManutencao)) {
            return false;
        }

        // Obter IDs de fornecedores da Carvalima
        $carvalimaDados = Fornecedor::where('nome_fornecedor', 'ILIKE', '%carvalima%')
            ->pluck('id_fornecedor')
            ->toArray();

        // Verificar se existem serviços excluindo fornecedores da Carvalima
        $resultados = OrdemServicoServicos::where('id_ordem_servico', $idOrdem)
            ->whereNotIn('id_fornecedor', $carvalimaDados)
            ->select('id_ordem_servico_serv')
            ->get();

        $validador = null;
        foreach ($resultados as $item) {
            $validador = $item->id_ordem_servico_serv;
        }


        if (empty($validador)) {
            return false;
        }

        // Chamar função do banco de dados
        $objects = DB::connection('pgsql')->select(
            "SELECT * FROM fc_inserir_compras_servicos_(?, ?, ?, ?)",
            [$idOrdem, $idUser, $filialVeiculo, $filialManutencao]
        );

        $retorno = null;
        if ($objects) {
            foreach ($objects as $item) {
                $retorno = $item->fc_inserir_compras_servicos_;
            }
        }

        if ($retorno == 1) {
            // Atualizar saldos de contratos
            // Esta parte seria melhor refatorada em um serviço separado
            $this->atualizarSaldosContratos($idOrdem);
            return true;
        }

        return false;
    }

    /**
     * Atualiza os saldos dos contratos após inserção de compras
     */
    private function atualizarSaldosContratos($idOrdem)
    {
        try {
            // Obter contratos e valores
            DB::beginTransaction();

            DB::statement("
                        UPDATE contrato_fornecedor cf
                        SET saldo_contrato = r.valor_total
                        FROM
                        (
                            SELECT
                                oss.id_contrato,
                                SUM(COALESCE(sp.valor_total_desconto,0)) AS valor_total
                            FROM pedido_compras pc
                                INNER JOIN solicitacoescompras sc ON sc.id_solicitacoes_compras = pc.id_solicitacoes_compras
                                INNER JOIN servicossolicitacoescompras ssc ON ssc.id_solicitacao_compra = sc.id_solicitacoes_compras
                                INNER JOIN ordem_servico_servicos oss ON oss.id_ordem_servico_serv = ssc.id_ordem_servico_serv
                                INNER JOIN servico_pedidos sp ON sp.id_pedido_compras = pc.id_pedido_compras AND oss.id_servicos = sp.id_servico
                            WHERE oss.id_contrato IN (
                                SELECT DISTINCT
                                    oss.id_contrato
                                FROM pedido_compras pc
                                    INNER JOIN solicitacoescompras sc ON sc.id_solicitacoes_compras = pc.id_solicitacoes_compras
                                    INNER JOIN servicossolicitacoescompras ssc ON ssc.id_solicitacao_compra = sc.id_solicitacoes_compras
                                    INNER JOIN ordem_servico_servicos oss ON oss.id_ordem_servico_serv = ssc.id_ordem_servico_serv
                                    INNER JOIN servico_pedidos sp ON sp.id_pedido_compras = pc.id_pedido_compras
                                WHERE oss.id_ordem_servico = ?
                            )
                            GROUP BY
                                oss.id_contrato
                        ) AS r
                        WHERE cf.id_contrato_forn = r.id_contrato;
                    ", [$idOrdem]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar saldos de contratos: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida os dados da ordem de serviço de acordo com regras de negócio
     *
     * @param Request $request
     * @return array
     */
    private function validateOrdemServico(Request $request, $isUpdate = false)
    {
        $this->normalizeSmartSelectParams($request);
        return $request->validate([
            'data_abertura' => 'required|date', // Obrigatório conforme sistema legado
            'data_previsao_saida' => 'required|date', // Obrigatório conforme sistema legado
            'prioridade_os' => 'nullable',
            'id_tipo_ordem_servico' => 'required', // Obrigatório conforme sistema legado
            'id_status_ordem_servico' => 'required', // Obrigatório conforme sistema legado
            'local_manutencao' => 'required', // Obrigatório conforme sistema legado
            'id_filial_manutencao' => 'nullable', // Obrigatório conforme sistema legado
            'id_motorista' => 'nullable',
            'telefone_motorista' => 'nullable',
            'servico_garantia' => 'nullable',
            'situacao_tipo_os_corretiva' => 'nullable',
            'id_veiculo' => 'nullable',
            'id_departamento' => 'nullable', // Obrigatório conforme sistema legado
            'observacao' => 'required', // Obrigatório conforme sistema legado
            'id_servicos' => 'nullable',
            'relato_problema' => 'nullable',
            //'id_manutencao' =>  'nullable',
            'km_atual' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    // Verificar se o veículo tem tração (verificar em outro método)
                    $idVeiculo = $request->id_veiculo;
                    if ($idVeiculo && $this->veiculoTemTracao($idVeiculo) && (empty($value) || $value == 0)) {
                        $fail('O campo KM Atual não pode ser zero para veículos com tração.');
                    }

                    if ($idVeiculo && $this->veiculoTemTracao($idVeiculo) && (empty($value) || $value == 0)) {
                        $fail('O campo KM Atual não pode ser zero para veículos com tração.');
                    }
                }
            ],
            'horas_manutencao_tk' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    // Verificar se o veículo é Thermo King
                    $idVeiculo = $request->id_veiculo;
                    if ($idVeiculo && $this->veiculoEhThermoKing($idVeiculo) && empty($value)) {
                        $fail('O campo Horas TK é obrigatório para veículos Thermo King.');
                    }
                }
            ],
            'tabelaServicos' => [
                'nullable',
                'json',
                function ($attribute, $value, $fail) {
                    $items = json_decode($value, true);

                    foreach ($items as $index => $item) {
                        // Usa ?? para definir null se a chave não existir
                        $valorServico = sanitizeToDouble($item['valorServico'] ?? null);
                        $valorTotDescServico = sanitizeToDouble($item['valorTotDescServico'] ?? null);

                        // Valida se a conversão foi bem-sucedida
                        if ($valorServico === null || !is_numeric($valorServico)) {
                            $fail("Item #" . ($index + 1) . ": 'valorServico' deve ser um número válido.");
                            return;
                        }

                        if ($valorTotDescServico === null || !is_numeric($valorTotDescServico)) {
                            $fail("Item #" . ($index + 1) . ": 'valorTotDescServico' deve ser um número válido.");
                            return;
                        }
                    }
                }
            ],
            'tabelaPecas' => [
                'nullable',
                'json',
                function ($attribute, $value, $fail) use ($isUpdate) {
                    if (!$isUpdate && empty($value)) {
                        // Só valida se não for update e estiver vazio
                        return;
                    }

                    $items = json_decode($value, true);

                    if (!is_array($items)) {
                        $fail("O formato dos itens de peça é inválido.");
                        return;
                    }

                    foreach ($items as $index => $item) {
                        $missingFields = [];

                        // Só valida campos obrigatórios se não for update OU se o campo foi enviado
                        if ((!$isUpdate || isset($item['idFornecedor'])) && !isset($item['idFornecedor'])) {
                            $missingFields[] = 'fornecedor';
                        }
                        if ((!$isUpdate || isset($item['idProduto'])) && !isset($item['idProduto'])) {
                            $missingFields[] = 'produto';
                        }
                        // ... resto dos campos com a mesma lógica

                        if (!empty($missingFields)) {
                            $fail("A Peça #" . ($index + 1) . " está faltando os seguintes campos: " . implode(', ', $missingFields));
                            return;
                        }
                    }
                    Log::info('Itens recebidos em tabelaPecas', $items);
                }
            ],
            // Nota: Para remoção intencional de todos os socorros envie a flag
            // 'tabelaSocorro_remover' = true no payload. Ausência de 'tabelaSocorro'
            // preserva os registros existentes. Se 'tabelaSocorro' for enviado
            // como array vazio e sem a flag 'tabelaSocorro_remover', não será
            // executada remoção.
            'tabelaSocorro' => [
                'nullable',
                'json',
                function ($attribute, $value, $fail) use ($isUpdate) {
                    // Se não informou o campo (update sem tabelaSocorro), não valida
                    if (!array_key_exists('tabelaSocorro', request()->all()) && $isUpdate) {
                        return;
                    }

                    // Se for create (não é update) e estiver vazio, não valida
                    if (!$isUpdate && empty($value)) {
                        return;
                    }

                    $items = json_decode($value, true);

                    // Aceitamos array vazio aqui (remoção só com flag explícita). Se não for array, falha
                    if (!is_array($items)) {
                        $fail("O formato dos itens de socorro é inválido.");
                        return;
                    }

                    foreach ($items as $index => $item) {
                        $missingFields = [];

                        // Validar apenas quando for create OU quando o campo foi enviado no payload (para update permitir campos omitidos)
                        if ((!$isUpdate || isset($item['idVeiculo'])) && !isset($item['idVeiculo'])) {
                            $missingFields[] = 'idVeiculo';
                        }
                        if ((!$isUpdate || isset($item['idSocorrista'])) && !isset($item['idSocorrista'])) {
                            $missingFields[] = 'idSocorrista';
                        }
                        if ((!$isUpdate || isset($item['idLocalSocorro'])) && !isset($item['idLocalSocorro'])) {
                            $missingFields[] = 'idLocalSocorro';
                        }

                        if (!empty($missingFields)) {
                            $fail("Registro de socorro #" . ($index + 1) . " está faltando os seguintes campos: " . implode(', ', $missingFields));
                            return;
                        }
                    }
                }
            ],
            'descricao_servico' => [
                'nullable',
                'json'
            ],
        ]);
    }

    /**
     * Verifica se o veículo tem tração (para validação de KM)
     *
     * @param int $idVeiculo
     * @return bool
     */
    private function veiculoTemTracao($idVeiculo)
    {
        // Consulta no banco para verificar se o veículo tem tração
        $tracao = DB::connection('pgsql')->table('veiculo')
            ->where('id_veiculo', $idVeiculo)
            ->value('is_possui_tracao');

        return (bool) $tracao;
    }

    /**
     * Verifica se o veículo é Thermo King (para validação de Horimetro)
     *
     * @param int $idVeiculo
     * @return bool
     */
    private function veiculoEhThermoKing($idVeiculo)
    {
        // Consulta no banco para verificar se o veículo é Thermo King
        $placa = DB::connection('pgsql')->table('veiculo')
            ->where('id_veiculo', $idVeiculo)
            ->value('placa');

        // Verificar se a placa termina com 'TK'
        return !empty($placa) && substr($placa, -2) === 'TK';
    }

    /**
     * Processa os itens de serviços da ordem de serviço
     */
    private function processItemsServicos($validated, $ordemServicoId, $os)
    {
        try {
            $servicosRaw = $validated['servicos '] ?? $validated['servicos'] ?? $validated['tabelaServicos'] ?? [];

            if (is_string($servicosRaw)) {
                $servicos = json_decode($servicosRaw, true);
            } else {
                $servicos = $servicosRaw;
            }

            if (is_array($servicos) && count($servicos) > 0) {
                DB::beginTransaction();

                // Coletar IDs dos serviços que vieram do frontend
                $idsRecebidos = [];

                // IMPORTANTE: Adicionar IDs dos serviços já solicitados que existem no banco
                // para garantir que não sejam excluídos, mesmo que não venham no payload
                $servicosSolicitados = OrdemServicoServicos::where('id_ordem_servico', $ordemServicoId)
                    ->where('is_solicitado', true)
                    ->pluck('id_ordem_servico_serv')
                    ->toArray();

                $idsRecebidos = array_merge($idsRecebidos, $servicosSolicitados);

                Log::info("=== INÍCIO PROCESSAMENTO SERVIÇOS ===", [
                    'OS' => $ordemServicoId,
                    'Total serviços no payload' => count($servicos),
                    'Serviços já solicitados no banco' => $servicosSolicitados
                ]);

                foreach ($servicos as $index => $servico) {
                    $fornecedor = intval($servico['idFornecedor'] ?? $servico['fornecedor']['id_fornecedor']);
                    $idServicos = isset($servico['idServico']) ? intval($servico['idServico']) : null ?? (isset($servico['servicos']) ? intval($servico['servicos']['id_servico']) : null);
                    $valUnitario = $this->convertMoneyToFloat($servico['valorServico'] ?? $servico['valor_servico'] ?? 0);
                    $qtdServico = intval($servico['qtdServico'] ?? $servico['quantidade_servico'] ?? 1);
                    $valDescServico = $this->convertMoneyToFloat($servico['valorDescServico'] ?? $servico['valor_descontoservico'] ?? '0');
                    $valTotDescServico = $this->convertMoneyToFloat($servico['valorTotDescServico'] ?? $servico['valor_total_com_desconto'] ?? '0');

                    if (!is_array($servico)) {
                        continue;
                    }

                    // Dados completos para inserção/atualização
                    $dadosCompletos = [
                        'id_ordem_servico' => intval($ordemServicoId),
                        'id_fornecedor' => $fornecedor,
                        'id_servicos' => $idServicos,
                        'valor_servico' => $valUnitario,
                        'quantidade_servico' => $qtdServico,
                        'valor_descontoservico' => $valDescServico,
                        'valor_total_com_desconto' => $valTotDescServico
                    ];

                    if ($os == 2) {
                        $dadosCompletos['id_manutencao'] = $servico['idManutencao'] ?? $servico['id_manutencao'] ?? null;
                    }

                    // Verificar se existe ID do registro (edição) ou se é novo
                    $idRegistro = $servico['id_ordem_servico_serv'] ?? $servico['id'] ?? null;

                    Log::info("Processando serviço #{$index}", [
                        'ID Registro' => $idRegistro,
                        'Fornecedor' => $fornecedor,
                        'ID Serviço' => $idServicos,
                        'Valor' => $valUnitario
                    ]);

                    if (!empty($idRegistro)) {
                        // Verificar se o serviço já foi solicitado (não pode ser alterado)
                        $servicoExistente = OrdemServicoServicos::where('id_ordem_servico_serv', $idRegistro)->first();

                        if ($servicoExistente && $servicoExistente->is_solicitado == true) {
                            // Serviço já solicitado - NÃO adicionar à lista (já foi adicionado no início)
                            // e NÃO atualizar os dados
                            Log::warning("Serviço NÃO ATUALIZADO (já solicitado) - ID: {$idRegistro}, OS: {$ordemServicoId}");
                            continue;
                        }

                        // Adicionar à lista de IDs recebidos
                        $idsRecebidos[] = $idRegistro;

                        // ATUALIZAR registro existente
                        OrdemServicoServicos::where('id_ordem_servico_serv', $idRegistro)
                            ->update($dadosCompletos);

                        Log::info("Serviço ATUALIZADO - ID: {$idRegistro}, OS: {$ordemServicoId}");
                    } else {
                        // IMPORTANTE: Antes de inserir, verificar se já existe um registro igual
                        // (mesmo serviço + fornecedor) para evitar duplicação quando o frontend
                        // não envia o ID de registros existentes
                        $servicoExistente = OrdemServicoServicos::where('id_ordem_servico', $ordemServicoId)
                            ->where('id_fornecedor', $fornecedor)
                            ->where('id_servicos', $idServicos)
                            ->first();

                        if ($servicoExistente) {
                            // Registro já existe - adicionar à lista e atualizar se não estiver solicitado
                            $idsRecebidos[] = $servicoExistente->id_ordem_servico_serv;

                            if ($servicoExistente->is_solicitado != true) {
                                // Atualizar apenas se não estiver solicitado
                                OrdemServicoServicos::where('id_ordem_servico_serv', $servicoExistente->id_ordem_servico_serv)
                                    ->update($dadosCompletos);
                                Log::info("Serviço existente ATUALIZADO (frontend não enviou ID) - ID: {$servicoExistente->id_ordem_servico_serv}, OS: {$ordemServicoId}");
                            } else {
                                Log::warning("Serviço existente IGNORADO (já solicitado) - ID: {$servicoExistente->id_ordem_servico_serv}, OS: {$ordemServicoId}");
                            }
                        } else {
                            // INSERIR novo registro apenas se realmente não existe
                            $novoServico = OrdemServicoServicos::create($dadosCompletos);

                            // Adicionar o ID do serviço recém-criado à lista
                            $idsRecebidos[] = $novoServico->id_ordem_servico_serv;

                            Log::info("Serviço INSERIDO - ID: {$novoServico->id_ordem_servico_serv}, OS: {$ordemServicoId}, Fornecedor: {$fornecedor}, Serviço: {$idServicos}");
                        }
                    }
                }

                Log::info("=== ANTES DA REMOÇÃO ===", [
                    'OS' => $ordemServicoId,
                    'IDs recebidos' => $idsRecebidos,
                    'Total IDs' => count($idsRecebidos),
                    'IDs únicos' => count(array_unique($idsRecebidos))
                ]);

                // Remover serviços que não estão mais na lista (foram excluídos no frontend)
                // PROTEÇÃO: Não remove serviços que já foram solicitados (is_solicitado = true)
                if (!empty($idsRecebidos)) {
                    $servicosRemovidos = OrdemServicoServicos::where('id_ordem_servico', $ordemServicoId)
                        ->whereNotIn('id_ordem_servico_serv', $idsRecebidos)
                        ->where(function ($query) {
                            $query->where('is_solicitado', '!=', true)
                                ->orWhereNull('is_solicitado');
                        })
                        ->delete();

                    if ($servicosRemovidos > 0) {
                        Log::info("Serviços REMOVIDOS - Quantidade: {$servicosRemovidos}, OS: {$ordemServicoId}");
                    }
                } else {
                    // Se não há IDs recebidos, remover apenas serviços que NÃO foram solicitados
                    $servicosRemovidos = OrdemServicoServicos::where('id_ordem_servico', $ordemServicoId)
                        ->where(function ($query) {
                            $query->where('is_solicitado', '!=', true)
                                ->orWhereNull('is_solicitado');
                        })
                        ->delete();

                    if ($servicosRemovidos > 0) {
                        Log::info("Todos os serviços NÃO SOLICITADOS foram REMOVIDOS da OS: {$ordemServicoId}");
                    }
                }

                DB::commit();
            } else {
                // Se não há serviços no payload, remover apenas serviços que NÃO foram solicitados
                DB::beginTransaction();
                $servicosRemovidos = OrdemServicoServicos::where('id_ordem_servico', $ordemServicoId)
                    ->where(function ($query) {
                        $query->where('is_solicitado', '!=', true)
                            ->orWhereNull('is_solicitado');
                    })
                    ->delete();
                DB::commit();

                if ($servicosRemovidos > 0) {
                    Log::info("Nenhum serviço enviado - Serviços NÃO SOLICITADOS foram REMOVIDOS da OS: {$ordemServicoId}");
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ERRO AO GRAVAR ITEM DE SERVIÇO: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }


    /**
     * Processa os itens de peças da ordem de serviço
     */
    private function processItemsPecas($validated, $ordemServicoId)
    {
        try {
            $pecas = json_decode($validated['tabelaPecas'], true);

            if (!empty($pecas) && is_array($pecas)) {
                DB::beginTransaction();

                // Coletar IDs das peças que vieram do frontend
                $idsRecebidos = [];

                // IMPORTANTE: Adicionar IDs das peças já solicitadas que existem no banco
                // para garantir que não sejam excluídas, mesmo que não venham no payload
                $pecasSolicitadas = OrdemServicoPecas::where('id_ordem_servico', $ordemServicoId)
                    ->where('jasolicitada', true)
                    ->pluck('id_ordem_servico_pecas')
                    ->toArray();

                $idsRecebidos = array_merge($idsRecebidos, $pecasSolicitadas);

                foreach ($pecas as $peca) {
                    $fornecedor     = $peca['idFornecedor'] ?? $peca['id_fornecedor'];
                    $idProduto      = $peca['idProduto'] ?? $peca['id_produto'];
                    $valPecas       = $peca['valorUnitario'] ?? $peca['valor_pecas'] ?? 0;
                    $valDesconto    = $peca['valorDesconto'] ?? $peca['valor_desconto'] ?? 0;
                    $qtdPecas       = $peca['qtdPecas'] ?? $peca['quantidade'] ?? 1;
                    $valTotDesconto = $peca['valorTotalDesconto'] ?? $peca['valor_total_com_desconto'] ?? 0;

                    // Dados completos para inserção/atualização
                    $dadosCompletos = [
                        'id_ordem_servico' => $ordemServicoId,
                        'id_fornecedor' => $fornecedor,
                        'id_produto' => $idProduto,
                        'valor_pecas' => $valPecas,
                        'valor_desconto' => $valDesconto,
                        'quantidade' => $qtdPecas,
                        'valor_total_com_desconto' => $valTotDesconto
                    ];

                    // Verificar se existe ID do registro (edição) ou se é novo
                    $idRegistro = $peca['id_ordem_servico_pecas'] ?? $peca['id'] ?? null;

                    if (!empty($idRegistro)) {
                        // Verificar se a peça já foi solicitada (não pode ser alterada)
                        $pecaExistente = OrdemServicoPecas::where('id_ordem_servico_pecas', $idRegistro)->first();

                        if ($pecaExistente && $pecaExistente->jasolicitada == true) {
                            // Peça já solicitada - NÃO adicionar à lista (já foi adicionado no início)
                            // e NÃO atualizar os dados
                            Log::warning("Peça NÃO ATUALIZADA (já solicitada) - ID: {$idRegistro}, OS: {$ordemServicoId}");
                            continue;
                        }

                        // Adicionar à lista de IDs recebidos
                        $idsRecebidos[] = $idRegistro;

                        // ATUALIZAR registro existente
                        OrdemServicoPecas::where('id_ordem_servico_pecas', $idRegistro)
                            ->update($dadosCompletos);

                        Log::info("Peça ATUALIZADA - ID: {$idRegistro}, OS: {$ordemServicoId}");
                    } else {
                        // IMPORTANTE: Antes de inserir, verificar se já existe um registro igual
                        // (mesmo produto + fornecedor) para evitar duplicação quando o frontend
                        // não envia o ID de registros existentes
                        $pecaExistente = OrdemServicoPecas::where('id_ordem_servico', $ordemServicoId)
                            ->where('id_fornecedor', $fornecedor)
                            ->where('id_produto', $idProduto)
                            ->first();

                        if ($pecaExistente) {
                            // Registro já existe - adicionar à lista e atualizar se não estiver solicitado
                            $idsRecebidos[] = $pecaExistente->id_ordem_servico_pecas;

                            if ($pecaExistente->jasolicitada != true) {
                                // Atualizar apenas se não estiver solicitada
                                OrdemServicoPecas::where('id_ordem_servico_pecas', $pecaExistente->id_ordem_servico_pecas)
                                    ->update($dadosCompletos);
                                Log::info("Peça existente ATUALIZADA (frontend não enviou ID) - ID: {$pecaExistente->id_ordem_servico_pecas}, OS: {$ordemServicoId}");
                            } else {
                                Log::warning("Peça existente IGNORADA (já solicitada) - ID: {$pecaExistente->id_ordem_servico_pecas}, OS: {$ordemServicoId}");
                            }
                        } else {
                            // INSERIR novo registro apenas se realmente não existe
                            $novaPeca = OrdemServicoPecas::create($dadosCompletos);

                            // Adicionar o ID da peça recém-criada à lista
                            $idsRecebidos[] = $novaPeca->id_ordem_servico_pecas;

                            Log::info("Peça INSERIDA - ID: {$novaPeca->id_ordem_servico_pecas}, OS: {$ordemServicoId}, Fornecedor: {$fornecedor}, Produto: {$idProduto}");
                        }
                    }
                }

                // Remover peças que não estão mais na lista (foram excluídas no frontend)
                // PROTEÇÃO: Não remove peças que já foram solicitadas (jasolicitada = true)
                if (!empty($idsRecebidos)) {
                    $pecasRemovidas = OrdemServicoPecas::where('id_ordem_servico', $ordemServicoId)
                        ->whereNotIn('id_ordem_servico_pecas', $idsRecebidos)
                        ->where(function ($query) {
                            $query->whereNull('jasolicitada')
                                ->orWhere('jasolicitada', '!=', true);
                        })
                        ->delete();

                    if ($pecasRemovidas > 0) {
                        Log::info("Peças REMOVIDAS - Quantidade: {$pecasRemovidas}, OS: {$ordemServicoId}");
                    }
                } else {
                    // Se não há IDs recebidos, remover apenas peças que NÃO foram solicitadas
                    $pecasRemovidas = OrdemServicoPecas::where('id_ordem_servico', $ordemServicoId)
                        ->where(function ($query) {
                            $query->whereNull('jasolicitada')
                                ->orWhere('jasolicitada', '!=', true);
                        })
                        ->delete();

                    if ($pecasRemovidas > 0) {
                        Log::info("Peças não solicitadas foram REMOVIDAS - Quantidade: {$pecasRemovidas}, OS: {$ordemServicoId}");
                    }
                }

                DB::commit();
            } else {
                // Se não há peças no payload, remover apenas peças que NÃO foram solicitadas
                DB::beginTransaction();
                $pecasRemovidas = OrdemServicoPecas::where('id_ordem_servico', $ordemServicoId)
                    ->where(function ($query) {
                        $query->whereNull('jasolicitada')
                            ->orWhere('jasolicitada', '!=', true);
                    })
                    ->delete();
                DB::commit();

                if ($pecasRemovidas > 0) {
                    Log::info("Nenhuma peça enviada - Peças não solicitadas foram REMOVIDAS - Quantidade: {$pecasRemovidas}, OS: {$ordemServicoId}");
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            LOG::ERROR('ERRO AO GRAVAR ITEM DE PEÇAS: ' . $e->getMessage());
        }
    }

    /**
     * Aplica filtros à consulta de ordens de serviço
     */
    private function applyFilters($query, $request)
    {
        if ($request->filled('id_ordem_servico')) {
            $query->where('id_ordem_servico', $request->id_ordem_servico);
        }

        if ($request->filled('data_abertura')) {
            $query->whereDate('data_abertura', $request->data_abertura);
        }

        if ($request->filled('id_tipo_ordem_servico')) {
            $query->where('id_tipo_ordem_servico', $request->id_tipo_ordem_servico);
        }

        if ($request->filled('id_status_ordem_servico')) {
            $query->where('id_status_ordem_servico', $request->id_status_ordem_servico);
        }

        if ($request->filled('id_lancamento_os_auxiliar')) {
            $query->where('id_lancamento_os_auxiliar', '=', $request->id_lancamento_os_auxiliar)
                ->orWhere('id_pre_os', '=', $request->id_lancamento_os_auxiliar);
        }

        if ($request->filled('recepcionista')) {
            $query->where('id_recepcionista', '=', $request->recepcionista);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', '=', $request->id_veiculo);
        }

        if ($request->filled('local_manutencao')) {
            $query->where('local_manutencao', '=', $request->local_manutencao);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('situacao_tipo_os_corretiva')) {
            $query->where('situacao_tipo_os_corretiva', $request->situacao_tipo_os_corretiva);
        }

        if ($request->filled('grupo_resolvedor')) {
            $query->where('grupo_resolvedor', $request->grupo_resolvedor);
        }
    }

    /**
     * Obtém opções para os formulários
     */
    private function getFormOptions()
    {
        return [
            'tipoOrdemServico' => TipoOrdemServico::select('id_tipo_ordem_servico as value', 'descricao_tipo_ordem as label')
                ->orderBy('label')
                ->get()
                ->toArray(),

            'statusOrdemServico' => StatusOrdemServico::select('id_status_ordem_servico as value', 'situacao_ordem_servico as label')
                ->orderBy('label')
                ->get()
                ->toArray(),

            'filial' => VFilial::select('id as value', 'name as label')
                ->orderBy('label')
                ->get()
                ->toArray(),

            'departamento' => Departamento::select('id_departamento as value', 'descricao_departamento as label')
                ->orderBy('label')
                ->get()
                ->toArray(),

            'manutencao' => Manutencao::select('id_manutencao as value', 'descricao_manutencao as label')
                ->where('ativar', true)
                ->orderBy('label')
                ->get()
                ->toArray(),

            'grupoServico' => GrupoServico::select('id_grupo as value', 'descricao_grupo as label')
                ->orderBy('label')
                ->get()
                ->toArray(),

            'municipio' => Municipio::select('id_municipio as value', 'nome_municipio as label')
                ->limit(50)
                ->orderBy('nome_municipio')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Obtém lista de veículos frequentes
     */
    private function getVeiculosFrequentes()
    {
        return Cache::remember('veiculos_frequentes', now()->addHours(12), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->orderBy('placa')
                ->limit(20)
                ->get(['id_veiculo as value', 'placa as label']);
        });
    }

    /**
     * Obtém lista de veículos frequentes com detalhes
     */
    private function getVeiculosFrequentesDetalhados()
    {
        return Cache::remember('veiculos_frequentes_detalhados', now()->addHours(2), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->limit(20)
                ->orderBy('chassi')
                ->get([
                    'id_veiculo as value',
                    'placa as label',
                    'chassi',
                    'id_filial'
                ])
                ->map(fn($veiculo) => [
                    'value' => $veiculo->value,
                    'label' => $veiculo->label,
                    'chassi' => $veiculo->chassi,
                    'id_filial' => $veiculo->id_filial,
                    'filial_nome' => $veiculo->filial->name ?? 'Sem Filial'
                ]);
        });
    }

    /**
     * Obtém lista de filiais
     */
    private function getFiliais()
    {
        return VFilial::orderBy('name')
            ->get()
            ->map(function ($filial) {
                return [
                    'label' => $filial->name,
                    'value' => $filial->id
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Obtém lista de tipos de ordem de serviço
     */
    private function getTiposOrdemServico()
    {
        return TipoOrdemServico::orderBy('descricao_tipo_ordem')
            ->get()
            ->map(function ($tipoOrdemServico) {
                return [
                    'label' => $tipoOrdemServico->descricao_tipo_ordem,
                    'value' => $tipoOrdemServico->id_tipo_ordem_servico
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Obtém lista de situações de ordem de serviço
     */
    private function getSituacoesOrdemServico()
    {
        return StatusOrdemServico::orderBy('situacao_ordem_servico')
            ->get()
            ->map(function ($situacaoOrdemServico) {
                return [
                    'label' => $situacaoOrdemServico->situacao_ordem_servico,
                    'value' => $situacaoOrdemServico->id_status_ordem_servico
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Obtém lista de usuários frequentes
     */
    private function getUsuariosFrequentes()
    {
        return Cache::remember('usuarios_frequentes', now()->addHours(12), function () {
            return User::orderBy('name')
                ->limit(20)
                ->get(['id as value', 'name as label'])
                ->values()
                ->toArray();
        });
    }

    /**
     * Obtém serviços recentes
     */
    private function getServicosRecentes()
    {
        return OrdemServicoServicos::orderBy('id_ordem_servico_serv')
            ->with('servicos')
            ->limit(20)
            ->get()
            ->map(function ($servicos) {
                return [
                    'label' => $servicos->id_ordem_servico_serv,
                    'value' => $servicos->id_ordem_servico,
                    'data_inclusao' => $servicos->data_inclusao,
                    'fornecedor' => $servicos->fornecedor->nome_fornecedor ?? "Não informado",
                    'manutencao' => $servicos->manutencao->descricao_manutencao ?? "Não informado",
                    'servico' => $servicos->servico->descricao_servico ?? "Não informado",
                    'quantidade_servico' => $servicos->quantidade_servico,
                    'valor_servico' => $servicos->valor_servico,
                    'valor_tota_com_desconto' => $servicos->valor_total_com_desconto,
                    'finalizado' => $servicos->finalizado,
                    'status' => $servicos->status_servico
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Obtém fornecedores frequentes
     */
    private function getFornecedoresFrequentes()
    {
        return Fornecedor::where('is_ativo', true)
            ->limit(20)
            ->orderBy('nome_fornecedor')
            ->get(['id_fornecedor as value', db::Raw("CONCAT(id_fornecedor, ' - ', nome_fornecedor) as label")]);
    }

    /**
     * Obtém produtos frequentes
     */
    private function getProdutosFrequentes()
    {
        return Cache::remember('produtos_frequentes', now()->addMinutes(15), function () {
            return Produto::where('is_ativo', true)
                ->limit(20)
                ->orderBy('descricao_produto')
                ->select([
                    'id_produto as value',
                    DB::raw("CONCAT('Cód. Unitop: ', id_produto, ' - ', descricao_produto, ' - ', 'Cód. Fabricante: ', cod_fabricante_) as label")
                ])
                ->get();
        });
    }

    /**
     * Obtém motoristas frequentes
     */
    private function getMotoristasFrequentes()
    {
        return Cache::remember('pessoas_frequentes', now()->addHours(12), function () {
            return Pessoal::where('ativo', true)
                ->limit(20)
                ->orderBy('nome')
                ->get(['id_pessoal as value', 'nome as label']);
        });
    }

    /**
     * Obtém serviços frequentes
     */
    private function getServicosFrequentes()
    {
        return Cache::remember('servicos_frequentes', now()->addMinutes(30), function () {
            return Servico::where('ativo_servico', true)
                ->limit(20)
                ->orderBy('descricao_servico')
                ->get([
                    'id_servico as value',
                    db::Raw("CONCAT(id_servico, ' - ', descricao_servico) as label")
                ]);
        });
    }

    /**
     * Método para ações após salvar a ordem de serviço
     *
     * @param int $idOrdemServico - ID da ordem de serviço salva
     * @param int $idVeiculo - ID do veículo da ordem
     * @param int $idFilialManutencao - ID da filial de manutenção
     * @return void
     */
    private function processarPosSalvamento($idOrdemServico, $idVeiculo, $idFilialManutencao)
    {
        try {
            // Verificar se é necessário inserir serviço de diagnóstico
            if (!$this->consultarServicoDiagnostico($idOrdemServico)) {
                $this->verificarNecessidadeDiagnostico($idOrdemServico);
            }

            // Atualizar valores de produtos e fornecedores
            $this->atualizarValorProdutosFornecedor($idOrdemServico, $idFilialManutencao);

            // Verificar serviços realizados nos últimos 30 dias (possível retorno)
            $this->verificarServicosRecentes($idOrdemServico, $idVeiculo);

            // Inserir serviço mecânico se a OS for interna
            $ordemServico = OrdemServico::find($idOrdemServico);
            if ($ordemServico && $ordemServico->local_manutencao === 'INTERNO') {
                $this->inserirServicoMecanico($idOrdemServico, $idFilialManutencao);
            }
        } catch (Exception $e) {
            Log::error('Erro ao processar ações pós-salvamento: ' . $e->getMessage());
        }
    }

    /**
     * Verifica se existe serviço de diagnóstico na OS
     *
     * @param int $idOrdemServico
     * @return bool
     */
    private function consultarServicoDiagnostico($idOrdemServico)
    {
        // ID padrão para serviço de diagnóstico (ajustar conforme seu sistema)
        $idServicoDiagnostico = 1; // Substitua pelo ID correto do serviço de diagnóstico

        return OrdemServicoServicos::where('id_ordem_servico', $idOrdemServico)
            ->where('id_servicos', $idServicoDiagnostico)
            ->exists();
    }

    /**
     * Exibe diálogo para o usuário sobre inserção de serviço de diagnóstico
     *
     * @param int $idOrdemServico
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarNecessidadeDiagnostico($idOrdemServico)
    {
        // No sistema legado, isso era um TQuestion (prompt de confirmação)
        // Aqui retornaremos um JSON para o frontend exibir o prompt
        return response()->json([
            'action' => 'confirmacao_diagnostico',
            'id_ordem_servico' => $idOrdemServico,
            'mensagem' => 'Deseja inserir o serviço de Diagnóstico para esse veículo?'
        ]);
    }

    /**
     * Insere serviço de diagnóstico após confirmação do usuário
     *
     * @param int $idOrdemServico
     * @return \Illuminate\Http\JsonResponse
     */
    public function inserirDiagnostico($idOrdemServico)
    {
        try {
            // ID padrão para serviço de diagnóstico (ajustar conforme seu sistema)
            $idServicoDiagnostico = 1; // Substitua pelo ID correto
            $idManutencao = 1; // Substitua pelo ID correto da manutenção para diagnóstico
            $idFornecedor = 1; // Carvalima (fornecedor interno)

            // Buscar informações do serviço de diagnóstico
            $servico = Servico::find($idServicoDiagnostico);
            if (!$servico) {
                return response()->json([
                    'success' => false,
                    'message' => 'Serviço de diagnóstico não encontrado'
                ]);
            }

            // Inserir o serviço de diagnóstico
            DB::connection('pgsql')->table('ordem_servico_servicos')->insert([
                'id_ordem_servico' => $idOrdemServico,
                'data_inclusao' => now(),
                'id_manutencao' => $idManutencao,
                'id_fornecedor' => $idFornecedor,
                'id_servicos' => $idServicoDiagnostico,
                'valor_servico' => $servico->valor_referencia ?? 0,
                'quantidade_servico' => 1,
                'valor_descontoservico' => 0,
                'valor_total_com_desconto' => $servico->valor_referencia ?? 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Serviço de diagnóstico inserido com sucesso'
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao inserir diagnóstico: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao inserir diagnóstico: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Atualiza valores de produtos e fornecedores para itens sem fornecedor definido
     *
     * @param int $idOrdemServico
     * @param int $idFilialManutencao
     * @return void
     */
    private function atualizarValorProdutosFornecedor($idOrdemServico, $idFilialManutencao)
    {
        try {
            // Buscar todos os itens de peças da OS
            $pecas = OrdemServicoPecas::where('id_ordem_servico', $idOrdemServico)->get();

            foreach ($pecas as $peca) {
                // Se não tem fornecedor ou o valor é zero, atualizar
                if (!$peca->id_fornecedor || $peca->valor_pecas == 0) {
                    // Buscar valor médio do produto
                    $valorMedio = $this->buscarValorMedioProduto($peca->id_produto);

                    // Atualizar o item
                    $peca->valor_pecas = $valorMedio > 0 ? $valorMedio : $peca->valor_pecas;
                    $peca->id_fornecedor = $peca->id_fornecedor ?: $idFilialManutencao; // Usar a filial se não tiver fornecedor
                    $peca->valor_total_com_desconto = ($peca->valor_pecas * $peca->quantidade) - $peca->valor_desconto;
                    $peca->save();
                }
            }
        } catch (Exception $e) {
            Log::error('Erro ao atualizar valores de produtos/fornecedores: ' . $e->getMessage());
        }
    }

    /**
     * Busca o valor médio de um produto
     *
     * @param int $idProduto
     * @return float
     */
    private function buscarValorMedioProduto($idProduto)
    {
        // Buscar o valor médio do produto nas últimas compras
        $valorMedio = DB::connection('pgsql')->table('pedido_compras AS pc')
            ->join('produto_pedidos AS pp', 'pc.id_pedido_compras', '=', 'pp.id_pedido_compras')
            ->where('pp.id_produto', $idProduto)
            ->where('pc.data_inclusao', '>=', now()->subMonths(6)) // Últimos 6 meses
            ->avg('pp.valor_unitario');

        return $valorMedio ?: 0;
    }

    /**
     * Verifica se existem serviços recentes (últimos 30 dias)
     *
     * @param int $idOrdemServico
     * @param int $idVeiculo
     * @return void
     */
    private function verificarServicosRecentes($idOrdemServico, $idVeiculo)
    {
        try {
            // Buscar todos os serviços da OS atual
            $servicos = OrdemServicoServicos::where('id_ordem_servico', $idOrdemServico)->get();

            foreach ($servicos as $servico) {
                // Buscar serviços similares nos últimos 30 dias
                $servicoAnterior = DB::connection('pgsql')->table('ordem_servico AS os')
                    ->join('ordem_servico_servicos AS oss', 'os.id_ordem_servico', '=', 'oss.id_ordem_servico')
                    ->where('os.id_veiculo', $idVeiculo)
                    ->where('os.id_ordem_servico', '!=', $idOrdemServico)
                    ->where('oss.id_servicos', $servico->id_servicos)
                    ->where('os.data_abertura', '>=', now()->subDays(30))
                    ->where('os.id_status_ordem_servico', 4) // Status finalizado
                    ->orderBy('os.data_abertura', 'desc')
                    ->first(['os.id_ordem_servico', 'os.data_abertura']);

                if ($servicoAnterior) {
                    // Registrar a informação para análise
                    Log::info("Possível retorno detectado: Veículo {$idVeiculo} teve o serviço {$servico->id_servicos} realizado na OS {$servicoAnterior->id_ordem_servico} em {$servicoAnterior->data_abertura}");

                    // Marcar a OS como possível retorno
                    DB::connection('pgsql')->table('ordem_servico')
                        ->where('id_ordem_servico', $idOrdemServico)
                        ->update([
                            'situacao_tipo_os_corretiva' => 4, // Código para retorno
                            'id_os_retorno' => $servicoAnterior->id_ordem_servico
                        ]);

                    // Aqui você pode adicionar uma notificação ou alerta para o usuário
                }
            }
        } catch (Exception $e) {
            Log::error('Erro ao verificar serviços recentes: ' . $e->getMessage());
        }
    }

    /**
     * Insere serviço mecânico para OS interna
     *
     * @param int $idOrdemServico
     * @param int $idFilialManutencao
     * @return void
     */
    private function inserirServicoMecanico($idOrdemServico, $idFilialManutencao)
    {
        try {
            // ID padrão para serviço mecânico (ajustar conforme seu sistema)
            $idServicoMecanico = 2; // Substitua pelo ID correto do serviço mecânico
            $idManutencao = 2; // Substitua pelo ID correto da manutenção mecânica
            $idFornecedor = $idFilialManutencao; // Usar a filial como fornecedor

            // Verificar se já existe o serviço
            $servicoExistente = OrdemServicoServicos::where('id_ordem_servico', $idOrdemServico)
                ->where('id_servicos', $idServicoMecanico)
                ->exists();

            if (!$servicoExistente) {
                // Buscar informações do serviço
                $servico = Servico::find($idServicoMecanico);
                if (!$servico) {
                    throw new Exception('Serviço mecânico não encontrado');
                }

                // Inserir o serviço mecânico
                DB::connection('pgsql')->table('ordem_servico_servicos')->insert([
                    'id_ordem_servico' => $idOrdemServico,
                    'data_inclusao' => now(),
                    'id_manutencao' => $idManutencao,
                    'id_fornecedor' => $idFornecedor,
                    'id_servicos' => $idServicoMecanico,
                    'valor_servico' => $servico->valor_referencia ?? 0,
                    'quantidade_servico' => 1,
                    'valor_descontoservico' => 0,
                    'valor_total_com_desconto' => $servico->valor_referencia ?? 0
                ]);
            }
        } catch (Exception $e) {
            Log::error('Erro ao inserir serviço mecânico: ' . $e->getMessage());
        }
    }

    public function existservicogerado($idordem)
    {
        $results = OrdemServicoServicos::where('id_ordem_servico', $idordem)
            ->where('status_servico', 'SERVIÇO GERADO')
            ->where('id_fornecedor', '!=', 1)
            ->exists() ? 1 : 0; // Obtém o resultado

        if ($results == 1) {
            $results == true;
        } else {
            $results == false;
        }

        return $results;
    }

    public function ExisteSerivcoPecas($idOrdem)
    {
        if (!empty($idOrdem)) {
            $pecas = OrdemServicoPecas::where('id_ordem_servico', '=', $idOrdem)->first();
            $servicos = OrdemServicoServicos::where('id_ordem_servico', '=', $idOrdem)->first();
            if (!empty($pecas) || !empty($servicos)) {
                if ($pecas->situacao_pecas !== 'APLICAÇÃO PNEU FINALIZADA') {
                    return true;
                } else {
                    return false;
                }
                return true;
            }
            return false;
        }
    }

    public function carregarUnidadeProduto(Request $request)
    {
        try {
            $idFornecedor   = $request->idFornecedor;
            $idProduto      = $request->idProduto;
            $idOrdemServico = $request->idOrdemServico;
            $idFilial       = $request->idFilial;

            $retorno        = $this->retornarValorPecasContrato($idFornecedor, $idProduto, $idOrdemServico);

            if (!empty($idProduto)) {
                $ativo = Produto::where('id_produto', $idProduto)->first();
                if (!$ativo->is_ativo) {
                    return response()->json([
                        'success' => false,
                        'message' => "O produto $ativo->descricao_produto está atualmente como inativo, consultar o cadastro do produto."
                    ]);
                }

                $estoque = null;
                if (!empty($idProduto) && $idFornecedor == 1) {
                    $estoque = ProdutosPorFilial::where('id_produto_unitop', $idProduto)->where('id_filial', $idFilial)->first();
                }


                //$enviarGrupo[$grupo] = $grupoValor->descricao_grupo;
                if (!empty($idProduto)) {
                    $produto = Produto::where('id_produto', $idProduto)->first();
                    $idUnidade = $produto->id_unidade_produto;
                    $descrUnidade = $produto->UnidadeProduto->descricao_unidade;
                }

                $precoMedio     = $this->BuscarPrecoMedio($idProduto, $idFilial);
                $isimobilizado  = $this->BuscarImobilizado($idProduto);
                $grupo          = $this->BuscarGrupo($idProduto);

                $obj = new stdClass();

                $valorContrato = null;

                if (!empty($retorno)) {
                    $obj->id_contrato = $retorno[1];

                    if ($retorno[1] != null) {
                        $valorContrato  = number_format($retorno[0], 2, ',', '.');
                    }
                }

                if (!empty($grupo)) {
                    $desc_grupo = $this->BuscarDescricaoGrupo($grupo);
                }

                if ($isimobilizado == true) {
                    $object = new stdClass();
                    $object->quantidade = 1;
                }

                $obj->valor_pecas  = $valorContrato != null ? $valorContrato : $precoMedio;
                $obj->estoque_     = $estoque == null ? 0 : $estoque->quantidade_produto;
                $obj->id_unidade   = $idUnidade;
                $obj->desc_unidade = $descrUnidade;
                $obj->id_grupo     = $grupo;
                $obj->desc_grupo   = $desc_grupo;

                return response()->json([
                    'success' => true,
                    'obj' => $obj
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erro ao carregar dados do produto: " . $e->getMessage()
            ]);
        }
    }

    public function retornarValorPecasContrato($idFornecedor, $idProduto, $idOrdemServico)
    {
        if (!empty($idFornecedor) && !empty($idProduto) && !empty($idOrdemServico)) {

            $ordem = OrdemServico::find($idOrdemServico);
            $veiculo = Veiculo::find($ordem->id_veiculo);

            $result = DB::connection('pgsql')->table('contrato_fornecedor as cf')
                ->join('pecas_fornecedor as pf', function ($join) {
                    $join->on('pf.id_contrato_forn', '=', 'cf.id_contrato_forn')
                        ->where('cf.is_valido', '!=', false);
                })
                ->leftJoin('contrato_modelo as cm', function ($join) use ($veiculo) {
                    $join->on('cm.id_contrato_modelo', '=', 'pf.id_contrato_modelo');
                    if ($veiculo->id_modelo_veiculo) {
                        $join->where('cm.id_modelo', $veiculo->id_modelo_veiculo)
                            ->where('cm.ativo', '!=', false);
                    } else {
                        $join->where('cm.ativo', '!=', false);
                    }
                })
                ->where('pf.id_produto', $idProduto)
                ->where('pf.id_fornecedor', $idFornecedor)
                ->where(function ($query) {
                    $query->whereNotNull('cm.id_contrato_modelo')
                        ->orWhere(DB::raw('1'), '=', DB::raw('1'));
                })
                ->orderBy('pf.data_inclusao', 'desc')
                ->select('pf.valor_produto', 'pf.id_contrato_forn')
                ->first();

            $array = [
                0 => $result ? $result->valor_produto : null,
                1 => $result ? $result->id_contrato_forn : null
            ];

            return $array;
        }
    }

    public static function BuscarPrecoMedio($idProduto, $idFilial)
    {
        if (!empty($idProduto)) {
            $produto = ProdutosPorFilial::where('id_produto_unitop', $idProduto)->where('id_filial', $idFilial)->first();

            $valor_limpo = str_replace(["R$", " ", "."], "", $produto->valor_medio);
            $valor_numerico = str_replace(",", ".", $valor_limpo);

            $precoMedio = empty($valor_numerico) ? number_format(0, 2, ",", ".") : number_format($valor_numerico, 2, ",", ".");

            return $precoMedio;
        }
    }

    public static function BuscarGrupo($idProduto)
    {
        if (!empty($idProduto)) {
            $produto = Produto::where('id_produto', $idProduto)->first();

            return $produto->id_grupo_servico;
        }
    }

    public static function BuscarImobilizado($idProduto)
    {
        if (!empty($idProduto)) {
            $produto = Produto::where('id_produto', $idProduto)->first();

            return $produto->is_imobilizado;
        }
    }

    public static function BuscarProdutoPorCodigoFornecedo($codFornecedor)
    {
        if (!empty($codFornecedor)) {
            $produto = Produto::where('cod_fabricante_', $codFornecedor)->first();

            return $produto->id_produto;
        }
    }

    public static function BuscarDescricaoGrupo($id_grupo)
    {
        if (!empty($id_grupo)) {
            $desc_grupo = GrupoServico::where('id_grupo', $id_grupo)->first();

            return $desc_grupo->descricao_grupo;
        }
    }

    public function ValidarManutencaoVinculada($idOS)
    {
        try {
            if (!empty($idOS)) {

                $query = "SELECT
                            CASE
                                WHEN EXISTS(
                                    SELECT id_manutencao
                                    FROM ordem_servico_servicos AS oss
                                    WHERE oss.id_manutencao IS NOT NULL
                                    AND oss.id_ordem_servico = ?
                                ) THEN TRUE ELSE FALSE
                            END AS validacao_manutencao";

                $objects = DB::connection('pgsql')->select($query, [$idOS]);

                $Retorno = $objects[0]->validacao_manutencao;

                return $Retorno;
            }
        } catch (Exception $e) {
            LOG::ERROR('error', $e->getMessage());
        }
    }

    public function onYesEncerrar($idOrdem, $idveiculo, $kmAtual, $valor)
    {
        try {
            // Validação 1: Verificar se a ordem existe
            if (empty($idOrdem)) {
                return response()->json([
                    'success' => false,
                    'message' => "Atenção: Ordem de serviço não foi gerada, não será possível encerra-la."
                ], 400);
            }

            // Validação 2: Verificar o KM ANTES de fazer qualquer operação
            if ($kmAtual == 0 || empty($kmAtual)) {
                return response()->json([
                    'success' => false,
                    'message' => "Atenção: O Km informado não pode ser Zero, por favor informe o km correto para o veículo."
                ], 400);
            }

            // Se passou nas validações, prosseguir com o encerramento
            $this->EncerrarOS($idOrdem);

            if (!$this->RetornarExisteServicoInternoStatic($idOrdem)) {
                $this->InserirRecepcionistaEncerramento($valor, 4, $idOrdem);
            } else {
                $this->InserirRecepcionistaEncerramento($valor, 8, $idOrdem);
            }

            $idSascar       = Veiculo::where('id_veiculo', $idveiculo)->value('id_sascar');
            $dataAbertura   = now()->format('Y-m-d H:i:s');
            $KmRetroativo   = $this->BuscarKmRetroativo($idSascar, $dataAbertura);
            $this->atualizarPreventivas($idveiculo);

            if (!empty($idSascar)) {
                if (empty($KmRetroativo)) {
                    $KmAbastecimento = $this->BuscarKmAbastecimentoStatico($idveiculo, $dataAbertura);
                    if (empty($KmAbastecimento)) {
                        $km = $kmAtual;
                    } else {
                        $km = $KmAbastecimento;
                    }
                } else {
                    $km = $KmRetroativo;
                }

                $this->InserirKmEncerramento($km, $idOrdem);
            } else {
                $km = $kmAtual;
                $this->InserirKmEncerramento($km, $idOrdem);
            }

            $idos      = OrdemServico::where('id_ordem_servico', $idOrdem)->first();
            $veiculo   = Veiculo::where('id_veiculo', $idos->id_veiculo)->first();
            $motorista = Pessoal::where('id_pessoal', $idos->id_motorista)->first();

            // Enviar mensagem apenas se houver motorista e telefone
            if ($motorista && !empty($idos->telefone_motorista)) {
                $telefone = preg_replace('/[^\d]/', '', $idos->telefone_motorista);
                $nome     = $motorista->nome;

                if (!empty($telefone)) {
                    $texto = "*Atenção:* $nome. \n"
                        . "O.S Preventiva N° " . $idOrdem . " encerrada para o veículo: {$veiculo->placa}.\n";
                    $this->enviarMensagem($texto, "$nome", "$telefone");
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Ordem de serviço encerrada com sucesso'
            ], 200);
        } catch (Exception $e) {
            Log::error('Erro ao encerrar a O.S.: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao encerrar ordem de serviço: ' . $e->getMessage()
            ], 500);
        }
    }

    public function EncerrarOS($idOrdem)
    {
        Log::debug("Iniciando encerramento da OS: $idOrdem");
        // if (!empty($idOrdem)) {
        //     $objects = DB::connection('pgsql')->select("SELECT * FROM fc_encerrar_os(?)", [$idOrdem]);

        //     if (in_array(1, $objects)) {
        //         return true;
        //     } else {
        //         return false;
        //     }
        // }
    }

    public function RetornarExisteServicoInternoStatic($idOrdem)
    {
        if (!empty($idOrdem)) {

            $query = "SELECT
                        	id_ordem_servico_serv
                        FROM ordem_servico_servicos nf
                        WHERE nf.id_ordem_servico = ?
                        AND nf.id_fornecedor NOT IN (
                        	SELECT
                        		oss.id_fornecedor
                        	FROM nf_ordem_servico oss
                        	WHERE oss.id_ordem_servico = ?
                        	AND oss.id_nf_compra_servico IS NOT NULL
                        )
                        AND nf.id_fornecedor NOT IN (
                        	SELECT
                        		f.id_fornecedor
                        	FROM fornecedor f
                        	WHERE f.nome_fornecedor @@ 'Carvalima'
                        )";


            $object = DB::connection('pgsql')->select($query, [$idOrdem, $idOrdem]);

            if ($object) {
                foreach ($object as $item) {
                    $Fornecedor = $item->id_ordem_servico_serv;
                }
            }

            if (!empty($Fornecedor)) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function InserirRecepcionistaEncerramento($idUser, $idStatus, $idOrdem)
    {
        if (!empty($idUser) && !empty($idStatus) && !empty($idOrdem)) {

            $Recepcionista = OrdemServico::where('id_ordem_servico', $idOrdem)->first();

            $Recepcionista->data_alteracao                = now();
            $Recepcionista->id_recepcionista_encerramento = $idUser;
            $Recepcionista->id_status_ordem_servico       = $idStatus;

            $Recepcionista->update();
        }
    }

    public function BuscarKmRetroativo($idsascar, $dataabertura)
    {
        $dataabertura = str_replace('T', ' ', $dataabertura);
        if (!empty($idsascar) && !empty($dataabertura)) {
            $objects = DB::connection('pgsql')->select("SELECT * FROM fc_km_retroativo_os(?, ?::TIMESTAMP)", [$idsascar, $dataabertura]);
            $retorno = $objects[0];
            return $retorno->fc_km_retroativo_os;
        }
    }

    public function atualizarPreventivas($idVeiculo)
    {
        if (!empty($idVeiculo)) {
            $query = "SELECT * FROM fc_processar_manutencoes_vencidas(?)";

            DB::connection('pgsql')->select($query, [$idVeiculo]);
        }
    }

    public static function BuscarKmAbastecimentoStatico($idVeiculo, $data)
    {
        if (!empty($idVeiculo) && !empty($data)) {
            $objects = DB::connection('pgsql')->select("SELECT
                                        lt.km_abastecimento
                                    FROM v_abastecimento_listar_todos AS lt
                                    JOIN veiculo AS v ON lt.placa = v.placa
                                    WHERE v.id_veiculo = ?
                                    AND lt.data_inicio <= ?
                                    ORDER BY
                                        lt.data_inicio
                                    DESC
                                    LIMIT 1", [intval($idVeiculo), $data]);

            if ($objects) {
                foreach ($objects as $object) {
                    $Km = $object->km_abastecimento;
                }
            }


            $Km = empty($Km) ? 0 : $Km;
            return $Km;
        }
    }

    public function InserirKmEncerramento($Km, $idOrdem)
    {
        if (!empty($Km) && !empty($idOrdem)) {
            $Recepcionista = OrdemServico::where('id_ordem_servico', $idOrdem)->first();

            $Recepcionista->data_alteracao = now();
            $Recepcionista->km_encerramento = $Km;

            $Recepcionista->update();
        }
    }

    public function enviarMensagem($mensagem, $nome, $numero)
    {
        $tokem          = "ad8d672c-b196-4f2e-bf24-aaa8523ef258";
        $accountId      = 2;
        $whatsappId     = 95;
        $messageTimeout = 600;
        $queued         = true;
        $from           = "UNITOP";
        $url            = "https://api.sacflow.io/api/send-text";

        $body = json_encode(array(
            "accountId" => $accountId,
            "whatsappId" => $whatsappId,
            "message" => $mensagem,
            "messageTimeout" => $messageTimeout,
            "from" => $from,
            "contact" => array(
                "name" => $nome,
                "phone" => $numero
            ),
            "queued" => true
        ));

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $tokem",
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Erro na requisição: ' . curl_error($ch);
        }

        curl_close($ch);

        return $response;
    }

    public function validarKMAtual(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        try {
            $veiculo      = Veiculo::where('placa', $request->veiculo)->first();
            $kmAtual      = $request->km;
            $dataAbertura = $request->data;

            if (!$veiculo->is_terceiro && isset($kmAtual)) {
                $kmAbastecimento = $this->BuscarKmAbastecimentoStatico($veiculo->id_veiculo, $dataAbertura); //retorna o último abastecimento conforme a data de abertura
                return response()->json(['success' => true, 'valid' => $kmAtual < $kmAbastecimento]); //$kmAtual < $kmAbastecimento;
            }
        } catch (\Exception $e) {
            log::error('Erro ao validar Km: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao validar Km: ' . $e->getMessage()
            ]); //$kmAtual < $kmAbastecimento;
        }
    }

    public function onvalidarkmabastecimento($kmAtual, $idVeiculo, $dataAbertura)
    {
        try {
            if (!$this->IsVeiculoTerceiroStatico($idVeiculo) && !empty($kmAtual)) //veirfica se o veículo é terceiro
            {
                $kmAbastecimento = $this->BuscarKmAbastecimentoStatico($idVeiculo, $dataAbertura); //retorna o último abastecimento conforme a data de abertura
                return $kmAtual < $kmAbastecimento;
            }
        } catch (\Exception $e) {
            LOG::ERROR('error', $e->getMessage());

            return redirect()->back()->with('notification', [
                'type' => 'error',
                'title' => 'Operação não concluída',
                'message' => "Erro ao validar Km: " . $e->getMessage(),
                'duration' => 5000, // opcional (padrão: 5000ms)
            ]);
        }
    }

    public function IsVeiculoTerceiroStatico($idVeiculo)
    {
        if ($idVeiculo) {
            $veiculo = Veiculo::where('id_veiculo', $idVeiculo)->first();
            return $veiculo->is_terceiro;
        }
    }

    public function carregarKm(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        try {
            if (is_numeric($request->id_veiculo)) {
                $idveiculo  = $request->id_veiculo;
            } else {
                $idveiculo  = Veiculo::where('placa', $request->id_veiculo)->value('id_veiculo');
            }
            $dataAbertura   = $request->data_abertura;
            $idSascar       = Veiculo::where('id_veiculo', $idveiculo)->first()->id_sascar;
            $placa          = Veiculo::where('id_veiculo', $idveiculo)->first()->placa;

            $iscarreta      = Veiculo::where('id_veiculo', $idveiculo)
                ->where(function ($query) {
                    $query->where('is_possui_tracao', '!=', true)
                        ->orWhereNull('is_possui_tracao');
                })
                ->whereNotIn('id_tipo_equipamento', [1, 2, 3, 52, 53, 54, 40, 44, 71, 49])
                ->whereRaw("TRIM(placa) NOT LIKE ?", ['%TK'])
                ->exists();

            $KmRetroativo    = $this->BuscarKmRetroativo($idSascar, $dataAbertura);
            $kmAbastecimento = $this->BuscarKmAbastecimentoStatico($idveiculo, $dataAbertura);

            $KmRetroativo = intval($KmRetroativo);
            $kmAbastecimento = intval($kmAbastecimento);

            $statusEncerramento = [4, 6, 8];

            $ordemServico  = OrdemServico::where('id_veiculo', $idveiculo)
                ->where('id_tipo_ordem_servico', 2)
                ->whereNotIn('id_status_ordem_servico', $statusEncerramento)
                ->where('is_cancelada', '!=', true)
                ->first();

            $idOrdemAberta = $ordemServico ? $ordemServico->id_ordem_servico : null;

            if ($idOrdemAberta != null) {
                $insconsistenci = DB::connection('pgsql')->select("SELECT * FROM fc_abastecimento_inconsistencia_mensal(?)", [$idveiculo]);
            }

            if (!empty($insconsistenci)) {
                return response()->json([
                    'success' => false,
                    'message' => "Atenção: Não é possível prosseguir com a ação pois a placa $placa possui os abastecimentos '$insconsistenci' na inconsistência ATS."
                ], 400);
            }

            if (!empty($KmRetroativo)) { //&& !empty($idSascar)

                if ($KmRetroativo == 0) {
                    $km = $kmAbastecimento;
                } else {
                    $km = $KmRetroativo;
                }

                // Retorna os dados de quilometragem com sucesso
                return response()->json([
                    'success' => true,
                    'data' => [
                        'km' => $km
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Atenção: Não foi possível encontrar o km através do rastreador desse veículo.'
                ], 400);
            }


            if (!empty($idOrdemAberta)) {
                return response()->json([
                    'success' => false,
                    'message' => "Atenção: A ordem de serviço corretiva n°: $idOrdemAberta já está aberta e registrada para esse veículo."
                ], 400);
            }

            if ($this->IsVeiculoTerceiroStatico($idveiculo) ==  true) {
                return response()->json([
                    'success' => false,
                    'message' => "Atenção: Veículo Informado é um veículo de Terceiro."
                ], 400);
            }

            if ($km == 0 && !empty($idveiculo)) {
                return response()->json([
                    'success' => false,
                    'message' => "Km do veiculo não encontrado, verificar o histórico de KM."
                ], 400);
            }

            if ($iscarreta == 1) {
                return response()->json([
                    'success' => false,
                    'message' => "Veículo informado é uma carreta."
                ], 400);
            }
        } catch (\Exception $e) {
            LOG::ERROR('Erro ao carregar o KM' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => "Erro ao consultar KM: " . $e->getMessage()
            ], 400);
        }
    }

    public function RetornarExisteProdutoInterno($idOrdem)
    {
        if (!empty($idOrdem)) {
            $query =   "SELECT osp.id_ordem_servico_pecas --comentado Edson 23/05/2025 15:00 pois não validava se o serviço tinha NF, só validava se estava finalizado.
                        FROM ordem_servico_pecas AS osp
                        WHERE osp.id_ordem_servico = $idOrdem
                        AND osp.situacao_pecas NOT LIKE '%COMPRA DE PEÇA APROVADA%'
                        AND osp.id_fornecedor NOT IN (
                        SELECT
                            f.id_fornecedor
                        FROM fornecedor f
                        WHERE f.nome_fornecedor @@ 'Carvalima'
                        );";

            $object = DB::connection('pgsql')->select($query);

            if ($object) {
                foreach ($object as $item) {
                    $Fornecedor = $item->id_ordem_servico_pecas;
                }
            }

            if (!empty($Fornecedor)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Método auxiliar para preparar o logo
     */
    private function prepararLogo(): ?string
    {
        try {
            $svgPath = public_path('images/logo_carvalima.svg');

            if (!file_exists($svgPath)) {
                Log::warning('Logo não encontrado no caminho: ' . $svgPath);
                return null;
            }

            $svgContent = file_get_contents($svgPath);

            if ($svgContent === false) {
                Log::warning('Erro ao ler conteúdo do logo');
                return null;
            }

            return base64_encode($svgContent);
        } catch (\Exception $e) {
            Log::error('Erro ao processar logo: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Método para validar dados antes da geração do PDF (opcional)
     */
    private function validarDadosOrdemServico(OrdemServico $ordemServico): bool
    {
        // Validações básicas
        if (!$ordemServico->id_ordem_servico) {
            return false;
        }

        // Adicione outras validações conforme necessário

        return true;
    }

    public function getManutencao(Request $request)
    {
        $id = $request->idVeiculo;
        $veiculo = Veiculo::findOrFail($id);
        $idPlanejamento = CategoriaPlanejamentoManutencao::where('id_categoria', $veiculo->id_categoria)->pluck('id_planejamento');
        $idManutencao = PlanejamentoManutencao::whereIn('id_planejamento_manutencao', $idPlanejamento)->pluck('id_manutencao');

        $manutencoes = manutencao::whereIn('id_manutencao', $idManutencao)->get();

        // Transformar para o formato value/label
        $lista = $manutencoes->map(function ($item) {
            return [
                'value' => $item->id_manutencao,
                'label' => $item->descricao_manutencao,
            ];
        });

        return response()->json($lista);
    }

    public function inserirServicosePecas(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $dados = $request[0];
        $id_os = intval($dados['id_ordem_servico']);
        $idmanutencao = $dados['id_manutencao'];
        $idFornecedor = $dados['id_fornecedor'];

        if (!empty($id_os) && !empty($idmanutencao) && !empty($idFornecedor)) {
            try {

                $retorno = DB::connection('pgsql')->select(
                    "SELECT * FROM fc_inserir_servicos_pecas_os(?, ?, ?)",
                    [$id_os, $idmanutencao, $idFornecedor]
                );

                $retorno = $retorno[0]->fc_inserir_servicos_pecas_os;

                if ($retorno == 1) {
                    return response()->json([
                        'success' => true,
                        'message' => "Os serviços e peças foram inseridos com sucesso!"
                    ], 200);
                } else {
                    LOG::ERROR('Erro ao inserir serviços e peças: ' . $retorno);
                    return response()->json([
                        'success' => false,
                        'message' => "Erro ao inserir serviços e peças"
                    ], 400);
                }
            } catch (\Exception $e) {
                LOG::ERROR('Erro ao inserir serviços e peças: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => "Erro ao inserir serviços e peças: " . $e->getMessage()
                ], 400);
            }
        }
    }

    public function getTiposCorretiva()
    {
        return [
            ['value' => 1, 'label' => 'Investimento'],
            ['value' => 2, 'label' => 'Sinistro'],
            ['value' => 3, 'label' => 'Socorro'],
            ['value' => 4, 'label' => 'Retorno'],
            ['value' => 5, 'label' => 'Programada'],
            ['value' => 6, 'label' => 'Borracharia']
        ];
    }

    public function getGrupoResolvedor()
    {
        return GrupoResolvedor::select('id_grupo_resolvedor as value', 'descricao_grupo_resolvedor as label')->get()->toArray();
    }

    public function processItemsSocorro($validated, $ordemServicoId)
    {
        try {
            // Se o payload NÃO incluiu 'tabelaSocorro', não alteramos os registros existentes
            if (!array_key_exists('tabelaSocorro', $validated)) {
                return;
            }

            $socorros = json_decode($validated['tabelaSocorro'], true);

            // Se veio algo que não é array (ex: null ou formato inválido), não prosseguir
            if (!is_array($socorros)) {
                // Se for uma string vazia ou null, interpretar como remoção intencional
                if (empty($validated['tabelaSocorro'])) {
                    DB::beginTransaction();
                    DB::table('socorro_ordem_servico')->where('id_ordem_servico', $ordemServicoId)->delete();
                    DB::commit();
                }
                return;
            }

            // Se o array for vazio: só removemos se houver flag explícita 'tabelaSocorro_remover' = true
            if (is_array($socorros) && count($socorros) === 0) {
                if (!empty($validated['tabelaSocorro_remover'])) {
                    DB::beginTransaction();
                    DB::table('socorro_ordem_servico')->where('id_ordem_servico', $ordemServicoId)->delete();
                    DB::commit();
                }
                // Se não houver a flag, não fazemos nada (preservar registros existentes)
                return;
            }

            // Caso tenha itens, aplicamos upsert por item:
            // - Se o item referencia um registro existente (vários formatos de chave aceitos),
            //   atualizamos apenas os campos que vieram no payload.
            // - Se não referencia, inserimos novo registro.
            DB::beginTransaction();
            foreach ($socorros as $item) {
                // Possíveis chaves de identificação que o frontend pode enviar
                $possibleIdKeys = ['idSocorro', 'id', 'id_socorro_ordem_servico', 'idSocorroOrdemServico', 'id_socorro'];
                $foundId = null;
                foreach ($possibleIdKeys as $k) {
                    if (isset($item[$k]) && !empty($item[$k])) {
                        $foundId = $item[$k];
                        break;
                    }
                }

                if ($foundId) {
                    $existing = SocorroOrdemServico::find($foundId);
                    if ($existing) {
                        // Atualizar apenas campos enviados
                        $updated = false;
                        if (array_key_exists('idVeiculo', $item)) {
                            $existing->id_veiculo = $item['idVeiculo'];
                            $updated = true;
                        }
                        if (array_key_exists('idSocorrista', $item)) {
                            $existing->id_socorrista = $item['idSocorrista'];
                            $updated = true;
                        }
                        if (array_key_exists('idLocalSocorro', $item)) {
                            $existing->local_socorro = $item['idLocalSocorro'];
                            $updated = true;
                        }

                        if ($updated) {
                            $existing->data_alteracao = now();
                            $existing->save();
                        }
                        continue;
                    }
                    // se o id foi fornecido mas não existe no banco, criamos novo
                }

                // Inserir novo registro (campos podem ser nulos se frontend não enviou —
                // a validação deve assegurar obrigatoriedade quando necessário)
                $socorro = new SocorroOrdemServico();
                $socorro->id_ordem_servico = $ordemServicoId;
                $socorro->data_inclusao    = now();
                if (array_key_exists('idVeiculo', $item)) {
                    $socorro->id_veiculo = $item['idVeiculo'];
                }
                if (array_key_exists('idSocorrista', $item)) {
                    $socorro->id_socorrista = $item['idSocorrista'];
                }
                if (array_key_exists('idLocalSocorro', $item)) {
                    $socorro->local_socorro = $item['idLocalSocorro'];
                }

                $socorro->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            LOG::ERROR('ERRO AO GRAVAR ITEM DE SOCORRO: ' . $e->getMessage());
        }
    }

    public function ValorServicoXFornecedor(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        if (isset($request->id_veiculo) && is_string($request->id_veiculo)) {
            $veiculo = Veiculo::where('placa', $request->id_veiculo)->first();
        } else {
            $veiculo = Veiculo::where('id_veiculo', $request->id_veiculo)->first();
        }

        $idVeiculo = $veiculo->id_veiculo;
        $idFornecedor = $request->id_fornecedor;
        $idServico = $request->id_servico;


        if (isset($idFornecedor) && isset($idServico) && isset($idVeiculo)) {

            $modelo = $veiculo->id_modelo_veiculo != null ? $veiculo->id_modelo_veiculo . ' AND cm.ativo IS NOT FALSE' : 'cm.id_modelo';

            if (!empty($modelo)) {

                $query =
                    "SELECT DISTINCT
                	sf.data_inclusao,
                	sf.valor_servico AS valor_servico_fornecedor,
                	sf.id_fornecedor,
                	cf.id_contrato_forn AS id_contrato
                FROM contrato_fornecedor cf
                	INNER JOIN servico_fornecedor sf ON sf.id_contrato_forn = cf.id_contrato_forn AND cf.is_valido IS NOT FALSE
                	LEFT JOIN contrato_modelo cm ON cm.id_contrato = cf.id_contrato_forn AND cm.id_modelo = $modelo
                WHERE sf.id_servico = $idServico
                AND sf.id_fornecedor = $idFornecedor
                AND
                	CASE
                		WHEN (COALESCE(sf.id_contrato_modelo,0) = cm.id_contrato_modelo)
                			THEN sf.id_contrato_modelo = cm.id_contrato_modelo
                		ELSE
                			sf.id_contrato_modelo IS NULL
                	END
                ORDER BY
                	sf.data_inclusao
                DESC
                LIMIT 1";

                $objects = DB::select($query);

                if ($objects) {
                    foreach ($objects as $object) {
                        if ($this->validarContrato($object->id_contrato)) {
                            $Retorno[0] = doubleval($object->valor_servico_fornecedor);
                            $Retorno[1] = $object->id_contrato;
                        }
                    }
                }
            }

            if (empty($Retorno[0])) {
                $retorno_   = ServicoFornecedor::where('id_fornecedor', '=', $idFornecedor)->where('id_servico', '=', $idServico)->first(); // -> Edson 26/11/24 10:00 estava pegando da tabela errada para retornar o valor caso não tenha contrato por modelo, tabela que estava consultando (servicoxfornecedor)
                if (!empty($retorno_)) {
                    $Retorno[2] = doubleval($retorno_->valor_servico_fornecedor);
                }
                $Retorno[2] = 0;
                $contrato = 0;
            }

            if (!empty($Retorno[0])) {
                $valorServico = number_format($Retorno[0], 2, ',', '.');
                $contrato = $Retorno[1];
            } else {
                $valorServico = number_format($Retorno[2], 2, ',', '.');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'valorServico' => $valorServico,
                    'contrato' => $contrato
                ]
            ], 200);
        }
    }

    public function validarContrato($idContrato)
    {
        if (!empty($idContrato)) {
            $query0 =
                "SELECT
        			SUM(COALESCE(sp.valor_total_desconto,0)) AS total_gasto
        		FROM pedido_compras pc
        			INNER JOIN solicitacoescompras sc ON sc.id_solicitacoes_compras = pc.id_solicitacoes_compras
        			INNER JOIN servicossolicitacoescompras ssc ON ssc.id_solicitacao_compra = sc.id_solicitacoes_compras
        			INNER JOIN ordem_servico_servicos oss ON oss.id_ordem_servico_serv = ssc.id_ordem_servico_serv
        			INNER JOIN servico_pedidos sp ON sp.id_pedido_compras = pc.id_pedido_compras AND oss.id_servicos = sp.id_servico
        		WHERE oss.id_contrato IN ($idContrato);";

            $query1 =
                "SELECT
                	COALESCE(cf.valor_contrato,100000) AS saldo_contrato,
                	CASE
                	    WHEN (CURRENT_DATE BETWEEN COALESCE(cf.data_inicial,CURRENT_DATE-1) AND COALESCE(cf.data_final,CURRENT_DATE+1))
                    		THEN
                    			TRUE
                    		ELSE
                    			FALSE
                	END AS no_prazo
                FROM contrato_fornecedor cf
                WHERE cf.id_contrato_forn = $idContrato;";


            $object = db::select($query0);

            if ($object) {
                foreach ($object as $item) {
                    $saldoConsumido = $item->total_gasto != null ? $item->total_gasto : 0;
                }
            }

            $objects = db::select($query1);

            if ($objects) {
                foreach ($objects as $item) {
                    $saldoTotal = $item->saldo_contrato;
                    $prazo = $item->no_prazo;
                }
            }


            if ($prazo == true && ($saldoTotal > $saldoConsumido)) {
                $retorno = true;
            } else {
                $query2 =
                    "UPDATE contrato_fornecedor cf
                        SET is_valido = FALSE
                        WHERE cf.id_contrato_forn = $idContrato";

                db::select($query2);

                $retorno = false;
            }

            return $retorno;
        }
    }

    public function onFinalizarServico(Request $param)
    {
        $this->normalizeSmartSelectParams($param);
        try {
            if (!empty($param["idSelecionado"])) {
                $servicos = [];
                foreach ($param["idSelecionado"] as $check_id) {
                    $servicos[] = $check_id;
                }

                if (!empty($servicos)) {
                    $placeholders_in = implode(",", array_fill(0, count($servicos), "?"));

                    // Verifica se já existem serviços finalizados
                    $result_finalizados = "
                SELECT
                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM ordem_servico_servicos AS oss
                            WHERE oss.id_ordem_servico_serv IN ($placeholders_in)
                            AND oss.finalizado IS TRUE
                        ) THEN 'Existe Serviço selecionado já finalizado'
                        ELSE 'Existem serviços não finalizados'
                    END AS resultado,
                    STRING_AGG(oss.id_ordem_servico_serv::TEXT, ', ') AS ids_finalizados
                FROM ordem_servico_servicos AS oss
                WHERE oss.id_ordem_servico_serv IN ($placeholders_in)
                AND oss.finalizado IS TRUE";

                    $params_finalizados = array_merge($servicos, $servicos);
                    $retorno = DB::select($result_finalizados, $params_finalizados);

                    $exists = null;
                    $ids = null;
                    if ($retorno) {
                        foreach ($retorno as $object) {
                            $exists = $object->resultado;
                            $ids    = $object->ids_finalizados;
                        }
                    }

                    $servicos_str_for_sql = implode(",", $servicos);

                    // Verifica se todos têm pedido de compra
                    $result_sem_pedido = "SELECT
                        CASE
                            WHEN SUM(CASE WHEN total_pedidos = 0 THEN 1 ELSE 0 END) > 0
                            THEN 'Existe pelo menos uma ordem de serviço sem pedido de compra'
                            ELSE 'Todos os registros têm pedido de compra gerado'
                        END AS mensagem_validacao,
                        STRING_AGG(CASE WHEN total_pedidos = 0 THEN subquery.id_ordem_servico_serv::TEXT END, ', ') AS ids_sem_pedido
                        FROM (
                            SELECT
                                ssc.id_ordem_servico_serv,
                                COUNT(pc.id_pedido_compras) AS total_pedidos
                            FROM
                                (SELECT unnest(string_to_array(?, ','))::integer AS id_ordem_servico_serv) AS ssc
                            LEFT JOIN
                                servicossolicitacoescompras sc ON sc.id_ordem_servico_serv = ssc.id_ordem_servico_serv
                            LEFT JOIN
                                pedido_compras pc ON pc.id_solicitacoes_compras = sc.id_solicitacao_compra
                            GROUP BY
                                ssc.id_ordem_servico_serv
                        ) subquery";

                    $objects = DB::select($result_sem_pedido, [$servicos_str_for_sql]);

                    $existsempedido = null;
                    $idsss = null;
                    if ($objects) {
                        foreach ($objects as $object) {
                            $existsempedido = $object->mensagem_validacao;
                            $idsss          = $object->ids_sem_pedido;
                        }
                    }

                    // 🔑 Nova regra: verifica status_servico
                    $servicosStatus = OrdemServicoServicos::whereIn('id_ordem_servico_serv', $servicos)
                        ->pluck('status_servico', 'id_ordem_servico_serv');

                    $servicosComContrato = $servicosStatus->filter(function ($status) {
                        return strtoupper(trim($status)) === 'SERVIÇO COM CONTRATO';
                    });

                    $permitirFinalizarMesmoSemPedido = $servicosComContrato->count() === count($servicos);

                    $ordens = OrdemServicoServicos::whereIn('id_ordem_servico_serv', $servicos)
                        ->pluck('id_ordem_servico');

                    $existeNFServico = OrdemServicoServicos::whereIn('id_ordem_servico', $ordens)
                        ->whereNotNull('numero_nota_fiscal_servicos')
                        ->where('numero_nota_fiscal_servicos', '>', 0)
                        ->exists();

                    $existeNFPeca = OrdemServicoPecas::whereIn('id_ordem_servico', $ordens)
                        ->whereNotNull('numero_nota_fiscal_pecas')
                        ->where('numero_nota_fiscal_pecas', '>', 0)
                        ->exists();

                    if (!$existeNFServico && !$existeNFPeca) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Não é possível finalizar: nenhuma Nota Fiscal foi informada em serviços ou peças.'
                        ], 400);
                    }

                    // ✅ Se está tudo ok OU todos são de contrato, finaliza serviços e peças juntos
                    if (
                        ($exists == 'Existem serviços não finalizados' && $existsempedido == 'Todos os registros têm pedido de compra gerado')
                        || $permitirFinalizarMesmoSemPedido
                    ) {
                        DB::beginTransaction();

                        // Finaliza serviços
                        foreach ($servicos as $porservico) {
                            $new = OrdemServicoServicos::findOrFail($porservico);
                            $new->finalizado = true;
                            $new->data_alteracao = date('Y-m-d H:i:s');
                            $new->save();
                        }

                        // Finaliza peças da mesma OS
                        // $pecas = OrdemServicoPecas::whereIn('id_ordem_servico', $ordens)->get();

                        // foreach ($pecas as $peca) {
                        //     $peca->is_finalizado = true;
                        //     $peca->data_alteracao = date('Y-m-d H:i:s');
                        //     $peca->save();
                        // }

                        // Atualiza status da OS
                        $ordensObjs = OrdemServico::whereIn('id_ordem_servico', $ordens)->get();

                        foreach ($ordensObjs as $ordem) {
                            $ordem->id_status_ordem_servico = 11; // SERVIÇOS FINALIZADOS
                            $ordem->save();
                        }

                        DB::commit();
                        return response()->json(['success' => true, 'message' => 'Serviços e peças finalizados com sucesso!'], 200);
                    } elseif ($exists == 'Existe Serviço selecionado já finalizado') {
                        return response()->json(['success' => false, 'message' => 'Existem Serviços (' . $ids . ') selecionados já finalizados'], 400);
                    } elseif ($existsempedido == 'Existe pelo menos uma ordem de serviço sem pedido de compra') {
                        return response()->json(['success' => false, 'message' => 'Existem ao menos um Serviço (' . $idsss . ') sem pedido de compras'], 400);
                    }
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Nenhum serviço foi selecionado.'], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            LOG::ERROR('Erro ao finalizar serviço/peça: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocorreu um erro: ' . $e->getMessage()], 500);
        }
    }

    public function onYesDestroyServico(Request $param)
    {
        $this->normalizeSmartSelectParams($param);
        try {
            if (isset($param["idSelecionado"])) {
                $pedidos = [];
                foreach ($param["idSelecionado"] as $check_id) {
                    $pedidos[] = $check_id;
                }

                if (!empty($pedidos)) {
                    $placeholders_in = implode(",", array_fill(0, count($pedidos), "?"));

                    $result_finalizados = "SELECT
                                    CASE
                                    WHEN EXISTS (
                                        SELECT oss.id_ordem_servico_serv
                                        FROM ordem_servico_servicos AS oss
                                        WHERE oss.id_ordem_servico_serv IN ($placeholders_in)
                                        AND oss.finalizado IS TRUE
                                    ) THEN 'Existe Serviço selecionado já finalizado'
                                    ELSE 'Existem serviços não finalizados'
                                    END AS resultado,
                                    STRING_AGG(oss.id_ordem_servico_serv::TEXT, ', ') AS ids_finalizados
                                FROM ordem_servico_servicos AS oss
                                WHERE oss.id_ordem_servico_serv IN ($placeholders_in)
                                AND oss.finalizado IS TRUE";

                    $params_finalizados = array_merge($pedidos, $pedidos);
                    $objects_finalizados = DB::select($result_finalizados, $params_finalizados);

                    $exists = null;
                    $ids = null;
                    if ($objects_finalizados) {
                        foreach ($objects_finalizados as $object) {
                            $exists = $object->resultado;
                            $ids    = $object->ids_finalizados;
                        }
                    }

                    $pedidos_str_for_sql = implode(",", $pedidos);

                    $result_sem_pedido = "SELECT
                                CASE
                                    WHEN SUM(CASE WHEN total_pedidos = 0 THEN 1 ELSE 0 END) > 0
                                    THEN 'Existe pelo menos uma ordem de serviço sem pedido de compra'
                                    ELSE 'Todos os registros têm pedido de compra gerado'
                                END AS mensagem_validacao,
                                STRING_AGG(
                                    CASE WHEN total_pedidos = 0 THEN subquery.id_ordem_servico_serv::TEXT END,
                                    ', '
                                ) FILTER (WHERE total_pedidos = 0) AS ids_sem_pedido
                            FROM (
                                SELECT
                                    ssc.id_ordem_servico_serv,
                                    COUNT(pc.id_pedido_compras) AS total_pedidos
                                FROM
                                    UNNEST(string_to_array(?, ',')::INTEGER[]) AS ssc(id_ordem_servico_serv)
                                LEFT JOIN
                                    servicossolicitacoescompras sc ON sc.id_ordem_servico_serv = ssc.id_ordem_servico_serv
                                LEFT JOIN
                                    pedido_compras pc ON pc.id_solicitacoes_compras = sc.id_solicitacao_compra
                                GROUP BY
                                    ssc.id_ordem_servico_serv
                            ) subquery";

                    $objects_sem_pedido = DB::select($result_sem_pedido, [$pedidos_str_for_sql]);

                    $existsempedido = null;
                    $idsss = null;
                    if ($objects_sem_pedido) {
                        foreach ($objects_sem_pedido as $object) {
                            $existsempedido = $object->mensagem_validacao;
                            $idsss          = $object->ids_sem_pedido;
                        }
                    }

                    $result_cotacao = "SELECT
                                    STRING_AGG(oss.id_ordem_servico_serv::text, ',') AS ids_nao_excluidos
                                FROM solicitacoescompras AS sl
                                JOIN servicossolicitacoescompras AS ss ON ss.id_solicitacao_compra = sl.id_solicitacoes_compras
                                JOIN ordem_servico_servicos AS oss ON oss.id_ordem_servico_serv = ss.id_ordem_servico_serv
                                WHERE oss.id_ordem_servico_serv IN ($placeholders_in)
                                AND sl.situacao_compra IN ('INICIADA', 'AGUARDANDO INÍCIO DE COMPRAS', 'AGUARDANDO APROVAÇÃO', 'INICIADO COTAÇÃO DE SERVIÇO')
                                AND sl.aprovado_reprovado IS NOT FALSE
                                AND sl.is_cancelada is not true;";

                    $objects_cotacao = DB::select($result_cotacao, $pedidos);

                    $iniciadocota = null;
                    if ($objects_cotacao) {
                        foreach ($objects_cotacao as $object) {
                            $iniciadocota = $object->ids_nao_excluidos;
                        }
                    }

                    if (
                        $exists == 'Existem serviços não finalizados'
                        && $existsempedido != 'Todos os registros têm pedido de compra gerado'
                        && $iniciadocota == NULL
                    ) {
                        DB::beginTransaction();
                        foreach ($pedidos as $porservico) {
                            $object = OrdemServicoServicos::findOrFail($porservico);
                            $object->delete();
                        }
                        DB::commit();
                        return response()->json([
                            'success' => true,
                            'message' => 'Serviços deletados com sucesso!'
                        ], 200);
                    } elseif ($iniciadocota != NULL) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Não será possivel realizar a exclusão pois processo de cotação já iniciado para os seguintes IDs (' . $iniciadocota . ')'
                        ], 400);
                    } elseif ($exists == 'Existe Serviço selecionado já finalizado') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Existem Serviços (' . $ids . ') selecionados já finalizados'
                        ], 400);
                    } elseif ($existsempedido != 'Todos os registros têm pedido de compra gerado') {
                        // Corrigido: aqui entra quando realmente tem registros sem pedido
                        return response()->json([
                            'success' => false,
                            'message' => 'Existem ao menos um Serviço (' . $idsss . ') sem pedido de compras'
                        ], 400);
                    }

                    // 🚨 fallback: sempre retorna algo
                    return response()->json([
                        'success' => false,
                        'message' => 'Nenhuma condição atendida para exclusão.'
                    ], 400);
                }
            }

            return response()->json(['success' => false, 'message' => 'Nenhum serviço foi selecionado.'], 400);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir os serviços: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao excluir os serviços: ' . $e->getMessage()
            ], 500);
        }
    }

    public function onDeletarPecas(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        try {
            Log::info('Dados recebidos:', $request->all());

            $idsSelecionados = $request->input('idSelecionado', []);
            if (!is_array($idsSelecionados)) {
                $idsSelecionados = [$idsSelecionados];
            }

            if (empty($idsSelecionados)) {
                return response()->json([
                    'error' => 'Atenção',
                    'message' => 'Nenhuma peça foi selecionada.'
                ], 400);
            }

            $placeholders_in = implode(",", array_fill(0, count($idsSelecionados), "?"));

            // --- Validação 1: Peças já baixadas ou solicitadas ---
            $sqlLiberados = "SELECT
                        CASE
                            WHEN NOT EXISTS (
                                SELECT 1
                                FROM ordem_servico_pecas osp
                                WHERE osp.id_ordem_servico_pecas IN ($placeholders_in)
                                AND (osp.jasolicitada = TRUE OR osp.situacao_pecas = 'BAIXADA')
                            ) THEN 'LIBERADOS'
                            ELSE 'NAO LIBERADOS'
                        END AS resultado";

            $resultadoLiberados = DB::select($sqlLiberados, $idsSelecionados);
            $liberados = $resultadoLiberados[0]->resultado ?? null;

            // --- Validação 2: Peças vinculadas a solicitações de compras ---
            $sqlPermitidos = "SELECT
                        CASE
                            WHEN NOT EXISTS (
                                SELECT 1
                                FROM ordem_servico AS os
                                JOIN ordem_servico_pecas AS osp ON osp.id_ordem_servico = os.id_ordem_servico
                                JOIN solicitacoescompras AS sl ON sl.id_ordem_servico = os.id_ordem_servico
                                JOIN itenssolicitacoescompras AS it ON it.id_solicitacao_compra = sl.id_solicitacoes_compras 
                                    AND osp.id_produto = it.id_produto
                                WHERE osp.id_ordem_servico_pecas IN ($placeholders_in)
                                AND (sl.aprovado_reprovado = TRUE OR sl.situacao_compra = 'AGUARDANDO INÍCIO DE COMPRAS')
                            ) THEN 'PERMITIDOS'
                            ELSE 'NAO PERMITIDOS'
                        END AS resultado_";

            $resultadoPermitidos = DB::select($sqlPermitidos, $idsSelecionados);
            $permitidos = $resultadoPermitidos[0]->resultado_ ?? null;

            // --- Bloqueios de exclusão ---
            if ($liberados === 'NAO LIBERADOS') {
                return response()->json([
                    'error' => 'Atenção',
                    'message' => 'Existem peças já baixadas ou solicitadas.'
                ], 400);
            }

            if ($permitidos === 'NAO PERMITIDOS') {
                return response()->json([
                    'error' => 'Atenção',
                    'message' => 'Existem peças em processo de compras e não podem ser excluídas.'
                ], 400);
            }

            // --- Exclusão segura ---
            DB::beginTransaction();
            Log::info('Iniciando exclusão de peças/devoluções', ['total' => count($idsSelecionados)]);

            foreach ($idsSelecionados as $id) {
                // Tenta primeiro como devolução
                $devolucao = DevolucaoProdutoOrdem::find($id);

                if ($devolucao) {
                    Log::info('Processando devolução', ['id' => $id]);

                    $pecaOS = OrdemServicoPecas::where('id_ordem_servico', $devolucao->id_ordem_servico)
                        ->where('id_produto', $devolucao->id_produto)
                        ->first();

                    if (!$pecaOS) {
                        Log::warning('Peça da OS não encontrada para devolução', ['id' => $id]);
                        continue;
                    }

                    // Verifica se foi devolvida parcialmente
                    if ($pecaOS->situacao_pecas === 'DEVOLVIDA PARCIALMENTE') {
                        DB::rollBack();
                        return response()->json([
                            'error' => 'Atenção',
                            'message' => 'Não é possível excluir a peça! A mesma foi devolvida parcialmente.'
                        ], 400);
                    }

                    // Subtrai a quantidade devolvida
                    $pecaOS->quantidade -= $devolucao->quantidade;

                    if ($pecaOS->quantidade <= 0.0001) {
                        // quantidade zerou → pode excluir
                        DB::table('ordem_servico_pecas')
                            ->where('id_ordem_servico_pecas', $pecaOS->id_ordem_servico_pecas)
                            ->delete();

                        $devolucao->delete();

                        Log::info('Peça e devolução excluídas (quantidade zerada)', [
                            'id_ordem_servico_pecas' => $pecaOS->id_ordem_servico_pecas
                        ]);
                    } else {
                        // ainda tem quantidade → apenas atualiza, não exclui
                        $pecaOS->save();

                        Log::info('Peça atualizada, devolução não excluída (ainda há quantidade)', [
                            'id_ordem_servico_pecas' => $pecaOS->id_ordem_servico_pecas,
                            'quantidade_restante' => $pecaOS->quantidade
                        ]);
                    }

                    continue;
                }

                // 🔍 Nova verificação — mesmo sem devolução:
                $peca = OrdemServicoPecas::find($id);
                if ($peca) {
                    if ($peca->quantidade > 0.0001) {
                        DB::rollBack();
                        Log::warning('Tentativa de exclusão de peça com quantidade > 0', [
                            'id_ordem_servico_pecas' => $id,
                            'quantidade' => $peca->quantidade
                        ]);

                        return response()->json([
                            'error' => 'Atenção',
                            'message' => "Não é permitido excluir a peça '{$peca->id_produto}' pois ainda possui quantidade maior que zero ({$peca->quantidade})."
                        ], 400);
                    }

                    Log::info('Excluindo peça diretamente da OS (quantidade zerada)', ['id_ordem_servico_pecas' => $id]);
                    $peca->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Sucesso',
                'message' => 'Peças e devoluções excluídas com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir peças/devoluções: ' . $e->getMessage());

            return response()->json([
                'error' => 'Erro',
                'message' => 'Ocorreu um erro ao excluir os registros: ' . $e->getMessage()
            ], 500);
        }
    }

    private function convertMoneyToFloat($value)
    {
        if (empty($value) || $value === null) {
            return 0.0;
        }

        $cleaned = preg_replace('/[^\d,.-]/', '', $value);

        if (strpos($cleaned, ',') !== false) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } else {
            $cleaned = str_replace(',', '', $cleaned);
        }

        $result = floatval($cleaned);

        return $result;
    }

    public function onimprimirkm(Request $param)
    {
        $this->normalizeSmartSelectParams($param);
        try {

            if (!is_numeric($param['id_veiculo'])) {
                $veiculo = Veiculo::where('placa', $param['id_veiculo'])->first()->id_veiculo ?? null;
            } else {
                $veiculo = $param['id_veiculo'];
            }

            if (!empty($veiculo)) {

                if (empty($param['id_veiculo'])) {
                    $in_veiculo  = '!=';
                    $id_veiculo  = '0';
                } else {
                    $in_veiculo  = 'IN';
                    $id_veiculo  = $veiculo;
                }

                $datainicial    =  date('2024-01-01');
                $datafinal      =  date('Y-m-d');

                $parametros = array(
                    'P_data_inicial' => $datainicial,
                    'P_data_final' => $datafinal,
                    'P_in_veiculo' => $in_veiculo,
                    'P_id_veiculo' => $id_veiculo
                );

                $name       = 'historico_km';
                $agora      = date('d-m-YH:i');
                $tipo       = '.pdf';
                $relatorio  = $name . $agora . $tipo;
                $barra      = '/';
                $partes = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
                $host = $partes['host'] . PHP_EOL;
                $pathrel = (explode('.', $host));
                $dominio = $pathrel[0];

                if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
                    $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                    $pastarelatorio = '/reports/homologacao/' . $name;
                    Log::info('Usando servidor de homologação');
                } elseif ($dominio == 'lcarvalima') {
                    $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                    $input = '/reports/carvalima/' . $name;
                    if (is_dir($input)) {
                        chmod($input, 0777);
                        Log::info('Permissões do diretório alteradas: ' . $input);
                    } else {
                        Log::warning('Diretório não encontrado: ' . $input);
                    }
                    $pastarelatorio = $input;
                    Log::info('Usando servidor de produção');
                } else {
                    $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                    $input = '/reports/' . $dominio . '/' . $name;
                    if (is_dir($input)) {
                        chmod($input, 0777);
                        Log::info('Permissões do diretório alteradas: ' . $input);
                    } else {
                        Log::warning('Diretório não encontrado: ' . $input);
                    }
                    $pastarelatorio = $input;
                    Log::info('Usando servidor de produção');
                }

                $jsi = new jasperserverintegration(
                    $jasperserver,
                    $pastarelatorio,
                    'pdf',
                    'unitop',
                    'unitop2022',
                    $parametros
                );

                try {
                    $data = $jsi->execute();
                    // Retorne o PDF diretamente na resposta HTTP
                    return response($data, 200)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'inline; filename="' . $relatorio . '"');
                } catch (\Exception $e) {
                    Log::error('erro ao imprimir km: ' . $e->getMessage());
                    return response()->json(['success' => false, 'message' => 'Erro ao gerar PDF: ' . $e->getMessage()], 500);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Atenção: Informe o veículo para emissão do relatório.'], 400);
            }
        } catch (\Exception $e) {
            Log::error('erro ao imprimir km: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao imprimir km: ' . $e->getMessage()], 500);
        }
    }

    public function getFilialVeiculo($idVeiculo)
    {
        $filialVeiculo = Veiculo::find($idVeiculo)->filial;
        return $filialVeiculo->id;
    }

    public function reabirOS(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        $id = $request->idOrdemServico;
        try {
            db::beginTransaction();
            $ordemServico = OrdemServico::find($id);

            $ordemServico->id_status_ordem_servico = 2;
            $ordemServico->save();

            db::commit();
            return response()->json(['success' => true, 'message' => 'Ordem de serviço reaberta com sucesso!'], 200);
        } catch (\Exception $e) {
            db::rollBack();
            Log::error('erro ao reabrir OS: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao reabrir OS: ' . $e->getMessage()], 500);
        }
    }

    public function checkOS(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        $idTipoOS = $request->id_tipo_ordem_servico;
        $situacaoTipoOSCorretiva = $request->situacao_tipo_os_corretiva;
        if (!is_numeric($request->id_veiculo)) {
            $idVeiculo = Veiculo::where('placa', $request->id_veiculo)->first()->id_veiculo ?? null;
        } else {
            $idVeiculo = $request->id_veiculo;
        }
        $localManutencao = $request->local_manutencao;

        $retorno = false;

        // 🔹 Verifica se todos os dados necessários vieram
        if (isset($idTipoOS) && isset($idVeiculo)) {

            // Se for OS corretiva, precisa verificar se já existe a mesma corretiva para o veículo
            if (!empty($situacaoTipoOSCorretiva)) {
                $retorno = OrdemServico::where('id_tipo_ordem_servico', $idTipoOS)
                    ->where('situacao_tipo_os_corretiva', $situacaoTipoOSCorretiva) // mesma corretiva
                    ->where('id_veiculo', $idVeiculo)
                    ->whereNotIn('id_status_ordem_servico', [4, 6, 8, 13])
                    ->exists();
            } else {
                // Se não for corretiva, basta verificar tipo + veículo + local
                $retorno = OrdemServico::where('id_tipo_ordem_servico', $idTipoOS)
                    ->where('id_veiculo', $idVeiculo)
                    ->where('local_manutencao', $localManutencao)
                    ->whereNotIn('id_status_ordem_servico', [4, 6, 8, 13])
                    ->exists();
            }
        }

        return $retorno;
    }

    public function show($id)
    {
        $ordem = OrdemServico::with('statusOrdemServico')->findOrFail($id);

        $steps = [
            'ABERTA',
            'AGUARDANDO ASSUMIR O.S',
            'AGUARDANDO PEÇA',
            'AGUARDANDO RETIRADA DA PEÇA',
            'CANCELADA',
            'EM EXECUÇÃO',
            'EM PROCESSO DE COMPRA',
            'ENCERRADA',
            'PENDENTE LANÇAMENTO NF',
            'PRÉ-O.S',
            'SERVIÇO INICIADO',
            'SERVIÇOS FINALIZADOS',
            'FINALIZADA',


        ];

        return view('admin.ordemservicos._form', compact('ordem', 'steps'));
    }

    public function getServicosBorracharia(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        try {
            $idOperacao = $request->operacao;
            $servicos = [];

            if ($idOperacao != 6) {
                // sem filtro
                $servicos = Servico::where('ativo_servico', true)
                    ->whereNotIn('id_grupo', [290, 600])
                    ->limit(20)
                    ->orderBy('descricao_servico')
                    ->get(['id_servico as value', db::Raw("CONCAT(id_servico, ' - ', descricao_servico) as label")]);
            } else {
                // Borracharia
                $servicos = Servico::where('ativo_servico', true)
                    ->whereIn('id_grupo', [290, 600]) // grupo 290 Rodagens e Pneus, grupo 600 borracharia
                    ->limit(20)
                    ->orderBy('descricao_servico')
                    ->get(['id_servico as value', db::Raw("CONCAT(id_servico, ' - ', descricao_servico) as label")]);
            }

            return response()->json([
                'success' => true,
                'servicos' => $servicos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar serviços: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProdutosBorracharia(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        try {
            $idOperacao = $request->operacao;
            $produtos = [];

            if ($idOperacao != 6) {
                // sem filtro
                $produtos = Produto::where('is_ativo', true)
                    ->whereNotIn('id_grupo_servico', [290, 600])
                    ->limit(20)
                    ->orderBy('descricao_produto')
                    ->select([
                        'id_produto as value',
                        DB::raw("CONCAT('Cód. Unitop: ', id_produto, ' - ', descricao_produto, ' - ', 'Cód. Fabricante: ', cod_fabricante_) as label")
                    ])
                    ->get();
            } else {
                // Borracharia
                $produtos = Produto::where('is_ativo', true)
                    ->whereIn('id_grupo_servico', [290, 600])
                    ->limit(20)
                    ->orderBy('descricao_produto')
                    ->select([
                        'id_produto as value',
                        DB::raw("CONCAT('Cód. Unitop: ', id_produto, ' - ', descricao_produto, ' - ', 'Cód. Fabricante: ', cod_fabricante_) as label")
                    ])
                    ->get();
            }

            return response()->json([
                'success' => true,
                'produtos' => $produtos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar produtos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getServicosSearch(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        $idOperacao = $request->operacao;
        $servicos = [];
        try {
            if ($idOperacao != 6) {
                $searchTerm = $request->input('term', '');
                try {
                    $servicos = Servico::where('ativo_servico', true)
                        ->whereNotIn('id_grupo', [290, 600])
                        ->where(function ($query) use ($searchTerm) {
                            $query->where('descricao_servico', 'ILIKE', '%' . $searchTerm . '%')
                                ->orWhere('id_servico', '=', $searchTerm);
                        })
                        ->limit(20)
                        ->orderBy('descricao_servico')
                        ->get(['id_servico as value', db::Raw("CONCAT('Cód. ', id_servico, ' - ', descricao_servico) as label")]);
                } catch (\Exception $e) {
                    Log::error('Erro ao buscar serviços: ' . $e->getMessage());
                }
            } else {
                $searchTerm = $request->input('term', '');

                $servicos = Servico::where('ativo_servico', true)
                    ->whereIn('id_grupo', [290, 600])
                    ->where(function ($query) use ($searchTerm) {
                        $query->where('descricao_servico', 'ILIKE', '%' . $searchTerm . '%')
                            ->orWhere('id_servico', '=', $searchTerm);
                    })
                    ->limit(20)
                    ->orderBy('descricao_servico')
                    ->get(['id_servico as value', db::Raw("CONCAT('Cód. ', id_servico, ' - ', descricao_servico) as label")]);
            }

            return response()->json($servicos);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar serviços: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProdutosSearch(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        $idOperacao = $request->operacao;
        $produtos = [];
        try {
            if ($idOperacao != 6) {
                $searchTerm = $request->input('term', '');

                $produtos = Produto::where('is_ativo', true)
                    ->whereNotIn('id_grupo_servico', [290, 600])
                    ->where(function ($query) use ($searchTerm) {
                        $query->where('descricao_produto', 'ILIKE', '%' . $searchTerm . '%')
                            ->orWhere('id_produto', 'ILIKE', '%' . $searchTerm . '%')
                            ->orWhere('cod_fabricante_', 'ILIKE', '%' . $searchTerm . '%');
                    })
                    ->limit(20)
                    ->orderBy('descricao_produto')
                    ->select([
                        'id_produto as value',
                        DB::raw("CONCAT('Cód. Unitop: ', id_produto, ' - ', descricao_produto, ' - ', 'Cód. Fabricante: ', cod_fabricante_) as label")
                    ])
                    ->get();
            } else {
                $searchTerm = $request->input('term', '');

                $produtos = Produto::where('is_ativo', true)
                    ->whereIn('id_grupo_servico', [290, 600])
                    ->where(function ($query) use ($searchTerm) {
                        $query->where('descricao_produto', 'ILIKE', '%' . $searchTerm . '%')
                            ->orWhere('id_produto', 'ILIKE', '%' . $searchTerm . '%')
                            ->orWhere('cod_fabricante_', 'ILIKE', '%' . $searchTerm . '%');
                    })
                    ->limit(20)
                    ->orderBy('descricao_produto')
                    ->select([
                        'id_produto as value',
                        DB::raw("CONCAT('Cód. Unitop: ', id_produto, ' - ', descricao_produto, ' - ', 'Cód. Fabricante: ', cod_fabricante_) as label")
                    ])
                    ->get();
            }

            return response()->json($produtos);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar produtos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function onSolicitarPneus($idOrdemServico, $idUsuarioSolicitante)
    {
        //verificar estoque dos pneus
        $ordemServico = OrdemServicoPecas::where('id_ordem_servico', $idOrdemServico)->get();

        foreach ($ordemServico as $peca) {
            $produto = Produto::find($peca->id_produto);
            if ($produto && $produto->id_modelo_pneu) {
                $estoqueAtual = ProdutosPorFilial::where('id_produto_unitop', $produto->id_produto)
                    ->where('id_filial', GetterFilial())
                    ->sum('quantidade_produto');

                if ($estoqueAtual < $peca->quantidade) {
                    // Se algum pneu não tiver estoque suficiente, retorna false
                    return 'Estoque insuficiente';
                }
            } else {
                // Se o produto não for um pneu, retorna false
                return 'Produto não é um pneu';
            }
        }

        $insertReqPneu = ("INSERT INTO requisicao_pneu(data_inclusao, id_filial, situacao, id_usuario_solicitante, observacao_solicitante, id_ordem_servico, is_aprovado)
                    select 
                        CURRENT_TIMESTAMP,
                        os.id_filial_manutencao,
                        'APROVADO',
                        $idUsuarioSolicitante,
                        concat('Ordem de serviço: ', os.id_ordem_servico, ' - ', os.relato_problema),
                        $idOrdemServico,
                        true
                    from ordem_servico as os
                    where os.id_ordem_servico = $idOrdemServico");


        $insertReqPneuModelo = (
            "INSERT INTO requisicao_pneu_modelos (data_inclusao, id_requisicao_pneu, id_modelo_pneu, quantidade, id_filial, id_produto)
                    select 
                        osp.data_inclusao,
                        (SELECT currval(pg_get_serial_sequence('requisicao_pneu', 'id_requisicao_pneu'))),
                        mdp.id_modelo_pneu,
                        osp.quantidade,
                        os.id_filial_manutencao,
                        osp.id_produto
                    from ordem_servico_pecas osp
                    join ordem_servico os on os.id_ordem_servico = osp.id_ordem_servico
                    join produto as p on p.id_produto = osp.id_produto
                    join modelopneu as mdp on mdp.id_modelo_pneu = p.id_modelo_pneu
                    where osp.id_ordem_servico = $idOrdemServico");

        $updateOrdemServicoPecas = ("update
                                        ordem_servico_pecas
                                    set jasolicitada = true, data_alteracao = now(), situacao_pecas = 'SOLICITADA'
                                    where id_ordem_servico = $idOrdemServico");

        $updateOrdemServico = ("update
                                    ordem_servico
                                set id_status_ordem_servico = 3, data_alteracao = now()
                                where id_ordem_servico = $idOrdemServico");

        try {
            DB::beginTransaction();
            foreach ([$insertReqPneu, $insertReqPneuModelo, $updateOrdemServicoPecas, $updateOrdemServico] as $result) {
                DB::statement($result);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao inserir requisição de pneu modelo: ' . $e->getMessage());
            return false;
        }
    }

    public function search(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        $term = strtolower($request->get('term'));

        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        // Cache para melhorar performance
        $ordens = Cache::remember('ordem_servico_search_' . $term, now()->addMinutes(30), function () use ($term) {
            return OrdemServico::select('id_ordem_servico')
                ->whereRaw('CAST(id_ordem_servico AS TEXT) LIKE ?', ["%{$term}%"])
                ->orderByDesc('id_ordem_servico')
                ->limit(30)
                ->get()
                ->map(function ($os) {
                    return [
                        'label' => (string) $os->id_ordem_servico, // só o número
                        'value' => $os->id_ordem_servico
                    ];
                })->toArray();
        });

        return response()->json($ordens);
    }

    /**
     * Buscar uma ordem pelo ID
     */
    public function getById($id)
    {
        $ordem = OrdemServico::where('id_ordem_servico', $id)->first();

        if (!$ordem) {
            return response()->json([], 404);
        }

        return response()->json([
            'value' => $ordem->id_ordem_servico,
            'label' => (string) $ordem->id_ordem_servico,
        ]);
    }

    public function marcarMarcacao(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $marcacao = OrdemServicoMarcacao::where('id_ordem_servico', $request->idOrdemservico)
            ->where('id_pneu', $request->id)
            ->first();

        if (!$marcacao) {
            return response()->json(['success' => false, 'message' => 'Marcação não encontrada']);
        }

        if ($request->marcado) {
            $marcacao->is_marcado = true;
            $marcacao->marcado_por = Auth::user()->id;
            $marcacao->data_alteracao = now();
        } else {
            $marcacao->is_marcado = false;
            $marcacao->data_alteracao = now();
            $marcacao->marcado_por = null;
        }

        $marcacao->save();

        return response()->json([
            'success' => true,
            'marcado_por' => Auth::user()->name
        ]);
    }

    public function marcarTodosMarcacoes(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        $user = Auth::user()->id;
        $ids = $request->ids ?? [];

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Nenhum ID recebido']);
        }

        OrdemServicoMarcacao::where('id_ordem_servico', $request->idOrdemservico)
            ->whereIn('id_pneu', $ids)->update([
                'marcado_por' => $user,
                'is_marcado' => true,
                'data_alteracao' => now()
            ]);


        return response()->json([
            'success' => true,
            'marcado_por' => Auth::user()->name
        ]);
    }

    public function contratoServicos(Request $request, $id)
    {
        $this->normalizeSmartSelectParams($request);
        $servico = OrdemServicoServicos::with('contrato', 'fornecedor')->findOrFail($id);

        $idFilial = auth()->user()->filial_id;

        $nomeFornecedor = $servico->fornecedor->nome_fornecedor ?? '';


        if (!$servico->contrato || !$servico->contrato->is_valido) {
            return response()->json([
                'success' => false,
                'message' => "O fornecedor {$nomeFornecedor} não possui contrato válido."
            ], 400);
        }

        try {
            $solicCompra = SolicitacaoCompra::create([
                'id_filial'             => $idFilial,
                'id_solicitante'        => auth()->id(),
                'id_comprador'          => auth()->id(),
                'id_aprovador'          => auth()->id(),
                'id_departamento'       => auth()->user()->departamento_id,
                'situacao_compra'       => 'FINALIZADO',
                'is_contrato'           => true, // 🚨 aqui você usava `$servico->true`, estava errado
                'tipo_solicitacao'      => 2,
                'data_finalizada'       => now(),
                'aprovado_reprovado'    => true
            ]);

            $itemSolic = ItemSolicitacaoCompra::create([
                'id_solicitacao_compra' => $solicCompra->id_solicitacao_compra,
                'data_inclusao'         => now(),
                'valor_total_produto'   => $servico->valor_servico,
                'id_produto'            => 1
            ]);

            $pedidoCompra = PedidoCompra::create([
                'valor_total_desconto'      => $servico->valor_servico,
                'id_fornecedor'             => $servico->id_fornecedor,
                'id_comprador'              => auth()->id(),
                'id_filial'                 => $idFilial,
                'id_aprovador_pedido'       => auth()->id(),
                'id_solicitacoes_compras'   => $itemSolic->id_itens_solicitacoes,
                'situacao_pedido'           => 2,
                'situacao'                  => 'APROVADO',
                'is_liberado'               => true,
                'tipo_pedido'               => 2,
                'valor_total_sem_percentual' => $servico->valor_servico
            ]);

            return response()->json([
                'success' => true,
                'message' => "Contrato validado e pedido de compra gerado com sucesso."
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar pedido de compra para o serviço ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar pedido de compra: ' . $e->getMessage()
            ], 500);
        }
    }
}
