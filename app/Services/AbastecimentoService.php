<?php

namespace App\Services;

use App\Modules\Abastecimentos\Models\AbastecimentoManual;
use App\Modules\Abastecimentos\Models\Bomba;
use App\Models\Departamento;
use App\Models\Fornecedor;
use App\Models\Motorista;
use App\Modules\Abastecimentos\Models\TipoCombustivel;
use App\Modules\Veiculos\Models\Veiculo;
use App\Models\VFilial;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Traits\AbastecimentoValidationTrait;

class AbastecimentoService
{
    use AbastecimentoValidationTrait;

    /**
     * Busca o KM atual do veículo com base no ID da Sascar
     *
     * @param int $idSascar ID do veículo na Sascar
     * @param string $dataConsulta Data de referência para busca
     * @param float $kmPadrao Valor padrão para retornar em caso de falha
     * @return float
     */
    public function buscarKmVeiculo($idSascar, $dataConsulta, $kmPadrao = 0)
    {
        Log::info("AbastecimentoService.buscarKmVeiculo: Iniciando busca de KM para ID Sascar {$idSascar}, Data: {$dataConsulta}");

        // Verificar parâmetros de entrada
        if (empty($idSascar) || $idSascar <= 0) {
            Log::warning("AbastecimentoService.buscarKmVeiculo: ID Sascar inválido: {$idSascar}");
            return $kmPadrao;
        }

        try {
            // 1. Primeiro, tentar via função fc_km_retroativo_os do banco
            Log::info("AbastecimentoService.buscarKmVeiculo: Tentando buscar via fc_km_retroativo_os para ID Sascar: {$idSascar}");
            try {
                $resultadoFuncao = DB::connection('pgsql')->select(
                    "SELECT * FROM fc_km_retroativo_os(?, ?::TIMESTAMP)",
                    [$idSascar, $dataConsulta]
                );

                if (
                    !empty($resultadoFuncao) &&
                    isset($resultadoFuncao[0]->fc_km_retroativo_os) &&
                    $resultadoFuncao[0]->fc_km_retroativo_os > 0
                ) {
                    $km = floatval($resultadoFuncao[0]->fc_km_retroativo_os);
                    Log::info("AbastecimentoService.buscarKmVeiculo: KM encontrado via fc_km_retroativo_os: {$km}");
                    return $km;
                } else {
                    Log::info("AbastecimentoService.buscarKmVeiculo: Nenhum resultado válido via fc_km_retroativo_os");
                }
            } catch (\Exception $e) {
                Log::warning("AbastecimentoService.buscarKmVeiculo: Erro ao consultar fc_km_retroativo_os: " . $e->getMessage());
                // Continuar com o próximo método
            }

            // 2. Tentar buscar na tabela pacoteposicaorangjson para datas recentes
            $dataLimite = '2024-01-24 00:00:00';

            if (strtotime($dataConsulta) >= strtotime($dataLimite)) {
                Log::info("AbastecimentoService.buscarKmVeiculo: Tentando buscar na tabela pacoteposicaorangjson (data recente)");
                try {
                    $ultimaPosicao = DB::connection('pgsql')->table('pacoteposicaorangjson')
                        ->where('idveiculo', $idSascar)
                        ->whereNotNull('odometroexato')
                        ->where('odometroexato', '>', 0)
                        ->where('datapacote', '<=', $dataConsulta)
                        ->orderBy('datapacote', 'desc')
                        ->first(['odometroexato']);

                    if ($ultimaPosicao && $ultimaPosicao->odometroexato > 0) {
                        $km = floatval($ultimaPosicao->odometroexato);
                        Log::info("AbastecimentoService.buscarKmVeiculo: KM encontrado via pacoteposicaorangjson: {$km}");
                        return $km;
                    } else {
                        Log::info("AbastecimentoService.buscarKmVeiculo: Nenhum resultado válido via pacoteposicaorangjson");
                    }
                } catch (\Exception $e) {
                    Log::warning("AbastecimentoService.buscarKmVeiculo: Erro ao consultar pacoteposicaorangjson: " . $e->getMessage());
                    // Continuar com o próximo método
                }
            } else {
                // 3. Para datas antigas, usar obterpacoteposicoesmotoristaporrange
                Log::info("AbastecimentoService.buscarKmVeiculo: Tentando buscar via obterpacoteposicoesmotoristaporrange (data antiga)");
                try {
                    $ultimaPosicao = DB::connection('pgsql')->table('obterpacoteposicoesmotoristaporrange')
                        ->join('obterveiculos', 'obterpacoteposicoesmotoristaporrange.idveiculo', '=', 'obterveiculos.idveiculo')
                        ->where('obterveiculos.idveiculo', $idSascar)
                        ->where('obterpacoteposicoesmotoristaporrange.datapacote', '<=', $dataConsulta)
                        ->orderBy('obterpacoteposicoesmotoristaporrange.datapacote', 'desc')
                        ->first(['obterpacoteposicoesmotoristaporrange.odometro']);

                    if ($ultimaPosicao && $ultimaPosicao->odometro > 0) {
                        $km = floatval($ultimaPosicao->odometro);
                        Log::info("AbastecimentoService.buscarKmVeiculo: KM encontrado via obterpacoteposicoesmotoristaporrange: {$km}");
                        return $km;
                    } else {
                        Log::info("AbastecimentoService.buscarKmVeiculo: Nenhum resultado válido via obterpacoteposicoesmotoristaporrange");
                    }
                } catch (\Exception $e) {
                    Log::warning("AbastecimentoService.buscarKmVeiculo: Erro ao consultar obterpacoteposicoesmotoristaporrange: " . $e->getMessage());
                    // Continuar com o próximo método
                }
            }

            // 4. Tentar buscar no histórico de abastecimentos
            Log::info("AbastecimentoService.buscarKmVeiculo: Tentando buscar via histórico de abastecimentos");

            try {
                // CORREÇÃO: Buscar veículo por id_sascar, não pelo próprio id_sascar como ID
                $veiculo = Veiculo::where('id_sascar', $idSascar)->first();

                if (!$veiculo) {
                    // Tentar localizar o veículo diretamente pelo ID Sascar (caso o id_sascar seja o próprio ID)
                    $veiculo = Veiculo::where('id_veiculo', $idSascar)->first();
                }

                if ($veiculo) {
                    $ultimoAbastecimento = DB::connection('pgsql')->table('v_abastecimento_listar_todos')
                        ->where('placa', $veiculo->placa)
                        ->where('data_inicio', '<', $dataConsulta)
                        ->orderBy('data_inicio', 'desc')
                        ->first(['km_abastecimento']);

                    if ($ultimoAbastecimento && $ultimoAbastecimento->km_abastecimento > 0) {
                        $km = floatval($ultimoAbastecimento->km_abastecimento);
                        Log::info("AbastecimentoService.buscarKmVeiculo: KM encontrado via histórico de abastecimentos: {$km}");
                        return $km;
                    } else {
                        Log::info("AbastecimentoService.buscarKmVeiculo: Nenhum abastecimento anterior encontrado para a placa");
                    }
                } else {
                    Log::warning("AbastecimentoService.buscarKmVeiculo: Veículo não encontrado para ID Sascar: {$idSascar}");
                }
            } catch (\Exception $e) {
                Log::warning("AbastecimentoService.buscarKmVeiculo: Erro ao buscar no histórico de abastecimentos: " . $e->getMessage());
                // Continuar para o valor padrão
            }

            // Se nada for encontrado, retornar o KM padrão
            Log::info("AbastecimentoService.buscarKmVeiculo: Nenhum KM encontrado, retornando valor padrão: {$kmPadrao}");
            return $kmPadrao;
        } catch (\Exception $e) {
            Log::error("AbastecimentoService.buscarKmVeiculo: Erro não tratado: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return $kmPadrao;
        }
    }

    /**
     * Salva um item de abastecimento
     *
     * @param int $idAbastecimento
     * @param array $item
     * @param int $index
     * @return int
     */
    public function salvarItemAbastecimento($idAbastecimento, $item, $index)
    {
        // Formatar data para garantir que tenha hora
        $data_abastecimento = $this->formatarDataAbastecimento($item['data_abastecimento']);

        // Preparar dados do item
        $itemData = [
            'id_abastecimento'   => $idAbastecimento,
            'data_inclusao'      => now(),
            'data_abastecimento' => $data_abastecimento,
            'id_combustivel'     => (int)$item['id_combustivel'],
            'id_bomba'           => $item['id_bomba'] ?? null,
            'litros_abastecido'  => (float)$item['litros'],
            'km_veiculo'         => (float)$item['km_veiculo'],
            'km_anterior'        => isset($item['km_anterior']) ? (float)$item['km_anterior'] : null,
            'valor_unitario'     => (float)$item['valor_unitario'],
            'valor_total'        => (float)$item['valor_total']
        ];

        Log::info('Inserindo item de abastecimento #' . ($index + 1) . ':', $itemData);
        $itemId = DB::connection('pgsql')->table('abastecimento_itens')->insertGetId($itemData, 'id_abastecimentos_itens');
        Log::info('Item #' . ($index + 1) . ' inserido com ID: ' . $itemId);

        return $itemId;
    }

    /**
     * Processa um lote de abastecimentos
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function processarLote(Request $request)
    {
        try {
            $abastecimentos = null;

            // Verificar como os dados foram enviados (array direto ou JSON)
            if ($request->has('abastecimentos') && is_string($request->abastecimentos)) {
                $abastecimentos = json_decode($request->abastecimentos, true);
            } elseif ($request->has('abastecimentos') && is_array($request->abastecimentos)) {
                $abastecimentos = $request->abastecimentos;
            } else {
                throw new Exception('Formato inválido de dados para processamento em lote');
            }

            if (empty($abastecimentos) || !is_array($abastecimentos)) {
                throw new Exception('Nenhum abastecimento válido enviado para processamento em lote');
            }

            Log::info('Processando lote com ' . count($abastecimentos) . ' abastecimentos');

            $resultados = [
                'sucesso' => 0,
                'falhas' => 0,
                'mensagens' => []
            ];

            DB::beginTransaction();

            foreach ($abastecimentos as $abastecimento) {
                try {
                    // Validar dados básicos do abastecimento
                    $validator = Validator::make($abastecimento, [
                        'id_fornecedor' => 'required|exists:fornecedor,id_fornecedor',
                        'id_filial' => 'required|exists:filiais,id',
                        'id_veiculo' => 'required|exists:veiculo,id_veiculo',
                        'id_departamento' => 'required|exists:departamento,id_departamento',
                        'numero_nota_fiscal' => 'nullable|string',
                        'items' => 'required|array|min:1'
                    ]);

                    if ($validator->fails()) {
                        throw new Exception('Validação falhou: ' . implode(', ', $validator->errors()->all()));
                    }

                    // Verificar se a NF já existe
                    $this->checkDuplicateNF($abastecimento['numero_nota_fiscal'], $abastecimento['id_fornecedor']);

                    // Buscar id_pessoal se houver id_motorista
                    $id_pessoal = $this->obterIdPessoalPorMotorista($abastecimento['id_motorista'] ?? null);

                    // Inserir registro principal
                    $abastecimentoData = [
                        'data_inclusao'      => now(),
                        'id_fornecedor'      => $abastecimento['id_fornecedor'],
                        'id_filial'          => $abastecimento['id_filial'],
                        'numero_nota_fiscal' => $abastecimento['numero_nota_fiscal'],
                        'chave_nf'           => $abastecimento['chave_nf'] ?? null,
                        'id_veiculo'         => $abastecimento['id_veiculo'],
                        'id_pessoal'         => $id_pessoal,
                        'id_departamento'    => $abastecimento['id_departamento'],
                        'id_user'            => auth()->id()
                    ];

                    $abastecimentoId = DB::connection('pgsql')->table('abastecimento')->insertGetId($abastecimentoData, 'id_abastecimento');
                    Log::info('Lote: Abastecimento inserido com ID: ' . $abastecimentoId);

                    // Inserir itens
                    foreach ($abastecimento['items'] as $index => $item) {
                        $this->salvarItemAbastecimento($abastecimentoId, $item, $index);
                    }

                    $resultados['sucesso']++;
                    $resultados['mensagens'][] = "Abastecimento NF {$abastecimento['numero_nota_fiscal']} registrado com sucesso.";
                } catch (\Exception $e) {
                    $resultados['falhas']++;
                    $resultados['mensagens'][] = "Falha ao processar abastecimento: " . $e->getMessage();
                    Log::error("Erro ao processar abastecimento no lote: {$e->getMessage()}");

                    // Se falhar em qualquer abastecimento, cancelar todo o lote
                    throw $e;
                }
            }

            DB::commit();
            Log::info('Processamento em lote concluído com sucesso');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Processamento concluído. {$resultados['sucesso']} abastecimentos registrados.",
                    'resultados' => $resultados
                ]);
            }

            return redirect()
                ->route('admin.abastecimentomanual.index')
                ->with('success', "Processamento concluído. {$resultados['sucesso']} abastecimentos registrados.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ERRO NO PROCESSAMENTO EM LOTE: ' . $e->getMessage());
            Log::error('Detalhes do erro: ' . $e->getTraceAsString());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao processar lote: ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Erro ao processar lote: ' . $e->getMessage());
        }
    }

    /**
     * Obtém o ID pessoal com base no ID do motorista
     *
     * @param int|null $idMotorista
     * @return int|null
     */
    public function obterIdPessoalPorMotorista($idMotorista = null)
    {
        if (empty($idMotorista)) {
            return null;
        }

        $motorista = Motorista::find($idMotorista);

        if ($motorista && $motorista->pessoal) {
            $id_pessoal = $motorista->pessoal->id_pessoal;
            Log::info("Motorista encontrado: ID motorista {$idMotorista}, ID pessoal {$id_pessoal}");
            return $id_pessoal;
        } else {
            Log::warning("Motorista não encontrado ou sem vínculo com pessoal: ID {$idMotorista}");
            return null;
        }
    }

    /**
     * Formata a data de abastecimento para garantir que tenha hora
     *
     * @param string $data
     * @return string
     */
    public function formatarDataAbastecimento($data)
    {
        if (empty($data)) {
            return now()->format('Y-m-d H:i:s');
        }

        // Se a data já tiver formato completo com hora
        if (strpos($data, ':') !== false) {
            return $data;
        }

        // Se a data estiver sem hora, adicionar a hora atual
        try {
            $dataObj = \Carbon\Carbon::parse($data);
            $horaAtual = \Carbon\Carbon::now();

            return $dataObj->setHour($horaAtual->hour)
                ->setMinute($horaAtual->minute)
                ->setSecond($horaAtual->second)
                ->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Log::error('Erro ao formatar data de abastecimento: ' . $e->getMessage());
            // Em caso de erro, retornar a data atual
            return now()->format('Y-m-d H:i:s');
        }
    }

    /**
     * Obtém dados de referência para os selects (com cache)
     *
     * @return array
     */
    public function getReferenceDatas()
    {
        return Cache::remember('abastecimento_reference_datas', now()->addHours(12), function () {
            return [
                'veiculosFrequentes' => Veiculo::where('situacao_veiculo', true)
                    ->orderBy('placa')
                    ->limit(20)
                    ->get(['id_veiculo as value', 'placa as label']),

                'fornecedoresFrequentes' => Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
                    ->orderBy('nome_fornecedor')
                    ->limit(20)
                    ->get(),

                'filiais' => VFilial::select('id as value', 'name as label')
                    ->orderBy('name')
                    ->get(),

                'departamentos' => Departamento::orderBy('descricao_departamento')
                    ->get('descricao_departamento'),

                // Filtrar bombas para excluir "bomba externa"
                'bombas' => Bomba::whereRaw("LOWER(descricao_bomba) NOT LIKE '%bomba externa%'")
                    ->orderBy('descricao_bomba')
                    ->get('descricao_bomba'),

                'tiposCombustivel' => TipoCombustivel::orderBy('descricao')
                    ->get('descricao'),

                'motoristas' => Motorista::where('ativo', 'S')
                    ->orderBy('nome')
                    ->get('nome')
            ];
        });
    }

    /**
     * Constrói query de busca para exportação
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildExportQuery($request)
    {
        $query = AbastecimentoManual::query();

        // Aplicar filtros
        if ($request->filled('id_abastecimento')) {
            $query->where('id_abastecimento', $request->id_abastecimento);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('numero_nota_fiscal')) {
            $query->where('numero_nota_fiscal', $request->numero_nota_fiscal);
        }

        // Usar whereRaw para data_inclusao
        if ($request->filled('data_inclusao')) {
            $query->whereRaw("data_inclusao::date >= ?", [$request->data_inclusao]);
        }

        if ($request->filled('data_final_abastecimento')) {
            $query->whereRaw("data_inclusao::date <= ?", [$request->data_final_abastecimento]);
        }

        // Usar whereRaw para data_abastecimento
        if ($request->filled('data_inicial_abastecimento')) {
            $query->whereRaw("data_abastecimento::date >= ?", [$request->data_inicial_abastecimento]);
        }

        if ($request->filled('data_fim_abastecimento')) {
            $query->whereRaw("data_abastecimento::date <= ?", [$request->data_fim_abastecimento]);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        return $query;
    }

    /**
     * Obtém lista de veículos frequentes, limitados a 20
     *
     * @return \Illuminate\Support\Collection
     */
    public function getVeiculosFrequentes()
    {
        return Cache::remember('veiculos_ativos_select', now()->addMinutes(15), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->whereRaw('is_possui_tracao IS NOT TRUE')
                ->whereIn('id_tipo_equipamento', [1, 2, 3, 52, 53, 54, 40, 44, 71, 49])
                ->whereRaw("TRIM(placa) NOT LIKE '%TK'")
                ->orderBy('placa')
                ->limit(20)
                ->get(['id_veiculo as value', 'placa as label']);
        });
    }

