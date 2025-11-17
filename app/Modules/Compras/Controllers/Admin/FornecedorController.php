<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContratoFornecedor;
use App\Models\ContratoModelo;
use App\Models\Endereco;
use App\Models\Estado;
use App\Models\Filial;
use App\Models\Fornecedor;
use App\Models\FornecedorXMecanico;
use App\Modules\Manutencao\Models\GrupoServico;
use App\Modules\Veiculos\Models\ModeloVeiculo;
use App\Models\Municipio;
use App\Models\PecasFornecedor;
use App\Models\Produto;
use App\Modules\Manutencao\Models\Servico;
use App\Modules\Manutencao\Models\ServicoFornecedor;
use App\Models\Telefone;
use App\Models\TipoFornecedor;
use App\Models\User;
use App\Models\VFilial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FornecedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->aplicarFiltros(Fornecedor::query(), $request);

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();

        $uf = Estado::select('id_uf as value', 'uf as label')
            ->orderBy('uf')
            ->get();

        $tipo = TipoFornecedor::select('id_tipo_fornecedor as value', 'descricao_tipo as label')
            ->orderBy('descricao_tipo')
            ->get();

        $forn = Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
            ->limit(30)
            ->orderBy('nome_fornecedor')
            ->get();

        if ($request->filled('id_uf')) {
            $query->whereHas('endereco', function ($q) use ($request) {
                $q->where('id_uf', $request->id_uf);
            });
        }

        if ($request->filled('id_tipo_fornecedor')) {
            $query->where('id_tipo_fornecedor', $request->input('id_tipo_fornecedor'));
        }
        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->input('id_fornecedor'));
        }

        $fornecedores = $query->latest('id_fornecedor')
            ->with('filial', 'tipoFornecedor')
            ->paginate(40)
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.fornecedores._table', compact('fornecedores'));
        }

        $dadosFormulario = $this->obterDadosFormulario();
        $fornecedoresData = $this->formatarFornecedoresParaTabela($fornecedores);

        return view(
            'admin.fornecedores.index',
            array_merge(
                compact('fornecedores', 'fornecedoresData', 'filial', 'uf', 'tipo', 'forn'),
                $dadosFormulario
            )
        );
    }

    private function aplicarFiltros($query, Request $request)
    {
        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }
        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }
        if ($request->filled('id_uf')) {
            $query->whereHas('endereco', function ($q) use ($request) {
                $q->where('id_uf', $request->id_uf);
            });
        }

        if ($request->filled('nome_fornecedor')) {
            $query->where('nome_fornecedor', 'ilike', '%' . $request->nome_fornecedor . '%');
        }

        $this->aplicarFiltroTipoFornecedor($query, $request);
        $this->aplicarFiltrosData($query, $request);

        if ($request->filled('is_ativo')) {
            $query->where('is_ativo', $request->is_ativo);
        }

        $this->aplicarFiltroFilial($query, $request);
        $this->aplicarFiltroUf($query, $request);
        $this->aplicarFiltroCnpj($query, $request);

        return $query;
    }

    private function aplicarFiltroTipoFornecedor($query, Request $request)
    {
        if ($request->filled('id_tipo_fornecedor')) {
            $idsTipoFornecedor = TipoFornecedor::where('descricao_tipo', 'ilike', '%' . $request->tipo_fornecedor . '%')
                ->pluck('id_tipo_fornecedor');

            if ($idsTipoFornecedor->isNotEmpty()) {
                $query->whereIn('id_tipo_fornecedor', $idsTipoFornecedor);
            }
        }
    }

    private function aplicarFiltrosData($query, Request $request)
    {
        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        }

        if ($request->filled('data_final_inclusao')) {
            $query->whereDate('data_inclusao', '<=', $request->data_final_inclusao);
        }
    }

    private function aplicarFiltroFilial($query, Request $request)
    {
        if ($request->filled('id_filial')) {
            $idsFilial = VFilial::where('name', 'ilike', '%' . $request->id_filial . '%')
                ->pluck('id');

            if ($idsFilial->isNotEmpty()) {
                $query->whereIn('id_filial', $idsFilial);
            }
        }
    }

    private function aplicarFiltroUf($query, Request $request)
    {
        if ($request->filled('id_uf')) {
            $query->whereHas('endereco', function ($q) use ($request) {
                $q->where('id_uf', $request->id_uf);
            });
        }
    }

    private function aplicarFiltroCnpj($query, Request $request)
    {
        if ($request->filled('cnpj')) {
            $query->where('cnpj_fornecedor', 'ilike', '%' . $request->cnpj . '%');
        }
    }

    private function obterDadosFormulario()
    {
        return [
            'tipoFornecedor' => TipoFornecedor::select('id_tipo_fornecedor as value', 'descricao_tipo as label')
                ->orderBy('descricao_tipo')
                ->get(),
            'modeloContrato' => ContratoFornecedor::select('id_contrato_forn as value', 'id_contrato_forn as label')
                ->orderBy('id_contrato_forn')
                ->limit(30)
                ->get(),
            'ativo' => collect([
                ['value' => 1, 'label' => 'Sim'],
                ['value' => 0, 'label' => 'Não'],
            ]),
        ];
    }

    private function formatarFornecedoresParaTabela($fornecedores)
    {
        $fornecedoresData = [];

        foreach ($fornecedores as $fornecedor) {
            $fornecedoresData[] = [
                'id_fornecedor' => $fornecedor->id_fornecedor,
                'nome_fornecedor' => $fornecedor->nome_fornecedor,
                'cnpj_fornecedor' => $fornecedor->cnpj_formatado ?? $fornecedor->cnpj_fornecedor,
                'descricao_tipo' => $fornecedor->tipoFornecedor->descricao_tipo ?? '',
                'is_ativo' => $fornecedor->is_ativo ? 'Sim' : 'Não',
                'data_inclusao' => $fornecedor->data_inclusao ? $fornecedor->data_inclusao->format('d/m/Y') : '',
                'email' => $fornecedor->email,
            ];
        }

        return $fornecedoresData;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($fornecedorId = null)
    {
        // Carregar dados para os combos
        $tipoFornecedores = TipoFornecedor::orderBy('descricao_tipo')->get();
        $tipoFornecedorData = $tipoFornecedores->map(function ($tipoFornecedor) {
            return [
                'value' => $tipoFornecedor->id_tipo_fornecedor,
                'label' => $tipoFornecedor->descricao_tipo,
            ];
        })->toArray();

        $estados = Estado::orderBy('uf')->get();
        $filiais = VFilial::orderBy('name')->get(['id as value', 'name as label']);

        // Dados para as novas abas
        $gruposServicos = GrupoServico::orderBy('descricao_grupo')
            ->get(['id_grupo as value', 'descricao_grupo as label']);

        $gruposPecas = GrupoServico::orderBy('descricao_grupo')
            ->get(['id_grupo as value', 'descricao_grupo as label']);

        $modelosVeiculos = ModeloVeiculo::orderBy('descricao_modelo_veiculo')
            ->get(['id_modelo_veiculo as value', 'descricao_modelo_veiculo as label']);

        $mecanicosSelect = User::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        $tipoFornecedor = TipoFornecedor::select('id_tipo_fornecedor as value', 'descricao_tipo as label')
            ->orderBy('descricao_tipo')
            ->get();

        if ($fornecedorId) {
            // Se foi passado ID, busca contratos do fornecedor
            $contratosModelo = ContratoModelo::with(['modelo', 'contrato'])
                ->where('id_fornecedor', $fornecedorId)
                ->orderBy('id_contrato_modelo')
                ->get();
        } else {
            // Se não foi passado ID, inicia vazio
            $contratosModelo = collect();
        }

        // Para listar mecânicos já cadastrados
        $mecanicosList = FornecedorXMecanico::with('mecanicoInterno')
            ->orderBy('data_inclusao', 'desc')
            ->paginate(10);

        $municipio = Municipio::select('id_municipio as value', 'nome_municipio as label')
            ->orderBy('nome_municipio')
            ->limit(30)
            ->get();

        $uf = Municipio::select('id_uf as value', 'uf as label')
            ->distinct()
            ->orderBy('uf')
            ->get();

        if ($fornecedorId) {
            $modeloContrato = ContratoFornecedor::select('id_contrato_forn as value', 'id_contrato_forn as label')
                ->where('id_fornecedor', $fornecedorId)
                ->orderByDesc('id_contrato_forn')
                ->limit(30)
                ->get();

            $modeloContratox = ContratoModelo::select('id_contrato_modelo as value', 'id_contrato_modelo as label')
                ->where('id_fornecedor', $fornecedorId)
                ->orderByDesc('id_contrato_modelo')
                ->limit(30)
                ->get();
        } else {
            $modeloContrato = collect();
            $modeloContratox = collect();
        }
        // Cria um fornecedor vazio para a view
        $fornecedor = new Fornecedor;
        $fornecedor->mecanicos = collect(); // Coleção vazia para os mecânicos

        // Debug: verifique o que está sendo gerado
        // dd($mecanicos);

        return view('admin.fornecedores.create', compact(
            'tipoFornecedorData',
            'estados',
            'filiais',
            'gruposServicos',
            'gruposPecas',
            'modelosVeiculos',
            'mecanicosSelect',
            'mecanicosList',
            'fornecedor',
            'municipio',
            'uf',
            'tipoFornecedor',
            'modeloContrato',
            'contratosModelo',
            'modeloContrato',
            'modeloContratox',
            'fornecedorId'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $conn = DB::connection('pgsql');
            $conn->beginTransaction();

            // Normalizar CPF/CNPJ para apenas dígitos antes de validar
            $cnpj = preg_replace('/\D/', '', (string) $request->input('cnpj_fornecedor'));
            $cpf  = preg_replace('/\D/', '', (string) $request->input('cpf_fornecedor'));
            $request->merge([
                'cnpj_fornecedor' => $cnpj,
                'cpf_fornecedor'  => $cpf,
            ]);

            // Validar CNPJ se for pessoa jurídica
            if (! empty($request->is_juridico) && $request->is_juridico == 1) {
                if (! $this->validarCNPJ($request->cnpj_fornecedor)) {
                    return back()->withErrors('CNPJ Inválido');
                }
            }

            // Validar CPF se for pessoa física
            if (! empty($request->is_juridico) && $request->is_juridico == 0) {
                if (! $this->validarCPF($request->cpf_fornecedor)) {
                    return back()->withErrors('CPF Inválido');
                }
            }

            // Validação dos dados básicos do fornecedor
            $validated = $request->validate([
                'nome_fornecedor' => 'required|string|max:255',
                'apelido_fornecedor' => 'nullable|string|max:255',
                'cnpj_fornecedor' => 'nullable|digits:14',
                'cpf_fornecedor' => 'nullable|digits:11',
                'id_tipo_fornecedor' => 'required',
                'site' => 'nullable|string|max:255',
                'email' => 'required|string|email|max:255',
                'is_ativo' => 'boolean',
                'inscricao_estadual' => 'nullable|string|max:255',
                'is_juridico' => 'boolean',
                'rua' => 'nullable|string|max:255',
                'cep' => 'nullable|string',
                'complemento' => 'nullable|string',
                'numero' => 'nullable|string',
                'bairro' => 'nullable|string',
                'nome_municipio' => 'nullable|string',
                'id_uf' => 'nullable|string',
                'id_filial' => 'nullable|string',
                'telefones' => 'nullable|array',
                'contratos' => 'nullable|array',
                'possui_percentual' => 'boolean',
                'percentual_compra' => 'nullable|numeric',
            ]);

            // Buscar o município pelo nome
            $municipio = null;
            if (! empty($validated['nome_municipio'])) {
                $municipio = Municipio::whereRaw(
                    'unaccent(LOWER(nome_municipio)) = unaccent(LOWER(?))',
                    [$validated['nome_municipio']]
                )->first();
            }

            // Criar o fornecedor - APENAS UMA VEZ!
            $fornecedor = new Fornecedor;
            $fornecedor->fill($validated);
            $fornecedor->data_inclusao = now();
            $fornecedor->possui_percentual = $request->input('possui_percentual', false);
            $fornecedor->percentual_compra = $request->input('percentual_compra', 0);

            if ($municipio) {
                $fornecedor->id_uf = $municipio->id_uf;
            }

            $fornecedor->save();

            /* Se tiver dados de endereço, criar o endereço
            if (!empty($validated['rua']) || !empty($validated['cep'])) {
                $endereco = new Endereco();
                $endereco->fill($validated);
                $endereco->data_inclusao = now();

                if ($municipio) {
                    $endereco->id_municipio = $municipio->id_municipio;
                    $endereco->id_uf = $municipio->id_uf;
                }

                $endereco->id_fornecedor_endereco = $fornecedor->id_fornecedor;
                $endereco->save();
            }
            */
            // Processar telefones enviados pelo formulário

            if ($request->has('telefones') && is_array($request->telefones)) {
                foreach ($request->telefones as $telefoneData) {
                    if (empty($telefoneData['telefone_fixo']) && empty($telefoneData['telefone_celular'])) {
                        continue;
                    }

                    Telefone::create([
                        'telefone_fixo' => $telefoneData['telefone_fixo'] ?? null,
                        'telefone_celular' => $telefoneData['telefone_celular'] ?? null,
                        'data_inclusao' => now(),
                        'id_fornecedor' => $fornecedor->id_fornecedor,
                    ]);
                }
            }

            if ($request->has('contratos') && is_array($request->contratos)) {
                foreach ($request->contratos as $contratoData) {
                    if (empty($contratoData['valor_contrato'])) {
                        continue;
                    }

                    // Conversão para formato numérico aceito pelo Postgres
                    $valorContrato = isset($contratoData['valor_contrato'])
                        ? (float) str_replace(',', '.', $contratoData['valor_contrato'])
                        : null;

                    $saldoContrato = isset($contratoData['saldo_contrato'])
                        ? (float) str_replace(',', '.', $contratoData['saldo_contrato'])
                        : 0;

                    ContratoFornecedor::create([
                        'valor_contrato' => $valorContrato,
                        'saldo_contrato' => $saldoContrato,
                        'id_fornecedor' => $fornecedor->id_fornecedor,
                        'id_user_cadastro' => Auth::id(),
                        'is_valido' => $contratoData['is_valido'] ?? 1,
                        'data_inclusao' => now(),
                        'data_inicial' => $contratoData['data_inicial'] ?? null,
                        'data_final' => $contratoData['data_final'] ?? null,
                    ]);
                }
            }

            if ($request->has('modelo') && is_array($request->modelo)) {
                foreach ($request->modelo as $modeloData) {

                    if (empty($modeloData['id_contrato_modelo'])) {
                        continue;
                    }

                    ContratoModelo::create([
                        'data_inclusao' => now(),
                        'id_modelo' => $modeloData['id_modelo'],   // vem do formulário
                        'id_contrato' => $modeloData['id_contrato'], // vem do formulário
                        'ativo' => $modeloData['ativo'] ?? 1,
                        'id_user' => Auth::id(),
                    ]);
                }
            }

            if ($request->has('servico') && is_array($request->servico)) {
                foreach ($request->servico as $servicoData) {

                    if (empty($servicoData['id_servico'])) {
                        continue;
                    }

                    $valorServico = isset($servicoData['valor_servico'])
                        ? (float) str_replace(',', '.', str_replace('.', '', $servicoData['valor_servico']))
                        : 0;

                    ServicoFornecedor::create([
                        'data_inclusao' => $servicoData['data_inclusao'],
                        'id_grupo' => $servicoData['id_grupo'],
                        'id_contrato_forn' => $servicoData['id_contrato_forn'],
                        'id_contrato_modelo' => $servicoData['id_contrato_modelo'],
                        'id_servico' => $servicoData['id_servico'],
                        'valor_servico' => $valorServico,
                        'is_valido' => $servicoData['is_valido'] ?? 1,

                    ]);
                }
            }

            if ($request->has('pecas') && is_array($request->pecas)) {
                foreach ($request->pecas as $pecasData) {

                    if (empty($pecasData['id_pecas_forn'])) {
                        continue;
                    }

                    $valorPeca = isset($pecasData['valor_produto'])
                        ? (float) str_replace(',', '.', str_replace('.', '', $pecasData['valor_produto']))
                        : 0;

                    PecasFornecedor::create([
                        'id_fornecedor' => $fornecedor->id_fornecedor,
                        'id_grupo_pecas' => $pecasData['id_grupo_pecas'] ?? null,
                        'id_contrato_forn' => $pecasData['id_contrato_forn'],
                        'id_contrato_modelo' => $pecasData['id_contrato_modelo'] ?? null,
                        'id_produto' => $pecasData['id_produto'] ?? null,
                        'valor_produto' => $valorPeca,
                        'is_valido' => $pecasData['is_valido'] ?? 1,

                    ]);
                }
            }

            // Processar mecânicos - CORRIGIDO: usar $fornecedor já criado
            if ($request->filled('mecanicos_json')) {
                try {
                    $mecanicos = collect(json_decode($request->mecanicos_json, true));

                    foreach ($mecanicos as $m) {
                        if (empty($m['nome_mecanico']) && empty($m['id_user_mecanico'])) {
                            continue;
                        }

                        $fornecedor->mecanicos()->create([
                            'nome_mecanico' => $m['nome_mecanico'] ?? null,
                            'id_user_mecanico' => $m['id_user_mecanico'] ?? null,
                            'data_inclusao' => now(),
                            'data_alteracao' => null,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Erro ao criar mecânicos:', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Processar endereços do JSON
            if ($request->filled('enderecos_json')) {
                try {
                    $enderecos = json_decode($request->enderecos_json, true, 512, JSON_THROW_ON_ERROR);

                    foreach ($enderecos as $e) {
                        if (empty($e['rua']) && empty($e['cep'])) {
                            continue;
                        }

                        $enderecoData = [
                            'rua' => $e['rua'] ?? null,
                            'cep' => $e['cep'] ?? null,
                            'complemento' => $e['complemento'] ?? null,
                            'numero' => $e['numero'] ?? null,
                            'bairro' => $e['bairro'] ?? null,
                            'id_municipio' => ($e['id_municipio'] && $e['id_municipio'] !== '-') ? (int) $e['id_municipio'] : null,
                            'id_uf' => ($e['id_uf'] && $e['id_uf'] !== '-') ? (int) $e['id_uf'] : null,
                            'id_fornecedor_endereco' => $fornecedor->id_fornecedor,
                            'data_inclusao' => now(),
                            'data_alteracao' => null,
                        ];

                        Endereco::create($enderecoData);
                    }
                } catch (\JsonException $e) {
                    Log::error('Erro ao decodificar JSON de endereços:', [
                        'error' => $e->getMessage(),
                        'json' => $request->enderecos_json,
                    ]);
                }
            }

            $conn->commit();
            Cache::forget('fornecedores_frequentes');

            return redirect()->route('admin.fornecedores.index')
                ->with('success', 'Fornecedor cadastrado com sucesso!');
        } catch (\Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            Log::error('Erro ao cadastrar fornecedor: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocorreu um erro ao cadastrar o fornecedor: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Buscar o fornecedor pelo ID
        $fornecedor = Fornecedor::findOrFail($id);

        // Obter o endereco do fornecedor
        $endereco = Endereco::where('id_fornecedor_endereco', $fornecedor->id_fornecedor)->first();

        // Obter os telefones do fornecedor
        $telefones = Telefone::where('id_fornecedor', $fornecedor->id_fornecedor)->get();
        $telefone = $telefones->first(); // Para compatibilidade com o código existente

        // Buscar município associado ao endereço, se existir
        $municipio = null;
        if ($endereco && $endereco->id_municipio) {
            $municipio = Municipio::where('id_municipio', $endereco->id_municipio)->first();
        }

        // Dados para os combos
        $tipoFornecedores = TipoFornecedor::orderBy('descricao_tipo')->get();
        $tipoFornecedorData = $tipoFornecedores->map(function ($tipoFornecedor) {
            return [
                'value' => $tipoFornecedor->id_tipo_fornecedor,
                'label' => $tipoFornecedor->descricao_tipo,
            ];
        })->toArray();

        $estados = Estado::orderBy('uf')->get();
        $filiais = VFilial::orderBy('name')->get(['id as value', 'name as label']);

        // Dados para as abas de Contratos
        // Grupos de serviço

        $gruposServicos = GrupoServico::orderBy('descricao_grupo')
            ->get(['id_grupo as value', 'descricao_grupo as label']);

        // Serviços filtrados pelo grupo selecionado (se existir)

        // //////////////////////////////////////////////////
        $gruposPecas = GrupoServico::orderBy('descricao_grupo')
            ->get(['id_grupo as value', 'descricao_grupo as label']);

        $modelosVeiculos = ModeloVeiculo::orderBy('descricao_modelo_veiculo')
            ->get(['id_modelo_veiculo as value', 'descricao_modelo_veiculo as label']);

        // Carregar contratos ativos do fornecedor
        $contratos = ContratoFornecedor::where('id_fornecedor', $id)
            ->orderBy('id_contrato_forn', 'desc')
            ->with('userCadastro')
            ->get();

        // Carregar vínculos contrato-modelo
        $contratosModelo = ContratoModelo::whereHas('contrato', function ($query) use ($id) {
            $query->where('id_fornecedor', $id);
        })
            ->with(['contrato', 'modelo'])
            ->orderBy('id_contrato_modelo', 'desc')
            ->get();

        // Carregar serviços vinculados ao fornecedor
        $servicos = ServicoFornecedor::where('id_fornecedor', $id)
            ->with(['servico', 'grupoServico', 'contrato', 'contratoModelo.modelo'])
            ->orderBy('id_servico_forn', 'desc')
            ->get();

        $servicosFormatados = $servicos->map(function ($servico) {
            return [
                'id_servico_forn' => $servico->id_servico_forn,
                'id_grupo_servico' => $servico->id_grupo_servico,
                'descricao_grupo' => $servico->grupoServico->descricao_grupo ?? '-',
                'id_servico' => $servico->id_servico,
                'descricao_servico' => $servico->servico->descricao ?? '-',
                'id_contrato_forn' => $servico->id_contrato_forn,
                'id_contrato_modelo' => $servico->id_contrato_modelo,
                'descricao_contrato_modelo' => $servico->contratoModelo->modelo->descricao_modelo_veiculo ?? '-',
                'valor_servico' => $servico->valor_servico,
                'is_valido' => $servico->is_valido,
            ];
        })->toArray();
        // Carregar peças vinculadas ao fornecedor
        $pecas = PecasFornecedor::where('id_fornecedor', $id)
            ->with(['produto', 'grupoPecas', 'contrato', 'contratoModelo.modelo'])
            ->orderBy('id_pecas_forn', 'desc')
            ->get();

        $tipoFornecedor = TipoFornecedor::select('id_tipo_fornecedor as value', 'descricao_tipo as label')
            ->orderBy('descricao_tipo')
            ->get();
        $municipio = Municipio::select('id_municipio as value', 'nome_municipio as label')
            ->orderBy('nome_municipio')
            ->limit(30)
            ->get();

        $uf = Municipio::select('id_uf as value', 'uf as label')
            ->distinct()
            ->orderBy('uf')
            ->get();

        $modeloContrato = ContratoFornecedor::select('id_contrato_forn as value', 'id_contrato_forn as label')
            ->where('id_fornecedor', $fornecedor->id_fornecedor) // pega só desse fornecedor
            ->orderByDesc('id_contrato_forn')
            ->limit(30)
            ->get();

        $contrato = ContratoFornecedor::where('id_fornecedor', $fornecedor->id_fornecedor)->first();

        $modeloContratox = $contrato?->modelos()
            ->select('id_contrato_modelo as value', 'id_contrato_modelo as label')
            ->orderByDesc('id_contrato_modelo')
            ->limit(30)
            ->get();

        $fornecedor = Fornecedor::with('mecanicos')->findOrFail($id);
        $mecanicosSelect = User::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        return view('admin.fornecedores.edit', compact(
            'fornecedor',
            'endereco',
            'telefone',
            'telefones',
            'municipio',
            'tipoFornecedorData',
            'estados',
            'filiais',
            'gruposServicos',
            'gruposPecas',
            'modelosVeiculos',
            'contratos',
            'contratosModelo',
            'servicos',
            'pecas',
            'mecanicosSelect',
            'tipoFornecedor',
            'uf',
            'municipio',
            'modeloContrato',
            'modeloContratox',
            'servicosFormatados'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Normalizar CPF/CNPJ para apenas dígitos antes de validar
            $cnpj = preg_replace('/\D/', '', (string) $request->input('cnpj_fornecedor'));
            $cpf  = preg_replace('/\D/', '', (string) $request->input('cpf_fornecedor'));
            $request->merge([
                'cnpj_fornecedor' => $cnpj,
                'cpf_fornecedor'  => $cpf,
            ]);

            // Validar CNPJ se for pessoa jurídica
            if (! empty($request->is_juridico) && $request->is_juridico == 1) {
                if (! $this->validarCNPJ($request->cnpj_fornecedor)) {
                    return back()->withErrors('CNPJ Inválido');
                }
            }

            // Validar CPF se for pessoa física
            if (! empty($request->is_juridico) && $request->is_juridico == 0) {
                if (! $this->validarCPF($request->cpf_fornecedor)) {
                    return back()->withErrors('CPF Inválido');
                }
            }

            // Validação dos dados
            $validated = $request->validate([
                'nome_fornecedor' => 'required|string|max:255',
                'apelido_fornecedor' => 'nullable|string|max:255',
                'cnpj_fornecedor' => 'required_if:is_juridico,1|digits:14',
                'cpf_fornecedor' => 'required_if:is_juridico,0|digits:11',
                'id_tipo_fornecedor' => 'required',
                'site' => 'nullable|string|max:255',
                'email' => 'required|string|email|max:255',
                'is_ativo' => 'boolean',
                'inscricao_estadual' => 'nullable|string|max:255',
                'is_juridico' => 'boolean',
                'rua' => 'nullable|string|max:255',
                'cep' => 'nullable|string',
                'complemento' => 'nullable|string',
                'numero' => 'nullable|string',
                'bairro' => 'nullable|string',
                'nome_municipio' => 'nullable|string',
                'id_uf' => 'nullable|string',
                'telefone_fixo' => 'nullable|string',
                'telefone_celular' => 'nullable|string',
                'telefone_contato' => 'nullable|string',
                'contato_comercial' => 'nullable|string',
                'possui_percentual' => 'boolean',
                'percentual_compra' => 'nullable|numeric',
            ]);

            $conn = DB::connection('pgsql');
            $conn->beginTransaction();

            // Buscar o município pelo nome
            $municipio = null;
            if (! empty($validated['nome_municipio'])) {
                $municipio = Municipio::whereRaw(
                    'unaccent(LOWER(nome_municipio)) = unaccent(LOWER(?))',
                    [$validated['nome_municipio']]
                )->first();

                if (! $municipio) {
                    throw new \Exception("Município '{$validated['nome_municipio']}' não encontrado.");
                }
            }

            // Atualizar o fornecedor
            $fornecedor = Fornecedor::findOrFail($id);
            $fornecedor->fill($validated);
            $fornecedor->data_alteracao = now();

            // Definir valores padrão para campos não presentes no request
            $fornecedor->possui_percentual = $request->has('possui_percentual') ? $request->input('possui_percentual') : $fornecedor->possui_percentual;
            $fornecedor->percentual_compra = $request->has('percentual_compra') ? $request->input('percentual_compra') : $fornecedor->percentual_compra;

            if ($municipio) {
                $fornecedor->id_uf = $municipio->id_uf;
            }

            $fornecedor->save();
            $contratosIds = [];

            // Processar contratos primeiro - apenas se vierem dados válidos
            if ($request->has('contratos') && is_array($request->contratos)) {

                // Cria uma cópia do array para modificar
                $contratosData = $request->contratos;

                foreach ($contratosData as $index => $contratoData) {

                    // Se o valor do contrato for zero ou vazio, pule a criação/atualização
                    if (empty($contratoData['valor_contrato']) || $contratoData['valor_contrato'] === '0,00') {

                        continue;
                    }

                    // Conversão para formato numérico
                    $valorContrato = isset($contratoData['valor_contrato'])
                        ? (float) str_replace(['.', ','], ['', '.'], $contratoData['valor_contrato'])
                        : null;

                    $saldoContrato = isset($contratoData['saldo_contrato'])
                        ? (float) str_replace(['.', ','], ['', '.'], $contratoData['saldo_contrato'])
                        : $valorContrato;

                    $idContratoExistente = $contratoData['id_contrato_forn'] ?? null;
                    $isNovoContrato = empty($idContratoExistente) || $idContratoExistente == '0' || $idContratoExistente == 0;

                    if (! $isNovoContrato) {

                        $contrato = ContratoFornecedor::find($idContratoExistente);

                        if ($contrato) {
                            $contrato->update([
                                'valor_contrato' => $valorContrato,
                                'saldo_contrato' => $saldoContrato,
                                'is_valido' => $contratoData['is_valido'] ?? 1,
                                'data_inicial' => $contratoData['data_inicial'] ?? null,
                                'data_final' => $contratoData['data_final'] ?? null,
                                'id_user_alteracao' => Auth::id(),
                                'data_alteracao' => now(),
                            ]);

                            $contratosIds[] = $contrato->id_contrato_forn;
                        } else {
                            // Não cria novo se era pra ser update mas não encontrou
                        }
                    } else {
                        // Só cria novo contrato se realmente for necessário
                        if ($valorContrato > 0) {

                            $novoContrato = ContratoFornecedor::create([
                                'valor_contrato' => $valorContrato,
                                'saldo_contrato' => $saldoContrato,
                                'id_fornecedor' => $fornecedor->id_fornecedor,
                                'id_user_cadastro' => Auth::id(),
                                'is_valido' => $contratoData['is_valido'] ?? 1,
                                'data_inclusao' => now(),
                                'data_inicial' => $contratoData['data_inicial'] ?? null,
                                'data_final' => $contratoData['data_final'] ?? null,
                            ]);

                            $contratosIds[] = $novoContrato->id_contrato_forn;

                            // Atualiza a cópia do array com o novo ID
                            $contratosData[$index]['id_contrato_forn'] = $novoContrato->id_contrato_forn;
                        } else {
                        }
                    }
                }

                // Se precisar que o request tenha os IDs atualizados, você pode fazer:
                $request->merge(['contratos' => $contratosData]);
            }
            // Processar modelos de contrato
            if ($request->has('modelo') && is_array($request->modelo)) {

                $contratoModeloIds = [];

                foreach ($request->modelo as $index => $modeloData) {

                    // VALIDAÇÃO IMPORTANTE: Use o id_contrato que veio do modeloData
                    // Se veio id_contrato no modelo, use esse (é o ID existente)
                    $idContrato = $modeloData['id_contrato'] ?? null;

                    if (empty($idContrato)) {

                        continue;
                    }

                    // Validar dados obrigatórios
                    if (empty($modeloData['id_modelo'])) {
                        continue;
                    }

                    // Verificar se o contrato realmente existe
                    $contrato = ContratoFornecedor::find($idContrato);
                    if (! $contrato) {
                        throw new \Exception("Contrato não encontrado para o modelo {$index}");
                    }

                    if (! empty($modeloData['id_contrato_modelo'])) {
                        // UPDATE do modelo existente
                        $modelo = ContratoModelo::find($modeloData['id_contrato_modelo']);

                        if ($modelo) {
                            $modelo->update([
                                'data_alteracao' => now(),
                                'id_modelo' => $modeloData['id_modelo'],
                                'id_contrato' => $idContrato,
                                'ativo' => $modeloData['ativo'] ?? 1,
                                'id_user' => Auth::id(),
                            ]);

                            $contratoModeloIds[] = $modelo->id_contrato_modelo;
                        } else {
                        }
                    } else {
                        // CREATE - Novo modelo

                        $novoModelo = ContratoModelo::create([
                            'data_inclusao' => now(),
                            'data_alteracao' => now(),
                            'id_modelo' => $modeloData['id_modelo'],
                            'id_contrato' => $idContrato,
                            'ativo' => $modeloData['ativo'] ?? 1,
                            'id_user' => Auth::id(),
                        ]);

                        $contratoModeloIds[] = $novoModelo->id_contrato_modelo;
                    }
                }

                // Opcional: Deletar modelos removidos
                // ContratoModelo::whereNotIn('id_contrato_modelo', $contratoModeloIds)->delete();
            }

            if ($request->has('servico') && is_array($request->servico)) {

                $servicoIds = [];

                foreach ($request->servico as $index => $servicoData) {

                    // Verificar se todos os campos obrigatórios estão presentes
                    if (empty($servicoData['id_contrato_forn'])) {
                        continue;
                    }

                    $valorServico = isset($servicoData['valor_servico'])
                        ? (float) str_replace(',', '.', str_replace('.', '', $servicoData['valor_servico']))
                        : 0;
                    // Verificar se é um update (tem ID numérico) ou create (ID vazio ou temporário)
                    $idServico = $servicoData['id_servico_forn'] ?? null;

                    // Se o ID começar com "temp_", é um novo registro
                    $isNovoRegistro = empty($idServico) || str_starts_with($idServico, 'temp_');

                    if (! $isNovoRegistro) {
                        // UPDATE - Serviço existente
                        $servico = ServicoFornecedor::find($idServico);

                        if ($servico) {
                            $servico->update([
                                'id_grupo_servico' => $servicoData['id_grupo_servico'] ?? null,
                                'id_contrato_forn' => $servicoData['id_contrato_forn'],
                                'id_contrato_modelo' => $servicoData['id_contrato_modelo'] ?? null,
                                'id_servico' => $servicoData['id_servico'] ?? null,
                                'valor_servico' => $valorServico ?? 0,
                                'is_valido' => $servicoData['is_valido'] ?? 1,
                            ]);

                            $servicoIds[] = $servico->id_servico_forn;
                        } else {
                        }
                    } else {
                        // CREATE - Novo serviço
                        $novoServico = ServicoFornecedor::create([
                            'id_fornecedor' => $fornecedor->id_fornecedor,
                            'id_grupo_servico' => $servicoData['id_grupo_servico'] ?? null,
                            'id_contrato_forn' => $servicoData['id_contrato_forn'],
                            'id_contrato_modelo' => $servicoData['id_contrato_modelo'] ?? null,
                            'id_servico' => $servicoData['id_servico'] ?? null,
                            'valor_servico' => $servicoData['valor_servico'] ?? 0,
                            'is_valido' => $servicoData['is_valido'] ?? 1,
                        ]);

                        $servicoIds[] = $novoServico->id_servico_forn;
                    }
                }

                // Opcional: Deletar serviços que foram removidos do formulário
                // ServicoFornecedor::where('id_fornecedor', $fornecedor->id_fornecedor)
                //                 ->whereNotIn('id_servico_forn', $servicoIds)
                //                 ->delete();
            }

            if ($request->has('pecas') && is_array($request->pecas)) {
                Log::info('pecas recebido:', $request->pecas);

                $pecasIds = [];

                foreach ($request->pecas as $index => $pecasData) {
                    Log::info("Processando pecas {$index}:", $pecasData);

                    // Verificar se todos os campos obrigatórios estão presentes
                    if (empty($pecasData['id_contrato_forn'])) {
                        Log::info('Contrato não informado para o serviço ' . $index);

                        continue;
                    }

                    // Verificar se é um update (tem ID numérico) ou create (ID vazio ou temporário)
                    $idpecas = $pecasData['id_pecas_forn'] ?? null;

                    // Se o ID começar com "temp_", é um novo registro
                    $isNovoRegistro = empty($idpecas) || str_starts_with($idpecas, 'temp_');

                    if (! $isNovoRegistro) {
                        // UPDATE - Serviço existente
                        $pecas = pecasFornecedor::find($idpecas);

                        if ($pecas) {
                            $pecas->update([
                                'id_grupo_pecas' => $pecasData['id_grupo_pecas'] ?? null,
                                'id_contrato_forn' => $pecasData['id_contrato_forn'],
                                'id_contrato_modelo' => $pecasData['id_contrato_modelo'] ?? null,
                                'id_produto' => $pecasData['id_produto'] ?? null,
                                'valor_produto' => $pecasData['valor_produto'] ?? 0,
                                'is_valido' => $pecasData['is_valido'] ?? 1,
                            ]);

                            $pecasIds[] = $pecas->id_pecas_forn;
                            Log::info("pecas {$idpecas} atualizado");
                        } else {
                            Log::warning("pecas {$idpecas} não encontrado para atualização");
                        }
                    } else {
                        // CREATE - Novo serviço
                        $novopecas = pecasFornecedor::create([
                            'id_fornecedor' => $fornecedor->id_fornecedor,
                            'id_grupo_pecas' => $pecasData['id_grupo_pecas'] ?? null,
                            'id_contrato_forn' => $pecasData['id_contrato_forn'],
                            'id_contrato_modelo' => $pecasData['id_contrato_modelo'] ?? null,
                            'id_produto' => $pecasData['id_produto'] ?? null,
                            'valor_produto' => $pecasData['valor_produto'] ?? 0,
                            'is_valido' => $pecasData['is_valido'] ?? 1,
                        ]);

                        $pecasIds[] = $novopecas->id_pecas_forn;
                        Log::info("Novo pecas criado: {$novopecas->id_pecas_forn}");
                    }
                }

                // Opcional: Deletar serviços que foram removidos do formulário
                // ServicoFornecedor::where('id_fornecedor', $fornecedor->id_fornecedor)
                //                 ->whereNotIn('id_servico_forn', $servicoIds)
                //                 ->delete();
            }

            // Atualizar o endereço
            if ($request->filled('enderecos_json')) {
                $enderecos = json_decode($request->enderecos_json, true);

                foreach ($enderecos as $dados) {
                    $endereco = Endereco::find($dados['id_endereco'] ?? null);

                    if ($endereco) {
                        // atualização
                        $endereco->rua = $dados['rua'] ?? null;
                        $endereco->cep = $dados['cep'] ?? null;
                        $endereco->complemento = $dados['complemento'] ?? null;
                        $endereco->numero = $dados['numero'] ?? null;
                        $endereco->bairro = $dados['bairro'] ?? null;
                        $endereco->id_municipio = $dados['id_municipio'] ?? null;
                        $endereco->id_uf = $dados['id_uf'] ?? null;
                        $endereco->data_alteracao = now();
                        $endereco->save();

                        Log::info('Endereço atualizado com sucesso', [
                            'dados' => $endereco->toArray(),
                        ]);
                    } else {
                        // criação
                        Endereco::create([
                            'id_fornecedor_endereco' => $fornecedor->id_fornecedor,
                            'rua' => $dados['rua'] ?? null,
                            'cep' => $dados['cep'] ?? null,
                            'complemento' => $dados['complemento'] ?? null,
                            'numero' => $dados['numero'] ?? null,
                            'bairro' => $dados['bairro'] ?? null,
                            'id_municipio' => $dados['id_municipio'] ?? null,
                            'id_uf' => $dados['id_uf'] ?? null,
                            'data_inclusao' => now(),
                        ]);
                    }
                }
            }
            // Atualizar o telefone
            if ($request->has('telefones') && is_array($request->telefones)) {
                foreach ($request->telefones as $telefoneData) {
                    if (empty($telefoneData['telefone_fixo']) && empty($telefoneData['telefone_celular'])) {
                        continue;
                    }

                    Telefone::updateOrCreate(
                        [
                            'id_fornecedor' => $fornecedor->id_fornecedor,
                        ],
                        [
                            'telefone_fixo' => $telefoneData['telefone_fixo'] ?? null,
                            'telefone_celular' => $telefoneData['telefone_celular'] ?? null,
                            'data_alteracao' => now(),
                        ]
                    );
                }
            }

            // Atualizar Mecanico
            // Atualizar Mecanico
            // No método update
            // $fornecedor = Fornecedor::with('mecanicos.mecanicoInterno')->findOrFail($id);
            // $fornecedor->update($request->except('mecanicos_json'));

            if ($request->filled('mecanicos_json')) {
                try {
                    $mecanicosEnviados = collect(json_decode($request->mecanicos_json, true));
                    $mecanicosAtuais = $fornecedor->mecanicos->keyBy('id_fornecedor_mecanico');

                    $idsManter = [];

                    foreach ($mecanicosEnviados as $mecanico) {
                        if (empty($mecanico['nome_mecanico']) && empty($mecanico['id_user_mecanico'])) {
                            continue;
                        }

                        $dados = [
                            'nome_mecanico' => $mecanico['nome_mecanico'] ?? null,
                            'id_user_mecanico' => $mecanico['id_user_mecanico'] ?? null,
                        ];

                        if (! empty($mecanico['id']) && strpos($mecanico['id'], 'new_') === false) {
                            if ($mecanicosAtuais->has($mecanico['id'])) {
                                $mecanicosAtuais->get($mecanico['id'])->update(
                                    $dados + ['data_alteracao' => now()]
                                );
                                $idsManter[] = $mecanico['id'];
                            }
                        } else {
                            $novo = $fornecedor->mecanicos()->create(
                                $dados + [
                                    'data_inclusao' => now(),
                                    'data_alteracao' => null,
                                ]
                            );
                            $idsManter[] = $novo->id_fornecedor_mecanico;
                        }
                    }

                    $fornecedor->mecanicos()
                        ->whereNotIn('id_fornecedor_mecanico', $idsManter)
                        ->delete();
                } catch (\Exception $e) {
                    Log::error('Erro ao atualizar mecânicos:', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Carrega os relacionamentos atualizados para retornar à view
            $fornecedor->load('mecanicos.mecanicoInterno');

            $conn->commit();

            // Limpar o cache após atualizar um fornecedor
            Cache::forget('fornecedores_frequentes');
            Cache::forget('fornecedor_' . $fornecedor->id_fornecedor);

            return redirect()->route('admin.fornecedores.index')
                ->with('success', 'Fornecedor atualizado com sucesso!');
        } catch (\Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            Log::error('Erro ao atualizar fornecedor', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocorreu um erro ao atualizar o fornecedor: ' . $e->getMessage());
        }
    }

    public function updateContrato(Request $request, $id)
    {

        $fornecedor = Fornecedor::where('id_fornecedor')->findOrFail($id);

        if ($request->has('contratos') && is_array($request->contratos)) {
            $contratosIds = [];

            foreach ($request->contratos as $contratoData) {
                if (empty($contratoData['valor_contrato'])) {
                    continue;
                }

                // Conversão para formato numérico aceito pelo Postgres
                $valorContrato = isset($contratoData['valor_contrato'])
                    ? (float) str_replace(['.', ','], ['', '.'], $contratoData['valor_contrato'])
                    : null;

                $saldoContrato = isset($contratoData['saldo_contrato'])
                    ? (float) str_replace(['.', ','], ['', '.'], $contratoData['saldo_contrato'])
                    : $valorContrato; // Se saldo não informado, usa o valor do contrato

                // Verificar se é um update (tem ID) ou create (não tem ID)
                if (! empty($contratoData['id_contrato_forn'])) {
                    // UPDATE - Contrato existente
                    $contrato = ContratoFornecedor::find($contratoData['id_contrato_forn']);

                    if ($contrato) {
                        $contrato->update([
                            'valor_contrato' => $valorContrato,
                            'saldo_contrato' => $saldoContrato,
                            'is_valido' => $contratoData['is_valido'] ?? 1,
                            'data_inicial' => $contratoData['data_inicial'] ?? null,
                            'data_final' => $contratoData['data_final'] ?? null,
                            'id_user_alteracao' => Auth::id(),
                            'data_alteracao' => now(),
                        ]);

                        $contratosIds[] = $contrato->id_contrato_forn;
                    }
                } else {
                    // CREATE - Novo contrato
                    $novoContrato = ContratoFornecedor::create([
                        'valor_contrato' => $valorContrato,
                        'saldo_contrato' => $saldoContrato,
                        'id_fornecedor' => $fornecedor->id_fornecedor,
                        'id_user_cadastro' => Auth::id(),
                        'is_valido' => $contratoData['is_valido'] ?? 1,
                        'data_inclusao' => now(),
                        'data_inicial' => $contratoData['data_inicial'] ?? null,
                        'data_final' => $contratoData['data_final'] ?? null,
                    ]);

                    $contratosIds[] = $novoContrato->id_contrato_forn;
                }
            }

            // Excluir contratos que foram removidos do formulário
            if (! empty($contratosIds)) {
                ContratoFornecedor::where('id_fornecedor', $fornecedor->id_fornecedor)
                    ->whereNotIn('id_contrato_forn', $contratosIds)
                    ->delete();
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            // Verificar se existem relações dependentes
            $fornecedor = Fornecedor::findOrFail($id);

            // Verificar contratos
            $hasContratos = ContratoFornecedor::where('id_fornecedor', $id)->exists();
            if ($hasContratos) {
                throw new \Exception('Não é possível excluir este fornecedor pois existem contratos associados.');
            }

            // Verificar serviços
            $hasServicos = ServicoFornecedor::where('id_fornecedor', $id)->exists();
            if ($hasServicos) {
                throw new \Exception('Não é possível excluir este fornecedor pois existem serviços associados.');
            }

            // Verificar peças
            $hasPecas = PecasFornecedor::where('id_fornecedor', $id)->exists();
            if ($hasPecas) {
                throw new \Exception('Não é possível excluir este fornecedor pois existem peças associadas.');
            }

            // Excluir telefones
            Telefone::where('id_fornecedor', $id)->delete();

            // Excluir endereços
            Endereco::where('id_fornecedor_endereco', $id)->delete();

            // Excluir o fornecedor
            $fornecedor->delete();

            DB::commit();

            // Limpar o cache após excluir um fornecedor
            Cache::forget('fornecedores_frequentes');
            Cache::forget('fornecedor_' . $id);

            return response()->json([
                'success' => true,
                'message' => 'Fornecedor excluído com sucesso!',
                'notification' => [
                    'title' => 'Sucesso',
                    'message' => 'Fornecedor excluído com sucesso!',
                    'type' => 'success',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir fornecedor: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'notification' => [
                    'title' => 'Erro',
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ],
            ], 422);
        }
    }

    /**
     * Buscar fornecedores para autocompletar
     */
    public function search(Request $request)
    {
        $term = $request->get('term');

        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        $termLower = strtolower($term);

        $query = Fornecedor::select('id_fornecedor', 'nome_fornecedor', 'cpf_fornecedor', 'cnpj_fornecedor')
            ->where('is_ativo', true);

        // Se o termo for numérico, busca PRIMEIRO por ID exato
        if (is_numeric($term)) {
            $fornecedorExato = Fornecedor::select('id_fornecedor', 'nome_fornecedor', 'cpf_fornecedor', 'cnpj_fornecedor')
                ->where('is_ativo', true)
                ->where('id_fornecedor', (int)$term)
                ->first();

            // Se encontrou o fornecedor pelo ID, retorna ele primeiro
            if ($fornecedorExato) {
                $suffix = '';
                if (!empty($fornecedorExato->cnpj_fornecedor)) {
                    $suffix = ' - ' . $fornecedorExato->cnpj_fornecedor;
                } elseif (!empty($fornecedorExato->cpf_fornecedor)) {
                    $suffix = ' - ' . $fornecedorExato->cpf_fornecedor;
                }

                $resultadoPrincipal = [
                    'label' => $fornecedorExato->id_fornecedor . ' - ' . $fornecedorExato->nome_fornecedor . $suffix,
                    'value' => $fornecedorExato->id_fornecedor
                ];

                // Busca outros fornecedores que tenham o termo no nome/apelido
                $outrosFornecedores = Fornecedor::select('id_fornecedor', 'nome_fornecedor', 'cpf_fornecedor', 'cnpj_fornecedor')
                    ->where('is_ativo', true)
                    ->where('id_fornecedor', '!=', (int)$term)
                    ->where(function ($q) use ($termLower) {
                        $q->whereRaw('LOWER(nome_fornecedor) LIKE ?', ["%{$termLower}%"])
                            ->orWhereRaw('LOWER(apelido_fornecedor) LIKE ?', ["%{$termLower}%"]);
                    })
                    ->orderBy('nome_fornecedor')
                    ->limit(29)
                    ->get()
                    ->map(function ($f) {
                        $suffix = '';
                        if (!empty($f->cpf_fornecedor)) {
                            $suffix = ' - ' . $f->cpf_fornecedor;
                        } elseif (!empty($f->cnpj_fornecedor)) {
                            $suffix = ' - ' . $f->cnpj_fornecedor;
                        }
                        return ['label' => $f->id_fornecedor . ' - ' . $f->nome_fornecedor . $suffix, 'value' => $f->id_fornecedor];
                    })->toArray();

                // Retorna o fornecedor exato primeiro, depois os outros
                return response()->json(array_merge([$resultadoPrincipal], $outrosFornecedores));
            }
        }

        // Se não encontrou por ID ou não é numérico, busca por nome/apelido
        $fornecedores = $query->where(function ($q) use ($termLower) {
            $q->whereRaw('LOWER(nome_fornecedor) LIKE ?', ["%{$termLower}%"])
                ->orWhereRaw('LOWER(apelido_fornecedor) LIKE ?', ["%{$termLower}%"])
                ->where('is_ativo', true);
        })
            ->orderBy('nome_fornecedor')
            ->limit(30)
            ->get()
            ->map(function ($f) {
                $suffix = '';
                if (!empty($f->cpf_fornecedor)) {
                    $suffix = ' - ' . $f->cpf_fornecedor;
                } elseif (!empty($f->cnpj_fornecedor)) {
                    $suffix = ' - ' . $f->cnpj_fornecedor;
                }
                return ['label' => $f->id_fornecedor . ' - ' . $f->nome_fornecedor . $suffix, 'value' => $f->id_fornecedor];
            })->toArray();

        return response()->json($fornecedores);
    }

    /**
     * Buscar um fornecedor pelo ID
     */
    public function getById($id)
    {
        $fornecedor = Fornecedor::where('is_ativo', true)->where('id_fornecedor', $id)->first();

        if (!$fornecedor) {
            return response()->json(['error' => 'Fornecedor não encontrado'], 404);
        }

        return response()->json([
            'value' => $fornecedor->id_fornecedor,
            'label' => $fornecedor->nome_fornecedor,
            'cnpj_fornecedor' => $fornecedor->cnpj_fornecedor,
            'nome_fornecedor' => $fornecedor->nome_fornecedor,
            'email' => $fornecedor->email,
        ]);
    }

    // Endpoints API para Contratos

    /**
     * Obter contratos de um fornecedor
     */
    public function getContratos($fornecedorId, $contratoId, $id)
    {
        $contratos = ContratoFornecedor::where('id_fornecedor', $id)
            ->with('userCadastro')
            ->orderBy('id_contrato_forn', 'desc')
            ->get();

        return response()->json($contratos);
    }

    /**
     * Obter um contrato específico
     */
    public function getContrato($id)
    {
        try {
            $contrato = ContratoFornecedor::with(['fornecedor', 'userCadastro'])
                ->findOrFail($id);

            return response()->json($contrato);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Contrato não encontrado',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Excluir um contrato
     */
    public function destroyContrato($id)
    {
        // Validação inicial do ID
        if (! is_numeric($id)) {
            return response()->json([
                'success' => false,
                'message' => 'ID do contrato inválido',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $contrato = ContratoFornecedor::find($id);

            if (! $contrato) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrato não encontrado',
                ], 404);
            }

            // Verificação de vínculos
            $relacoes = [
                ContratoModelo::class => ['coluna' => 'id_contrato', 'nome' => 'modelos'],
                ServicoFornecedor::class => ['coluna' => 'id_contrato_forn',    'nome' => 'serviços'],
                PecasFornecedor::class => ['coluna' => 'id_contrato_forn',      'nome' => 'peças'],
            ];

            foreach ($relacoes as $model => $dados) {
                if ($model::where($dados['coluna'], $id)->exists()) {
                    throw new \Exception("Não é possível excluir: existem vínculos com {$dados['nome']}.");
                }
            }

            // Exclusão do documento
            if ($contrato->doc_contrato && Storage::disk('public')->exists($contrato->doc_contrato)) {
                Storage::disk('public')->delete($contrato->doc_contrato);
            }

            $contrato->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contrato excluído com sucesso',
                'id' => $id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao excluir contrato {$id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Clonar um contrato
     */
    public function cloneContrato($id)
    {
        try {
            DB::beginTransaction();

            $contrato = ContratoFornecedor::findOrFail($id);

            // Criar um novo contrato com os mesmos dados
            $novoContrato = $contrato->replicate();
            $novoContrato->data_inclusao = now();
            $novoContrato->data_alteracao = null;
            $novoContrato->id_user_cadastro = Auth::id();

            // Copia o documento do contrato, se existir
            if ($contrato->doc_contrato) {
                $oldPath = $contrato->doc_contrato;
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newPath = 'contratos/copia-' . time() . '-' . uniqid() . '.' . $extension;

                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->copy($oldPath, $newPath);
                    $novoContrato->doc_contrato = $newPath;
                }
            }

            $novoContrato->push();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contrato clonado com sucesso',
                'contrato' => $novoContrato->load(['userCadastro', 'fornecedor']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao clonar contrato: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao clonar contrato: ' . $e->getMessage(),
            ], 422);
        }
    }

    // Endpoints API para Contratos-Modelo

    /**
     * Obter um contrato-modelo específico
     */
    public function getContratoModelo($id)
    {
        $contratoModelo = ContratoModelo::with(['contrato', 'modelo'])->findOrFail($id);

        return response()->json($contratoModelo);
    }

    /**
     * Obter modelos vinculados a um contrato
     */
    public function getContratosModelo($contratoId)
    {
        $contratosModelo = ContratoModelo::where('id_contrato', $contratoId)
            ->with(['modelo'])
            ->get();

        return response()->json($contratosModelo);
    }

    /**
     * Criar ou atualizar um contrato-modelo
     */
    public function storeContratoModelo(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'id_contrato' => 'required|exists:contrato_fornecedor,id_contrato_forn',
                'id_modelo' => 'required|exists:modelo_veiculo,id_modelo_veiculo',
                'ativo' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            // Verificar se já existe um vínculo para este contrato e modelo
            $contratoModeloExistente = ContratoModelo::where('id_contrato', $validated['id_contrato'])
                ->where('id_modelo', $validated['id_modelo'])
                ->where('id_contrato_modelo', '!=', $request->id_contrato_modelo ?? 0)
                ->first();

            if ($contratoModeloExistente) {
                throw new \Exception('Já existe um vínculo para este contrato e modelo.');
            }

            // Criar ou atualizar o contrato-modelo
            $contratoModelo = null;
            if ($request->filled('id_contrato_modelo')) {
                $contratoModelo = ContratoModelo::findOrFail($request->id_contrato_modelo);
                $contratoModelo->data_alteracao = now();
            } else {
                $contratoModelo = new ContratoModelo;
                $contratoModelo->data_inclusao = now();
                $contratoModelo->id_user = Auth::id();
            }

            $contratoModelo->id_contrato = $validated['id_contrato'];
            $contratoModelo->id_modelo = $validated['id_modelo'];
            $contratoModelo->ativo = $validated['ativo'];

            $contratoModelo->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vínculo contrato-modelo salvo com sucesso',
                'contratoModelo' => $contratoModelo->load(['contrato', 'modelo']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar contrato-modelo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar vínculo: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Excluir um contrato-modelo
     */
    public function destroyContratoModelo($id)
    {
        try {
            DB::beginTransaction();

            $contratoModelo = ContratoModelo::findOrFail($id);

            // Verificar se existem vínculos
            $hasServicos = ServicoFornecedor::where('id_contrato_modelo', $id)->exists();
            if ($hasServicos) {
                throw new \Exception('Não é possível excluir este vínculo pois existem serviços associados.');
            }

            $hasPecas = PecasFornecedor::where('id_contrato_modelo', $id)->exists();
            if ($hasPecas) {
                throw new \Exception('Não é possível excluir este vínculo pois existem peças associadas.');
            }

            $contratoModelo->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vínculo contrato-modelo excluído com sucesso',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir contrato-modelo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Clonar um contrato-modelo
     */
    public function cloneContratoModelo($id)
    {
        try {
            DB::beginTransaction();

            $contratoModelo = ContratoModelo::findOrFail($id);

            // Criar um novo contrato-modelo com os mesmos dados
            $novoContratoModelo = $contratoModelo->replicate();
            $novoContratoModelo->data_inclusao = now();
            $novoContratoModelo->data_alteracao = null;
            $novoContratoModelo->id_user = Auth::id();
            $novoContratoModelo->push();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vínculo contrato-modelo clonado com sucesso',
                'contratoModelo' => $novoContratoModelo->load(['contrato', 'modelo']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao clonar contrato-modelo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao clonar vínculo: ' . $e->getMessage(),
            ], 422);
        }
    }

    // Endpoints API para Serviços

    /**
     * Obter serviços de um grupo
     */
    public function getServicos($grupoId)
    {
        $servicos = Servico::where('id_grupo', $grupoId)
            ->orderBy('descricao')
            ->get();

        return response()->json($servicos);
    }

    /**
     * Obter um serviço de fornecedor específico
     */
    public function getServicoFornecedor($id)
    {
        $servicoFornecedor = ServicoFornecedor::with(['servico', 'grupoServico', 'contrato', 'contratoModelo.modelo'])
            ->findOrFail($id);

        return response()->json($servicoFornecedor);
    }

    /**
     * Criar ou atualizar um serviço de fornecedor
     */
    public function storeServicoFornecedor(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'id_fornecedor' => 'required|exists:fornecedor,id_fornecedor',
                'id_contrato' => 'required|exists:contrato_fornecedor,id_contrato_forn',
                'id_grupo' => 'required|exists:grupo_servico,id_grupo',
                'id_servico' => 'required|exists:servico,id_servico',
                'valor_servico' => 'required|numeric|min:0',
                'ativo' => 'required|boolean',
                'id_contrato_modelo' => 'nullable|exists:contrato_modelo,id_contrato_modelo',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            // Verificar se já existe um serviço para este contrato/modelo/serviço
            $query = ServicoFornecedor::where('id_fornecedor', $validated['id_fornecedor'])
                ->where('id_contrato_forn', $validated['id_contrato'])
                ->where('id_servico', $validated['id_servico']);

            if ($request->filled('id_contrato_modelo')) {
                $query->where('id_contrato_modelo', $validated['id_contrato_modelo']);
            } else {
                $query->whereNull('id_contrato_modelo');
            }

            if ($request->filled('id_servico_forn')) {
                $query->where('id_servico_forn', '!=', $request->id_servico_forn);
            }

            $servicoExistente = $query->first();

            if ($servicoExistente) {
                throw new \Exception('Já existe um serviço cadastrado para este contrato/modelo/serviço.');
            }

            // Criar ou atualizar o serviço
            $servico = null;
            if ($request->filled('id_servico_forn')) {
                $servico = ServicoFornecedor::findOrFail($request->id_servico_forn);
                $servico->data_alteracao = now();
            } else {
                $servico = new ServicoFornecedor;
                $servico->data_inclusao = now();
                $servico->id_user = Auth::id();
            }

            $servico->id_fornecedor = $validated['id_fornecedor'];
            $servico->id_contrato_forn = $validated['id_contrato'];
            $servico->id_grupo = $validated['id_grupo'];
            $servico->id_servico = $validated['id_servico'];
            $servico->valor_servico = $validated['valor_servico'];
            $servico->ativo = $validated['ativo'];

            if ($request->filled('id_contrato_modelo')) {
                $servico->id_contrato_modelo = $validated['id_contrato_modelo'];
            } else {
                $servico->id_contrato_modelo = null;
            }

            $servico->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Serviço salvo com sucesso',
                'servico' => $servico->load(['servico', 'grupoServico', 'contrato', 'contratoModelo.modelo']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar serviço: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar serviço: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Excluir um serviço de fornecedor
     */
    public function destroyServicoFornecedor($id)
    {
        try {
            DB::beginTransaction();

            $servico = ServicoFornecedor::findOrFail($id);
            $servico->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Serviço excluído com sucesso',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir serviço: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // Endpoints API para Peças

    /**
     * Obter produtos de um grupo
     */
    public function getProdutos($grupoId)
    {
        $produtos = Produto::where('id_grupo_pecas', $grupoId)
            ->orderBy('descricao')
            ->get();

        return response()->json($produtos);
    }

    /**
     * Obter uma peça de fornecedor específica
     */
    public function getPecaFornecedor($id)
    {
        $pecaFornecedor = PecasFornecedor::with(['produto', 'grupoServico', 'contrato', 'contratoModelo.modelo'])
            ->findOrFail($id);

        return response()->json($pecaFornecedor);
    }

    /**
     * Criar ou atualizar uma peça de fornecedor
     */
    public function storePecaFornecedor(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'id_fornecedor' => 'required|exists:fornecedor,id_fornecedor',
                'id_contrato' => 'required|exists:contrato_fornecedor,id_contrato_forn',
                'id_grupo_pecas' => 'required|exists:grupo_pecas,id_grupo_pecas',
                'id_produto' => 'required|exists:produto,id_produto',
                'valor_peca' => 'required|numeric|min:0',
                'ativo' => 'required|boolean',
                'id_contrato_modelo' => 'nullable|exists:contrato_modelo,id_contrato_modelo',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            // Verificar se já existe uma peça para este contrato/modelo/produto
            $query = PecasFornecedor::where('id_fornecedor', $validated['id_fornecedor'])
                ->where('id_contrato_forn', $validated['id_contrato'])
                ->where('id_produto', $validated['id_produto']);

            if ($request->filled('id_contrato_modelo')) {
                $query->where('id_contrato_modelo', $validated['id_contrato_modelo']);
            } else {
                $query->whereNull('id_contrato_modelo');
            }

            if ($request->filled('id_pecas_forn')) {
                $query->where('id_pecas_forn', '!=', $request->id_pecas_forn);
            }

            $pecaExistente = $query->first();

            if ($pecaExistente) {
                throw new \Exception('Já existe uma peça cadastrada para este contrato/modelo/produto.');
            }

            // Criar ou atualizar a peça
            $peca = null;
            if ($request->filled('id_pecas_forn')) {
                $peca = PecasFornecedor::findOrFail($request->id_pecas_forn);
                $peca->data_alteracao = now();
            } else {
                $peca = new PecasFornecedor;
                $peca->data_inclusao = now();
                $peca->id_user = Auth::id();
            }

            $peca->id_fornecedor = $validated['id_fornecedor'];
            $peca->id_contrato_forn = $validated['id_contrato'];
            $peca->id_grupo_pecas = $validated['id_grupo_pecas'];
            $peca->id_produto = $validated['id_produto'];
            $peca->valor_peca = $validated['valor_peca'];
            $peca->ativo = $validated['ativo'];

            if ($request->filled('id_contrato_modelo')) {
                $peca->id_contrato_modelo = $validated['id_contrato_modelo'];
            } else {
                $peca->id_contrato_modelo = null;
            }

            $peca->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Peça salva com sucesso',
                'peca' => $peca->load(['produto', 'grupoServico', 'contrato', 'contratoModelo.modelo']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar peça: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar peça: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Excluir uma peça de fornecedor
     */
    public function destroyPecaFornecedor($id)
    {
        try {
            DB::beginTransaction();

            $peca = PecasFornecedor::findOrFail($id);
            $peca->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Peça excluída com sucesso',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir peça: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Verifica se um CNPJ já existe no sistema
     */
    private function cnpjJaExiste($cnpj, $excluirId = null)
    {
        $cnpjSemMascara = preg_replace('/[^0-9]/', '', $cnpj);
        $query = Fornecedor::where(function ($q) use ($cnpj, $cnpjSemMascara) {
            $q->where('cnpj_fornecedor', $cnpj)
                ->orWhere('cnpj_fornecedor', $cnpjSemMascara);
        })->where('is_ativo', true);

        if ($excluirId) {
            $query->where('id_fornecedor', '!=', $excluirId);
        }

        return $query->exists();
    }

    /**
     * Validar um CNPJ
     */
    private function validarCNPJ($cnpj)
    {
        // Implementação da validação de CNPJ
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Verifica se o CNPJ tem 14 dígitos
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1+$/', $cnpj)) {
            return false;
        }

        // Algoritmo de validação do CNPJ
        $j = 5;
        $k = 6;
        $soma1 = 0;
        $soma2 = 0;

        for ($i = 0; $i < 13; $i++) {
            $j = $j == 1 ? 9 : $j;
            $k = $k == 1 ? 9 : $k;

            $soma2 += ($cnpj[$i] * $k);

            if ($i < 12) {
                $soma1 += ($cnpj[$i] * $j);
            }

            $k--;
            $j--;
        }

        $digito1 = $soma1 % 11 < 2 ? 0 : 11 - $soma1 % 11;
        $digito2 = $soma2 % 11 < 2 ? 0 : 11 - $soma2 % 11;

        return ($cnpj[12] == $digito1) && ($cnpj[13] == $digito2);
    }

    /**
     * Validar um CPF
     */
    private function validarCPF($cpf)
    {
        // Implementação da validação de CPF
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verifica se o CPF tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }

        // Algoritmo de validação do CPF
        for ($t = 9; $t < 11; $t++) {
            $d = 0;

            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$t] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retorna um município específico pelo ID
     */
    public function single($id)
    {
        $fornecedor = Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
            ->where('is_ativo', true)->where('id_fornecedor', $id)->first();

        return response()->json($fornecedor);
    }

    public function getFornecedores(Request $request)
    {
        try {
            $cnpj = $request->input('cnpj');

            // Validação básica
            if (! $cnpj) {
                return response()->json(['error' => 'CNPJ é obrigatório'], 400);
            }

            $fornecedor = Fornecedor::select('id_fornecedor', 'nome_fornecedor')
                ->with('endereco.municipio')
                ->where('is_ativo', true)
                ->where('cnpj_fornecedor', $cnpj)
                ->first();

            if (! $fornecedor) {
                return response()->json(['error' => 'Fornecedor não encontrado'], 404);
            }

            $endereco = null;
            if ($fornecedor->endereco) {
                if ($fornecedor->endereco instanceof \Illuminate\Database\Eloquent\Collection) {
                    $endereco = $fornecedor->endereco->first();
                } else {
                    $endereco = $fornecedor->endereco;
                }
            }

            $dadosFormatados = [
                'id_fornecedor' => $fornecedor->id_fornecedor,
                'nome_fornecedor' => $fornecedor->nome_fornecedor,
                'rua' => $endereco?->rua ?? 'N/A',
                'numero' => $endereco?->numero ?? 'N/A',
                'bairro' => $endereco?->bairro ?? 'N/A',
                'cidade' => $endereco?->cidade ?? 'N/A',
                'id_municipio' => $endereco?->id_municipio ?? null,
                'nome_municipio' => $endereco?->municipio?->nome_municipio ?? 'N/A',
                'uf' => $endereco?->municipio?->uf ?? 'N/A',
                'cep' => $endereco?->cep ?? 'N/A',
            ];

            return response()->json($dadosFormatados);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar fornecedor por CNPJ: ' . $e->getMessage());

            return response()->json([
                'error' => 'Erro interno do servidor',
            ], 500);
        }
    }

    public function getByGrupo($id)
    {
        $servicos = Servico::where('id_grupo', $id)
            ->where('ativo_servico', true)
            ->select('id_servico as value', 'descricao_servico as label')
            ->orderBy('descricao_servico')
            ->get();

        return response()->json($servicos);
    }

    public function getByPecas($id)
    {
        $produtos = Produto::where('id_grupo_servico', $id)
            ->where('is_ativo', true)
            ->select('id_produto as value', 'descricao_produto as label')
            ->orderBy('descricao_produto')
            ->get();

        return response()->json($produtos);
    }

    public function formatarValorParaBanco($valor)
    {
        if (is_null($valor) || $valor === '') {
            return 0;
        }

        // Remove pontos de milhar
        $valor = str_replace('.', '', $valor);

        // Substitui vírgula decimal por ponto
        $valor = str_replace(',', '.', $valor);

        return (float) $valor;
    }

    public function destroyEndereco($id)
    {
        try {
            DB::beginTransaction();

            $endereco = Endereco::findOrFail($id);
            $endereco->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Endereço excluído com sucesso',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir endereço: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
