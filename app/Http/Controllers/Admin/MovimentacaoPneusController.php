<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistoricoPneu;
use App\Models\ManutencaoPneusEntradaItens;
use App\Models\OrdemServico;
use App\Models\Pneu;
use App\Models\PneusAplicados;
use App\Models\RequisicaoPneu;
use App\Models\RequisicaoPneuItens;
use App\Models\RequisicaoPneuModelos;
use App\Models\TipoEquipamento;
use App\Models\Veiculo;
use App\Models\VeiculoXPneu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Traits\SanitizesMonetaryValues;
use App\Traits\HasPneusParadosTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\PneusDeposito;

class MovimentacaoPneusController extends Controller
{
    use SanitizesMonetaryValues;
    use HasPneusParadosTrait;

    /**
     * Busca ou cria um registro VeiculoXPneu ativo para o veículo especificado
     * 
     * @param int $idVeiculo
     * @param int $numeroEixos
     * @return VeiculoXPneu
     */
    private function obterOuCriarVeiculoXPneu($idVeiculo, $numeroEixos = 2)
    {
        $veiculoXPneu = VeiculoXPneu::where('id_veiculo', $idVeiculo)
            ->where('situacao', true)
            ->first();

        if (!$veiculoXPneu) {
            Log::warning("⚠️ VeiculoXPneu não encontrado - criando novo registro", ['id_veiculo' => $idVeiculo]);

            $veiculoXPneu = VeiculoXPneu::create([
                'data_inclusao' => now(),
                'data_alteracao' => now(),
                'id_veiculo' => $idVeiculo,
                'eixos_veiculos' => $numeroEixos,
                'situacao' => true
            ]);

            Log::info("✅ VeiculoXPneu criado automaticamente", [
                'id_veiculo_pneu' => $veiculoXPneu->id_veiculo_pneu,
                'id_veiculo' => $idVeiculo,
                'eixos_veiculos' => $veiculoXPneu->eixos_veiculos
            ]);
        }

        return $veiculoXPneu;
    }


    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        // Bloqueio: não permite iniciar movimentação se existirem pneus parados no depósito por mais de 24 horas
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->route('admin.ordemservicos.index')
                ->with('notification', [
                    'type' => 'error',
                    'title' => 'Operação bloqueada',
                    'message' => 'Existem pneus parados no depósito há mais de 24 horas. Movimentação bloqueada.',
                    'duration' => 5000,
                ]);
        }
        // Buscar ordens de serviço com id_tipo_ordem = 3 e id_status_servico = 10
        $ordensServico = OrdemServico::join('veiculo', 'veiculo.id_veiculo', '=', 'ordem_servico.id_veiculo')
            ->select(
                'ordem_servico.id_ordem_servico as value',
                DB::raw("CONCAT('OS: ', ordem_servico.id_ordem_servico, ' - ', veiculo.placa) as label"),
                'ordem_servico.id_veiculo'
            )
            ->where('ordem_servico.id_tipo_ordem_servico', 3)
            ->where('ordem_servico.id_status_ordem_servico', 2)
            ->where('veiculo.situacao_veiculo', true)
            ->where('veiculo.is_terceiro', false)
            ->where('veiculo.id_veiculo', '!=', 11231) // Excluir veículo 11231 - BOR0001
            ->orderBy('ordem_servico.id_ordem_servico', 'desc')
            ->get();

        // Verificar se não há ordens de serviço disponíveis
        $necessarioAbrirOS = $ordensServico->isEmpty();

        LOG::DEBUG(['Necessário abrir OS?' => $necessarioAbrirOS]);
        LOG::DEBUG(['Ordens de Serviço disponíveis:' => $ordensServico->toArray()]);

        // Pneus serão carregados dinamicamente baseados na ordem de serviço selecionada
        $pneus = collect();

        return view('admin.movimentacaopneus.index', compact('ordensServico', 'pneus', 'necessarioAbrirOS'));
    }

    public function getOrdemServicoData(Request $request)
    {
        try {
            Log::info("🔍 Iniciando getOrdemServicoData", [
                'ordem_servico' => $request->ordem_servico,
                'timestamp' => now()
            ]);

            // Buscar a ordem de serviço e o veículo associado
            $ordemServico = OrdemServico::with('veiculo')
                ->where('id_ordem_servico', $request->ordem_servico)
                ->where('id_tipo_ordem_servico', 3)
                ->where('id_status_ordem_servico', 2)
                ->firstOrFail();

            Log::info("✅ Ordem de serviço encontrada", [
                'id_ordem_servico' => $ordemServico->id_ordem_servico,
                'veiculo_id' => $ordemServico->veiculo ? $ordemServico->veiculo->id_veiculo : 'null'
            ]);

            $veiculo = $ordemServico->veiculo;

            if (!$veiculo) {
                return response()->json(['error' => 'Veículo não encontrado para esta ordem de serviço'], 404);
            }

            $pneuVeiculoIds = VeiculoXPneu::select('id_veiculo_pneu')
                ->where('id_veiculo', $veiculo->id_veiculo)
                ->where('situacao', true)
                ->first();

            $pneusAplicados = collect();
            if ($pneuVeiculoIds) {
                $pneusAplicados = PneusAplicados::where('id_veiculo_x_pneu', $pneuVeiculoIds->id_veiculo_pneu)->get();
            }

            $pneusAplicadosFormatados = $pneusAplicados->map(function ($pneu) {
                return [
                    'id_pneu'       => $pneu->id_pneu,
                    'localizacao'   => $pneu->localizacao,
                    'suco_pneu'     => $pneu->sulco_pneu_adicionado,
                ];
            })->toArray();

            $kmAtual = DB::connection('pgsql')->table('veiculo as v')
                ->select(DB::raw('fc_km_relatorio(v.id_veiculo) AS km_atual'))
                ->where('v.id_veiculo', $veiculo->id_veiculo)
                ->value('km_atual');

            $tipoEquipamentoPneus = TipoEquipamento::select('numero_eixos', 'numero_pneus_eixo_1', 'numero_pneus_eixo_2', 'numero_pneus_eixo_3', 'numero_pneus_eixo_4', 'id_desenho_eixos')
                ->where('id_tipo_equipamento', '=', $veiculo->id_tipo_equipamento)
                ->first();

            Log::info("🔧 Tipo equipamento buscado", [
                'id_tipo_equipamento' => $veiculo->id_tipo_equipamento,
                'tipo_encontrado' => $tipoEquipamentoPneus ? 'sim' : 'não',
                'id_desenho_eixos' => $tipoEquipamentoPneus ? $tipoEquipamentoPneus->id_desenho_eixos : 'null'
            ]);

            if (!$tipoEquipamentoPneus) {
                Log::error("❌ Tipo de equipamento não encontrado para o veículo", [
                    'id_veiculo' => $veiculo->id_veiculo,
                    'id_tipo_equipamento' => $veiculo->id_tipo_equipamento
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de equipamento não encontrado para este veículo.'
                ], 500);
            }

            // ✅ BUSCAR LOCALIZAÇÕES DINÂMICAS DA TABELA EIXOS
            $localizacoesDisponiveis = [];
            if ($tipoEquipamentoPneus->id_desenho_eixos) {
                try {
                    Log::info("🔍 Tentando buscar localizações dinâmicas", [
                        'id_desenho_eixos' => $tipoEquipamentoPneus->id_desenho_eixos,
                        'veiculo' => $veiculo->id_veiculo
                    ]);

                    $localizacoesResult = DB::table('eixos')
                        ->select('localizacao')
                        ->where('id_desenho_eixos', $tipoEquipamentoPneus->id_desenho_eixos)
                        ->orderBy('localizacao')
                        ->get();

                    Log::info("✅ Query executada com sucesso", [
                        'resultados_encontrados' => $localizacoesResult->count(),
                        'id_desenho_eixos' => $tipoEquipamentoPneus->id_desenho_eixos
                    ]);

                    if ($localizacoesResult->isEmpty()) {
                        Log::warning("⚠️ Nenhuma localização encontrada na tabela eixos", [
                            'id_desenho_eixos' => $tipoEquipamentoPneus->id_desenho_eixos,
                            'veiculo' => $veiculo->id_veiculo
                        ]);
                    }

                    // Organizar localizações por eixo baseado no primeiro dígito
                    foreach ($localizacoesResult as $loc) {
                        // Extrair número do eixo do primeiro caractere da localização (1D → eixo 1)
                        $eixoNum = intval($loc->localizacao[0]);
                        $eixoIndex = $eixoNum - 1; // Converter para índice baseado em 0

                        // Se não conseguir extrair eixo, colocar no eixo 0
                        if ($eixoIndex < 0) {
                            $eixoIndex = 0;
                        }

                        if (!isset($localizacoesDisponiveis[$eixoIndex])) {
                            $localizacoesDisponiveis[$eixoIndex] = [];
                        }
                        $localizacoesDisponiveis[$eixoIndex][] = [
                            'localizacao' => $loc->localizacao,
                            'x' => 0, // Valor padrão já que a tabela não tem essas colunas
                            'y' => 0  // Valor padrão já que a tabela não tem essas colunas
                        ];
                    }

                    Log::info("🔧 Localizações carregadas dinamicamente", [
                        'veiculo' => $veiculo->id_veiculo,
                        'id_desenho_eixos' => $tipoEquipamentoPneus->id_desenho_eixos,
                        'localizacoes_encontradas' => $localizacoesResult->count(),
                        'localizacoes_por_eixo' => $localizacoesDisponiveis
                    ]);
                } catch (\Exception $e) {
                    Log::error("❌ Erro ao buscar localizações dinâmicas", [
                        'id_desenho_eixos' => $tipoEquipamentoPneus->id_desenho_eixos,
                        'veiculo' => $veiculo->id_veiculo,
                        'erro' => $e->getMessage()
                    ]);

                    // Continuar sem as localizações dinâmicas em caso de erro
                    $localizacoesDisponiveis = [];
                }
            } else {
                Log::info("ℹ️ Veículo sem id_desenho_eixos definido - usando posições padrão", [
                    'veiculo' => $veiculo->id_veiculo
                ]);
            }

            // Formatar os dados para o frontend
            $formattedData = [
                'eixos' => $tipoEquipamentoPneus->numero_eixos,
                'pneus_por_eixo' => [
                    $tipoEquipamentoPneus->numero_pneus_eixo_1,
                    $tipoEquipamentoPneus->numero_pneus_eixo_2,
                    $tipoEquipamentoPneus->numero_pneus_eixo_3,
                    $tipoEquipamentoPneus->numero_pneus_eixo_4
                ],
                'pneusAplicadosFormatados' => $pneusAplicadosFormatados,
                'localizacoesDisponiveis' => $localizacoesDisponiveis, // ✅ NOVO: localizações dinâmicas
            ];

            log::debug(response()->json([
                'id_ordem_servico'      => $ordemServico->id_ordem_servico,
                'id_veiculo'            => $veiculo->id_veiculo ?? 'Não informado',
                'placa'                 => $veiculo->placa ?? 'Não informado',
                'id_tipo_equipamento'   => $veiculo->tipoEquipamento->descricao_tipo ?? 'Não informado',
                'id_categoria'          => $veiculo->categoriaVeiculo->descricao_categoria ?? 'Não informado',
                'id_modelo_veiculo'     => $veiculo->modeloVeiculo->descricao_modelo_veiculo ?? 'Não informado',
                'chassi'                => $veiculo->chassi ?? 'Não informado',
                'km_atual'              => $kmAtual ?? 'Não informado',
                'tipoEquipamentoPneus'  => $formattedData,
                'pneusRequisicao'       => $this->getPneusDaRequisicao($ordemServico->id_ordem_servico),
            ]));

            return response()->json([
                'id_ordem_servico'      => $ordemServico->id_ordem_servico,
                'id_veiculo'            => $veiculo->id_veiculo ?? 'Não informado',
                'placa'                 => $veiculo->placa ?? 'Não informado',
                'id_tipo_equipamento'   => $veiculo->tipoEquipamento->descricao_tipo ?? 'Não informado',
                'id_categoria'          => $veiculo->categoriaVeiculo->descricao_categoria ?? 'Não informado',
                'id_modelo_veiculo'     => $veiculo->modeloVeiculo->descricao_modelo_veiculo ?? 'Não informado',
                'chassi'                => $veiculo->chassi ?? 'Não informado',
                'km_atual'              => $kmAtual ?? 'Não informado',
                'is_possui_tracao'      => $veiculo->is_possui_tracao ?? false,
                'tipoEquipamentoPneus'  => $formattedData,
                'pneusRequisicao'       => $this->getPneusDaRequisicao($ordemServico->id_ordem_servico),
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao buscar dados da ordem de serviço', [
                'id_ordem_servico' => $idOrdemServico ?? 'não informado',
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
                'arquivo' => $e->getFile(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao buscar dados da ordem de serviço: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ==========================================
     * BUSCAR PNEUS DA REQUISIÇÃO DA ORDEM DE SERVIÇO
     * ==========================================
     */
    protected function getPneusDaRequisicao($idOrdemServico)
    {
        try {
            // Log para debug
            Log::info("🔍 Buscando pneus para ordem de serviço: {$idOrdemServico}");

            // 1. Buscar pneus específicos vinculados à requisição da ordem de serviço
            $pneusRequisicao = collect();

            $requisicaoExists = DB::table('requisicao_pneu')
                ->where('id_ordem_servico', $idOrdemServico)
                ->exists();

            Log::info("🔍 Requisição existe: " . ($requisicaoExists ? 'SIM' : 'NÃO'));

            if ($requisicaoExists) {
                $pneusRequisicao = DB::table('requisicao_pneu as rp')
                    ->join('requisicao_pneu_modelos as rpm', 'rpm.id_requisicao_pneu', '=', 'rp.id_requisicao_pneu')
                    ->join('requisicao_pneu_itens as rpi', 'rpi.id_requisicao_pneu_modelos', '=', 'rpm.id_requisicao_pneu_modelos')
                    ->join('pneu as p', 'p.id_pneu', '=', 'rpi.id_pneu')
                    ->select(
                        'p.id_pneu',
                        'p.status_pneu',
                        DB::raw("'requisicao' as origem")
                    )
                    ->where('rp.id_ordem_servico', $idOrdemServico)
                    ->whereIn('p.status_pneu', ['DEPOSITO', 'ESTOQUE'])
                    ->whereNull('p.deleted_at')
                    ->get();

                Log::info("🔍 Pneus da requisição encontrados: " . $pneusRequisicao->count());
            }

            // 2. Buscar TODOS os pneus com status DEPOSITO
            $todosPneusDeposito = DB::table('pneu as p')
                ->select(
                    'p.id_pneu',
                    'p.status_pneu',
                    DB::raw("'deposito' as origem")
                )
                ->where('p.status_pneu', 'DEPOSITO')
                ->whereNull('p.deleted_at')
                ->get();

            Log::info("🔍 Total de pneus em depósito encontrados: " . $todosPneusDeposito->count());

            // 3. Combinar os resultados, removendo duplicatas
            $pneusCombinados = $pneusRequisicao->concat($todosPneusDeposito)
                ->unique('id_pneu') // Remove duplicatas baseado no id_pneu
                ->values(); // Reindexar

            Log::info("🔍 Total de pneus combinados (requisição + depósito): " . $pneusCombinados->count());

            // 4. Separar pneus da requisição e do depósito para ordenação
            $pneusRequisicaoFormatados = collect();
            $pneusDepositoFormatados = collect();

            foreach ($pneusCombinados as $pneu) {
                $formatado = [
                    'value' => $pneu->id_pneu,
                    'status' => $pneu->status_pneu,
                    'origem' => $pneu->origem
                ];

                if ($pneu->origem === 'requisicao') {
                    // Pneus da requisição: label especial e vêm primeiro
                    $formatado['label'] = $pneu->id_pneu . ' - OS: ' . $idOrdemServico;
                    $pneusRequisicaoFormatados->push($formatado);
                } else {
                    // Pneus apenas do depósito: label normal
                    $formatado['label'] = (string) $pneu->id_pneu . ' - DISPONIVEL EM DEPÓSITO';
                    $pneusDepositoFormatados->push($formatado);
                }
            }

            // 5. Ordenar cada grupo por ID e depois juntar (requisição primeiro)
            $resultado = $pneusRequisicaoFormatados->sortBy('value')
                ->concat($pneusDepositoFormatados->sortBy('value'))
                ->values()
                ->toArray();

            Log::info("🔍 Resultado final: " . count($resultado) . " pneus (" .
                $pneusRequisicaoFormatados->count() . " da requisição + " .
                $pneusDepositoFormatados->count() . " do depósito)");

            return $resultado;
        } catch (\Exception $e) {
            Log::error('❌ Erro ao buscar pneus da requisição: ' . $e->getMessage());
            Log::error('❌ Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    public function getPneuData(Request $request)
    {
        try {
            $pneu = Pneu::with('ultimaManutencaoEntrada.tipoReforma')->findOrFail($request->pneu);

            if (!$pneu) {
                return response()->json(['error' => 'Pneu não encontrado'], 404);
            }

            return response()->json([
                'id_pneu'    => $pneu->id_pneu,
                'sulco'      => $pneu->sulco ?? null, // ✅ Usar o campo sulco da tabela pneu
                'tipo_pneu'  => $pneu->ultimaManutencaoEntrada->tipoReforma->descricao_tipo_reforma ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do pneu: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar dados do pneu'], 500);
        }
    }

    public function getPneusFrequentes()
    {
        $pneus = Pneu::select('id_pneu as value', 'id_pneu as label')
            ->whereNotIn('status_pneu', ['APLICADO', 'DEPOSITO', 'DESCARTE', 'EM MANUTENÇÃO'])
            ->whereNotlike('status_pneu', 'VENDIDO%')
            ->orderBy('id_pneu')
            ->limit(20)
            ->get();

        return $pneus;
    }

    /**
     * ==========================================
     * MÉTODO PÚBLICO PARA RECEBER DADOS DO AUTO-SAVE
     * ==========================================
     */
    public function getSalvarData(Request $request)
    {
        try {
            // Log da requisição recebida
            Log::info('📨 getSalvarData recebido', [
                'method' => $request->method(),
                'has_data' => $request->hasAny(['dadosVeiculo', 'operacao', 'pneusAplicados']),
                'content_type' => $request->header('Content-Type'),
                'data_keys' => array_keys($request->all())
            ]);

            // Verificar se é uma operação de auto-save ou salvamento manual
            $isAutoSave = $request->input('auto_save', false);

            if ($isAutoSave) {
                // Para auto-save, usar handleAutoSave (sessão/cache)
                $resultado = $this->handleAutoSave($request);
                return response()->json($resultado);
            } else {
                // Para salvamento manual, usar handleManualSave (banco de dados)
                Log::info('📤 Processando salvamento MANUAL no banco de dados');
                return $this->handleManualSave($request);
            }
        } catch (\Exception $e) {
            Log::error('❌ ERRO no getSalvarData: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erro ao processar dados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ==========================================
     * AUTO-SAVE: Gerenciar sessões e operações
     * ==========================================
     */
    protected function handleAutoSave(Request $request)
    {
        try {
            $dadosVeiculo = $request->input('dadosVeiculo', []);
            $operacao = $request->input('operacao', []);

            // ✅ NOVA LÓGICA: Detectar se é uma remoção de pneu
            if (isset($operacao['type']) && $operacao['type'] === 'remocao_pneu') {
                $dadosRemocao = $operacao['data'] ?? [];

                Log::info("🔍 DEBUG - Operação completa recebida:", $operacao);
                Log::info("🔍 DEBUG - Dados da operação extraídos:", $dadosRemocao);

                // ✅ PROCESSAR REMOÇÃO NO BANCO IMEDIATAMENTE
                if (!empty($dadosRemocao['pneu_removido_id'])) {

                    DB::beginTransaction();
                    try {
                        Log::info("🔍 Dados recebidos do frontend:", $dadosRemocao);

                        // Construir dados no formato esperado pelo processarRemocaoPneu
                        $pneuRemovido = [
                            'id_pneu' => $dadosRemocao['pneu_removido_id'],
                            'kmRemovido' => $dadosRemocao['km_removido'] ?? null,
                            'sulcoRemovido' => $dadosRemocao['sulco_removido'] ?? null,
                            'localizacao' => $dadosRemocao['localizacao'] ?? null,
                            'status' => $this->determinarStatusDestino($dadosRemocao['destino'] ?? 'deposito'),
                            'destinacao_solicitada' => $dadosRemocao['destinacao_solicitada'] ?? null
                        ];

                        Log::info("🔄 Auto-save processando remoção do pneu: {$pneuRemovido['id_pneu']}", [
                            'dados_pneu' => $pneuRemovido,
                            'destinacao_recebida' => $dadosRemocao['destinacao_solicitada'] ?? 'NÃO RECEBIDA'
                        ]);

                        Log::info("🔄 Auto-save processando remoção do pneu: {$pneuRemovido['id_pneu']}", [
                            'dados_pneu' => $pneuRemovido
                        ]);

                        $this->processarRemocaoPneu($pneuRemovido, $dadosVeiculo);

                        DB::commit();

                        // Limpar cache para este veículo após sucesso
                        $userId = Auth::id();
                        $sessionKey = "movimentacao_pneus_{$dadosVeiculo['id_veiculo']}_{$userId}";
                        Cache::forget($sessionKey);

                        return [
                            'success' => true,
                            'message' => 'Remoção processada automaticamente no banco de dados',
                            'processado_no_banco' => true,
                            'pneu_processado' => $pneuRemovido['id_pneu']
                        ];
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("❌ Auto-save: erro ao processar remoção no banco: " . $e->getMessage());

                        return [
                            'success' => false,
                            'error' => 'Erro ao processar remoção: ' . $e->getMessage()
                        ];
                    }
                } else {
                    Log::error("❌ ID do pneu removido não encontrado nos dados");
                    return [
                        'success' => false,
                        'error' => 'ID do pneu removido não encontrado'
                    ];
                }
            }

            // ✅ NOVA LÓGICA: Detectar se é uma aplicação de pneu avulso
            if (isset($operacao['type']) && $operacao['type'] === 'aplicacao_pneu_avulso') {
                $dadosAplicacao = $operacao['data'] ?? [];

                // ✅ PROCESSAR APLICAÇÃO NO BANCO IMEDIATAMENTE
                if (!empty($dadosAplicacao['pneu_avulso_id'])) {

                    DB::beginTransaction();
                    try {
                        // Construir dados no formato esperado
                        $pneuAvulso = [
                            'id_pneu' => $dadosAplicacao['pneu_avulso_id'],
                            'status' => 'APLICADO'
                        ];

                        // Para aplicação avulsa, usar dados da aplicação 
                        $pneuRemovido = [
                            'kmRemovido' => $dadosAplicacao['km_aplicado'] ?? null,
                            'localizacao' => $dadosAplicacao['localizacao'] ?? null,
                            'sulcoAplicado' => $dadosAplicacao['sulco_aplicado'] ?? null, // ✅ Adicionar sulco
                        ];

                        // Verificar se veículo possui tração para validação
                        $veiculo = Veiculo::find($dadosVeiculo['id_veiculo']);
                        $possuiTracao = $veiculo && $veiculo->is_possui_tracao;

                        $this->processarAplicacaoPneu($pneuAvulso, $pneuRemovido, $dadosVeiculo, $possuiTracao);

                        DB::commit();

                        // Limpar cache para este veículo após sucesso
                        $userId = Auth::id();
                        $sessionKey = "movimentacao_pneus_{$dadosVeiculo['id_veiculo']}_{$userId}";
                        Cache::forget($sessionKey);

                        return [
                            'success' => true,
                            'message' => 'Aplicação processada automaticamente no banco de dados',
                            'processado_no_banco' => true,
                            'pneu_processado' => $pneuAvulso['id_pneu']
                        ];
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("❌ Auto-save: erro ao processar aplicação no banco: " . $e->getMessage());

                        return [
                            'success' => false,
                            'error' => 'Erro ao processar aplicação: ' . $e->getMessage()
                        ];
                    }
                } else {
                    Log::error("❌ ID do pneu avulso não encontrado nos dados");
                    return [
                        'success' => false,
                        'error' => 'ID do pneu avulso não encontrado'
                    ];
                }
            }

            // ✅ LÓGICA ORIGINAL PARA OUTRAS OPERAÇÕES (aplicações, rodízios, etc.)
            // Validação básica
            if (empty($dadosVeiculo['id_veiculo'])) {
                Log::error('❌ ID do veículo vazio');
                return [
                    'success' => false,
                    'error' => 'ID do veículo é obrigatório'
                ];
            }

            // ✅ TENTAR INSERIR NO BANCO IMEDIATAMENTE

            // ✅ TENTAR INSERIR NO BANCO IMEDIATAMENTE
            $registroSalvoNoBanco = false;
            $historicoId = null;
            $motivoErro = '';

            try {
                // Verificar se veículo existe
                $veiculo = Veiculo::find($dadosVeiculo['id_veiculo']);
                if (!$veiculo) {
                    throw new \Exception("Veículo não encontrado: {$dadosVeiculo['id_veiculo']}");
                }


                $idPneu = $this->obterIdPneuValidoComLog($operacao);


                // Se não encontrou ID específico, pegar do primeiro pneu aplicado
                if (!$idPneu) {
                    $pneusAplicados = $request->input('pneusAplicados', []);
                    if (!empty($pneusAplicados)) {
                        $idPneu = $pneusAplicados[0]['id_pneu'] ?? null;
                    }
                }

                // Como último recurso, usar qualquer pneu
                if (!$idPneu) {
                    $idPneu = Pneu::first()?->id_pneu;
                }

                // Obter dados do pneu para preencher id_modelo e id_vida_pneu
                $dadosPneu = null;
                if ($idPneu) {
                    $dadosPneu = DB::connection('carvalima_production')
                        ->table('pneu')
                        ->select('id_modelo_pneu', 'id_controle_vida_pneu')
                        ->where('id_pneu', $idPneu)
                        ->first();
                }

                // Dados mínimos para inserção
                $dadosInsercao = [
                    'data_inclusao' => now(),
                    'id_veiculo' => $dadosVeiculo['id_veiculo'],
                    'id_pneu' => $idPneu,
                    'status_movimentacao' => 'MOV_PNEU',
                    'origem_operacao' => 'MOV_PNEU',
                    'observacoes_operacao' => 'Auto-Save: ' . ($operacao['type'] ?? 'sem_tipo'),
                    'eixo_aplicado' => null,
                    'id_usuario' => Auth::id(),
                ];

                // Adicionar campos específicos se dados do pneu foram encontrados
                if ($dadosPneu) {
                    $dadosInsercao['id_modelo'] = $dadosPneu->id_modelo_pneu;
                    $dadosInsercao['id_vida_pneu'] = $dadosPneu->id_controle_vida_pneu;
                }

                // EXECUTAR INSERT
                $historico = HistoricoPneu::create($dadosInsercao);
                $historicoId = $historico->id_historico_pneu;

                $registroSalvoNoBanco = true;
            } catch (\Exception $bancoError) {
                $motivoErro = $bancoError->getMessage();
            }

            // Salvar sessão (mesmo que banco falhe)
            $userId = Auth::id();
            $sessionKey = "movimentacao_pneus_{$dadosVeiculo['id_veiculo']}_{$userId}";

            $sessionData = Cache::get($sessionKey, [
                'last_update' => now()->toISOString(),
                'operacoes' => [],
                'dados_veiculo' => $dadosVeiculo
            ]);

            $sessionData['operacoes'][] = [
                'tipo' => $operacao['type'] ?? 'teste',
                'timestamp' => time() * 1000,
                'salvo_banco' => $registroSalvoNoBanco,
                'historico_id' => $historicoId,
                'erro_banco' => $motivoErro
            ];

            $sessionData['last_update'] = now()->toISOString();
            Cache::put($sessionKey, $sessionData, now()->addHours(2));

            $resultado = [
                'success' => true,
                'message' => 'Auto-save processado',
                'salvo_banco' => $registroSalvoNoBanco,
                'historico_id' => $historicoId,
                'session_key' => $sessionKey,
                'operacoes_count' => count($sessionData['operacoes']),
                'erro_banco' => $motivoErro
            ];

            return $resultado;
        } catch (\Exception $e) {
            Log::error('💥 ERRO GERAL NO HANDLE AUTO-SAVE', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return [
                'success' => false,
                'error' => 'Erro no auto-save: ' . $e->getMessage()
            ];
        }
    }

    private function mapearTipoOperacaoParaStatusComLog($tipoOperacao)
    {
        $mapa = [
            'troca_pneus' => 'RODIZIO',
            'rodizio_automatico' => 'RODIZIO',
            'aplicacao_pneu_avulso' => 'APLICADO',
            'remocao_pneu' => 'REMOVIDO',
            'teste_manual_debug' => 'TESTE_MOV_PNEU',
            'teste_correcao_banco' => 'TESTE_MOV_PNEU',
            'teste_simples' => 'TESTE_MOV_PNEU',
            'teste_tempo_real' => 'TESTE_MOV_PNEU'
        ];

        $status = $mapa[$tipoOperacao] ?? 'MOV_PNEU_OPERACAO';

        return $status;
    }

    private function criarObservacaoAutoSaveComLog($operacao)
    {
        $tipo = $operacao['type'] ?? 'operacao_desconhecida';
        $dados = $operacao['data'] ?? [];

        $observacao = "Auto-save: {$tipo}";

        // Adicionar IDs específicos na observação para debug
        if ($tipo === 'troca_pneus') {
            $pneu1 = $dados['pneu1_id'] ?? 'N/A';
            $pneu2 = $dados['pneu2_id'] ?? 'N/A';
            $observacao .= " - pneu1_id: {$pneu1}, pneu2_id: {$pneu2}";
        } elseif ($tipo === 'aplicacao_pneu_avulso') {
            $pneuAvulso = $dados['pneu_avulso_id'] ?? 'N/A';
            $localizacao = $dados['localizacao'] ?? 'N/A';
            $observacao .= " - pneu_avulso_id: {$pneuAvulso}, loc: {$localizacao}";
        } elseif ($tipo === 'remocao_pneu') {
            $pneuRemovido = $dados['pneu_removido_id'] ?? 'N/A';
            $destino = $dados['destino'] ?? 'N/A';
            $observacao .= " - pneu_removido_id: {$pneuRemovido}, destino: {$destino}";
        }

        $observacaoFinal = substr($observacao, 0, 255);

        return $observacaoFinal;
    }

    private function extrairEixoDaOperacaoComLog($operacao)
    {
        $dados = $operacao['data'] ?? [];

        $eixo = null;
        if (isset($dados['localizacao'])) {
            $eixo = substr($dados['localizacao'], 0, 10);
        } else if (isset($dados['eixo'])) {
            $eixo = substr($dados['eixo'], 0, 10);
        }

        return $eixo;
    }

    private function obterIdPneuValidoComLog($operacao)
    {
        $dados = $operacao['data'] ?? [];
        $tipo = $operacao['type'] ?? '';

        // Para troca de pneus, usar pneu1_id
        if ($tipo === 'troca_pneus') {
            if (isset($dados['pneu1_id']) && is_numeric($dados['pneu1_id'])) {
                return $dados['pneu1_id'];
            }
            if (isset($dados['pneu2_id']) && is_numeric($dados['pneu2_id'])) {
                return $dados['pneu2_id'];
            }
        }

        // Para outras operações
        $chaves = ['pneu_avulso_id', 'pneu_removido_id', 'pneu_id', 'id_pneu'];
        foreach ($chaves as $chave) {
            if (isset($dados[$chave]) && is_numeric($dados[$chave])) {
                return $dados[$chave];
            }
        }

        Log::warning('❌ Nenhum ID específico encontrado na operação');
        return null;
    }

    private function extrairIdPneuDaOperacaoMelhorado($operacao)
    {
        $dados = $operacao['data'] ?? [];
        $tipo = $operacao['type'] ?? '';

        // Mapeamento específico por tipo de operação
        switch ($tipo) {
            case 'troca_pneus':
                // Para troca, priorizar pneu1_id
                if (isset($dados['pneu1_id']) && is_numeric($dados['pneu1_id'])) {
                    return $dados['pneu1_id'];
                }
                if (isset($dados['pneu2_id']) && is_numeric($dados['pneu2_id'])) {
                    return $dados['pneu2_id'];
                }
                break;

            case 'aplicacao_pneu_avulso':
                if (isset($dados['pneu_avulso_id']) && is_numeric($dados['pneu_avulso_id'])) {
                    return $dados['pneu_avulso_id'];
                }
                break;

            case 'remocao_pneu':
                if (isset($dados['pneu_removido_id']) && is_numeric($dados['pneu_removido_id'])) {
                    return $dados['pneu_removido_id'];
                }
                break;
        }

        // Tentar chaves genéricas
        $chavesGenericas = [
            'pneu_id',
            'id_pneu',
            'pneuId',
            'pneu',
            'pneu1_id',
            'pneu2_id',
            'pneu_avulso_id',
            'pneu_removido_id'
        ];

        foreach ($chavesGenericas as $chave) {
            if (isset($dados[$chave]) && is_numeric($dados[$chave])) {
                return $dados[$chave];
            }
        }

        Log::warning('❌ Nenhum ID encontrado nos dados da operação');
        return null;
    }

    private function obterIdPneuValido($operacao)
    {
        // Primeiro, tentar extrair da operação
        $idPneu = $this->extrairIdPneuDaOperacao($operacao);

        if ($idPneu) {
            // Verificar se o pneu existe no banco
            $pneuExiste = Pneu::where('id_pneu', $idPneu)->exists();
            if ($pneuExiste) {
                return $idPneu;
            }
        }

        // Se não encontrou ou não existe, buscar o primeiro pneu disponível
        $pneuDisponivel = Pneu::first();

        return $pneuDisponivel ? $pneuDisponivel->id_pneu : null;
    }

    private function extrairEixoDaOperacao($operacao)
    {
        $dados = $operacao['data'] ?? [];

        if (isset($dados['localizacao'])) {
            return substr($dados['localizacao'], 0, 10); // Limitar tamanho
        }

        if (isset($dados['eixo'])) {
            return substr($dados['eixo'], 0, 10);
        }

        return null;
    }

    public function statusDetalhado(Request $request)
    {
        try {
            $stats = [
                'cache' => [
                    'driver' => config('cache.default'),
                    'working' => false
                ],
                'database' => [
                    'connection' => config('database.default'),
                    'working' => false
                ],
                'historico' => [
                    'total' => 0,
                    'auto_save' => 0,
                    'manual' => 0,
                    'ultimos_10' => []
                ]
            ];

            // Testar cache
            try {
                $testKey = 'test_' . time();
                Cache::put($testKey, 'test_value', 60);
                $testRead = Cache::get($testKey);
                Cache::forget($testKey);
                $stats['cache']['working'] = ($testRead === 'test_value');
            } catch (\Exception $e) {
                $stats['cache']['error'] = $e->getMessage();
            }

            // Testar banco
            try {
                DB::connection()->getPdo();
                $stats['database']['working'] = true;

                // Estatísticas do histórico
                $stats['historico']['total'] = HistoricoPneu::count();
                $stats['historico']['auto_save'] = HistoricoPneu::where('origem_operacao', 'MOV_PNEU')->count();
                $stats['historico']['manual'] = HistoricoPneu::where('origem_operacao', 'MANUAL')->count();

                $stats['historico']['ultimos_10'] = HistoricoPneu::orderBy('data_inclusao', 'desc')
                    ->limit(10)
                    ->get(['id_historico_pneu', 'data_inclusao', 'origem_operacao', 'status_movimentacao'])
                    ->toArray();
            } catch (\Exception $e) {
                $stats['database']['error'] = $e->getMessage();
            }


            return response()->json([
                'success' => true,
                'stats' => $stats,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('❌ ERRO NO STATUS DETALHADO', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function mapearTipoOperacaoParaStatus($tipoOperacao)
    {
        $mapa = [
            'troca_pneus' => 'RODIZIO',
            'rodizio_automatico' => 'RODIZIO',
            'aplicacao_pneu_avulso' => 'APLICADO',
            'remocao_pneu' => 'REMOVIDO',
            'teste_manual_debug' => 'TESTE_MOV_PNEU',
            'teste_correcao_banco' => 'TESTE_MOV_PNEU'
        ];

        return $mapa[$tipoOperacao] ?? 'MOV_PNEU_OPERACAO';
    }

    private function criarObservacaoAutoSave($operacao)
    {
        $tipo = $operacao['type'] ?? 'operacao_desconhecida';
        $dados = $operacao['data'] ?? [];

        $observacao = "Auto-save: {$tipo}";

        if (!empty($dados)) {
            $detalhes = [];
            foreach ($dados as $chave => $valor) {
                if (is_string($valor) || is_numeric($valor)) {
                    $detalhes[] = "{$chave}: " . substr($valor, 0, 50); // Limitar tamanho
                }
            }

            if (!empty($detalhes)) {
                $observacao .= " - " . implode(', ', array_slice($detalhes, 0, 2)); // Máx 2 detalhes
            }
        }

        return substr($observacao, 0, 255); // Limitar tamanho total
    }

    private function extrairIdPneuDaOperacao($operacao)
    {
        $dados = $operacao['data'] ?? [];
        $tipo = $operacao['type'] ?? '';

        // Mapeamento específico por tipo de operação
        switch ($tipo) {
            case 'troca_pneus':
                // Priorizar pneu1_id, depois pneu2_id
                if (isset($dados['pneu1_id']) && is_numeric($dados['pneu1_id'])) {
                    return $dados['pneu1_id'];
                }
                if (isset($dados['pneu2_id']) && is_numeric($dados['pneu2_id'])) {
                    return $dados['pneu2_id'];
                }
                break;

            case 'aplicacao_pneu_avulso':
                if (isset($dados['pneu_avulso_id']) && is_numeric($dados['pneu_avulso_id'])) {
                    return $dados['pneu_avulso_id'];
                }
                break;

            case 'remocao_pneu':
                // Para remoção, tentar extrair do selectedPneu1 ou contexto
                if (isset($dados['pneu_removido_id']) && is_numeric($dados['pneu_removido_id'])) {
                    return $dados['pneu_removido_id'];
                }
                break;

            case 'rodizio_automatico':
                // Para rodízio, não há pneu específico
                return null;
        }

        // Tentar outras chaves genéricas
        $chavesGenericas = [
            'pneu_id',
            'id_pneu',
            'pneuId',
            'pneu',
            'pneu1_id',
            'pneu2_id',
            'pneu_avulso_id'
        ];

        foreach ($chavesGenericas as $chave) {
            if (isset($dados[$chave]) && is_numeric($dados[$chave])) {
                return $dados[$chave];
            }
        }

        return null;
    }

    /**
     * ==========================================
     * SALVAMENTO MANUAL: Lógica existente
     * ==========================================
     */
    protected function handleManualSave(Request $request)
    {
        Log::info("🎯 handleManualSave INICIADO", [
            'request_data' => $request->all(),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type')
        ]);

        // Aqui vai toda a lógica existente do getSalvarData original
        $dadosVeiculo = $request->input('dadosVeiculo');
        $pneusAplicados = $request->input('pneusAplicados');
        $pneusRemovidos = $request->input('pneusRemovidos');
        $pneusAvulsos = $request->input('pneusAvulsos');

        Log::info("📋 Dados extraídos do request", [
            'dadosVeiculo' => $dadosVeiculo,
            'pneusAplicados_count' => count($pneusAplicados ?? []),
            'pneusRemovidos_count' => count($pneusRemovidos ?? []),
            'pneusAvulsos_count' => count($pneusAvulsos ?? []),
            'pneusRemovidos' => $pneusRemovidos,
            'pneusAvulsos' => $pneusAvulsos
        ]);

        // ✅ NOVA VALIDAÇÃO: Verificar se todos os pneus da requisição estão aplicados
        if (isset($dadosVeiculo['id_ordem_servico'])) {
            $validacaoRequisicao = $this->validarPneusRequisicaoAplicados($dadosVeiculo['id_ordem_servico']);
            if (!$validacaoRequisicao['valido']) {
                Log::warning("❌ Validação de requisição falhou", [
                    'ordem_servico' => $dadosVeiculo['id_ordem_servico'],
                    'pneus_nao_aplicados' => $validacaoRequisicao['pneus_nao_aplicados'] ?? []
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $validacaoRequisicao['mensagem']
                ], 400);
            }
        }

        if (empty($pneusRemovidos) && empty($pneusAvulsos)) {
            Log::info("🔄 Direcionando para processarRodizio");
            // Lógica para rodízio (só atualizar posições)
            return $this->processarRodizio($dadosVeiculo, $pneusAplicados);
        } else {
            Log::info("🔄 Direcionando para processarRemocaoAplicacao");
            // Lógica para remoção + aplicação
            return $this->processarRemocaoAplicacao($dadosVeiculo, $pneusAplicados, $pneusRemovidos, $pneusAvulsos);
        }
    }

    /**
     * ==========================================
     * PROCESSAR RODÍZIO
     * ==========================================
     */
    protected function processarRodizio($dadosVeiculo, $pneusAplicados)
    {
        // Iniciar transação
        DB::beginTransaction();

        try {
            // Busca veiculo
            $veiculo = Veiculo::where('id_veiculo', $dadosVeiculo['id_veiculo'])->first();
            $tipoEquipamento = TipoEquipamento::where('id_tipo_equipamento', $veiculo['id_tipo_equipamento'])->first();

            $numeroEixos = $tipoEquipamento->numero_eixos;
            $totalPneus = 0;

            for ($i = 1; $i <= $numeroEixos; $i++) {
                $campoPneus = 'numero_pneus_eixo_' . $i;
                $totalPneus += $tipoEquipamento->$campoPneus ?? 0;
            }

            $pneusSemEstepes = array_filter($pneusAplicados, function ($pneu) {
                return strpos($pneu['localizacao'], 'E') !== 0;
            });
            $quantidadePneus = count($pneusSemEstepes);

            if ($totalPneus != $quantidadePneus) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao salvar dados, caminhão manco. O caminhão precisa ser aplicado em todas as posições'
                ], 400);
            }

            Log::info("🔄 Processando rodízio para veículo {$dadosVeiculo['id_veiculo']}", [
                'total_pneus_aplicados' => count($pneusAplicados)
            ]);

            $veiculoXPneus = VeiculoXPneu::where('id_veiculo', $dadosVeiculo['id_veiculo'])
                ->where('situacao', true)
                ->pluck('id_veiculo_pneu')
                ->toArray();

            $pneusAplicadosBD = PneusAplicados::whereIn('id_veiculo_x_pneu', $veiculoXPneus)
                ->get()
                ->keyBy('id_pneu');

            $updates = [];

            foreach ($pneusAplicados as $pneuAplicado) {
                $idPneu = $pneuAplicado['id_pneu'];
                $localizacao = $pneuAplicado['localizacao'];

                if (isset($pneusAplicadosBD[$idPneu])) {
                    $updates[] = [
                        'id_pneu_aplicado' => $pneusAplicadosBD[$idPneu]->id_pneu_aplicado,
                        'localizacao' => $localizacao,
                    ];

                    $historicoMaisRecente = HistoricoPneu::where('id_pneu', $idPneu)
                        ->orderBy('data_inclusao', 'desc')
                        ->first();

                    if ($historicoMaisRecente) {
                        HistoricoPneu::where('id_historico_pneu', $historicoMaisRecente->id_historico_pneu)
                            ->update([
                                'eixo_aplicado' => $localizacao,
                                'status_movimentacao' => 'RODIZIO',
                                'data_alteracao' => now(),
                            ]);

                        Log::info("🔄 Rodízio atualizado para pneu {$idPneu}: {$localizacao}");
                    }
                }
            }

            foreach ($updates as $update) {
                PneusAplicados::where('id_pneu_aplicado', $update['id_pneu_aplicado'])
                    ->update(['localizacao' => $update['localizacao']]);
            }

            DB::commit();
            Log::info("✅ Rodízio finalizado com sucesso");

            // Limpar sessão após salvamento manual bem-sucedido
            $this->limparSessaoAutoSave($dadosVeiculo['id_veiculo']);

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Erro no rodízio, rollback executado: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erro ao processar rodízio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ==========================================
     * PROCESSAR REMOÇÃO + APLICAÇÃO
     * ==========================================
     */
    protected function processarRemocaoAplicacao($dadosVeiculo, $pneusAplicados, $pneusRemovidos, $pneusAvulsos)
    {
        $countPneusRemovidos = count($pneusRemovidos);
        $countpneusAvulsos = count($pneusAvulsos);

        if ($countPneusRemovidos != $countpneusAvulsos) {
            return response()->json([
                'success' => false,
                'error' => 'Erro ao salvar dados, caminhão manco. O caminhão precisa ser aplicado em todas as posições'
            ], 400);
        }

        // Iniciar transação
        DB::beginTransaction();

        try {
            $veiculo = Veiculo::where('id_veiculo', $dadosVeiculo['id_veiculo'])->first();
            $possuiTracao = $veiculo->is_possui_tracao;

            Log::info("🔧 Processando remoção/aplicação para veículo {$dadosVeiculo['id_veiculo']}", [
                'pneus_removidos' => count($pneusRemovidos),
                'pneus_avulsos' => count($pneusAvulsos)
            ]);

            // Processar remoções
            foreach ($pneusRemovidos as $pneuRemovido) {
                Log::info("🔴 Removendo pneu: {$pneuRemovido['id_pneu']}");
                $this->processarRemocaoPneu($pneuRemovido, $dadosVeiculo);
            }

            // Processar aplicações
            foreach ($pneusAvulsos as $index => $pneuAvulso) {
                $pneuRemovido = $pneusRemovidos[$index] ?? null;
                if (!$pneuRemovido) {
                    throw new \Exception("Localização do pneu removido não encontrada para o pneu avulso: " . $pneuAvulso['id_pneu']);
                }

                Log::info("🔵 Aplicando pneu: {$pneuAvulso['id_pneu']} na posição: {$pneuRemovido['localizacao']}");
                $this->processarAplicacaoPneu($pneuAvulso, $pneuRemovido, $dadosVeiculo, $possuiTracao);
            }

            DB::commit();
            Log::info("✅ Transação finalizada com sucesso");

            // Limpar sessão após salvamento manual bem-sucedido
            $this->limparSessaoAutoSave($dadosVeiculo['id_veiculo']);

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Erro na transação, rollback executado: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erro ao processar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ==========================================
     * PROCESSAR REMOÇÃO DE UM PNEU
     * ==========================================
     */
    protected function processarRemocaoPneu($pneuRemovido, $dadosVeiculo)
    {
        Log::info("🔴 INICIANDO processarRemocaoPneu para pneu: {$pneuRemovido['id_pneu']}", [
            'veiculo' => $dadosVeiculo['id_veiculo'],
            'status_destino' => $pneuRemovido['status'] ?? 'N/A',
            'km_removido' => $pneuRemovido['kmRemovido'] ?? 'N/A'
        ]);

        // Usar BORRACHARIA como destino (cabe no campo de 20 caracteres)
        if (isset($pneuRemovido['status']) && $pneuRemovido['status'] === 'BORRACHARIA') {
            $pneuRemovido['status'] = 'BORRACHARIA';
        }

        $attSttusPneu = Pneu::where('id_pneu', $pneuRemovido['id_pneu'])->first();
        Log::info("🔍 Pneu encontrado na tabela pneu", [
            'status_atual' => $attSttusPneu->status_pneu ?? 'N/A'
        ]);

        $historicoPneu = HistoricoPneu::where('id_pneu', $pneuRemovido['id_pneu'])
            ->orderBy('data_inclusao', 'desc')
            ->first();

        if (!$historicoPneu) {
            Log::warning("⚠️ HistoricoPneu não encontrado para pneu: {$pneuRemovido['id_pneu']} - criando histórico básico para remoção");

            // Criar um histórico básico com valores padrão para permitir a remoção
            $historicoPneu = (object) [
                'id_historico_pneu' => null,
                'km_final' => 0,
                'id_ordem_servico' => $dadosVeiculo['id_ordem_servico'] ?? null,
                'data_inclusao' => now(),
            ];
        }

        Log::info("🔍 Histórico encontrado/processado", [
            'id_historico' => $historicoPneu->id_historico_pneu ?? 'N/A (criado basic)',
            'status_atual' => $historicoPneu->status_movimentacao ?? 'N/A',
            'data_inclusao' => $historicoPneu->data_inclusao ?? 'N/A'
        ]);

        $veiculoXPneus = VeiculoXPneu::where('id_veiculo', $dadosVeiculo['id_veiculo'])
            ->where('situacao', true)
            ->pluck('id_veiculo_pneu')
            ->toArray();
        Log::info("🔍 VeiculoXPneu IDs encontrados", [
            'ids' => $veiculoXPneus
        ]);

        $pneuAvulsoApliacado = PneusAplicados::whereIn('id_veiculo_x_pneu', $veiculoXPneus)
            ->where('id_pneu', $pneuRemovido['id_pneu'])
            ->first();

        if (!$pneuAvulsoApliacado) {
            Log::error("❌ Pneu aplicado não encontrado na tabela pneus_aplicados", [
                'id_pneu' => $pneuRemovido['id_pneu'],
                'veiculo_x_pneu_ids' => $veiculoXPneus
            ]);
            throw new \Exception("Pneu removido não encontrado: " . $pneuRemovido['id_pneu']);
        }

        Log::info("🔍 PneusAplicados encontrado", [
            'id_pneu_aplicado' => $pneuAvulsoApliacado->id_pneu_aplicado,
            'localizacao_atual' => $pneuAvulsoApliacado->localizacao
        ]);

        // Atualiza o pneu removido
        Log::info("📝 Atualizando PneusAplicados...");
        $pneuAvulsoApliacado->update([
            'data_alteracao' => now(),
            'km_removido' => $pneuRemovido['kmRemovido'],
            // ✅ NÃO alterar localizacao - manter para histórico
            'sulco_pneu_removido' => $pneuRemovido['sulcoRemovido'],
            // ✅ SOFT DELETE - Marcar como removido
            'deleted_at' => now(),
            'is_ativo' => false  // ✅ Marcar como inativo
        ]);
        Log::info("✅ PneusAplicados atualizado com soft delete (localização preservada)");

        // INSERIR novo registro no HistoricoPneu (NUNCA atualizar!)
        Log::info("📝 Inserindo novo registro no HistoricoPneu...");

        // Criar novo registro de movimentação
        $novoHistorico = [
            'id_pneu' => $pneuRemovido['id_pneu'],
            'id_veiculo' => $dadosVeiculo['id_veiculo'],
            'data_inclusao' => now(),
            'data_retirada' => now(),
            'status_movimentacao' => 'MOV_PNEU_REMOVIDO',
            'km_inicial' => $historicoPneu ? $historicoPneu->km_final : 0,
            'km_final' => $pneuRemovido['kmRemovido'],
            'origem_operacao' => 'AUTO_SAVE',
            'observacoes_operacao' => "Remoção automática via auto-save para {$pneuRemovido['status']}",
            'id_ordem_servico' => $historicoPneu ? $historicoPneu->id_ordem_servico : null,
            'localizacao' => $pneuAvulsoApliacado->localizacao,
            'id_usuario' => Auth::user()->id ?? null,
        ];

        Log::info("🔍 Dados para inserir no histórico:", $novoHistorico);

        $historicoInserido = HistoricoPneu::create($novoHistorico);

        Log::info("✅ Novo registro HistoricoPneu inserido", [
            'id_historico_pneu' => $historicoInserido->id_historico_pneu,
            'status_movimentacao' => $historicoInserido->status_movimentacao
        ]);

        // Atualiza status do Pneu
        Log::info("📝 Atualizando status do Pneu...");
        $attSttusPneu->update([
            'data_alteracao' => now(),
            'status_pneu' => $pneuRemovido['status'],
        ]);
        Log::info("✅ Status do pneu atualizado para: {$pneuRemovido['status']}");

        // ====== INSERIR PNEU NO DEPÓSITO COM DESTINAÇÃO SOLICITADA ======
        try {
            $destinacaoSolicitada = $pneuRemovido['destinacao_solicitada'] ?? null;

            Log::info("🔍 DEBUG - Preparando inserção no PneusDeposito:", [
                'id_pneu' => $pneuRemovido['id_pneu'],
                'destinacao_solicitada_raw' => $pneuRemovido['destinacao_solicitada'] ?? 'NÃO DEFINIDA',
                'destinacao_solicitada_processed' => $destinacaoSolicitada,
                'status_pneu' => $pneuRemovido['status']
            ]);

            // Definir descrição de destino baseada no status
            $descricaoDestino = match ($pneuRemovido['status']) {
                'BORRACHARIA' => 'BORRACHARIA',
                'DEPOSITO' => 'DEPÓSITO',
                default => 'DEPÓSITO'
            };

            $dadosInsercao = [
                'data_inclusao' => now(),
                'data_alteracao' => now(),
                'id_pneu' => $pneuRemovido['id_pneu'],
                'datahora_processamento' => null,
                'descricao_destino' => $descricaoDestino,
                'destinacao_solicitada' => $destinacaoSolicitada
            ];

            Log::info("🔍 DEBUG - Dados que serão inseridos:", $dadosInsercao);

            $pneuDeposito = PneusDeposito::create($dadosInsercao);

            Log::info("✅ Pneu inserido no depósito com sucesso", [
                'id_deposito_pneu' => $pneuDeposito->id_deposito_pneu,
                'id_pneu' => $pneuRemovido['id_pneu'],
                'descricao_destino' => $descricaoDestino,
                'destinacao_solicitada' => $destinacaoSolicitada
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Erro ao inserir pneu no depósito: " . $e->getMessage(), [
                'id_pneu' => $pneuRemovido['id_pneu'],
                'status' => $pneuRemovido['status'],
                'destinacao_solicitada' => $pneuRemovido['destinacao_solicitada'] ?? null
            ]);
        }

        // ====== ATUALIZAR CAMPO 'sulco' NA TABELA 'pneu' COM O VALOR INFORMADO NA REMOÇÃO ======
        try {
            $sulcoInformado = $pneuRemovido['sulcoRemovido'] ?? null;

            // Normalizar valor: aceitar string numérica ou número; converter para float
            if (!is_null($sulcoInformado) && $sulcoInformado !== '') {
                // Remover espaços e substituições de vírgula por ponto
                $sulcoNormalized = str_replace(',', '.', trim((string) $sulcoInformado));

                if (is_numeric($sulcoNormalized)) {
                    $sulcoFinal = (float) $sulcoNormalized;
                } else {
                    // Se não for numérico, manter null e logar
                    $sulcoFinal = null;
                    Log::warning("⚠️ Valor de sulco informado não é numérico e será ignorado", [
                        'id_pneu' => $pneuRemovido['id_pneu'],
                        'sulco_informado' => $sulcoInformado
                    ]);
                }
            } else {
                $sulcoFinal = null;
            }

            if (!is_null($sulcoFinal)) {
                // Atualizar tabela pneu com novo valor de sulco
                $updateResult = Pneu::where('id_pneu', $pneuRemovido['id_pneu'])
                    ->update([
                        'sulco' => $sulcoFinal,
                        'data_alteracao' => now()
                    ]);

                if ($updateResult) {
                    Log::info("✅ Sulco do pneu atualizado com sucesso", [
                        'id_pneu' => $pneuRemovido['id_pneu'],
                        'sulco' => $sulcoFinal
                    ]);
                } else {
                    Log::warning("⚠️ Não foi possível atualizar o sulco do pneu (nenhuma linha afetada)", [
                        'id_pneu' => $pneuRemovido['id_pneu'],
                        'sulco' => $sulcoFinal
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Não interromper o fluxo de remoção por causa de falha ao atualizar o sulco
            Log::error("❌ Erro ao atualizar sulco do pneu: " . $e->getMessage(), [
                'id_pneu' => $pneuRemovido['id_pneu'],
                'sulco_informado' => $pneuRemovido['sulcoRemovido'] ?? null
            ]);
        }

        Log::info("🎉 processarRemocaoPneu CONCLUÍDO com sucesso para pneu: {$pneuRemovido['id_pneu']}");
    }

    /**
     * ==========================================
     * PROCESSAR APLICAÇÃO DE UM PNEU
     * ==========================================
     */
    protected function processarAplicacaoPneu($pneuAvulso, $pneuRemovido, $dadosVeiculo, $possuiTracao)
    {
        Log::info("🟢 INICIANDO processarAplicacaoPneu para pneu: {$pneuAvulso['id_pneu']}", [
            'veiculo' => $dadosVeiculo['id_veiculo'],
            'localizacao' => $pneuRemovido['localizacao'],
            'km_aplicado' => $pneuRemovido['kmRemovido']
        ]);

        $localizacaoVerificaPneuEixo = $pneuRemovido['localizacao'];

        // Verificar se é primeiro eixo e se veículo possui tração
        // ✅ NOVA REGRA: Bloquear apenas se for primeiro eixo E o veículo possui tração
        if (substr($localizacaoVerificaPneuEixo, 0, 1) === '1' && $possuiTracao) {
            $pneuManutencaoItens = ManutencaoPneusEntradaItens::where('id_pneu', $pneuAvulso['id_pneu'])
                ->whereIn('id_tipo_reforma', [1, 2])
                ->get();

            if ($pneuManutencaoItens->count() > 0) {
                Log::warning("🚫 APLICAÇÃO BLOQUEADA: Pneu recapado/vulcanizado no primeiro eixo de veículo tracionado", [
                    'id_pneu' => $pneuAvulso['id_pneu'],
                    'localizacao' => $localizacaoVerificaPneuEixo,
                    'veiculo_possui_tracao' => $possuiTracao
                ]);
                throw new \Exception('O caminhão não pode conter pneus vulcanizado ou recapado no primeiro eixo quando possui tração');
            }
        } elseif (substr($localizacaoVerificaPneuEixo, 0, 1) === '1' && !$possuiTracao) {
            // Log informativo para veículos sem tração
            $pneuManutencaoItens = ManutencaoPneusEntradaItens::where('id_pneu', $pneuAvulso['id_pneu'])
                ->whereIn('id_tipo_reforma', [1, 2])
                ->get();

            if ($pneuManutencaoItens->count() > 0) {
                Log::info("✅ APLICAÇÃO PERMITIDA: Pneu recapado/vulcanizado no primeiro eixo de veículo sem tração", [
                    'id_pneu' => $pneuAvulso['id_pneu'],
                    'localizacao' => $localizacaoVerificaPneuEixo,
                    'veiculo_possui_tracao' => $possuiTracao
                ]);
            }
        }

        $pneu = Pneu::where('id_pneu', $pneuAvulso['id_pneu'])->first();

        if (!$pneu) {
            Log::error("❌ Pneu não encontrado", ['id_pneu' => $pneuAvulso['id_pneu']]);
            throw new \Exception("Pneu não encontrado: {$pneuAvulso['id_pneu']}");
        }

        Log::info("🔍 Pneu encontrado", [
            'id_pneu' => $pneu->id_pneu,
            'status_atual' => $pneu->status_pneu
        ]);

        $historicoPneu = HistoricoPneu::where('id_pneu', $pneuAvulso['id_pneu'])
            ->orderBy('data_inclusao', 'desc')
            ->first();

        if ($historicoPneu) {
            Log::info("🔍 Histórico anterior encontrado", [
                'id_historico' => $historicoPneu->id_historico_pneu,
                'status_anterior' => $historicoPneu->status_movimentacao
            ]);
        }

        $novoPneuApliacado = PneusAplicados::where('id_pneu', $pneuAvulso['id_pneu'])
            ->orderBy('data_inclusao', 'desc')
            ->first();

        $veiculoXPneu = $this->obterOuCriarVeiculoXPneu(
            $dadosVeiculo['id_veiculo'],
            $dadosVeiculo['eixos'] ?? 2
        );

        Log::info("🔍 VeiculoXPneu encontrado", [
            'id_veiculo_pneu' => $veiculoXPneu->id_veiculo_pneu,
            'situacao' => $veiculoXPneu->situacao
        ]);

        // Criar novo registro de PneusAplicados
        Log::info("📝 Criando registro PneusAplicados...");
        $pneuAplicado = PneusAplicados::create([
            'data_inclusao' => now(),
            'id_pneu' => $pneuAvulso['id_pneu'],
            'km_adicionado' => $pneuRemovido['kmRemovido'], // ✅ KM informado pelo usuário (campo correto)
            'km_removido' => null,
            'total_km' => null,
            'id_veiculo_x_pneu' => $veiculoXPneu->id_veiculo_pneu,
            'localizacao' => $pneuRemovido['localizacao'],
            'sulco_pneu_adicionado' => $pneuRemovido['sulcoAplicado'], // ✅ Sulco informado pelo usuário
            'sulco_pneu_removido' => null,
            'is_ativo' => true, // ✅ Sempre true quando aplicado
        ]);

        Log::info("✅ PneusAplicados criado", [
            'id_pneu_aplicado' => $pneuAplicado->id_pneu_aplicado,
            'localizacao' => $pneuRemovido['localizacao'],
            'km_adicionado' => $pneuRemovido['kmRemovido'],
            'sulco_adicionado' => $pneuRemovido['sulcoAplicado'],
            'is_ativo' => true
        ]);
        Log::info("📝 Atualizando status do pneu...");
        $pneu->update([
            'data_alteracao' => now(),
            'status_pneu' => $pneuAvulso['status'],
        ]);
        Log::info("✅ Status do pneu atualizado", [
            'novo_status' => $pneuAvulso['status']
        ]);

        Log::info("📝 Criando registro no HistoricoPneu...");
        $novoHistorico = HistoricoPneu::create([
            'data_inclusao' => now(),
            'id_veiculo' => $dadosVeiculo['id_veiculo'],
            'km_inicial' => $pneuRemovido['kmRemovido'],
            'id_pneu' => $pneuAvulso['id_pneu'],
            'eixo_aplicado' => $pneuRemovido['localizacao'],
            'id_modelo' => $historicoPneu->id_modelo ?? null,
            'id_vida_pneu' => $historicoPneu->id_vida_pneu ?? null,
            'status_movimentacao' => 'MOV_PNEU_APLICADO',
            'origem_operacao' => 'AUTO_SAVE', // ✅ Marcar origem
            'observacoes_operacao' => "Aplicação automática na posição {$pneuRemovido['localizacao']}",
            'id_usuario' => Auth::user()->id ?? null
        ]);

        Log::info("✅ HistoricoPneu criado", [
            'id_historico' => $novoHistorico->id_historico_pneu,
            'status_movimentacao' => $novoHistorico->status_movimentacao
        ]);
        Log::info("🎉 processarAplicacaoPneu CONCLUÍDO com sucesso para pneu: {$pneuAvulso['id_pneu']}");
    }

    /**
     * ==========================================
     * VERIFICAR STATUS DA SESSÃO AUTO-SAVE
     * ==========================================
     */
    public function autoSaveStatus(Request $request)
    {
        try {
            $idVeiculo = $request->input('id_veiculo');

            if (!$idVeiculo) {
                return response()->json([
                    'success' => false,
                    'error' => 'ID do veículo é obrigatório'
                ], 400);
            }

            $userId = Auth::id();
            $sessionKey = "movimentacao_pneus_{$idVeiculo}_{$userId}";
            $sessionData = Cache::get($sessionKey);

            if ($sessionData) {
                return response()->json([
                    'success' => true,
                    'has_session' => true,
                    'session_key' => $sessionKey,
                    'last_update' => $sessionData['last_update'],
                    'operacoes_count' => count($sessionData['operacoes'] ?? [])
                ]);
            }

            return response()->json([
                'success' => true,
                'has_session' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status da sessão: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erro ao verificar sessão'
            ], 500);
        }
    }

    /**
     * ==========================================
     * RESTAURAR SESSÃO AUTO-SAVE
     * ==========================================
     */
    public function restoreSession(Request $request)
    {
        try {
            $idVeiculo = $request->input('id_veiculo');

            if (!$idVeiculo) {
                return response()->json([
                    'success' => false,
                    'error' => 'ID do veículo é obrigatório'
                ], 400);
            }

            $userId = Auth::id();
            $sessionKey = "movimentacao_pneus_{$idVeiculo}_{$userId}";
            $sessionData = Cache::get($sessionKey);

            if (!$sessionData) {
                return response()->json([
                    'success' => true,
                    'has_session' => false
                ]);
            }

            // Buscar dados atualizados do veículo
            $veiculo = Veiculo::select([
                'id_veiculo',
                'id_tipo_equipamento',
                'id_categoria',
                'id_modelo_veiculo',
                'chassi'
            ])->where('id_veiculo', $idVeiculo)->firstOrFail();

            $pneuVeiculoIds = VeiculoXPneu::select('id_veiculo_pneu')
                ->where('id_veiculo', $veiculo->id_veiculo)
                ->where('situacao', true)
                ->first();

            $pneusAplicados = PneusAplicados::where('id_veiculo_x_pneu', $pneuVeiculoIds->id_veiculo_pneu)->get();

            $pneusAplicadosFormatados = $pneusAplicados->map(function ($pneu) {
                return [
                    'id_pneu' => $pneu->id_pneu,
                    'localizacao' => $pneu->localizacao,
                    'suco_pneu' => $pneu->sulco_pneu_adicionado,
                ];
            })->toArray();

            $kmAtual = DB::connection('pgsql')->table('veiculo as v')
                ->select(DB::raw('fc_km_relatorio(v.id_veiculo) AS km_atual'))
                ->where('v.id_veiculo', $veiculo->id_veiculo)
                ->value('km_atual');

            $tipoEquipamentoPneus = TipoEquipamento::select('numero_eixos', 'numero_pneus_eixo_1', 'numero_pneus_eixo_2', 'numero_pneus_eixo_3', 'numero_pneus_eixo_4')
                ->where('id_tipo_equipamento', '=', $veiculo->id_tipo_equipamento)
                ->first();

            $dadosVeiculo = [
                'id_veiculo' => $veiculo->id_veiculo,
                'id_tipo_equipamento' => $veiculo->tipoEquipamento->descricao_tipo ?? 'Não informado',
                'id_categoria' => $veiculo->categoriaVeiculo->descricao_categoria ?? 'Não informado',
                'id_modelo_veiculo' => $veiculo->modeloVeiculo->descricao_modelo_veiculo ?? 'Não informado',
                'chassi' => $veiculo->chassi ?? 'Não informado',
                'km_atual' => $kmAtual ?? 'Não informado',
                'eixos' => $tipoEquipamentoPneus->numero_eixos,
                'pneus_por_eixo' => [
                    $tipoEquipamentoPneus->numero_pneus_eixo_1,
                    $tipoEquipamentoPneus->numero_pneus_eixo_2,
                    $tipoEquipamentoPneus->numero_pneus_eixo_3,
                    $tipoEquipamentoPneus->numero_pneus_eixo_4
                ],
                'pneusAplicadosFormatados' => $pneusAplicadosFormatados,
            ];

            return response()->json([
                'success' => true,
                'has_session' => true,
                'dados_veiculo' => $dadosVeiculo,
                'session_data' => $sessionData
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao restaurar sessão: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erro ao restaurar sessão'
            ], 500);
        }
    }

    /**
     * ==========================================
     * LIMPAR SESSÃO AUTO-SAVE
     * ==========================================
     */
    protected function limparSessaoAutoSave($idVeiculo)
    {
        $userId = Auth::id();
        $sessionKey = "movimentacao_pneus_{$idVeiculo}_{$userId}";
        Cache::forget($sessionKey);
    }

    /* ==========================================
     * API SEARCH PNEUS (Adicionar ao MovimentacaoPneusController)
     * ==========================================
     */
    public function searchPneus(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $limit = $request->get('limit', 20);
            $idOrdemServico = $request->get('id_ordem_servico', null);

            if (empty($search)) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Se há ordem de serviço, buscar apenas pneus da requisição
            if ($idOrdemServico) {
                $cacheKey = 'pneu_search_os_' . md5($search . $limit . $idOrdemServico);

                $pneus = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($search, $limit, $idOrdemServico) {
                    return DB::table('requisicao_pneu as rp')
                        ->join('requisicao_pneu_modelos as rpm', 'rpm.id_requisicao_pneu', '=', 'rp.id_requisicao_pneu')
                        ->join('requisicao_pneu_itens as rpi', 'rpi.id_requisicao_pneu_modelos', '=', 'rpm.id_requisicao_pneu_modelos')
                        ->join('pneu as p', 'p.id_pneu', '=', 'rpi.id_pneu')
                        ->select('p.id_pneu as value', 'p.id_pneu as label', 'p.status_pneu')
                        ->where('rp.id_ordem_servico', $idOrdemServico)
                        ->where('p.id_pneu', 'LIKE', "%{$search}%")
                        ->where('p.status_pneu', 'ESTOQUE')
                        ->whereNull('p.deleted_at')
                        ->orderBy('p.id_pneu')
                        ->limit($limit)
                        ->get();
                });
            } else {
                // Cache da busca por 30 minutos
                $cacheKey = 'pneu_search_' . md5($search . $limit);

                $pneus = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($search, $limit) {
                    return Pneu::select('id_pneu as value', 'id_pneu as label', 'status_pneu')
                        ->where('id_pneu', 'LIKE', "%{$search}%")
                        ->where('status_pneu', 'ESTOQUE') // Apenas pneus em estoque
                        ->whereNull('deleted_at')
                        ->orderBy('id_pneu')
                        ->limit($limit)
                        ->get();
                });
            }

            // Adicionar informações do tipo de pneu
            $pneusComInfo = $pneus->map(function ($pneu) {
                $pneuModel = Pneu::with('ultimaManutencaoEntrada.tipoReforma')->find($pneu->value);
                $tipoInfo = $pneuModel ? $pneuModel->getTipoPneuInfo() : null;

                return [
                    'value' => $pneu->value,
                    'label' => $pneu->label,
                    'status' => $pneu->status_pneu,
                    'tipo_info' => $tipoInfo
                ];
            });

            // Salvar chave de cache para limpeza posterior
            $searchKeys = Cache::get('pneu_search_keys', []);
            $searchKeys[] = $cacheKey;
            Cache::put('pneu_search_keys', array_unique($searchKeys), now()->addDay());

            return response()->json([
                'success' => true,
                'data' => $pneusComInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Erro na busca de pneus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erro na busca de pneus'
            ], 500);
        }
    }

    /**
     * ==========================================
     * API SEARCH ORDEM DE SERVIÇO
     * ==========================================
     */
    public function searchOrdemServico(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $limit = $request->get('limit', 20);

            if (empty($search)) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Cache da busca por 30 minutos
            $cacheKey = 'ordemservico_search_' . md5($search . $limit);

            $ordensServico = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($search, $limit) {
                return OrdemServico::join('veiculo', 'veiculo.id_veiculo', '=', 'ordem_servico.id_veiculo')
                    ->select(
                        'ordem_servico.id_ordem_servico as value',
                        DB::raw("CONCAT('OS: ', ordem_servico.id_ordem_servico, ' - ', veiculo.placa) as label"),
                        'ordem_servico.id_veiculo'
                    )
                    ->where('ordem_servico.id_tipo_ordem_servico', 3)
                    ->where('ordem_servico.id_status_ordem_servico', 2)
                    ->where('veiculo.situacao_veiculo', true)
                    ->where('veiculo.is_terceiro', false)
                    ->where(function ($q) use ($search) {
                        $q->where('ordem_servico.id_ordem_servico', 'LIKE', "%{$search}%")
                            ->orWhere('veiculo.placa', 'LIKE', "%{$search}%");
                    })
                    ->orderBy('ordem_servico.id_ordem_servico', 'desc')
                    ->limit($limit)
                    ->get();
            });

            // Salvar chave de cache para limpeza posterior
            $searchKeys = Cache::get('ordemservico_search_keys', []);
            $searchKeys[] = $cacheKey;
            Cache::put('ordemservico_search_keys', array_unique($searchKeys), now()->addDay());

            return response()->json([
                'success' => true,
                'data' => $ordensServico
            ]);
        } catch (\Exception $e) {
            Log::error('Erro na busca de ordens de serviço: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erro na busca de ordens de serviço'
            ], 500);
        }
    }

    /**
     * ==========================================
     * API SEARCH PNEUS POR ORDEM DE SERVIÇO
     * ==========================================
     */
    public function searchPneusPorOrdemServico(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $limit = $request->get('limit', 20);
            $idOrdemServico = $request->get('id_ordem_servico');

            // Log para debug
            Log::info("🔍 API searchPneusPorOrdemServico chamada - OS: {$idOrdemServico}, search: '{$search}'");

            if (empty($idOrdemServico)) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Usar nossa nova função que combina pneus da requisição + todos do depósito
            $pneus = $this->getPneusDaRequisicao($idOrdemServico);

            // Se houver filtro de busca, aplicar
            if (!empty($search)) {
                $pneus = array_filter($pneus, function ($pneu) use ($search) {
                    return stripos($pneu['label'], $search) !== false ||
                        stripos($pneu['value'], $search) !== false;
                });
                $pneus = array_values($pneus); // Reindexar
            }

            // Aplicar limite se necessário
            if ($limit && count($pneus) > $limit) {
                $pneus = array_slice($pneus, 0, $limit);
            }

            Log::info("🔍 API retornando " . count($pneus) . " pneus");

            return response()->json([
                'success' => true,
                'data' => $pneus
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro na busca de pneus por OS: ' . $e->getMessage());
            Log::error('❌ Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'error' => 'Erro na busca de pneus'
            ], 500);
        }
    }

    /**
     * ==========================================
     * MÉTODO HELPER: Verificar integridade dos dados
     * ==========================================
     */
    protected function validarIntegridadeDados($request)
    {
        $errors = [];

        // Validar estrutura básica
        if (!$request->has('dadosVeiculo.id_veiculo')) {
            $errors[] = 'ID do veículo é obrigatório';
        }

        // Validar arrays
        $pneusAplicados = $request->input('pneusAplicados', []);
        $pneusRemovidos = $request->input('pneusRemovidos', []);
        $pneusAvulsos = $request->input('pneusAvulsos', []);

        if (!is_array($pneusAplicados)) {
            $errors[] = 'pneusAplicados deve ser um array';
        }

        if (!is_array($pneusRemovidos)) {
            $errors[] = 'pneusRemovidos deve ser um array';
        }

        if (!is_array($pneusAvulsos)) {
            $errors[] = 'pneusAvulsos deve ser um array';
        }

        // Validar correspondência entre removidos e avulsos
        if (!empty($pneusRemovidos) && !empty($pneusAvulsos)) {
            if (count($pneusRemovidos) !== count($pneusAvulsos)) {
                $errors[] = 'Número de pneus removidos deve ser igual ao número de pneus avulsos';
            }
        }

        // Validar timestamp se for auto-save
        if ($request->input('auto_save') && $request->has('timestamp')) {
            $timestamp = $request->input('timestamp');
            $now = time() * 1000; // Converter para milliseconds
            $diff = abs($now - $timestamp) / 1000; // Diferença em segundos

            if ($diff > 300) { // Mais de 5 minutos
                $errors[] = 'Dados muito antigos, recarregue a página';
            }
        }

        return $errors;
    }

    /**
     * ==========================================
     * MÉTODO HELPER: Log detalhado de operações
     * ==========================================
     */
    protected function logOperacao($tipo, $dados, $sucesso = true, $erro = null)
    {
        $logData = [
            'user_id' => Auth::id(),
            'tipo_operacao' => $tipo,
            'veiculo_id' => $dados['dadosVeiculo']['id_veiculo'] ?? null,
            'sucesso' => $sucesso,
            'timestamp' => now()->toISOString()
        ];

        if ($erro) {
            $logData['erro'] = $erro;
        }

        // Adicionar contadores
        if (isset($dados['pneusAplicados'])) {
            $logData['total_pneus_aplicados'] = count($dados['pneusAplicados']);
        }

        if (isset($dados['pneusRemovidos'])) {
            $logData['total_pneus_removidos'] = count($dados['pneusRemovidos']);
        }

        if (isset($dados['pneusAvulsos'])) {
            $logData['total_pneus_avulsos'] = count($dados['pneusAvulsos']);
        }

        if ($sucesso) {
            Log::info("Operação {$tipo} concluída", $logData);
        } else {
            Log::error("Falha na operação {$tipo}", $logData);
        }
    }

    /**
     * ==========================================
     * MÉTODO HELPER: Estatísticas do auto-save
     * ==========================================
     */
    public function getAutoSaveStats(Request $request)
    {
        try {
            $userId = Auth::id();
            $stats = [
                'sessoes_ativas' => 0,
                'sessoes_antigas' => 0,
                'total_operacoes' => 0,
                'ultima_atividade' => null
            ];

            // Buscar todas as sessões do usuário
            $pattern = "movimentacao_pneus_*_{$userId}";

            // Para desenvolvimento/teste (implementação simplificada)
            $cacheKeys = Cache::get('user_sessions_' . $userId, []);

            $cutoffTime = now()->subHours(2);

            foreach ($cacheKeys as $key) {
                $sessionData = Cache::get($key);

                if ($sessionData && isset($sessionData['last_update'])) {
                    $lastUpdate = \Carbon\Carbon::parse($sessionData['last_update']);

                    if ($lastUpdate->gt($cutoffTime)) {
                        $stats['sessoes_ativas']++;
                    } else {
                        $stats['sessoes_antigas']++;
                    }

                    $stats['total_operacoes'] += count($sessionData['operacoes'] ?? []);

                    if (!$stats['ultima_atividade'] || $lastUpdate->gt($stats['ultima_atividade'])) {
                        $stats['ultima_atividade'] = $lastUpdate->toISOString();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar estatísticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erro ao buscar estatísticas'
            ], 500);
        }
    }

    public function testeAutoSaveComBanco(Request $request)
    {
        try {
            // Simular operação de auto-save
            $request->merge([
                'dadosVeiculo' => ['id_veiculo' => $request->input('id_veiculo', 1)],
                'operacao' => [
                    'type' => 'teste_correcao_auto_save',
                    'data' => [
                        'timestamp' => now()->toISOString(),
                        'teste' => true
                    ]
                ],
                'timestamp' => time() * 1000,
                'auto_save' => true,
                'pneusAplicados' => [],
                'pneusRemovidos' => [],
                'pneusAvulsos' => []
            ]);

            $resultado = $this->handleAutoSave($request);

            // Verificar se foi salvo no banco
            $ultimoRegistro = HistoricoPneu::where('origem_operacao', 'AUTO_SAVE')
                ->orderBy('data_inclusao', 'desc')
                ->first();

            return response()->json([
                'auto_save_resultado' => $resultado,
                'ultimo_registro_banco' => $ultimoRegistro ? [
                    'id' => $ultimoRegistro->id_historico_pneu,
                    'data_inclusao' => $ultimoRegistro->data_inclusao,
                    'origem_operacao' => $ultimoRegistro->origem_operacao,
                    'status_movimentacao' => $ultimoRegistro->status_movimentacao,
                    'observacoes_operacao' => $ultimoRegistro->observacoes_operacao
                ] : null,
                'teste_bem_sucedido' => $resultado['success'] && $ultimoRegistro !== null
            ]);
        } catch (\Exception $e) {
            Log::error('❌ ERRO NO TESTE AUTO-SAVE COM BANCO', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function determinarStatusMovimentacao($operacao)
    {
        switch ($operacao['tipo']) {
            case 'aplicacao':
                return 'APLICADO';
            case 'remocao':
                return $operacao['dados']['destino'] ?? 'ESTOQUE';
            case 'troca':
                return 'RODIZIO';
            default:
                return 'MOVIMENTACAO';
        }
    }

    private function gerarObservacaoOperacao($operacao)
    {
        $observacoes = [];

        if (isset($operacao['dados']['origem_operacao'])) {
            $observacoes[] = "Origem: {$operacao['dados']['origem_operacao']}";
        }

        if (isset($operacao['dados']['destino'])) {
            $observacoes[] = "Destino: {$operacao['dados']['destino']}";
        }

        if (isset($operacao['dados']['km_adicionado'])) {
            $observacoes[] = "KM: {$operacao['dados']['km_adicionado']}";
        }

        return implode(' | ', $observacoes);
    }

    /**
     * MÉTODO DE DEBUG - Adicione temporariamente ao MovimentacaoPneusController
     */
    public function debugRegrasNegocio(Request $request)
    {
        try {

            $dados = $request->all();

            // Verificar se o serviço está funcionando
            $pneuAplicadoService = new \App\Services\PneuAplicadoService();

            // Verificar dados básicos
            if (!isset($dados['dadosVeiculo']['id_veiculo'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'DEBUG: ID do veículo não fornecido',
                    'dados_recebidos' => $dados
                ]);
            }

            $idVeiculo = $dados['dadosVeiculo']['id_veiculo'];

            // Verificar se existe VeiculoXPneu ativo
            $veiculoXPneu = VeiculoXPneu::where('id_veiculo', $idVeiculo)
                ->where('situacao', true)
                ->first();

            if (!$veiculoXPneu) {
                return response()->json([
                    'success' => false,
                    'error' => 'DEBUG: Nenhum registro ativo encontrado em veiculo_x_pneu',
                    'id_veiculo' => $idVeiculo,
                    'registros_veiculo_x_pneu' => VeiculoXPneu::where('id_veiculo', $idVeiculo)->get()
                ]);
            }


            // Verificar pneus aplicados atuais
            $pneusAplicadosAtuais = PneusAplicados::where('id_veiculo_x_pneu', $veiculoXPneu->id_veiculo_pneu)
                ->get();


            // Verificar operações enviadas
            $operacoes = [];

            if (!empty($dados['pneusRemovidos'])) {
                foreach ($dados['pneusRemovidos'] as $pneu) {
                    $operacoes[] = [
                        'tipo' => 'remocao',
                        'pneu_removido_id' => $pneu['id_pneu'],
                        'localizacao' => $pneu['localizacao'],
                        'dados' => [
                            'origem_operacao' => 'DEBUG_TEST',
                            'destino' => $pneu['status'] ?? 'ESTOQUE',
                            'km_removido' => $dados['dadosVeiculo']['km_atual'] ?? null,
                        ]
                    ];
                }
            }

            if (!empty($dados['pneusAplicados'])) {
                foreach ($dados['pneusAplicados'] as $pneu) {
                    $operacoes[] = [
                        'tipo' => 'aplicacao',
                        'pneu_adicionado_id' => $pneu['id_pneu'],
                        'localizacao' => $pneu['localizacao'],
                        'dados' => [
                            'origem_operacao' => 'DEBUG_TEST',
                            'km_adicionado' => $dados['dadosVeiculo']['km_atual'] ?? null,
                        ]
                    ];
                }
            }


            if (empty($operacoes)) {
                return response()->json([
                    'success' => false,
                    'error' => 'DEBUG: Nenhuma operação detectada',
                    'dados_enviados' => $dados,
                    'pneus_aplicados_atuais' => $pneusAplicadosAtuais->toArray()
                ]);
            }

            // Executar uma operação de teste
            $primeiraOperacao = $operacoes[0];

            $resultado = $pneuAplicadoService->processarTrocaPneus(
                $idVeiculo,
                $primeiraOperacao['pneu_removido_id'] ?? null,
                $primeiraOperacao['pneu_adicionado_id'] ?? null,
                $primeiraOperacao['localizacao'],
                $primeiraOperacao['dados']
            );


            // Verificar estado após operação
            $pneusAplicadosDepois = PneusAplicados::where('id_veiculo_x_pneu', $veiculoXPneu->id_veiculo_pneu)
                ->withTrashed()
                ->get();

            return response()->json([
                'success' => true,
                'debug_info' => [
                    'veiculo_id' => $idVeiculo,
                    'veiculo_x_pneu_id' => $veiculoXPneu->id_veiculo_pneu,
                    'operacoes_detectadas' => count($operacoes),
                    'primeira_operacao' => $primeiraOperacao,
                    'resultado_operacao' => $resultado,
                    'pneus_antes' => $pneusAplicadosAtuais->count(),
                    'pneus_depois' => $pneusAplicadosDepois->count(),
                    'pneus_aplicados_antes' => $pneusAplicadosAtuais->toArray(),
                    'pneus_aplicados_depois' => $pneusAplicadosDepois->toArray()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ DEBUG: Erro crítico', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'DEBUG: Erro crítico: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function gravarHistoricoForcado($idVeiculo, $dados, $operacoes)
    {
        try {
            foreach ($operacoes as $operacao) {
                $idPneu = $operacao['pneu_adicionado_id'] ?? $operacao['pneu_removido_id'];

                // Obter dados do pneu para preencher id_modelo e id_vida_pneu
                $dadosPneu = DB::connection('carvalima_production')
                    ->table('pneu')
                    ->select('id_modelo_pneu', 'id_controle_vida_pneu')
                    ->where('id_pneu', $idPneu)
                    ->first();

                // Determinar se é remoção ou aplicação
                $isRemocao = isset($operacao['pneu_removido_id']);

                // Preparar dados base
                $dadosHistorico = [
                    'data_inclusao' => now(),
                    'data_alteracao' => now(),
                    'id_veiculo' => $idVeiculo,
                    'id_pneu' => $idPneu,
                    'km_inicial' => $dados['dadosVeiculo']['km_atual'] ?? null,
                    'eixo_aplicado' => $operacao['localizacao'],
                    'status_movimentacao' => $this->determinarStatusMovimentacao($operacao),
                    'origem_operacao' => $operacao['dados']['origem_operacao'] ?? 'MANUAL',
                    'observacoes_operacao' => $this->gerarObservacaoOperacao($operacao),
                    'id_usuario' => Auth::id(),
                ];

                // Adicionar campos específicos se dados do pneu foram encontrados
                if ($dadosPneu) {
                    $dadosHistorico['id_modelo'] = $dadosPneu->id_modelo_pneu;
                    $dadosHistorico['id_vida_pneu'] = $dadosPneu->id_controle_vida_pneu;
                }

                // Para remoções, adicionar km_final e data_retirada
                if ($isRemocao) {
                    $dadosHistorico['km_final'] = $operacao['dados']['km_removido'] ?? $dados['dadosVeiculo']['km_atual'] ?? null;
                    $dadosHistorico['data_retirada'] = now()->toDateString();
                }

                HistoricoPneu::create($dadosHistorico);
            }
        } catch (\Exception $e) {
            Log::error('❌ ERRO AO GRAVAR HISTÓRICO', [
                'error' => $e->getMessage(),
                'veiculo_id' => $idVeiculo,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function limparConflitosExistentes(Request $request)
    {
        try {
            $idVeiculo = $request->input('id_veiculo');

            if (!$idVeiculo) {
                return response()->json(['success' => false, 'error' => 'ID do veículo obrigatório']);
            }


            // Buscar veículo x pneu
            $veiculoXPneu = VeiculoXPneu::where('id_veiculo', $idVeiculo)
                ->where('situacao', true)
                ->first();

            if (!$veiculoXPneu) {
                return response()->json(['success' => false, 'error' => 'Veículo não encontrado']);
            }

            // Buscar todos os pneus aplicados ativos
            $pneusAplicados = PneusAplicados::where('id_veiculo_x_pneu', $veiculoXPneu->id_veiculo_pneu)
                ->whereNull('deleted_at')
                ->orderBy('data_inclusao', 'asc') // Manter o mais antigo
                ->get();

            // Detectar conflitos por localização
            $conflitos = $pneusAplicados
                ->groupBy('localizacao')
                ->filter(function ($pneus) {
                    return $pneus->count() > 1;
                });

            if ($conflitos->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nenhum conflito encontrado',
                    'conflitos_resolvidos' => 0
                ]);
            }

            $conflitosResolvidos = 0;

            foreach ($conflitos as $localizacao => $pneusDuplicados) {

                // Manter apenas o primeiro (mais antigo) e remover os outros
                $pneuParaManter = $pneusDuplicados->first();
                $pneusParaRemover = $pneusDuplicados->skip(1);

                foreach ($pneusParaRemover as $pneuParaRemover) {
                    // Soft delete do pneu conflitante
                    $pneuParaRemover->update([
                        'km_removido' => null,
                        'sulco_pneu_removido' => null,
                        'data_alteracao' => now(),
                        'origem_operacao' => 'LIMPEZA_CONFLITO',
                        'destino' => 'ESTOQUE',
                    ]);

                    $pneuParaRemover->delete(); // Soft delete

                    // Atualizar status do pneu para ESTOQUE
                    Pneu::where('id_pneu', $pneuParaRemover->id_pneu)
                        ->update([
                            'status_pneu' => 'ESTOQUE',
                            'data_alteracao' => now()
                        ]);


                    $conflitosResolvidos++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Limpeza concluída. {$conflitosResolvidos} conflitos resolvidos.",
                'conflitos_resolvidos' => $conflitosResolvidos
            ]);
        } catch (\Exception $e) {
            Log::error('❌ ERRO NA LIMPEZA DE CONFLITOS', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro na limpeza: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Finalizar a aplicação de pneu
     */
    public function finalizarAplicacao(Request $request)
    {
        // Bloqueio: não permite finalizar movimentação se existirem pneus parados no depósito por mais de 24 horas
        if ($this->hasPneusParadosMais24Horas()) {
            return response()->json([
                'success' => false,
                'message' => 'Existem pneus parados no depósito há mais de 24 horas. Finalização de movimentação bloqueada.'
            ], 423); // 423 Locked
        }
        try {
            // Debug: verificar dados recebidos
            Log::info('🔍 DADOS RECEBIDOS na finalização:', [
                'all_data' => $request->all(),
                'id_ordem_servico' => $request->input('id_ordem_servico'),
                'method' => $request->method()
            ]);

            $idOrdemServico = $request->input('id_ordem_servico');

            // Fallback: se id_ordem_servico estiver vazio, tentar buscar da sessão ou contexto
            if (empty($idOrdemServico)) {
                Log::info('🔄 ID da OS vazio, tentando fallback inteligente...');

                // Método 1: Buscar ordem de serviço ativa recente do tipo Borracharia
                $ordemServicoRecente = DB::table('ordem_servico')
                    ->where('id_tipo_ordem_servico', 3) // Borracharia
                    ->where('id_status_ordem_servico', 2) // Em Andamento
                    ->whereDate('data_abertura', '>=', now()->subDays(7)) // Últimos 7 dias
                    ->orderBy('data_inclusao', 'desc')
                    ->first();

                if ($ordemServicoRecente) {
                    $idOrdemServico = $ordemServicoRecente->id_ordem_servico;
                    Log::info('🎯 Fallback Método 1: OS recente encontrada:', [
                        'id_ordem_servico' => $idOrdemServico,
                        'data_abertura' => $ordemServicoRecente->data_abertura
                    ]);
                } else {
                    // Método 2: Se não encontrou recente, buscar qualquer uma ativa
                    $ordemServicoQualquer = DB::table('ordem_servico')
                        ->where('id_tipo_ordem_servico', 3)
                        ->where('id_status_ordem_servico', 2)
                        ->orderBy('id_ordem_servico', 'desc')
                        ->first();

                    if ($ordemServicoQualquer) {
                        $idOrdemServico = $ordemServicoQualquer->id_ordem_servico;
                        Log::info('🎯 Fallback Método 2: Qualquer OS ativa:', ['id_ordem_servico' => $idOrdemServico]);
                    }
                }
            }

            $validated = $request->validate([
                'id_ordem_servico' => 'sometimes|integer'
            ], [
                'id_ordem_servico.integer' => 'ID da ordem de serviço deve ser um número válido'
            ]);

            // Se ainda não temos ID, usar o que conseguimos obter
            if (empty($validated['id_ordem_servico']) && !empty($idOrdemServico)) {
                $validated['id_ordem_servico'] = $idOrdemServico;
            }

            // Validação final
            if (empty($validated['id_ordem_servico'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID da ordem de serviço é obrigatório. Por favor, selecione uma ordem de serviço.'
                ], 400);
            }

            $idOrdemServico = $validated['id_ordem_servico'];

            // Buscar a ordem de serviço primeiro para obter o ID do veículo
            $ordemServico = DB::table('ordem_servico')
                ->where('id_ordem_servico', $idOrdemServico)
                ->where('id_tipo_ordem_servico', 3) // Tipo Borracharia
                ->first();

            if (!$ordemServico) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ordem de serviço não encontrada ou não é do tipo Borracharia.'
                ], 404);
            }

            $idVeiculo = $ordemServico->id_veiculo;

            Log::info('🎯 Iniciando finalização da aplicação de pneu', [
                'id_ordem_servico' => $idOrdemServico,
                'id_veiculo' => $idVeiculo,
                'usuario' => Auth::id()
            ]);

            // ✅ NOVA VALIDAÇÃO: Verificar se todos os pneus da requisição estão aplicados
            Log::info('🔍 Validando requisição de pneus antes da finalização...');
            $validacaoRequisicao = $this->validarPneusRequisicaoAplicados($idOrdemServico);

            if (!$validacaoRequisicao['valido']) {
                Log::warning("❌ Finalização bloqueada - requisição de pneus não completamente aplicada", [
                    'ordem_servico' => $idOrdemServico,
                    'pneus_nao_aplicados' => $validacaoRequisicao['pneus_nao_aplicados'] ?? []
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $validacaoRequisicao['mensagem']
                ], 400);
            }

            Log::info('✅ Validação da requisição de pneus passou - todos os pneus estão aplicados');

            // Verificar se já está finalizada
            if ($ordemServico->id_status_ordem_servico == 11) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta ordem de serviço já foi finalizada.'
                ], 400);
            }

            DB::beginTransaction();
            // 1. Determinar pneus aplicados para esta movimentação.
            // Preferir a lista enviada pelo frontend (pneus aplicados durante a movimentação).
            $pneusAplicados = collect();
            $pneusFonte = 'db';

            $pneusDoRequest = $request->input('pneusAplicados');
            if (is_array($pneusDoRequest) && count($pneusDoRequest) > 0) {
                // Extrair IDs (aceita tanto array de IDs quanto array de objetos {id_pneu, ...})
                $ids = array_map(function ($item) {
                    if (is_array($item)) {
                        return isset($item['id_pneu']) ? (int) $item['id_pneu'] : (int) ($item['id'] ?? 0);
                    }
                    return (int) $item;
                }, $pneusDoRequest);

                $pneusAplicados = collect(array_values(array_filter($ids, function ($v) {
                    return $v > 0;
                })));
                $pneusFonte = 'request';
            }

            if ($pneusAplicados->isEmpty()) {
                // Fallback: buscar pneus aplicados no banco (estado atual do veículo)
                $pneusAplicados = DB::table('pneus_aplicados')
                    ->join('veiculo_x_pneu', 'veiculo_x_pneu.id_veiculo_pneu', '=', 'pneus_aplicados.id_veiculo_x_pneu')
                    ->join('pneu', 'pneu.id_pneu', '=', 'pneus_aplicados.id_pneu')
                    ->where('veiculo_x_pneu.id_veiculo', $idVeiculo)
                    ->where('pneu.status_pneu', 'APLICADO')
                    ->whereNull('pneus_aplicados.deleted_at')
                    ->pluck('pneu.id_pneu');
                $pneusFonte = 'db';
            }

            Log::info('✅ Pneus aplicados determinados', [
                'fonte' => $pneusFonte,
                'quantidade' => $pneusAplicados->count(),
                'pneus' => $pneusAplicados->toArray()
            ]);

            // 2. Atualizar situacao_pecas em ordem_servico_pecas para pneus
            $pecasAtualizadas = DB::table('ordem_servico_pecas')
                ->where('id_ordem_servico', $idOrdemServico)
                ->whereIn('situacao_pecas', ['SOLICITADA'])
                ->update([
                    'situacao_pecas' => 'APLICAÇÃO PNEU FINALIZADA',
                    'data_alteracao' => now()
                ]);

            // 2.1 Gravar pneus aplicados na coluna pneus_aplicados da tabela ordem_servico_pecas
            // Converter a coleção de pneus aplicados para array simples
            $pneusAplicadosArray = $pneusAplicados->values()->all();

            try {
                DB::table('ordem_servico_pecas')
                    ->where('id_ordem_servico', $idOrdemServico)
                    ->whereIn('situacao_pecas', ['APLICAÇÃO PNEU FINALIZADA'])
                    ->update([
                        'pneus_aplicados' => json_encode($pneusAplicadosArray)
                    ]);

                Log::info('✅ Pneus aplicados gravados em ordem_servico_pecas.pneus_aplicados', [
                    'id_ordem_servico' => $idOrdemServico,
                    'pneus' => $pneusAplicadosArray
                ]);
            } catch (\Exception $e) {
                Log::warning('⚠️ Não foi possível gravar pneus_aplicados em ordem_servico_pecas', [
                    'erro' => $e->getMessage()
                ]);
                // Não interromper o processo; apenas logar. A gravação não é crítica para finalizar.
            }

            Log::info('✅ Peças atualizadas para APLICAÇÃO PNEU FINALIZADA', [
                'quantidade_atualizada' => $pecasAtualizadas
            ]);

            // 3. Alterar status da ordem de serviço para 11 (Finalizada)
            DB::table('ordem_servico')
                ->where('id_ordem_servico', $idOrdemServico)
                ->update([
                    'id_status_ordem_servico' => 11,
                    'data_alteracao' => now()
                ]);

            Log::info('✅ Ordem de serviço finalizada', [
                'id_ordem_servico' => $idOrdemServico,
                'novo_status' => 11
            ]);

            DB::commit();

            Log::info('✅ Finalização da aplicação concluída com sucesso', [
                'id_ordem_servico' => $idOrdemServico,
                'pneus_aplicados_count' => $pneusAplicados->count(),
                'pecas_atualizadas' => $pecasAtualizadas
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aplicação de pneu finalizada com sucesso!',
                'dados' => [
                    'pneus_aplicados_count' => $pneusAplicados->count(),
                    'pecas_atualizadas' => $pecasAtualizadas,
                    'ordem_servico' => $idOrdemServico
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Erro ao finalizar aplicação de pneu', [
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
                'arquivo' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar aplicação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar se uma ordem de serviço pode ser finalizada (para testes)
     */
    public function verificarFinalizacao(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_ordem_servico' => 'required|integer'
            ]);

            $idOrdemServico = $validated['id_ordem_servico'];

            // Verificar ordem de serviço
            $ordemServico = DB::table('ordem_servico')
                ->where('id_ordem_servico', $idOrdemServico)
                ->first();

            if (!$ordemServico) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ordem de serviço não encontrada'
                ]);
            }

            // Verificar pneus aplicados para esta OS
            $pneusAplicados = DB::table('pneus_aplicados')
                ->join('veiculo_x_pneu', 'veiculo_x_pneu.id_veiculo_pneu', '=', 'pneus_aplicados.id_veiculo_x_pneu')
                ->join('pneu', 'pneu.id_pneu', '=', 'pneus_aplicados.id_pneu')
                ->where('veiculo_x_pneu.id_veiculo', $ordemServico->id_veiculo)
                ->where('pneu.status_pneu', 'APLICADO')
                ->whereNull('pneus_aplicados.deleted_at')
                ->count();

            // Verificar peças relacionadas a pneus (contar todas as peças da OS)
            $pecasPneus = DB::table('ordem_servico_pecas')
                ->where('id_ordem_servico', $idOrdemServico)
                ->count();

            return response()->json([
                'success' => true,
                'dados' => [
                    'id_ordem_servico' => $idOrdemServico,
                    'status_atual' => $ordemServico->id_status_ordem_servico,
                    'pode_finalizar' => $ordemServico->id_status_ordem_servico != 11,
                    'pneus_aplicados' => $pneusAplicados,
                    'pecas_pneus' => $pecasPneus,
                    'tipo_ordem' => $ordemServico->id_tipo_ordem_servico,
                    'ordem_servico_completa' => $ordemServico
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar: ' . $e->getMessage()
            ], 500);
        }
    }

    // hasPneusParadosMais24Horas moved to HasPneusParadosTrait

    /**
     * Determinar o status de destino baseado no tipo de drop zone
     */
    private function determinarStatusDestino($destino)
    {
        // ✅ SEMPRE DEFINIR COMO DEPOSITO PARA PNEUS REMOVIDOS
        // Independentemente da zona visual de destino, todos os pneus removidos vão para o depósito
        return 'DEPOSITO';

        /* Mapeamento original (comentado):
        $mapeamento = [
            'deposito' => 'DEPOSITO',
            'borracharia' => 'BORRACHARIA',
            'descarte' => 'DESCARTE',
            'reforma' => 'REFORMA',
        ];

        $destinoLower = strtolower($destino);
        return $mapeamento[$destinoLower] ?? 'DEPOSITO';
        */
    }

    /**
     * ==========================================
     * VALIDAR SE TODOS OS PNEUS DA REQUISIÇÃO ESTÃO APLICADOS
     * ==========================================
     */
    protected function validarPneusRequisicaoAplicados($idOrdemServico)
    {
        try {
            Log::info("🔍 Validando pneus da requisição para OS: {$idOrdemServico}");

            // 1. Verificar se existe requisição para esta ordem de serviço
            $requisicaoExists = DB::table('requisicao_pneu')
                ->where('id_ordem_servico', $idOrdemServico)
                ->exists();

            if (!$requisicaoExists) {
                Log::info("✅ Nenhuma requisição de pneu encontrada para OS {$idOrdemServico} - validação passou");
                return [
                    'valido' => true,
                    'mensagem' => 'Nenhuma requisição de pneu vinculada a esta ordem de serviço'
                ];
            }

            // 2. Buscar todos os pneus da requisição e verificar seus status
            $pneusRequisicao = DB::table('requisicao_pneu as rp')
                ->join('requisicao_pneu_modelos as rpm', 'rpm.id_requisicao_pneu', '=', 'rp.id_requisicao_pneu')
                ->join('requisicao_pneu_itens as rpi', 'rpi.id_requisicao_pneu_modelos', '=', 'rpm.id_requisicao_pneu_modelos')
                ->join('pneu as p', 'p.id_pneu', '=', 'rpi.id_pneu')
                ->select(
                    'p.id_pneu',
                    'p.status_pneu',
                    'rpi.id_requisicao_pneu_itens'
                )
                ->where('rp.id_ordem_servico', $idOrdemServico)
                ->whereNull('p.deleted_at')
                ->get();

            if ($pneusRequisicao->isEmpty()) {
                Log::info("✅ Nenhum pneu específico encontrado na requisição para OS {$idOrdemServico} - validação passou");
                return [
                    'valido' => true,
                    'mensagem' => 'Requisição não possui pneus específicos selecionados'
                ];
            }

            Log::info("📊 Encontrados " . $pneusRequisicao->count() . " pneus na requisição da OS {$idOrdemServico}");

            // 3. Identificar pneus que não estão aplicados
            $pneusNaoAplicados = $pneusRequisicao->filter(function ($pneu) {
                return $pneu->status_pneu !== 'APLICADO';
            });

            if ($pneusNaoAplicados->isNotEmpty()) {
                $listaPneusNaoAplicados = $pneusNaoAplicados->pluck('id_pneu')->toArray();
                $statusDetalhado = $pneusNaoAplicados->map(function ($pneu) {
                    return "Pneu {$pneu->id_pneu}: {$pneu->status_pneu}";
                })->toArray();

                Log::warning("❌ Encontrados " . $pneusNaoAplicados->count() . " pneus não aplicados", [
                    'pneus_nao_aplicados' => $listaPneusNaoAplicados,
                    'status_detalhado' => $statusDetalhado
                ]);

                $mensagemDetalhada = "MOVIMENTAÇÃO BLOQUEADA!\n\n" .
                    "Nem todos os pneus da requisição desta ordem de serviço foram aplicados.\n\n" .
                    "Pneus que ainda não estão aplicados:\n" .
                    implode("\n", $statusDetalhado) . "\n\n" .
                    "A movimentação não pode ser finalizada até que todos os pneus da requisição sejam aplicados no veículo.\n\n" .
                    "Verifique se todos os pneus foram corretamente aplicados antes de tentar finalizar a movimentação.";

                return [
                    'valido' => false,
                    'mensagem' => $mensagemDetalhada,
                    'pneus_nao_aplicados' => $listaPneusNaoAplicados
                ];
            }

            Log::info("✅ Todos os " . $pneusRequisicao->count() . " pneus da requisição estão aplicados - validação passou");

            return [
                'valido' => true,
                'mensagem' => 'Todos os pneus da requisição estão aplicados'
            ];
        } catch (\Exception $e) {
            Log::error("❌ Erro na validação de pneus da requisição: " . $e->getMessage());

            return [
                'valido' => false,
                'mensagem' => 'Erro interno ao validar requisição de pneus: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obter as localizações obrigatórias de um veículo específico
     * baseado na configuração do tipo de equipamento
     */
    public function getLocalizacoesObrigatorias($idVeiculo)
    {
        try {
            Log::info("📍 Obtendo localizações obrigatórias para veículo ID: {$idVeiculo}");

            // Buscar o veículo com o tipo de equipamento
            $veiculo = Veiculo::with('tipoEquipamento')
                ->where('id_veiculo', $idVeiculo)
                ->where('is_terceiro', false)
                ->where('situacao_veiculo', true)
                ->first();

            if (!$veiculo) {
                Log::warning("⚠️ Veículo {$idVeiculo} não encontrado ou inativo");
                return response()->json([
                    'success' => false,
                    'message' => 'Veículo não encontrado ou inativo',
                    'localizacoes' => []
                ]);
            }

            if (!$veiculo->tipoEquipamento) {
                Log::warning("⚠️ Tipo de equipamento não encontrado para veículo {$idVeiculo}");
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de equipamento não configurado para este veículo',
                    'localizacoes' => []
                ]);
            }

            $tipoEquipamento = $veiculo->tipoEquipamento;
            $numeroEixos = $tipoEquipamento->numero_eixos;

            // Gerar localizações obrigatórias baseadas na configuração do tipo de equipamento
            $localizacoesObrigatorias = [];

            for ($eixo = 1; $eixo <= $numeroEixos; $eixo++) {
                $campoPneus = "numero_pneus_eixo_{$eixo}";
                $numeroPneus = $tipoEquipamento->$campoPneus ?? 0;

                if ($numeroPneus > 0) {
                    if ($numeroPneus == 2) {
                        // Para eixos com 2 pneus - usar nomenclatura padrão (D e E)
                        $localizacoesObrigatorias[] = [
                            'localizacao' => $eixo . 'D',
                            'tipo_veiculo' => $tipoEquipamento->descricao_tipo
                        ];
                        $localizacoesObrigatorias[] = [
                            'localizacao' => $eixo . 'E',
                            'tipo_veiculo' => $tipoEquipamento->descricao_tipo
                        ];
                    } elseif ($numeroPneus == 4) {
                        // Para eixos com 4 pneus - usar nomenclatura completa (DI, DE, EI, EE)
                        $localizacoesObrigatorias[] = [
                            'localizacao' => $eixo . 'DI',
                            'tipo_veiculo' => $tipoEquipamento->descricao_tipo
                        ];
                        $localizacoesObrigatorias[] = [
                            'localizacao' => $eixo . 'DE',
                            'tipo_veiculo' => $tipoEquipamento->descricao_tipo
                        ];
                        $localizacoesObrigatorias[] = [
                            'localizacao' => $eixo . 'EI',
                            'tipo_veiculo' => $tipoEquipamento->descricao_tipo
                        ];
                        $localizacoesObrigatorias[] = [
                            'localizacao' => $eixo . 'EE',
                            'tipo_veiculo' => $tipoEquipamento->descricao_tipo
                        ];
                    }
                }
            }

            if (empty($localizacoesObrigatorias)) {
                Log::warning("⚠️ Nenhuma localização obrigatória configurada para o veículo {$idVeiculo}");
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma localização obrigatória configurada para este veículo',
                    'localizacoes' => []
                ]);
            }

            Log::info("✅ Encontradas " . count($localizacoesObrigatorias) . " localizações obrigatórias para veículo {$idVeiculo}: " .
                implode(', ', array_column($localizacoesObrigatorias, 'localizacao')));

            return response()->json([
                'success' => true,
                'message' => 'Localizações obrigatórias obtidas com sucesso',
                'localizacoes' => $localizacoesObrigatorias,
                'total' => count($localizacoesObrigatorias)
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Erro ao obter localizações obrigatórias do veículo {$idVeiculo}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao obter localizações obrigatórias: ' . $e->getMessage(),
                'localizacoes' => []
            ], 500);
        }
    }
}