    /**
     * Obtém lista de fornecedores frequentes, garantindo que Carvalima esteja incluído
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFornecedoresFrequentes()
    {
        return Cache::remember('fornecedores_ativos_select', now()->addHour(), function () {
            // Buscar um número misto de fornecedores, garantindo que Carvalima esteja incluído

            // 1. Buscar alguns fornecedores Carvalima (até 3)
            $carvalima = Fornecedor::select('id_fornecedor', 'nome_fornecedor', 'cpf_fornecedor', 'cnpj_fornecedor')
                ->where('nome_fornecedor', 'ilike', '%carvalima%')
                ->where('is_ativo', '!=', 'false')
                ->limit(3)
                ->get()
                ->map(function ($f) {
                    $suffix = '';
                    if (!empty($f->cpf_fornecedor)) {
                        $suffix = ' - ' . $f->cpf_formatado;
                    } elseif (!empty($f->cnpj_fornecedor)) {
                        $suffix = ' - ' . $f->cnpj_formatado;
                    }
                    return ['label' => $f->nome_fornecedor . $suffix, 'value' => $f->id_fornecedor];
                });

            // 2. Buscar fornecedores mais utilizados - mantendo a ordenação alfabética
            $carvalmaIds = $carvalima->pluck('value')->toArray();

            $fornecedoresFrequentes = Fornecedor::select('id_fornecedor', 'nome_fornecedor', 'cpf_fornecedor', 'cnpj_fornecedor')
                ->where('is_ativo', '!=', 'false')
                ->whereNotIn('id_fornecedor', $carvalmaIds)
                ->orderBy('nome_fornecedor')
                ->limit(17) // Limitado para ter 20 no total incluindo Carvalima
                ->get()
                ->map(function ($f) {
                    $suffix = '';
                    if (!empty($f->cpf_fornecedor)) {
                        $suffix = ' - ' . $f->cpf_formatado;
                    } elseif (!empty($f->cnpj_fornecedor)) {
                        $suffix = ' - ' . $f->cnpj_formatado;
                    }
                    return ['label' => $f->nome_fornecedor . $suffix, 'value' => $f->id_fornecedor];
                });

            // 3. Combinar os resultados: Carvalima primeiro + outros fornecedores
            $resultado = collect([]);

            // Adicionar Carvalima primeiro
            $resultado = $resultado->merge($carvalima);

            // Adicionar outros fornecedores
            $resultado = $resultado->merge($fornecedoresFrequentes);

            return $resultado;
        });
    }

    /**
     * Obtém lista de filiais
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFiliais()
    {
        return VFilial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtém departamentos ativos para o select
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDepartamentosAtivos()
    {
        return Cache::remember('departamentos_ativos', now()->addHour(), function () {
            return Departamento::where('ativo', true)
                ->orderBy('descricao_departamento')
                ->get(['id_departamento as value', 'descricao_departamento as label']);
        });
    }

    /**
     * Obtém bombas ativas para uso no formulário de abastecimento,
     * filtrando apenas as bombas internas (Carvalima)
     *
     * @param bool $comObjetos Se verdadeiro, retorna objetos stdClass em vez de arrays
     * @return array|\Illuminate\Support\Collection
     */
    public function getBombasAtivas($comObjetos = true)
    {
        // Obter as bombas internas como array
        $bombas = Bomba::bombasInternasParaSelect(true, true);

        // Se precisar converter para objetos para compatibilidade com o template
        if ($comObjetos) {
            return collect($bombas)->map(function ($item) {
                return (object) $item;
            });
        }

        return $bombas;
    }

