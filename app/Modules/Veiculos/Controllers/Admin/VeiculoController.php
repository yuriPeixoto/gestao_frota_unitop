<?php

namespace App\Modules\Veiculos\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BaseVeiculo;
use App\Modules\Imobilizados\Models\CadastroImobilizado;
use App\Models\ControlesVeiculo;
use App\Models\Departamento;
use App\Models\Estado;
use App\Models\Filial;
use App\Modules\Compras\Models\Fornecedor;
use App\Models\KmComotado;
use App\Models\ModeloVeiculo;
use App\Models\Motorista;
use App\Models\Municipio;
use App\Models\PneusAplicados;
use App\Modules\Compras\Models\RegistroCompraVenda;
use App\Models\SubCategoriaVeiculo;
use App\Models\TipoCategoria;
use App\Modules\Abastecimentos\Models\TipoCombustivel;
use App\Models\TipoEquipamento;
use App\Models\TipoOperacao;
use App\Models\TipoVeiculo;
use App\Models\TransferenciaVeiculo;
use App\Models\Veiculo;
use App\Models\VeiculoNaoTracionado;
use App\Models\VeiculoXPneu;
use App\Traits\ExportableTrait;
use App\Traits\SanitizesMonetaryValues;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VeiculoController extends Controller
{
    use ExportableTrait, SanitizesMonetaryValues;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Veiculo::with(['filialVeiculo', 'categoriaVeiculo', 'modeloVeiculo', 'baseVeiculo'])
            // ->where('situacao_veiculo', true)
            ->select(
                'id_veiculo as id',
                'placa',
                'id_filial',
                'id_base_veiculo',
                'id_categoria',
                'renavam',
                'marca_veiculo',
                'data_compra',
                'is_terceiro',
                'situacao_veiculo',
                'status_cadastro_imobilizado'
            )
            ->orderBy('id_veiculo', 'desc')
            ->distinct();

        if ($request->filled('data_compra')) {
            $query->where('data_compra', $request->data_compra);
        }

        if ($request->filled('placa')) {
            if (is_numeric($request->placa)) {
                $query->where('id_veiculo', $request->placa);
            } else {
                $query->where('placa', 'ILIKE', '%' . $request->placa . '%');
            }
        }
        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('base_veiculo')) {
            $query->where('base', $request->base_veiculo);
        }

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        if ($request->filled('renavam')) {
            $query->where('renavam', $request->renavam);
        }

        if ($request->filled('marca_veiculo')) {
            $query->where('marca_veiculo', $request->marca_veiculo);
        }

        if ($request->filled('is_terceiro')) {
            $query->where('is_terceiro', $request->is_terceiro);
        }

        if ($request->filled('situacao_veiculo')) {
            $query->where('situacao_veiculo', $request->situacao_veiculo);
        } else {
            $query->where('situacao_veiculo', true);
        }

        $query->orderBy('id_veiculo');

        $veiculos = $query->paginate(10);

        $veiculos->getCollection()->transform(function ($item) {
            $item->data_compra = $item->data_compra;

            return $item;
        });

        $formOptions = [
            'filiais' => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'bases' => BaseVeiculo::select('descricao_base as label', 'id_base_veiculo as value')->orderBy('label')->get()->toArray(),
            'categorias' => TipoCategoria::select('descricao_categoria as label', 'id_categoria as value')->orderBy('label')->get()->toArray(),
            'departamentos' => Departamento::select('descricao_departamento as label', 'id_departamento as value')->orderBy('label')->get()->toArray(),
            'modelos' => ModeloVeiculo::select('descricao_modelo_veiculo as label', 'id_modelo_veiculo as value')->orderBy('label')->get()->toArray(),
            'tipoVeiculo' => TipoVeiculo::select('descricao as label', 'id as value')->orderBy('label')->get()->toArray(),
            'tipoCombustiveis' => TipoCombustivel::select('descricao as label', 'id_tipo_combustivel as value')->orderBy('label')->get()->toArray(),
            'fornecedores' => Fornecedor::select('nome_fornecedor as label', 'id_fornecedor as value')->orderBy('label')->get()->toArray(),
            'placa' => Veiculo::select('placa as value', 'placa as label')->orderBy('placa')->limit(30)->get()->toArray(),
            'renavams' => Veiculo::select('renavam as label')
                ->whereNotNull('renavam')
                ->groupBy('renavam')
                ->orderBy('renavam')
                ->get()
                ->map(function ($item) {
                    return [
                        'label' => $item->label,
                        'value' => $item->label, // ou pode mapear para o id se precisar
                    ];
                })
                ->toArray(),
            'veiculos_terceiro' => [
                [
                    'label' => 'Sim',
                    'value' => true,
                ],
                [
                    'label' => 'Não',
                    'value' => false,
                ],
            ],
        ];

        return view('admin.veiculos.index', compact('veiculos', 'formOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $formOptions = [
            'ufs' => Estado::select('uf as label', 'id_uf as value')->orderBy('label')->get()->toArray(),
            'motoristas' => Motorista::select('nome as label', 'idobtermotorista as value')->get()->toArray(),
            'municipios' => Municipio::select('nome_municipio as label', 'id_municipio as value')->limit(30)->orderBy('label')->get()->toArray(),
            'filiais' => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'bases' => BaseVeiculo::select('descricao_base as label', 'id_base_veiculo as value')->orderBy('label')->get()->toArray(),
            'categorias' => TipoCategoria::select('descricao_categoria as label', 'id_categoria as value')->orderBy('label')->get()->toArray(),
            'departamentos' => Departamento::select('descricao_departamento as label', 'id_departamento as value')->orderBy('label')->get()->toArray(),
            'modelos' => ModeloVeiculo::select('descricao_modelo_veiculo as label', 'id_modelo_veiculo as value')->orderBy('label')->get()->toArray(),
            'tipoVeiculo' => TipoVeiculo::select('descricao as label', 'id as value')->orderBy('label')->get()->toArray(),
            'tipoCombustiveis' => TipoCombustivel::select('descricao as label', 'id_tipo_combustivel as value')->orderBy('label')->get()->toArray(),
            'fornecedores' => Fornecedor::where('is_ativo', true)->limit(20)->orderBy('nome_fornecedor')->get(['id_fornecedor as value', 'nome_fornecedor as label']),
            'subCategorias' => SubCategoriaVeiculo::select('descricao_subcategoria as label', 'id_subcategoria as value')->orderBy('label')->get()->toArray(),
            'tipoOperacoes' => TipoOperacao::select('descricao_tipo_operacao as label', 'id_tipo_operacao as value')->orderBy('label')->get()->toArray(),
            'tipoEquipamentos' => TipoEquipamento::select(DB::raw("descricao_tipo || CASE WHEN numero_eixos IS NOT NULL THEN ' - Eixos: ' || numero_eixos::text ELSE '' END AS label,id_tipo_equipamento AS value"))->get()->toArray(),
            // tarzer dados pneus
        ];

        $fornecedoresFrequentes = Cache::remember('fornecedores_frequentes', now()->addMinutes(15), function () {
            return Fornecedor::where('is_ativo', true)
                ->limit(20)
                ->orderBy('nome_fornecedor')
                ->get(['id_fornecedor as value', 'nome_fornecedor as label']);
        });

        $tipoEquipamentoPneus = TipoEquipamento::orderBy('id_tipo_equipamento')->get()->toArray();

        foreach ($tipoEquipamentoPneus as $tipoEquipamentoPneu) {
            $formattedData = [
                'eixos' => $tipoEquipamentoPneu['numero_eixos'],
                'pneus_por_eixo' => [
                    $tipoEquipamentoPneu['numero_pneus_eixo_1'],
                    $tipoEquipamentoPneu['numero_pneus_eixo_2'],
                    $tipoEquipamentoPneu['numero_pneus_eixo_3'],
                    $tipoEquipamentoPneu['numero_pneus_eixo_4'],
                ],
            ];
        }
        $filial = Filial::orderBy('name')
            ->get()
            ->keyBy('id') // <-- chaveando pelo id
            ->map(function ($filial) {
                return $filial->name;
            });

        return view('admin.veiculos.create', compact(
            'formOptions',
            'formattedData',
            'fornecedoresFrequentes',
            'filial'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // Sanitizar os valores monetários
        $this->sanitizeMonetaryValues($request, [
            'valor_venal',
            'valor_do_bem',
            'valor_parcela',
            'valor_processo',
            'valor_da_compra',
            'valor_da_venda',
        ]);

        // Validações
        $dadosVeiculo = $request->validate([
            'imagem_veiculo' => 'nullable|image|max:2048',
            'placa' => 'required|string|max:10',
            'renavam' => 'required|integer',
            'chassi' => 'required|string|max:200',
            'marca_veiculo' => 'required|string|max:200',
            'ano_fabricacao' => 'required|date_format:Y',
            'ano_modelo' => 'required|date_format:Y',
            'capacidade_tanque_principal' => 'required|integer',
            'capacidade_tanque_secundario' => 'required|integer',
            'capacidade_arla' => 'required|integer',
            'cor_veiculo' => 'required|string|max:200',
            'data_compra' => 'nullable|date_format:Y-m-d',
            'valor_venal' => 'nullable|numeric',
            'numero_frota' => 'nullable|integer',
            'km_inicial' => 'nullable|integer',
            'horas_iniciais' => 'nullable|integer',
            'id_uf' => 'nullable|exists:estado,id_uf',
            'id_municipio' => 'nullable|exists:municipio,id_municipio',
            'situacao_veiculo' => 'required|boolean',
            'is_possui_tracao' => 'nullable|boolean',
            'is_marcador_quilometragem' => 'nullable|boolean',
            'is_horas' => 'nullable|boolean',
            'descricao_equipamento' => 'nullable|string|max:200',
            'empresa' => 'nullable|string|max:200',
            'id_tipo_equipamento' => 'nullable|exists:tipoequipamento,id_tipo_equipamento',
            'id_filial' => 'nullable|exists:filiais,id',
            'id_base_veiculo' => 'nullable|exists:base_veiculo,id_base_veiculo',
            'id_departamento' => 'nullable|exists:departamento,id_departamento',
            'id_categoria' => 'nullable|exists:categoria_veiculo,id_categoria',
            'id_modelo_veiculo' => 'nullable|exists:modelo_veiculo,id_modelo_veiculo',
            'id_tipo_combustivel' => 'nullable|exists:tipocombustivel,id_tipo_combustivel',
            'id_tipo_veiculo' => 'nullable|exists:tipo_veiculo,id',
            'telemetria' => 'required|boolean',
            'is_terceiro' => 'required',
            'id_fornecedor' => 'nullable',
            'contrato_manutencao' => 'required|boolean',
            'id_subcategoria_veiculo' => 'nullable|exists:subcategoria,id_subcategoria',
            'id_operacao' => 'nullable|exists:tipo_operacao,id_tipo_operacao',
            'id_fornecedor_comodato' => 'nullable',
            'data_comodato' => 'nullable',
        ]);

        $dadosCompraVeiculo = $request->validate([
            'financiador' => 'nullable',
            'data_inicio_financiamento' => 'nullable',
            'valor_do_bem' => 'nullable',
            'numero_de_parcelas' => 'nullable',
            'valor_parcela' => 'nullable',
            'numero_processo' => 'nullable',
            'reclamante_nome' => 'nullable',
            'valor_processo' => 'nullable',
            'valor_da_compra' => 'nullable',
            'data_compra' => 'nullable',
            'numero_patrimonio' => 'nullable',
            'data_venda' => 'nullable',
            'valor_da_venda' => 'nullable',
            'km_final' => 'nullable',
            'hora_final' => 'nullable',
            'motivo_venda' => 'nullable',
        ]);

        $historicosControle = json_decode($request->historicosControle);

        $historicosKm = json_decode($request->historicosKm);

        $historicos_nao_tracionado = json_decode($request->historicos_nao_tracionado);

        DB::beginTransaction();

        if (filter_var($dadosVeiculo['is_terceiro'], FILTER_VALIDATE_BOOLEAN) && empty($dadosVeiculo['id_fornecedor'])) {
            return redirect()
                ->back()
                ->withInput()
                ->withNotification([
                    'title' => 'Atenção!',
                    'type' => 'warning',
                    'message' => 'Se o veículo é de terceiros, o fornecedor deve ser informado.',
                ]);
        }

        try {
            // Upload da imagem, se necessário
            if ($request->hasFile('imagem_veiculo') && $request->file('imagem_veiculo')->isValid()) {
                $fotoPath = $request->file('imagem_veiculo')->store('veiculos', 'public');
                $dadosVeiculo['imagem_veiculo'] = $fotoPath;
            }

            // Caso o veiculo não seja terceiro, ele automaticamente é do fornecedor carvalima (matriz)
            if ($dadosVeiculo['is_terceiro'] === 0) {
                $dadosVeiculo['id_fornecedor'] = 1;
            }

            // Colocar dados obrigatorios que nao vieram pelo form ou que precisam ser manual
            $dadosVeiculo['data_inclusao'] = now();

            // Adicionar ID do usuário que está realizando a alteração
            $dadosVeiculo['id_user_alteracao'] = Auth::id();

            // Criar registro do veiculo
            $veiculo = Veiculo::create($dadosVeiculo);

            // Aqui eu verifico se eh necessario criar um registro de compra/venda.
            // Se tiver um dos campos preenchidos de compra ou de venda, entao é necessario.
            if (! empty($dadosCompraVeiculo['financiador']) || ! empty($dadosCompraVeiculo['data_venda'])) {
                $dadosCompraVeiculo['data_inclusao'] = now();
                $dadosCompraVeiculo['data_compra'] = $dadosCompraVeiculo['data_compra'] ?? null;
                $dadosCompraVeiculo['id_filial'] = $veiculo['id_filial'];
                $dadosCompraVeiculo['id_veiculo'] = $veiculo->id_veiculo;
                $dadosCompraVeiculo['id_usuario_cadastro'] = Auth::id();

                // Se os dados de venda do veiculo ja estiverem cadastrados entao ele não está ativo
                if (! empty($dadosCompraVeiculo['data_venda'])) {
                    $veiculo->situacao_veiculo = false;
                    $veiculo->veiculo_baixado = true;
                    $veiculo->save();
                }

                // Criar o registro de compra/venda.
                RegistroCompraVenda::create($dadosCompraVeiculo);
            }

            if (is_array($historicosKm) || is_object($historicosKm)) {
                foreach ($historicosKm as $item) {
                    $itemArray = (array) $item;

                    KmComotado::create([
                        'data_inclusao' => now(),
                        'data_realizacao' => $itemArray['data_realizacao'],
                        'km_realizacao' => $itemArray['km_realizacao'],
                        'horimetro' => $itemArray['horimetro'],
                        'id_veiculo' => $veiculo->id_veiculo,
                    ]);
                }
            } else {
                Log::error('historicosKm inválido ou ausente no request', [
                    'conteudo' => $request->historicosKm,
                ]);
            }

            // Inserir na model TransferenciaVeiculo
            if (is_array($historicos_nao_tracionado) || is_object($historicos_nao_tracionado)) {
                foreach ($historicos_nao_tracionado as $item) {
                    $itemArray = (array) $item;

                    VeiculoNaoTracionado::create([
                        'data_inclusao' => $itemArray['data_inclusao'],
                        'modelo_carroceria' => $itemArray['modelo_carroceria'],
                        'marca_carroceria' => $itemArray['marca_carroceria'],
                        'tara_nao_tracionado' => $itemArray['tara_nao_tracionado'],
                        'lotacao_nao_tracionado' => $itemArray['lotacao_nao_tracionado'],
                        'ano_carroceria' => $itemArray['ano_carroceria'],
                        'refrigeracao_carroceria' => $itemArray['refrigeracao_carroceria'],
                        'comprimento_carroceria' => $itemArray['comprimento_carroceria'],
                        'largura_carroceria' => $itemArray['largura_carroceria'],
                        'altura_carroceria' => $itemArray['altura_carroceria'],
                        'capacidade_volumetrica_1' => $itemArray['capacidade_volumetrica_1'],
                        'capacidade_volumetrica_2' => $itemArray['capacidade_volumetrica_2'],
                        'capacidade_volumetrica_3' => $itemArray['capacidade_volumetrica_3'],
                        'capacidade_volumetrica_4' => $itemArray['capacidade_volumetrica_4'],
                        'capacidade_volumetrica_5' => $itemArray['capacidade_volumetrica_5'],
                        'capacidade_volumetrica_6' => $itemArray['capacidade_volumetrica_6'],
                        'capacidade_volumetrica_7' => $itemArray['capacidade_volumetrica_7'],
                        'id_veiculo' => $veiculo->id_veiculo,
                    ]);
                }
            } else {
                Log::error('historicos_nao_tracionado inválido ou ausente no request', [
                    'conteudo' => $request->historicos_nao_tracionado,
                ]);
            }

            if (is_array($historicosControle) || is_object($historicosControle)) {
                foreach ($historicosControle as $item) {
                    $itemArray = (array) $item;

                    ControlesVeiculo::create([
                        'veiculo_id' => $veiculo->id_veiculo,
                        'is_considera_para_rateio' => $itemArray['is_considera_para_rateio'] === 'true' ? true : false,
                        'is_controle_manutencao' => $itemArray['is_controle_manutencao'] === 'true' ? true : false,
                        'is_controla_licenciamento' => $itemArray['is_controla_licenciamento'] === 'true' ? true : false,
                        'is_controla_seguro_obrigatorio' => $itemArray['is_controla_seguro_obrigatorio'] === 'true' ? true : false,
                        'is_controla_ipva' => $itemArray['is_controla_ipva'] === 'true' ? true : false,
                        'is_controla_pneu' => $itemArray['is_controla_pneu'] === 'true' ? true : false,
                        'data_inclusao' => $itemArray['data_inclusao'],
                    ]);
                }
            } else {
                Log::error('historicosControle inválido ou ausente no request', [
                    'conteudo' => $request->historicosControle,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.veiculos.index')
                ->withNotification([
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Veículo cadastrado com sucesso!',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro na criação de veículo:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(), // Adiciona os dados enviados no log para diagnóstico
            ]);

            // Determinar mensagem específica com base na exceção
            $errorMessage = 'Não foi possível cadastrar o veículo.';

            // Se estiver em ambiente de desenvolvimento, mostra mensagem detalhada
            if (config('app.env') !== 'production') {
                $errorMessage .= ' Erro: ' . $e->getMessage();
            }

            return redirect()
                ->route('admin.veiculos.index')
                ->withNotification([
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => $errorMessage,
                ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Veiculo $veiculo)
    {
        $registroCompra = RegistroCompraVenda::where('id_veiculo', '=', $veiculo->id_veiculo)->first();

        $transferencias = TransferenciaVeiculo::where('id_veiculo', '=', $veiculo->id_veiculo)
            ->with(['filialOrigem', 'filialDestino'])
            ->orderBy('data_transferencia')
            ->get()
            ->toArray();

        $kmComodato = KmComotado::where('id_veiculo', '=', $veiculo->id_veiculo)->orderBy('data_realizacao')->get()->toArray();

        $historicoTransferencia = TransferenciaVeiculo::where('id_transferencia', '=', $veiculo->id_veiculo)
            ->select('data_inclusao', 'data_alteracao', 'id_filial_origem', 'id_filial_destino', 'km_transferencia', 'data_transferencia')
            ->get()
            ->toArray();

        $filial = Filial::where('id', '=', $veiculo->id_filial)->first();

        $municipio = Municipio::where('id_municipio', '=', $veiculo->id_municipio)->first();

        return view('admin.veiculos.show', compact('veiculo', 'registroCompra', 'transferencias', 'kmComodato', 'historicoTransferencia', 'filial', 'municipio'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Veiculo $veiculo)
    {
        $registroCompra = RegistroCompraVenda::where('id_veiculo', '=', $veiculo->id_veiculo)->first();

        $transferencias = TransferenciaVeiculo::where('id_veiculo', '=', $veiculo->id_veiculo)
            ->with(['filialOrigem', 'filialDestino'])
            ->orderBy('data_transferencia')
            ->get()
            ->toArray();

        $kmComodato = KmComotado::where('id_veiculo', '=', $veiculo->id_veiculo)->orderBy('data_realizacao')->get()->toArray();

        $historicoTransferencia = TransferenciaVeiculo::where('id_transferencia', '=', $veiculo->id_veiculo)
            ->select('data_inclusao', 'data_alteracao', 'id_filial_origem', 'id_filial_destino', 'km_transferencia', 'data_transferencia')
            ->get()
            ->toArray();

        $formOptions = [
            'ufs' => Estado::select('uf as label', 'id_uf as value')->orderBy('label')->get()->toArray(),
            'municipios' => Municipio::select('nome_municipio as label', 'id_municipio as value')->limit(30)->orderBy('label')->get()->toArray(),
            'motoristas' => Motorista::select('nome as label', 'idobtermotorista as value')->get()->toArray(),
            'filiais' => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'bases' => BaseVeiculo::select('descricao_base as label', 'id_base_veiculo as value')->orderBy('label')->get()->toArray(),
            'categorias' => TipoCategoria::select('descricao_categoria as label', 'id_categoria as value')->orderBy('label')->get()->toArray(),
            'departamentos' => Departamento::select('descricao_departamento as label', 'id_departamento as value')->orderBy('label')->get()->toArray(),
            'modelos' => ModeloVeiculo::select('descricao_modelo_veiculo as label', 'id_modelo_veiculo as value')->orderBy('label')->get()->toArray(),
            'tipoVeiculo' => TipoVeiculo::select('descricao as label', 'id as value')->orderBy('label')->get()->toArray(),
            'tipoCombustiveis' => TipoCombustivel::select('descricao as label', 'id_tipo_combustivel as value')->orderBy('label')->get()->toArray(),
            'fornecedores' => Fornecedor::where('is_ativo', true)->limit(20)->orderBy('nome_fornecedor')->get(['id_fornecedor as value', 'nome_fornecedor as label']),
            'subCategorias' => SubCategoriaVeiculo::select('descricao_subcategoria as label', 'id_subcategoria as value')->orderBy('label')->get()->toArray(),
            'tipoOperacoes' => TipoOperacao::select('descricao_tipo_operacao as label', 'id_tipo_operacao as value')->orderBy('label')->get()->toArray(),
            'tipoEquipamentos' => TipoEquipamento::select(DB::raw("descricao_tipo || CASE WHEN numero_eixos IS NOT NULL THEN ' - Eixos: ' || numero_eixos::text ELSE '' END AS label,id_tipo_equipamento AS value"))->get()->toArray(),
            // tarzer dados pneus
        ];

        $kmAtual = DB::connection('carvalima_production')->table('veiculo as v')
            ->select(DB::raw('fc_km_relatorio(v.id_veiculo) AS km_atual'))
            ->where('v.id_veiculo', $veiculo->id_veiculo)
            ->value('km_atual');

        $tipoEquipamentoPneus = TipoEquipamento::select('id_tipo_equipamento', 'numero_eixos', 'numero_pneus_eixo_1', 'numero_pneus_eixo_2', 'numero_pneus_eixo_3', 'numero_pneus_eixo_4')
            ->where('id_tipo_equipamento', '=', $veiculo->id_tipo_equipamento)
            ->first();

        $fornecedoresFrequentes = Cache::remember('fornecedores_frequentes', now()->addHours(12), function () {
            return Fornecedor::where('is_ativo', true)
                ->limit(20)
                ->orderBy('nome_fornecedor')
                ->get(['id_fornecedor as value', 'nome_fornecedor as label']);
        });

        $pneuVeiculoIds = VeiculoXPneu::select('id_veiculo_pneu')
            ->where('id_veiculo', $veiculo->id_veiculo)
            ->where('situacao', true)
            ->first();

        $controlesVeiculo = ControlesVeiculo::where('id_veiculo', '=', $veiculo->id_veiculo)->get()->toArray();

        $filial = Filial::orderBy('name')
            ->get()
            ->keyBy('id') // <-- chaveando pelo id
            ->map(function ($filial) {
                return $filial->name;
            });

        $veiculonaotracionado = VeiculoNaoTracionado::where('id_veiculo', $veiculo->id_veiculo)
            ->orderBy('id_veiculo_nao_tracionado')->get()->toArray();

        Log::info('🔍 Buscando pneus para veículo', [
            'id_veiculo' => $veiculo->id_veiculo,
            'pneuVeiculoIds' => $pneuVeiculoIds
        ]);

        // Inicializar formattedData vazio
        $formattedData = [
            'eixos' => $tipoEquipamentoPneus->numero_eixos ?? 0,
            'pneus_por_eixo' => [
                $tipoEquipamentoPneus->numero_pneus_eixo_1 ?? 0,
                $tipoEquipamentoPneus->numero_pneus_eixo_2 ?? 0,
                $tipoEquipamentoPneus->numero_pneus_eixo_3 ?? 0,
                $tipoEquipamentoPneus->numero_pneus_eixo_4 ?? 0,
            ],
            'pneusAplicadosFormatados' => [],
        ];

        if (! empty($pneuVeiculoIds->id_veiculo_pneu)) {
            $pneusAplicados = PneusAplicados::where('id_veiculo_x_pneu', $pneuVeiculoIds->id_veiculo_pneu)->get();

            Log::info('🔧 Pneus encontrados', [
                'id_veiculo_x_pneu' => $pneuVeiculoIds->id_veiculo_pneu,
                'quantidade' => $pneusAplicados->count(),
                'pneus' => $pneusAplicados->toArray()
            ]);

            // Formatar os pneus aplicados para o frontend
            $pneusAplicadosFormatados = $pneusAplicados->map(function ($pneu) {

                return [
                    'id_pneu' => $pneu->id_pneu,
                    'localizacao' => $pneu->localizacao,
                    'suco_pneu' => $pneu->sulco_pneu_adicionado,
                    'km_adicionado' => $pneu->km_adcionado,
                    'data_inclusao' => $pneu->data_inclusao ? format_date($pneu->data_inclusao, 'd/m/Y') : '',
                ];
            })->toArray();

            // Atualizar formattedData com os pneus aplicados
            $formattedData['pneusAplicadosFormatados'] = $pneusAplicadosFormatados;
        }

        return view('admin.veiculos.edit', compact(
            'veiculo',
            'formOptions',
            'registroCompra',
            'transferencias',
            'kmComodato',
            'tipoEquipamentoPneus',
            'formattedData',
            'kmAtual',
            'fornecedoresFrequentes',
            'controlesVeiculo',
            'filial',
            'veiculonaotracionado'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Veiculo $veiculo)
    {
        // Ao dar o update será necessario colocar o id_user_alteracao que esta realizando o update na tabela veiculo.
        // Isso vale para a tabela registro compra venda tambem, em caso de alteracao.
        // Data alteracao.
        // A tabela transferencia_veiculos é responsavel por guardar a transfencia. (há data inclusao e data alteração).
        // Vai surgir tambem o km_comotado, saber qual logica aplicar quando essa parte for realizada.
        // E aqui tambem tem a questao dos pneus aplicados. Por enquanto apenas exibição.
        // veiculo_x_pneu, pneus_aplicados (Sao referentes aos pneus, mas ha informacoes faltando ainda.)

        // uptade de dados sobre penus, onde o store nao tem

        // Sanitizar os valores monetários
        $this->sanitizeMonetaryValues($request, [
            'valor_venal',
            'valor_do_bem',
            'valor_parcela',
            'valor_processo',
            'valor_da_compra',
            'valor_da_venda',
        ]);

        // Validações
        $dadosVeiculo = $request->validate([
            'imagem_veiculo' => 'nullable|image|max:2048',
            'placa' => 'required|string|max:10',
            'renavam' => 'required|integer',
            'chassi' => 'required|string|max:200',
            'marca_veiculo' => 'required|string|max:200',
            'ano_fabricacao' => 'required|date_format:Y',
            'ano_modelo' => 'required|date_format:Y',
            'capacidade_tanque_principal' => 'required|integer',
            'capacidade_tanque_secundario' => 'required|integer',
            'capacidade_arla' => 'required|integer',
            'cor_veiculo' => 'required|string|max:200',
            'data_compra' => 'nullable|date_format:Y-m-d',
            'valor_venal' => 'nullable|numeric',
            'numero_frota' => 'nullable|integer',
            'km_inicial' => 'nullable|integer',
            'horas_iniciais' => 'nullable|integer',
            'id_uf' => 'nullable|exists:estado,id_uf',
            'id_municipio' => 'nullable',
            'situacao_veiculo' => 'required|boolean',
            'is_possui_tracao' => 'nullable|boolean',
            'is_marcador_quilometragem' => 'nullable|boolean',
            'is_horas' => 'nullable|boolean',
            'descricao_equipamento' => 'nullable|string|max:200',
            'empresa' => 'nullable|string|max:200',
            'id_tipo_equipamento' => 'nullable|exists:tipoequipamento,id_tipo_equipamento',
            'id_filial' => 'nullable|exists:filiais,id',
            'id_base_veiculo' => 'nullable|exists:base_veiculo,id_base_veiculo',
            'id_departamento' => 'nullable|exists:departamento,id_departamento',
            'id_categoria' => 'nullable|exists:categoria_veiculo,id_categoria',
            'id_modelo_veiculo' => 'nullable|exists:modelo_veiculo,id_modelo_veiculo',
            'id_tipo_combustivel' => 'nullable|exists:tipocombustivel,id_tipo_combustivel',
            'id_tipo_veiculo' => 'nullable|exists:tipo_veiculo,id',
            'id_fornecedor' => 'nullable',
            'telemetria' => 'required|boolean',
            'is_terceiro' => 'nullable',
            'contrato_manutencao' => 'required|boolean',
            'id_subcategoria_veiculo' => 'nullable|exists:subcategoria,id_subcategoria',
            'id_operacao' => 'nullable|exists:tipo_operacao,id_tipo_operacao',
            'rota_1' => 'nullable',
            'capacidade_carregamento_m3' => 'nullable',
            'capacidade_carregamento_cubado' => 'nullable',
            'capacidade_carregamento_real' => 'nullable',
            'id_fornecedor_comodato' => 'nullable',
            'data_comodato' => 'nullable',
        ]);

        $dadosCompraVeiculo = $request->validate([
            'financiador' => 'nullable',
            'data_inicio_financiamento' => 'nullable',
            'valor_do_bem' => 'nullable',
            'numero_de_parcelas' => 'nullable',
            'valor_parcela' => 'nullable',
            'numero_processo' => 'nullable',
            'reclamante_nome' => 'nullable',
            'valor_processo' => 'nullable',
            'valor_da_compra' => 'nullable',
            'data_compra' => 'nullable',
            'numero_patrimonio' => 'nullable',
            'id_fornecedor_comprador' => 'nullable',
            'data_venda' => 'nullable',
            'valor_da_venda' => 'nullable',
            'km_final' => 'nullable',
            'hora_final' => 'nullable',
            'motivo_venda' => 'nullable',
        ]);

        $historicoDeTrasferencia = $request->validate([
            'id_filial_origem' => 'nullable',
            'id_filial_destino' => 'nullable',
            'km_transferencia' => 'nullable',
            'data_transferencia' => 'nullable',
        ]);

        $veiculoEmComodato = $request->validate([
            'data_realizacao' => 'nullable',
            'km_realizacao' => 'nullable',
            'horimetro' => 'nullable',
        ]);

        $historicosControle = json_decode($request->historicosControle);

        $historicos_transferencia = json_decode($request->historicos_transferencia);

        $historicosKm = json_decode($request->historicosKm);

        $historicos_nao_tracionado = json_decode($request->historicos_nao_tracionado);

        DB::beginTransaction();

        try {
            $veiculo = Veiculo::findOrFail($veiculo->id_veiculo);

            if (!is_numeric($request->id_municipio)) {
                $municipio = Municipio::where('nome_municipio', $request->id_municipio)->first();
                if ($municipio) {
                    $dadosVeiculo['id_municipio'] = $municipio->id_municipio;
                }
            }

            // Atualização da imagem
            if ($request->hasFile('imagem_veiculo') && $request->file('imagem_veiculo')->isValid()) {
                $fotoPath = $request->file('imagem_veiculo')->store('veiculos', 'public');
                $dadosVeiculo['imagem_veiculo'] = $fotoPath;
            }

            if ($dadosVeiculo['is_terceiro'] === 0) {
                $dadosVeiculo['id_fornecedor'] = 1;
            }

            $veiculo->update($dadosVeiculo);

            if (! empty($dadosCompraVeiculo['financiador']) || ! empty($dadosCompraVeiculo['data_venda'])) {
                $registroCompraVenda = RegistroCompraVenda::where('id_veiculo', $veiculo->id_veiculo)->first();

                if ($registroCompraVenda) {
                    $registroCompraVenda->update($dadosCompraVeiculo);
                } else {
                    $dadosCompraVeiculo['data_inclusao'] = now();
                    $dadosCompraVeiculo['data_compra'] = $dadosCompraVeiculo['data_compra'];
                    $dadosCompraVeiculo['id_filial'] = $veiculo['id_filial'];
                    $dadosCompraVeiculo['id_veiculo'] = $veiculo->id_veiculo;
                    $dadosCompraVeiculo['id_usuario_cadastro'] = Auth::user()->id;

                    // Se os dados de venda do veiculo ja estiverem cadastrados entao ele não está ativo
                    if (! empty($dadosCompraVeiculo['data_venda'])) {
                        $veiculo->situacao_veiculo = false;
                        $veiculo->veiculo_baixado = true;
                        $veiculo->save();
                    }

                    // Criar o registro de compra/venda.
                    RegistroCompraVenda::create($dadosCompraVeiculo);
                }
            }

            // Inserir na model TransferenciaVeiculo
            if (is_array($historicos_nao_tracionado) || is_object($historicos_nao_tracionado)) {
                foreach ($historicos_nao_tracionado as $item) {
                    $itemArray = (array) $item;

                    VeiculoNaoTracionado::create([
                        'data_inclusao' => $itemArray['data_inclusao'],
                        'modelo_carroceria' => $itemArray['modelo_carroceria'],
                        'marca_carroceria' => $itemArray['marca_carroceria'],
                        'tara_nao_tracionado' => $itemArray['tara_nao_tracionado'],
                        'lotacao_nao_tracionado' => $itemArray['lotacao_nao_tracionado'],
                        'ano_carroceria' => $itemArray['ano_carroceria'],
                        'refrigeracao_carroceria' => $itemArray['refrigeracao_carroceria'],
                        'comprimento_carroceria' => $itemArray['comprimento_carroceria'],
                        'largura_carroceria' => $itemArray['largura_carroceria'],
                        'altura_carroceria' => $itemArray['altura_carroceria'],
                        'capacidade_volumetrica_1' => $itemArray['capacidade_volumetrica_1'],
                        'capacidade_volumetrica_2' => $itemArray['capacidade_volumetrica_2'],
                        'capacidade_volumetrica_3' => $itemArray['capacidade_volumetrica_3'],
                        'capacidade_volumetrica_4' => $itemArray['capacidade_volumetrica_4'],
                        'capacidade_volumetrica_5' => $itemArray['capacidade_volumetrica_5'],
                        'capacidade_volumetrica_6' => $itemArray['capacidade_volumetrica_6'],
                        'capacidade_volumetrica_7' => $itemArray['capacidade_volumetrica_7'],
                        'id_veiculo' => $veiculo->id_veiculo,
                    ]);
                }
            } else {
                Log::error('historicos_nao_tracionado inválido ou ausente no request', [
                    'conteudo' => $request->historicos_nao_tracionado,
                ]);
            }

            // Inserir na model TransferenciaVeiculo
            if (is_array($historicosKm) || is_object($historicosKm)) {
                foreach ($historicosKm as $item) {
                    $itemArray = (array) $item;

                    KmComotado::create([
                        'data_inclusao' => now(),
                        'data_realizacao' => $itemArray['data_realizacao'],
                        'km_realizacao' => $itemArray['km_realizacao'],
                        'horimetro' => $itemArray['horimetro'],
                        'id_veiculo' => $veiculo->id_veiculo,
                    ]);
                }
            } else {
                Log::error('historicosKm inválido ou ausente no request', [
                    'conteudo' => $request->historicosKm,
                ]);
            }

            if (is_array($historicos_transferencia) || is_object($historicos_transferencia)) {
                foreach ($historicos_transferencia as $item) {
                    $itemArray = (array) $item;

                    TransferenciaVeiculo::create([
                        'data_inclusao' => $itemArray['data_inclusao'],
                        'id_filial_origem' => $itemArray['id_filial_origem'],
                        'id_filial_destino' => $itemArray['id_filial_destino'],
                        'km_transferencia' => $itemArray['km_transferencia'],
                        'data_transferencia' => $itemArray['data_transferencia'],
                        'id_veiculo' => $veiculo->id_veiculo,
                    ]);
                }
            } else {
                Log::error('historicos_transferencia inválido ou ausente no request', [
                    'conteudo' => $request->historicos_transferencia,
                ]);
            }

            if (is_array($historicosControle) || is_object($historicosControle)) {
                // Coletar todas as datas de inclusão que vieram no request
                $datasInclusaoRecebidas = [];

                foreach ($historicosControle as $item) {
                    $itemArray = (array) $item;
                    $datasInclusaoRecebidas[] = $itemArray['data_inclusao'];

                    // Verificar se já existe um registro para esta data_inclusao
                    $controleExistente = ControlesVeiculo::where('id_veiculo', $veiculo->id_veiculo)
                        ->where('data_inclusao', $itemArray['data_inclusao'])
                        ->first();

                    $dados = [
                        'is_considera_para_rateio' => $itemArray['is_considera_para_rateio'] === 'true' ? true : false,
                        'is_controle_manutencao' => $itemArray['is_controle_manutencao'] === 'true' ? true : false,
                        'is_controla_licenciamento' => $itemArray['is_controla_licenciamento'] === 'true' ? true : false,
                        'is_controla_seguro_obrigatorio' => $itemArray['is_controla_seguro_obrigatorio'] === 'true' ? true : false,
                        'is_controla_ipva' => $itemArray['is_controla_ipva'] === 'true' ? true : false,
                        'is_controla_pneu' => $itemArray['is_controla_pneu'] === 'true' ? true : false,
                    ];

                    if ($controleExistente) {
                        // Atualizar registro existente
                        $controleExistente->update($dados);
                    } else {
                        // Criar novo registro
                        $dados['id_veiculo'] = $veiculo->id_veiculo;
                        $dados['data_inclusao'] = $itemArray['data_inclusao'];
                        ControlesVeiculo::create($dados);
                    }
                }

                // Deletar registros que não vieram no request (foram removidos no frontend)
                ControlesVeiculo::where('id_veiculo', $veiculo->id_veiculo)
                    ->whereNotIn('data_inclusao', $datasInclusaoRecebidas)
                    ->delete();
            } else {
                // Se não houver dados, deletar todos os controles existentes
                ControlesVeiculo::where('id_veiculo', $veiculo->id_veiculo)->delete();

                Log::error('historicosControle inválido ou ausente no request', [
                    'conteudo' => $request->historicosControle,
                ]);
            }

            if ($veiculo->status_cadastro_imobilizado) {
                $cadastroImobilizado = CadastroImobilizado::find($veiculo->id_cadastro_imobilizado);

                // Mudar o status do cadastro imobilizado para Finalizado
                $status_cadastro_imobilizado = 3;

                $cadastroImobilizado->update([
                    'status_cadastro_imobilizado' => $status_cadastro_imobilizado,
                ]);

                $veiculo->update([
                    'status_cadastro_imobilizado' => $status_cadastro_imobilizado,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.veiculos.index')
                ->withNotification([
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Veículo atualizado com sucesso!',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro na atualização de veículo:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('admin.veiculos.index')
                ->withNotification([
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Não foi possível atualizar o veículo.',
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

            $veiculo = Veiculo::findOrFail($id);

            RegistroCompraVenda::where('id_veiculo', $id)->delete();
            TransferenciaVeiculo::where('id_veiculo', $id)->delete();
            KmComotado::where('id_veiculo', $id)->delete();

            $veiculo->delete(); // Aqui será acionada sua trait

            DB::commit();

            return response()->json([
                'notification' => [
                    'title' => 'Veiculo desativado!',
                    'type' => 'success',
                    'message' => 'Veiculo desativado com sucesso',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao desativar o veículo: ' . $e->getMessage());

            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => 'Não foi possível desativar Veiculo: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    public function onActionBaixarVeiculo($veiculoId)
    {
        try {
            // Verificações antes de realizar a baixa do veículo

            // 1. Verificar se existe Ordem de Serviço aberta
            $ordensAbertas = DB::connection('carvalima_production')->table('ordem_servico')
                ->where('id_veiculo', $veiculoId)
                ->whereIn('id_status_ordem_servico', [1, 2, 3, 5])
                ->count();

            if ($ordensAbertas > 0) {
                return response()->json([
                    'notification' => [
                        'title' => 'Atenção',
                        'type' => 'warning',
                        'message' => 'Não é possível realizar a baixa para o veículo pois o mesmo possui Ordem de serviço aberta.',
                    ],
                ]);
            }

            // 2. Verificar status do IPVA
            $statusIpva = DB::connection('carvalima_production')->table('ipvaveiculo')
                ->where('id_veiculo', $veiculoId)
                ->orderBy('id', 'desc')
                ->value('status_ipva');

            if ($statusIpva == 'Parcial') {
                return response()->json([
                    'notification' => [
                        'title' => 'Atenção',
                        'type' => 'warning',
                        'message' => 'Não é possível realizar a baixa para o veículo pois o mesmo possui IPVA em aberto.',
                    ],
                ]);
            }

            // 3. Verificar registro de compra e venda
            $possuiRegistroCompraVenda = DB::connection('carvalima_production')->table('registrocompravenda')
                ->where('id_veiculo', $veiculoId)
                ->exists();

            if (! $possuiRegistroCompraVenda) {
                return response()->json([
                    'notification' => [
                        'title' => 'Atenção',
                        'type' => 'warning',
                        'message' => 'Não é possível realizar a baixa deste veículo. Antes de prosseguir, certifique-se de que o registro de compra e venda esteja cadastrado no sistema.',
                    ],
                ]);
            }

            // Após todas as verificações, realizar a baixa do veículo
            $veiculo = Veiculo::findOrFail($veiculoId);
            $veiculo->situacao_veiculo = false;
            $veiculo->veiculo_baixado = true;
            $veiculo->deleted_at = now();
            $veiculo->save();

            // Atualizar os pneus vinculados ao veículo para status VENDIDO - placa
            DB::connection('pgsql')->table('pneu')
                ->join('historicopneu', 'pneu.id_pneu', '=', 'historicopneu.id_pneu')
                ->where('historicopneu.id_veiculo', $veiculoId)
                ->whereNull('historicopneu.data_retirada')
                ->update([
                    'pneu.status_pneu' => DB::raw("'VENDIDO - ' || (SELECT placa FROM veiculo WHERE id_veiculo = $veiculoId)"),
                    'pneu.data_alteracao' => DB::raw('now()'),
                ]);

            // Atualizar todos os veiculoXPneu relacionados
            $veiculosXPneus = veiculoXPneu::where('id_veiculo', $veiculoId)->get();

            foreach ($veiculosXPneus as $veiculoXPneu) {
                $veiculoXPneu->situacao = false;
                $veiculoXPneu->save();

                // Atualizar todos os pneusAplicados relacionados a cada veiculoXPneu
                $pneusAplicados = pneusAplicados::where('id_veiculo_x_pneu', $veiculoXPneu->id)->get();

                foreach ($pneusAplicados as $pneu) {
                    $pneu->is_ativo = false;
                    $pneu->deleted_at = now();
                    $pneu->save();
                }
            }

            return response()->json([
                'notification' => [
                    'title' => 'Veiculo baixado!',
                    'type' => 'success',
                    'message' => 'Veiculo baixado com sucesso',
                ],
                'redirect' => route('admin.veiculos.index'),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao baixar o veículo: ' . $e->getMessage());

            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => 'Não foi possível baixar Veiculo: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $term = strtolower($request->get('term'));

            // Cache para melhorar performance
            $veiculos = Cache::remember('veiculos_search_' . $term, now()->addMinutes(30), function () use ($term) {
                return Veiculo::whereRaw('LOWER(placa) LIKE ?', ["%{$term}%"])
                    ->orderBy('placa')
                    ->where('situacao_veiculo', '!=', 'false')
                    ->limit(30)
                    ->get(['id_veiculo as value', 'placa as label', 'chassi as chassi', 'renavam as renavam']);
            });

            return response()->json($veiculos);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar veículos: ' . $e->getMessage());

            return response()->json(['error' => 'Erro ao buscar veículos.'], 500);
        }
    }

    public function getById($id)
    {
        // Cache para melhorar performance
        $veiculo = Cache::remember('veiculo_' . $id, now()->addHours(24), function () use ($id) {
            return Veiculo::findOrFail($id);
        });

        return response()->json([
            'value' => $veiculo->id_veiculo,
            'label' => $veiculo->placa,
        ]);
    }

    /**
     * Retorna dados específicos do veículo para preenchimento de outros campos
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDados($id)
    {
        try {
            LOG::DEBUG($id);
            $veiculo = Veiculo::with('filial', 'departamento')->findOrFail($id);

            // Garantir que todos os campos necessários estejam presentes
            $dados = [
                'capacidade_tanque_principal' => $veiculo->capacidade_tanque_principal,
                'capacidade_tanque' => $veiculo->capacidade_tanque_principal, // campo alternativo
                'km_atual' => $veiculo->km_inicial, // KM atual é o campo km_inicial no modelo
                'id_departamento' => $veiculo->id_departamento,
                'renavam' => $veiculo->renavam,
                'chassi' => $veiculo->chassi,
                'filial_veiculo' => $veiculo->filial->name,
            ];

            return response()->json($dados);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do veículo:', [
                'id' => $id,
                'erro' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao buscar dados do veículo: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function buildExportQuery(Request $request)
    {

        $query = Veiculo::query();

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('base_veiculo')) {
            $query->where('base', $request->base_veiculo);
        }

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        if ($request->filled('renavam')) {
            $query->where('renavam', $request->renavam);
        }

        if ($request->filled('marca_veiculo')) {
            $query->where('marca_veiculo', $request->marca_veiculo);
        }

        if ($request->filled('is_terceiro')) {
            $query->where('is_terceiro', $request->is_terceiro);
        }

        if ($request->filled('situacao_veiculo')) {
            $query->where('situacao_veiculo', $request->situacao_veiculo);
        }

        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'id_veiculo',
            'id_categoria',
            'id_filial',
            'base_veiculo',
            'id_categoria',
            'renavam',
            'marca_veiculo',
            'is_terceiro',
            'situacao_veiculo',
        ];
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);

            // Se a exportação direta pelo trait não funcionar, tente um método alternativo
            if (! $this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true,
                ]);
            }

            if ($request->has('confirmed') || ! $this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                // Configurar opções do PDF
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.veiculos.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('veiculos_' . date('Y-m-d_His') . '.pdf');
            } else {
                // Confirmação para grande volume
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => 'Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.',
                    'export_confirmation' => true,
                    'export_url' => $currentUrl,
                ]);
            }
        } catch (\Exception $e) {
            // Log detalhado do erro
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true,
            ]);
        }
    }

    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'placa' => 'Placa',
            'filialVeiculo.name' => 'Filial',
            'baseVeiculo.descricao_base' => 'Base Veiculo',
            'categoriaVeiculo.descricao_categoria' => 'Código Categoria',
            'renavam' => 'Renavam',
            'modelo' => 'Marca Veiculo',
            'data_compra' => 'Data Compra',
            'is_terceiro' => 'Veiculo de Terceiro',
            'situacao_veiculo' => 'Veiculo Ativo',
        ];

        return $this->exportToCsv($request, $query, $columns, 'veiculos', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'placa' => 'Placa',
            'filialVeiculo.name' => 'Filial',
            'baseVeiculo.descricao_base' => 'Base Veiculo',
            'categoriaVeiculo.descricao_categoria' => 'Código Categoria',
            'renavam' => 'Renavam',
            'modelo' => 'Marca Veiculo',
            'data_compra' => 'Data Compra',
            'is_terceiro' => 'Veiculo de Terceiro',
            'situacao_veiculo' => 'Veiculo Ativo',
        ];

        return $this->exportToExcel($request, $query, $columns, 'veiculos', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'placa' => 'placa',
            'filial' => 'filialVeiculo.name',
            'base_veiculo' => 'baseVeiculo.descricao_base',
            'codigo_categoria' => 'categoriaVeiculo.descricao_categoria',
            'renavam' => 'renavam',
            'marca_veiculo' => 'modelo',
            'data_compra' => 'data_compra',
            'veiculo_terceiro' => 'is_terceiro',
            'veiculo_ativo' => 'situacao_veiculo',
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'veiculos',
            'veiculo',
            'veiculos',
            $this->getValidExportFilters()
        );
    }
}
