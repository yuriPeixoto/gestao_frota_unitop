<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Models\DevolucaoImobilizadoVeiculo;
use App\Models\InconsistenciaTruckPag;
use App\Models\PedidoCompra;
use App\Models\Produto;
use App\Models\SolicitacaoCompra;
use App\Models\TransferenciaImobilizadoVeiculo;
use App\Models\VEstoqueImobilizado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard principal do sistema
     *
     * @return View
     */
    public function index()
    {
        // Dados do usuário logado
        $user = Auth::user();

        // Obter dados dos módulos
        $moduleData = $this->getModulesData();

        // Dados para a view
        $dashboardData = [
            'user' => $user,
            'current_date' => now()->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y'),
            'current_time' => now()->format('H:i:s'),
            'modules' => $moduleData,
        ];

        return view('admin.dashboard', $dashboardData);
    }

    /**
     * Obtém dados de todos os módulos do sistema
     * Otimizado para melhor performance com cache inteligente
     *
     * @return array
     */
    private function getModulesData()
    {
        // Cache geral dos módulos por 10 minutos para evitar recalcular tudo
        return Cache::remember('dashboard_all_modules_'.Auth::id(), now()->addMinutes(10), function () {
            $modules = [];

            // Abastecimentos - com dados reais
            if (PermissionHelper::hasModuleAccess('abastecimentos')) {
                $modules['abastecimentos'] = $this->getAbastecimentosData();
            }

            // Outros módulos - com dados simulados por enquanto
            if (PermissionHelper::hasModuleAccess('compras')) {
                $modules['compras'] = $this->getComprasData();
            }

            if (PermissionHelper::hasModuleAccess('estoque')) {
                $modules['estoque'] = $this->getEstoqueData();
            }

            if (PermissionHelper::hasModuleAccess('imobilizados')) {
                $modules['imobilizados'] = $this->getImobilizadosData();
            }

            // Manutenção deve aparecer logo após Imobilizados
            if (PermissionHelper::hasModuleAccess('manutencao')) {
                $modules['manutencao'] = $this->getManutencaoData();
            }

            // Pessoas deve aparecer logo após Manutenção
            if (PermissionHelper::hasModuleAccess('pessoal')) {
                $modules['pessoal'] = $this->getPessoalData();
            }

            // Pneus deve aparecer logo após Pessoal
            if (PermissionHelper::hasModuleAccess('pneus')) {
                $modules['pneus'] = $this->getPneusData();
            }

            // Sinistros deve aparecer logo após Pneus
            if (PermissionHelper::hasModuleAccess('sinistros')) {
                $modules['sinistros'] = $this->getSinistrosData();
            }

            if (PermissionHelper::hasModuleAccess('veiculos')) {
                $modules['veiculos'] = $this->getVeiculosData();
            }

            return $modules;
        });
    }

    /**
     * Obtém dados do módulo de Abastecimentos
     *
     * @return array
     */
    private function getAbastecimentosData()
    {
        try {
            // Cache as queries mais lentas por mais tempo
            $cacheKey = 'dashboard_abastecimentos_queries_'.now()->format('Y-m-d-H');
            [$inconsistenciasAts, $inconsistenciasTruckPag] = Cache::remember($cacheKey, now()->addHours(1), function () {
                // Otimização: usar raw query com índices específicos para performance
                $inconsistenciasAts = DB::selectOne('
                    SELECT COUNT(*) as total
                    FROM v_inconsistencias_ats
                    WHERE data_inclusao >= ?
                    AND (tratado = false OR tratado IS NULL)
                ', [now()->subDays(7)])->total ?? 0;

                // TruckPag inconsistencies (simplified - no tratado field)
                $inconsistenciasTruckPag = InconsistenciaTruckPag::where('data_inclusao', '>=', now()->subDays(7))
                    ->count();

                return [$inconsistenciasAts, $inconsistenciasTruckPag];
            });

            $totalInconsistencias = $inconsistenciasAts + $inconsistenciasTruckPag;

            // Verificar tanques com baixo estoque (menos de 30%)
            $tanquesBaixoEstoque = $this->getTanquesBaixoEstoque();

            // Métricas
            $metrics = [
                ['value' => number_format($totalInconsistencias), 'label' => 'Inconsistências'],
                ['value' => count($tanquesBaixoEstoque), 'label' => 'Tanques < 30%'],
            ];

            // Alertas
            $alerts = [];
            if ($totalInconsistencias > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Inconsistências Pendentes',
                    'description' => "{$totalInconsistencias} inconsistências precisam ser tratadas",
                    'count' => $totalInconsistencias,
                ];
            }

            if (count($tanquesBaixoEstoque) > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'title' => 'Tanques com Baixo Estoque',
                    'description' => count($tanquesBaixoEstoque).' tanques abaixo de 30%',
                    'count' => count($tanquesBaixoEstoque),
                ];
            }

            // Ações rápidas
            $actions = [
                [
                    'label' => 'Ver Inconsistências',
                    'url' => route('admin.inconsistencias.index'),
                    'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>',
                ],
                [
                    'label' => 'Estoque Combustível',
                    'url' => route('admin.estoque-combustivel.dashboard'),
                    'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
                ],
            ];

            return [
                'title' => 'Abastecimentos',
                'color' => count($alerts) > 0 ? 'orange' : 'blue',
                'route' => route('admin.abastecimentomanual.index'),
                'icon' => 'icons.modules.abastecimentos',
                'alerts' => $alerts,
                'metrics' => $metrics,
                'actions' => $actions,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados de Abastecimentos: '.$e->getMessage());

            return [
                'title' => 'Abastecimentos',
                'color' => 'gray',
                'route' => route('admin.abastecimentomanual.index'),
                'icon' => 'icons.modules.abastecimentos',
                'alerts' => [[
                    'type' => 'warning',
                    'title' => 'Erro ao carregar dados',
                    'description' => 'Não foi possível carregar as informações do módulo',
                    'count' => 1,
                ]],
                'metrics' => [],
                'actions' => [],
            ];
        }
    }

    /**
     * Verifica tanques com estoque abaixo de 30%
     *
     * @return array
     */
    private function getTanquesBaixoEstoque()
    {
        try {
            // Use the VEstoqueCombustivel view which already provides calculated stock values
            return Cache::remember('tanques_baixo_estoque', now()->addMinutes(30), function () {
                // Threshold: keep the same 30% logic used before
                $threshold = 0.30;

                return \App\Models\VEstoqueCombustivel::query()
                    ->select(['id_tanque', 'tanque', 'tipo_combustivel', 'nome_filial', 'quantidade_em_estoque', 'data_alteracao', 'capacidade_tanque'])
                    ->where('capacidade_tanque', '>', 0)
                    ->whereRaw('(quantidade_em_estoque / capacidade_tanque) < ?', [$threshold])
                    ->orderBy('nome_filial')
                    ->orderBy('tanque')
                    ->get()
                    ->toArray();
            });
        } catch (\Exception $e) {
            Log::error('Erro ao verificar tanques com baixo estoque (view): '.$e->getMessage());

            return [];
        }
    }

    private function getComprasData()
    {
        try {
            // Cachear para reduzir carga no banco
            return Cache::remember('dashboard_compras_card', now()->addMinutes(15), function () {
                // Métricas principais
                $solicitacoesPendentes = SolicitacaoCompra::pendentes()->count();
                $pedidosPendentesAprovacao = PedidoCompra::pendentesAprovacao()->count();

                // Total do mês corrente (desconsiderando cancelados - situacao_pedido 6)
                $valorTotalMes = (float) PedidoCompra::query()
                    ->where('data_inclusao', '>=', now()->startOfMonth())
                    ->where('situacao_pedido', '!=', 6)
                    ->sum('valor_total');

                // Formatadores
                $formatCurrency = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
                $formatInt = fn ($v) => number_format((int) $v, 0, ',', '.');

                // Definir alertas
                $alerts = [];
                if ($pedidosPendentesAprovacao > 0) {
                    $alerts[] = [
                        'type' => $pedidosPendentesAprovacao >= 20 ? 'danger' : 'warning',
                        'title' => 'Pedidos aguardando aprovação',
                        'description' => $formatInt($pedidosPendentesAprovacao).' pedidos aguardam aprovação',
                        'count' => (int) $pedidosPendentesAprovacao,
                    ];
                }

                if ($solicitacoesPendentes > 0) {
                    $alerts[] = [
                        'type' => 'info',
                        'title' => 'Solicitações pendentes',
                        'description' => $formatInt($solicitacoesPendentes).' solicitações aguardando processamento',
                        'count' => (int) $solicitacoesPendentes,
                    ];
                }

                // Ações rápidas
                $actions = [
                    [
                        'label' => 'Ver pendentes de aprovação',
                        'url' => route('admin.compras.pedidos.pendentes-aprovacao'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v1h1a2 2 0 012 2v7a2 2 0 01-2 2H9a2 2 0 01-2-2v-1H6a2 2 0 01-2-2V5z"/></svg>',
                    ],
                    [
                        'label' => 'Dashboard de Compras',
                        'url' => route('admin.compras.dashboard'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 01.894.553l6 12A1 1 0 0116 16H4a1 1 0 01-.894-1.447l6-12A1 1 0 0110 2zm0 3.618L5.447 14h9.106L10 5.618z" clip-rule="evenodd"/></svg>',
                    ],
                ];

                // Cor baseada na severidade
                $color = 'green';
                if ($pedidosPendentesAprovacao >= 20) {
                    $color = 'red';
                } elseif ($pedidosPendentesAprovacao > 0 || $solicitacoesPendentes > 0) {
                    $color = 'orange';
                }

                return [
                    'title' => 'Compras',
                    'color' => $color,
                    'route' => route('admin.compras.dashboard'),
                    'icon' => 'icons.modules.compras',
                    'metrics' => [
                        ['value' => $formatInt($pedidosPendentesAprovacao), 'label' => 'Pedidos pendentes de aprovação'],
                        ['value' => $formatInt($solicitacoesPendentes), 'label' => 'Solicitações pendentes'],
                        ['value' => $formatCurrency($valorTotalMes), 'label' => 'Valor do mês'],
                    ],
                    'alerts' => $alerts,
                    'actions' => $actions,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados do módulo de Compras: '.$e->getMessage());

            return [
                'title' => 'Compras',
                'color' => 'gray',
                'route' => route('admin.compras.dashboard'),
                'icon' => 'icons.modules.compras',
                'alerts' => [[
                    'type' => 'warning',
                    'title' => 'Erro ao carregar dados',
                    'description' => 'Não foi possível carregar as informações do módulo',
                    'count' => 1,
                ]],
                'metrics' => [],
                'actions' => [],
            ];
        }
    }

    private function getEstoqueData()
    {
        try {
            return Cache::remember('dashboard_estoque_card', now()->addMinutes(15), callback: function () {
                // Métricas principais (usar apenas Produto)
                $produtosAtivos = Produto::ativo()->count();
                $produtosAbaixoMinimo = Produto::ativo()->whereNotNull('estoque_minimo')->where('estoque_minimo', '>', 0)->whereColumn('quantidade_atual_produto', '<', 'estoque_minimo')->count();

                // Valor total (quantidade_atual_produto * valor_medio), usando conexão do Produto
                $produtoConn = (new Produto)->getConnectionName();
                $db = $produtoConn ? DB::connection($produtoConn) : DB::connection();
                $valorTotal = (float) $db->table('produto')->where('is_ativo', true)->sum(DB::raw('COALESCE(quantidade_atual_produto, 0) * COALESCE(valor_medio, 0)'));

                $formatCurrency = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
                $formatInt = fn ($v) => number_format((int) $v, 0, ',', '.');

                // Alertas
                $alerts = [];
                if ($produtosAbaixoMinimo > 0) {
                    $alerts[] = [
                        'type' => $produtosAbaixoMinimo >= 20 ? 'danger' : 'warning',
                        'title' => 'Itens abaixo do mínimo',
                        'description' => $formatInt($produtosAbaixoMinimo).' itens estão abaixo do estoque mínimo',
                        'count' => (int) $produtosAbaixoMinimo,
                    ];
                }

                // Ações rápidas
                $actions = [
                    [
                        'label' => 'Ver estoque baixo',
                        'url' => route('admin.estoque.estoque-baixo'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 00-8 8h2a6 6 0 1112 0h2a8 8 0 00-8-8zM4 10a6 6 0 0012 0h-2a4 4 0 11-8 0H4z"/></svg>',
                    ],
                    [
                        'label' => 'Dashboard de Estoque',
                        'url' => route('admin.estoque.dashboard'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V3zm3 3h2v2H6V6zm0 4h2v2H6v-2zm4-4h2v2h-2V6zm0 4h2v2h-2v-2z" clip-rule="evenodd"/></svg>',
                    ],
                ];

                // Cor baseada na severidade
                $color = 'purple';
                if ($produtosAbaixoMinimo >= 20) {
                    $color = 'red';
                } elseif ($produtosAbaixoMinimo > 0) {
                    $color = 'orange';
                }

                return [
                    'title' => 'Estoque',
                    'color' => $color,
                    'route' => route('admin.estoque.dashboard'),
                    'icon' => 'icons.modules.estoque',
                    'metrics' => [
                        ['value' => $formatInt($produtosAtivos), 'label' => 'Produtos ativos'],
                        ['value' => $formatInt($produtosAbaixoMinimo), 'label' => 'Abaixo do mínimo'],
                        ['value' => $formatCurrency($valorTotal), 'label' => 'Valor total'],
                    ],
                    'alerts' => $alerts,
                    'actions' => $actions,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados do módulo de Estoque: '.$e->getMessage());

            return [
                'title' => 'Estoque',
                'color' => 'gray',
                'route' => route('admin.estoque.dashboard'),
                'icon' => 'icons.modules.estoque',
                'alerts' => [[
                    'type' => 'warning',
                    'title' => 'Erro ao carregar dados',
                    'description' => 'Não foi possível carregar as informações do módulo',
                    'count' => 1,
                ]],
                'metrics' => [],
                'actions' => [],
            ];
        }
    }

    private function getImobilizadosData()
    {
        try {
            return Cache::remember('dashboard_imobilizados_card', now()->addMinutes(15), function () {
                // Resumo de estoque de imobilizados
                $resumo = VEstoqueImobilizado::query()
                    ->selectRaw('COALESCE(SUM(quantidade_imobilizados), 0) as total_qtd')
                    ->selectRaw('COALESCE(SUM(total), 0) as total_valor')
                    ->first();

                $qtdEstoque = (int) ($resumo->total_qtd ?? 0);
                $valorEstoque = (float) ($resumo->total_valor ?? 0.0);

                // Transferências em andamento (exclui concluído 9 e reprovado 8)
                $transfEmAndamento = TransferenciaImobilizadoVeiculo::whereIn('status', [2, 3, 4, 5, 10])->count();
                // Devoluções em andamento (considera 5 como concluído; mantemos [2,3,4,10])
                $devolucoesEmAndamento = DevolucaoImobilizadoVeiculo::whereIn('status', [2, 3, 4, 10])->count();

                $formatCurrency = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
                $formatInt = fn ($v) => number_format((int) $v, 0, ',', '.');

                // Alertas
                $alerts = [];
                if ($transfEmAndamento > 0) {
                    $alerts[] = [
                        'type' => $transfEmAndamento >= 20 ? 'danger' : 'warning',
                        'title' => 'Transferências em andamento',
                        'description' => $formatInt($transfEmAndamento).' transferências aguardando etapas',
                        'count' => (int) $transfEmAndamento,
                    ];
                }
                if ($devolucoesEmAndamento > 0) {
                    $alerts[] = [
                        'type' => 'info',
                        'title' => 'Devoluções em andamento',
                        'description' => $formatInt($devolucoesEmAndamento).' devoluções aguardando etapas',
                        'count' => (int) $devolucoesEmAndamento,
                    ];
                }

                // Ações rápidas
                $actions = [
                    [
                        'label' => 'Ver estoque de imobilizados',
                        'url' => route('admin.estoqueimobilizado.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V3zm3 3h2v2H6V6zm0 4h2v2H6v-2zm4-4h2v2h-2V6zm0 4h2v2h-2v-2z" clip-rule="evenodd"/></svg>',
                    ],
                    [
                        'label' => 'Transferências de veículos',
                        'url' => route('admin.transfimobilizadoveiculo.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M3 10h14M10 3l3 3-3 3M10 17l3-3-3-3"/></svg>',
                    ],
                    [
                        'label' => 'Devoluções de veículos',
                        'url' => route('admin.devolucaoimobilizadoveiculo.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M7 7h6v6H7z"/></svg>',
                    ],
                ];

                // Cor baseada na severidade
                $color = 'teal';
                if ($transfEmAndamento >= 20) {
                    $color = 'red';
                } elseif ($transfEmAndamento > 0 || $devolucoesEmAndamento > 0) {
                    $color = 'orange';
                }

                return [
                    'title' => 'Imobilizados',
                    'color' => $color,
                    'route' => route('admin.estoqueimobilizado.index'),
                    'icon' => 'icons.modules.imobilizados',
                    'metrics' => [
                        ['value' => $formatInt($qtdEstoque), 'label' => 'Qtd em estoque'],
                        ['value' => $formatCurrency($valorEstoque), 'label' => 'Valor total'],
                        ['value' => $formatInt($transfEmAndamento), 'label' => 'Transferências em andamento'],
                        ['value' => $formatInt($devolucoesEmAndamento), 'label' => 'Devoluções em andamento'],
                    ],
                    'alerts' => $alerts,
                    'actions' => $actions,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados do módulo de Imobilizados: '.$e->getMessage());

            return [
                'title' => 'Imobilizados',
                'color' => 'gray',
                'route' => route('admin.estoqueimobilizado.index'),
                'icon' => 'icons.modules.imobilizados',
                'alerts' => [[
                    'type' => 'warning',
                    'title' => 'Erro ao carregar dados',
                    'description' => 'Não foi possível carregar as informações do módulo',
                    'count' => 1,
                ]],
                'metrics' => [],
                'actions' => [],
            ];
        }
    }

    private function getVeiculosData()
    {
        try {
            return Cache::remember('dashboard_veiculos_card', now()->addMinutes(15), function () {
                $formatInt = fn ($v) => number_format((int) $v, 0, ',', '.');

                // Totais principais
                $ativos = \App\Models\Veiculo::query()
                    ->where('situacao_veiculo', true)
                    ->count();

                // Veículos com OS abertas (distintos por placa, já que a view não expõe id_veiculo)
                $emManutencao = \App\Models\v_manutencao_abertas::query()
                    ->distinct('placa')
                    ->count('placa');

                // Disponibilidade
                $disponibilidade = 0.0;
                if ($ativos > 0) {
                    $disponibilidade = max(0.0, ($ativos - $emManutencao) / max(1, $ativos));
                }
                $formatPercent = fn ($p) => number_format($p * 100, 1, ',', '.').'%';

                // IPVA pendente ou parcial (registros ativos)
                $ipvaPendentes = \App\Models\IpvaVeiculo::query()
                    ->whereIn('status_ipva', ['PENDENTE', 'PARCIAL'])
                    ->ativos()
                    ->count();

                // Licenciamento a vencer
                // Tenta usar a coluna 'situacao' = 'A Vencer'; em falha (ex.: coluna ausente), usa fallback por campos de vencimento/pagamento
                try {
                    $licAvencer = \App\Models\LicenciamentoVeiculo::query()
                        ->where('situacao', 'A Vencer')
                        ->ativos()
                        ->count();
                } catch (\Throwable $e) {
                    // Fallback: registros ativos sem pagamento e com data de vencimento definida (considerados "a vencer")
                    $licAvencer = \App\Models\LicenciamentoVeiculo::query()
                        ->whereNull('valor_pago_licenciamento')
                        ->whereNotNull('data_vencimento')
                        ->ativos()
                        ->count();
                }

                // Alertas
                $alerts = [];
                if ($emManutencao > 0) {
                    $alerts[] = [
                        'type' => $emManutencao >= 10 ? 'danger' : 'warning',
                        'title' => 'Veículos em manutenção',
                        'description' => $formatInt($emManutencao).' veículos com OS abertas',
                        'count' => (int) $emManutencao,
                    ];
                }
                if ($ipvaPendentes > 0) {
                    $alerts[] = [
                        'type' => $ipvaPendentes >= 50 ? 'danger' : 'info',
                        'title' => 'IPVA pendente/parcial',
                        'description' => $formatInt($ipvaPendentes).' veículos com IPVA não quitado',
                        'count' => (int) $ipvaPendentes,
                    ];
                }
                if ($licAvencer > 0) {
                    $alerts[] = [
                        'type' => 'info',
                        'title' => 'Licenciamento a vencer',
                        'description' => $formatInt($licAvencer).' registros com situação "A Vencer"',
                        'count' => (int) $licAvencer,
                    ];
                }

                // Ações rápidas
                $actions = [
                    [
                        'label' => 'Gerir Veículos',
                        'url' => route('admin.veiculos.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M3 7h14l-1 8H4L3 7zm3-3h8l1 3H5l1-3z"/></svg>',
                    ],
                    [
                        'label' => 'IPVA',
                        'url' => route('admin.ipvaveiculos.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h7l5-5V5a2 2 0 00-2-2H4z"/></svg>',
                    ],
                    [
                        'label' => 'Licenciamentos',
                        'url' => route('admin.licenciamentoveiculos.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h14a1 1 0 010 2H3a1 1 0 01-1-1z"/></svg>',
                    ],
                ];

                // Cor baseada em severidade
                $color = 'blue';
                if ($emManutencao >= 10 || $ipvaPendentes >= 50) {
                    $color = 'red';
                } elseif ($emManutencao > 0 || $ipvaPendentes > 0 || $licAvencer > 0) {
                    $color = 'orange';
                }

                return [
                    'title' => 'Veículos',
                    'color' => $color,
                    'route' => route('admin.veiculos.index'),
                    'icon' => 'icons.modules.veiculos',
                    'metrics' => [
                        ['value' => $formatInt($ativos), 'label' => 'Frota ativa'],
                        ['value' => $formatInt($emManutencao), 'label' => 'Em manutenção'],
                        ['value' => $formatPercent($disponibilidade), 'label' => 'Disponibilidade'],
                    ],
                    'alerts' => $alerts,
                    'actions' => $actions,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados do módulo de Veículos: '.$e->getMessage());

            return [
                'title' => 'Veículos',
                'color' => 'gray',
                'route' => route('admin.veiculos.index'),
                'icon' => 'icons.modules.veiculos',
                'alerts' => [[
                    'type' => 'warning',
                    'title' => 'Erro ao carregar dados',
                    'description' => 'Não foi possível carregar as informações do módulo',
                    'count' => 1,
                ]],
                'metrics' => [],
                'actions' => [],
            ];
        }
    }

    private function getManutencaoData()
    {
        try {
            return Cache::remember('dashboard_manutencao_card', now()->addMinutes(15), function () {
                // Totais a partir da view v_manutencao_abertas (somente OS abertas)
                $totalAbertas = \App\Models\v_manutencao_abertas::query()->count();
                $preventivas = \App\Models\v_manutencao_abertas::query()
                    ->where('descricao_tipo_ordem', 'Ordem de Serviço Preventiva')
                    ->count();
                // Considera demais tipos como corretivas quando não são preventivas (ajuste se houver descrição específica)
                $corretivas = max(0, $totalAbertas - $preventivas);

                // Atrasadas: data_previsao_saida passada
                $atrasadas = \App\Models\v_manutencao_abertas::query()
                    ->whereNotNull('data_previsao_saida')
                    ->where('data_previsao_saida', '<', now())
                    ->count();

                // Custo mensal: soma de NF de OS no mês corrente (valor_liquido_nf)
                $valorMensal = (float) \App\Models\NfOrdemServico::query()
                    ->whereNotNull('data_emissao_nf')
                    ->where('data_emissao_nf', '>=', now()->startOfMonth())
                    ->sum('valor_liquido_nf');

                $formatCurrency = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
                $formatInt = fn ($v) => number_format((int) $v, 0, ',', '.');

                // Alertas
                $alerts = [];
                if ($atrasadas > 0) {
                    $alerts[] = [
                        'type' => $atrasadas >= 10 ? 'danger' : 'warning',
                        'title' => 'Manutenções Atrasadas',
                        'description' => $formatInt($atrasadas).' ordens de serviço estão atrasadas',
                        'count' => (int) $atrasadas,
                    ];
                }

                // Ações rápidas
                $actions = [
                    [
                        'label' => 'Monitorar Manutenções',
                        'url' => route('admin.monitoramentoDasManutencoes.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v4h2v-6a1 1 0 011-1h2a1 1 0 011 1v6h2v-8a1 1 0 011-1h2a1 1 0 011 1v10H2V11z"/></svg>',
                    ],
                    [
                        'label' => 'Ordens de Serviço',
                        'url' => route('admin.ordemservicos.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h7l5-5V5a2 2 0 00-2-2H4z"/></svg>',
                    ],
                ];

                // Cor do card baseada na severidade
                $color = 'orange';
                if ($atrasadas >= 10) {
                    $color = 'red';
                } elseif ($atrasadas > 0) {
                    $color = 'amber';
                }

                return [
                    'title' => 'Manutenção',
                    'color' => $color,
                    'route' => route('admin.monitoramentoDasManutencoes.index'),
                    'icon' => 'icons.modules.manutencao',
                    'metrics' => [
                        ['value' => $formatInt($totalAbertas), 'label' => 'OS Abertas'],
                        ['value' => $formatInt($preventivas), 'label' => 'Preventivas'],
                        ['value' => $formatCurrency($valorMensal), 'label' => 'Custo Mensal'],
                    ],
                    'alerts' => $alerts,
                    'actions' => $actions,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados do módulo de Manutenção: '.$e->getMessage());

            return [
                'title' => 'Manutenção',
                'color' => 'gray',
                'route' => route('admin.monitoramentoDasManutencoes.index'),
                'icon' => 'icons.modules.manutencao',
                'alerts' => [[
                    'type' => 'warning',
                    'title' => 'Erro ao carregar dados',
                    'description' => 'Não foi possível carregar as informações do módulo',
                    'count' => 1,
                ]],
                'metrics' => [],
                'actions' => [],
            ];
        }
    }

    private function getPneusData()
    {
        try {
            return Cache::remember('dashboard_pneus_card', now()->addMinutes(15), function () {
                $formatInt = fn ($v) => number_format((int) $v, 0, ',', '.');

                // Contagens por status
                $total = \App\Models\Pneu::query()->count();
                $estoque = \App\Models\Pneu::query()->estoque()->count();
                $aplicados = \App\Models\Pneu::query()->aplicado()->count();
                $manutencao = \App\Models\Pneu::query()->manutencao()->count();
                $descarte = \App\Models\Pneu::query()->descarte()->count();

                // Alertas
                $alerts = [];
                if ($manutencao > 0) {
                    $alerts[] = [
                        'type' => $manutencao >= 50 ? 'danger' : 'warning',
                        'title' => 'Pneus em manutenção',
                        'description' => $formatInt($manutencao).' pneus estão em manutenção',
                        'count' => (int) $manutencao,
                    ];
                }
                // Estoque baixo proporcionalmente ao total (menos de 5% disponíveis)
                if ($total > 0 && $estoque / max(1, $total) < 0.05) {
                    $alerts[] = [
                        'type' => 'info',
                        'title' => 'Estoque de pneus baixo',
                        'description' => 'Apenas '.$formatInt($estoque).' em estoque',
                        'count' => (int) $estoque,
                    ];
                }
                if ($descarte > 0) {
                    $alerts[] = [
                        'type' => 'info',
                        'title' => 'Pneus aguardando descarte',
                        'description' => $formatInt($descarte).' pneus marcados para descarte',
                        'count' => (int) $descarte,
                    ];
                }

                // Ações rápidas
                $actions = [
                    [
                        'label' => 'Gerir Pneus',
                        'url' => route('admin.pneus.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zM9 6h2v5H9V6zm0 6h2v2H9v-2z"/></svg>',
                    ],
                    [
                        'label' => 'Manutenção de Pneus',
                        'url' => route('admin.manutencaopneus.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v4h2v-6a1 1 0 011-1h2a1 1 0 011 1v6h2v-8a1 1 0 011-1h2a1 1 0 011 1v10H2V11z"/></svg>',
                    ],
                    [
                        'label' => 'Calibragens',
                        'url' => route('admin.calibragempneus.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M3 3h14v14H3z"/></svg>',
                    ],
                    [
                        'label' => 'Movimentações',
                        'url' => route('admin.movimentacaopneus.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M3 10h14M10 3l3 3-3 3M10 17l3-3-3-3"/></svg>',
                    ],
                ];

                // Cor baseada na severidade
                $color = 'indigo';
                if ($manutencao >= 50) {
                    $color = 'red';
                } elseif ($manutencao > 0 || ($total > 0 && $estoque / max(1, $total) < 0.05)) {
                    $color = 'orange';
                }

                return [
                    'title' => 'Pneus',
                    'color' => $color,
                    'route' => route('admin.pneus.index'),
                    'icon' => 'icons.modules.pneus',
                    'metrics' => [
                        ['value' => $formatInt($total), 'label' => 'Total de pneus'],
                        ['value' => $formatInt($aplicados), 'label' => 'Aplicados'],
                        ['value' => $formatInt($manutencao), 'label' => 'Em manutenção'],
                        ['value' => $formatInt($estoque), 'label' => 'Em estoque'],
                    ],
                    'alerts' => $alerts,
                    'actions' => $actions,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados do módulo de Pneus: '.$e->getMessage());

            return [
                'title' => 'Pneus',
                'color' => 'gray',
                'route' => route('admin.pneus.index'),
                'icon' => 'icons.modules.pneus',
                'alerts' => [[
                    'type' => 'warning',
                    'title' => 'Erro ao carregar dados',
                    'description' => 'Não foi possível carregar as informações do módulo',
                    'count' => 1,
                ]],
                'metrics' => [],
                'actions' => [],
            ];
        }
    }

    private function getPessoalData()
    {
        try {
            return Cache::remember('dashboard_pessoal_card', now()->addMinutes(15), function () {
                // Contagens principais
                $totalPessoasAtivas = \App\Models\Pessoal::query()->where('ativo', true)->count();
                $totalFornecedoresAtivos = \App\Models\Fornecedor::query()->ativo()->count();

                // Contratos
                $contratosValidos = \App\Models\ContratoFornecedor::query()->validos()->count();
                $contratosVencendo = \App\Models\ContratoFornecedor::query()->proximosVencimento(30)->count();
                $contratosVencidos = \App\Models\ContratoFornecedor::query()->vencidos()->count();

                $formatInt = fn ($v) => number_format((int) $v, 0, ',', '.');

                // Alertas focados em contratos
                $alerts = [];
                if ($contratosVencidos > 0) {
                    $alerts[] = [
                        'type' => $contratosVencidos >= 10 ? 'danger' : 'warning',
                        'title' => 'Contratos vencidos',
                        'description' => $formatInt($contratosVencidos).' contratos estão vencidos',
                        'count' => (int) $contratosVencidos,
                    ];
                }
                if ($contratosVencendo > 0) {
                    $alerts[] = [
                        'type' => 'info',
                        'title' => 'Contratos a vencer (30 dias)',
                        'description' => $formatInt($contratosVencendo).' contratos vencem em até 30 dias',
                        'count' => (int) $contratosVencendo,
                    ];
                }

                // Ações rápidas
                $actions = [
                    [
                        'label' => 'Gerir Pessoas',
                        'url' => route('admin.pessoas.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M13 7a3 3 0 11-6 0 3 3 0 016 0z"/><path fill-rule="evenodd" d="M4 13a4 4 0 014-4h4a4 4 0 014 4v1H4v-1z" clip-rule="evenodd"/></svg>',
                    ],
                    [
                        'label' => 'Fornecedores',
                        'url' => route('admin.fornecedores.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V5z"/></svg>',
                    ],
                    [
                        'label' => 'Contratos',
                        'url' => route('admin.contratos.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h7l5-5V5a2 2 0 00-2-2H4z"/></svg>',
                    ],
                ];

                // Cor baseada na severidade
                $color = 'emerald';
                if ($contratosVencidos >= 10) {
                    $color = 'red';
                } elseif ($contratosVencidos > 0 || $contratosVencendo > 0) {
                    $color = 'orange';
                }

                return [
                    'title' => 'Pessoal',
                    'color' => $color,
                    'route' => route('admin.pessoas.index'),
                    'icon' => 'icons.modules.pessoal',
                    'metrics' => [
                        ['value' => $formatInt($totalPessoasAtivas), 'label' => 'Pessoas ativas'],
                        ['value' => $formatInt($totalFornecedoresAtivos), 'label' => 'Fornecedores ativos'],
                        ['value' => $formatInt($contratosValidos), 'label' => 'Contratos válidos'],
                    ],
                    'alerts' => $alerts,
                    'actions' => $actions,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados do módulo de Pessoal: '.$e->getMessage());

            return [
                'title' => 'Pessoal',
                'color' => 'gray',
                'route' => route('admin.pessoas.index'),
                'icon' => 'icons.modules.pessoal',
                'alerts' => [[
                    'type' => 'warning',
                    'title' => 'Erro ao carregar dados',
                    'description' => 'Não foi possível carregar as informações do módulo',
                    'count' => 1,
                ]],
                'metrics' => [],
                'actions' => [],
            ];
        }
    }

    private function getVencimentarioData()
    {
        try {
            return Cache::remember('dashboard_vencimentario_card', now()->addMinutes(30), function () {
                $formatInt = fn ($v) => number_format((int) $v, 0, ',', '.');
                $today = now()->startOfDay();
                $in15 = now()->addDays(15)->endOfDay();
                $in30 = now()->addDays(30)->endOfDay();

                // Cache das queries por mais tempo para evitar recalcular constantemente
                $cacheKey = 'vencimentario_queries_'.now()->format('Y-m-d-H');
                $results = Cache::remember($cacheKey, now()->addHours(2), function () use ($today, $in15, $in30) {

                    // CNH - usando raw queries para performance
                    $cnhVencidas = DB::table('smartec_cnh')
                        ->whereNotNull('vencimento')
                        ->where('vencimento', '<', $today)->count();
                    $cnhAVencer = DB::table('smartec_cnh')
                        ->whereNotNull('vencimento')
                        ->whereBetween('vencimento', [$today, $in30])->count();

                    // IPVA - usando raw queries
                    $ipvaVencidos = DB::table('smartec_vencimentos_ipva')
                        ->whereNotNull('boleto_cota_unica_vencimento')
                        ->where('boleto_cota_unica_vencimento', '<', $today)->count();
                    $ipvaAVencer = DB::table('smartec_vencimentos_ipva')
                        ->whereNotNull('boleto_cota_unica_vencimento')
                        ->whereBetween('boleto_cota_unica_vencimento', [$today, $in15])->count();

                    // Licenças - usando raw queries
                    $licencasVencidas = DB::table('v_smartec_licenca')
                        ->whereNotNull('datavencimento')
                        ->where('datavencimento', '<', $today)->count();
                    $licencasAVencer = DB::table('v_smartec_licenca')
                        ->whereNotNull('datavencimento')
                        ->whereBetween('datavencimento', [$today, $in30])->count();

                    // Cronotacógrafo - usando raw queries
                    $cronoVencidos = DB::table('smartec_cronotacografo')
                        ->whereNotNull('vencimento')
                        ->where('vencimento', '<', $today)->count();
                    $cronoAVencer = DB::table('smartec_cronotacografo')
                        ->whereNotNull('vencimento')
                        ->whereBetween('vencimento', [$today, $in30])->count();

                    // Multas - usando raw queries
                    $multasVencidas = DB::table('v_smartec_multas_sne_detran')
                        ->where('confirmacao_pagamento', false)
                        ->whereNotNull('boleto_vencimento')
                        ->where('boleto_vencimento', '<', $today)->count();
                    $multasAVencer = DB::table('v_smartec_multas_sne_detran')
                        ->where('confirmacao_pagamento', false)
                        ->whereNotNull('boleto_vencimento')
                        ->whereBetween('boleto_vencimento', [$today, $in15])->count();

                    // Notificações - usando raw queries
                    $notifVencidas = DB::table('v_smartec_notificacoes_sne_detran')
                        ->where('confirmacao_pagamento', false)
                        ->whereNotNull('boleto_vencimento')
                        ->where('boleto_vencimento', '<', $today)->count();
                    $notifAVencer = DB::table('v_smartec_notificacoes_sne_detran')
                        ->where('confirmacao_pagamento', false)
                        ->whereNotNull('boleto_vencimento')
                        ->whereBetween('boleto_vencimento', [$today, $in15])->count();

                    return compact('cnhVencidas', 'cnhAVencer', 'ipvaVencidos', 'ipvaAVencer',
                        'licencasVencidas', 'licencasAVencer', 'cronoVencidos', 'cronoAVencer',
                        'multasVencidas', 'multasAVencer', 'notifVencidas', 'notifAVencer');
                });

                // Extrair valores do cache
                extract($results);

                $criticos = $cnhVencidas + $ipvaVencidos + $licencasVencidas + $cronoVencidos + $multasVencidas + $notifVencidas;

                $alerts = [];
                if ($criticos > 0) {
                    $alerts[] = [
                        'type' => 'danger',
                        'title' => 'Itens vencidos',
                        'description' => $formatInt($criticos).' itens com vencimento expirado',
                        'count' => (int) $criticos,
                    ];
                }
                $aVencerTotal = $cnhAVencer + $ipvaAVencer + $licencasAVencer + $cronoAVencer + $multasAVencer + $notifAVencer;
                if ($aVencerTotal > 0) {
                    $alerts[] = [
                        'type' => 'warning',
                        'title' => 'Itens a vencer',
                        'description' => $formatInt($aVencerTotal).' itens vencem em breve',
                        'count' => (int) $aVencerTotal,
                    ];
                }

                $actions = [
                    ['label' => 'Licenciamentos', 'url' => route('admin.licenciamentos.index'), 'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3h12v14H4z"/></svg>'],
                    ['label' => 'IPVA', 'url' => route('admin.listagemipva.index'), 'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16z"/></svg>'],
                    ['label' => 'Multas', 'url' => route('admin.listagemmultas.index'), 'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11h16v2H2z"/></svg>'],
                    ['label' => 'Notificações', 'url' => route('admin.listagemnotificacoes.index'), 'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2l6 6-6 6-6-6z"/></svg>'],
                    ['label' => 'Licenças', 'url' => route('admin.controlelicencavencimentario.index'), 'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M3 3h14v4H3z"/></svg>'],
                    ['label' => 'Cronotacógrafo', 'url' => route('admin.cronotacografovencimentario.index'), 'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="8"/></svg>'],
                    ['label' => 'Condutores', 'url' => route('admin.condutores.index'), 'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a4 4 0 100-8 4 4 0 000 8z"/></svg>'],
                    ['label' => 'Cadastro Veículos', 'url' => route('admin.cadastroveiculovencimentario.index'), 'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M3 10h14v6H3z"/></svg>'],
                ];

                $color = $criticos > 0 ? 'red' : ($aVencerTotal > 0 ? 'amber' : 'indigo');

                return [
                    'title' => 'Vencimentário',
                    'color' => $color,
                    'route' => route('admin.controlelicencavencimentario.index'),
                    'icon' => 'icons.modules.vencimentario',
                    'metrics' => [
                        ['value' => $formatInt($criticos), 'label' => 'Vencidos'],
                        ['value' => $formatInt($aVencerTotal), 'label' => 'A vencer'],
                        ['value' => $formatInt($ipvaVencidos), 'label' => 'IPVA vencidos'],
                    ],
                    'alerts' => $alerts,
                    'actions' => $actions,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados do módulo de Vencimentário: '.$e->getMessage());

            return [
                'title' => 'Vencimentário',
                'color' => 'gray',
                'route' => route('admin.controlelicencavencimentario.index'),
                'icon' => 'icons.modules.vencimentario',
                'alerts' => [[
                    'type' => 'warning',
                    'title' => 'Erro ao carregar dados',
                    'description' => 'Não foi possível carregar as informações do módulo',
                    'count' => 1,
                ]],
                'metrics' => [],
                'actions' => [],
            ];
        }
    }

    private function getSinistrosData()
    {
        try {
            return Cache::remember('dashboard_sinistros_card', now()->addMinutes(15), function () {
                $formatInt = fn ($v) => number_format((int) $v, 0, ',', '.');
                $formatMoney = function ($v) {
                    $num = (float) $v;

                    return 'R$ '.number_format($num, 2, ',', '.');
                };

                // Contagens por status
                $total = \App\Models\Sinistro::query()->count();
                $emAndamento = \App\Models\Sinistro::query()->where('status', 'Em Andamento')->count();
                $finalizadas = \App\Models\Sinistro::query()->where('status', 'Finalizada')->count();

                // Valor estimado pendente (considera valor_apagar dos não finalizados)
                $valorPendente = (float) (\App\Models\Sinistro::query()
                    ->where('status', '!=', 'Finalizada')
                    ->sum('valor_apagar'));

                // Prazo vencido: data_sinistro + prazo_em_dias < hoje, e não finalizado
                $vencidos = \App\Models\Sinistro::query()
                    ->where(function ($q) {
                        $q->whereNotNull('data_sinistro')
                            ->whereNotNull('prazo_em_dias');
                    })
                    ->where('status', '!=', 'Finalizada')
                    ->whereRaw("(data_sinistro + (prazo_em_dias || ' days')::interval) < now()")
                    ->count();

                // Alertas
                $alerts = [];
                if ($emAndamento > 0) {
                    $alerts[] = [
                        'type' => $emAndamento >= 20 ? 'warning' : 'info',
                        'title' => 'Sinistros em andamento',
                        'description' => $formatInt($emAndamento).' sinistros ativos aguardando conclusão',
                        'count' => (int) $emAndamento,
                    ];
                }
                if ($vencidos > 0) {
                    $alerts[] = [
                        'type' => $vencidos >= 10 ? 'danger' : 'warning',
                        'title' => 'Prazos vencidos',
                        'description' => $formatInt($vencidos).' sinistros com prazo estourado',
                        'count' => (int) $vencidos,
                    ];
                }

                // Ações rápidas
                $actions = [
                    [
                        'label' => 'Gerir Sinistros',
                        'url' => route('admin.sinistros.index'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V9l-5-6H4z"/></svg>',
                    ],
                    [
                        'label' => 'Novo Sinistro',
                        'url' => route('admin.sinistros.create'),
                        'icon' => '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>',
                    ],
                ];

                // Cor baseada na severidade
                $color = 'indigo';
                if ($vencidos >= 10) {
                    $color = 'red';
                } elseif ($vencidos > 0 || $emAndamento > 0) {
                    $color = 'orange';
                }

                return [
                    'title' => 'Sinistros',
                    'color' => $color,
                    'route' => route('admin.sinistros.index'),
                    'icon' => 'icons.modules.sinistros',
                    'metrics' => [
                        ['value' => $formatInt($total), 'label' => 'Total de sinistros'],
                        ['value' => $formatInt($emAndamento), 'label' => 'Em andamento'],
                        ['value' => $formatInt($finalizadas), 'label' => 'Finalizadas'],
                        ['value' => $formatMoney($valorPendente), 'label' => 'Valor pendente'],
                    ],
                    'alerts' => $alerts,
                    'actions' => $actions,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados do módulo de Sinistros: '.$e->getMessage());

            return [
                'title' => 'Sinistros',
                'color' => 'gray',
                'route' => route('admin.sinistros.index'),
                'icon' => 'icons.modules.sinistros',
                'alerts' => [[
                    'type' => 'warning',
                    'title' => 'Erro ao carregar dados',
                    'description' => 'Não foi possível carregar as informações do módulo',
                    'count' => 1,
                ]],
                'metrics' => [],
                'actions' => [],
            ];
        }
    }

    /**
     * API endpoint para atualizar horário em tempo real
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentTime()
    {
        return response()->json([
            'time' => now()->format('H:i:s'),
            'date' => now()->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y'),
            'timestamp' => now()->timestamp,
        ]);
    }
}