    /**
     * Obtém tipos de combustível para o select
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTiposCombustivel()
    {
        return Cache::remember('tipos_combustivel', now()->addHour(), function () {
            return TipoCombustivel::orderBy('descricao')
                ->get(['id_tipo_combustivel as value', 'descricao as label']);
        });
    }

    /**
     * Obtém motoristas ativos para o select
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMotoristasAtivos()
    {
        return Cache::remember('motoristas_ativos', now()->addHour(), function () {
            return Motorista::where('ativo', '1')
                ->orderBy('nome')
                ->get(['idobtermotorista as value', 'nome as label']);
        });
    }

    public function getBuscarKmHistoricoAbastecimento($idVeiculo, $dataAbertura)
    {
        if (!empty($idVeiculo) && !empty($dataAbertura)) {
            try {
                $result = DB::connection('pgsql')->select(
                    "SELECT * FROM fc_km_historico_abastecimento(?, ?::TIMESTAMP)",
                    [$idVeiculo, $dataAbertura]
                );

                if (!empty($result) && isset($result[0]->fc_km_historico_abastecimento)) {
                    $retorno = $result[0]->fc_km_historico_abastecimento;
                    Log::info("AbastecimentoService.getBuscarKmHistoricoAbastecimento: KM encontrado via função: {$retorno}");
                    return floatval($retorno);
                }
            } catch (\Exception $e) {
                Log::warning("AbastecimentoService.getBuscarKmHistoricoAbastecimento: Erro ao consultar função: " . $e->getMessage());
            }
        }

        return 0;
    }
}
