<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;

/**
 * Serviço para gerenciar permissões baseadas em módulos
 *
 * Estrutura hierárquica:
 * - Módulo principal (ex: Abastecimentos)
 *   - Permissão de acesso ao módulo
 *   - Permissões granulares por funcionalidade
 */
class ModulePermissionService
{
    /**
     * Estrutura completa de módulos e suas permissões
     *
     * Cada módulo possui:
     * - nome: nome técnico do módulo
     * - nome_amigavel: nome exibido ao usuário
     * - descricao: descrição do módulo
     * - permissoes: array de permissões com estrutura hierárquica
     */
    public static function getModulesStructure(): array
    {
        return [
            'abastecimentos' => [
                'nome' => 'abastecimentos',
                'nome_amigavel' => 'Abastecimentos',
                'descricao' => 'Módulo de controle e gerenciamento de abastecimentos',
                'icone' => 'fuel',
                'ordem' => 1,
                'permissoes' => [
                    // Permissão de acesso ao módulo
                    'acessar_modulo' => [
                        'nome' => 'abastecimentos.acessar_modulo',
                        'nome_amigavel' => 'Acessar Módulo de Abastecimentos',
                        'descricao' => 'Permite visualizar e acessar o módulo de abastecimentos',
                        'obrigatoria' => true,
                    ],

                    // Funcionalidades principais
                    'abastecimento_manual' => [
                        'nome' => 'abastecimentos.abastecimento_manual',
                        'nome_amigavel' => 'Abastecimento Manual',
                        'descricao' => 'Visualizar, criar, editar e excluir abastecimentos manuais',
                        'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
                    ],
                    'listar_abastecimentos' => [
                        'nome' => 'abastecimentos.listar',
                        'nome_amigavel' => 'Listar Abastecimentos (TruckPag, ATS e Manual)',
                        'descricao' => 'Visualizar listagem de todos os abastecimentos integrados',
                        'acoes' => ['visualizar', 'exportar'],
                    ],
                    'ajuste_km' => [
                        'nome' => 'abastecimentos.ajuste_km',
                        'nome_amigavel' => 'Ajuste de KM Manual',
                        'descricao' => 'Gerenciar ajustes de quilometragem de abastecimentos',
                        'acoes' => ['visualizar', 'criar', 'editar'],
                    ],
                    'encerrantes' => [
                        'nome' => 'abastecimentos.encerrantes',
                        'nome_amigavel' => 'Encerrantes de Abastecimento',
                        'descricao' => 'Gerenciar encerrantes de bombas de combustível',
                        'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
                    ],
                    'estoque_combustivel' => [
                        'nome' => 'abastecimentos.estoque_combustivel',
                        'nome_amigavel' => 'Saldo de Estoque de Combustível',
                        'descricao' => 'Visualizar e gerenciar estoque de combustível',
                        'acoes' => ['visualizar', 'atualizar'],
                    ],
                    'cadastros' => [
                        'nome' => 'abastecimentos.cadastros',
                        'nome_amigavel' => 'Cadastros (Bombas, Tanques, etc)',
                        'descricao' => 'Gerenciar cadastros auxiliares do módulo',
                        'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
                    ],
                    'inconsistencias' => [
                        'nome' => 'abastecimentos.inconsistencias',
                        'nome_amigavel' => 'Inconsistências',
                        'descricao' => 'Visualizar e tratar inconsistências de integração',
                        'acoes' => ['visualizar', 'editar'],
                    ],
                    'reprocessar_integracoes' => [
                        'nome' => 'abastecimentos.reprocessar',
                        'nome_amigavel' => 'Reprocessar Integrações',
                        'descricao' => 'Reprocessar integrações ATS e TruckPag',
                        'acoes' => ['executar'],
                    ],
                    'relatorios' => [
                        'nome' => 'abastecimentos.relatorios',
                        'nome_amigavel' => 'Relatórios de Abastecimentos',
                        'descricao' => 'Acessar todos os relatórios do módulo',
                        'acoes' => ['visualizar', 'exportar'],
                    ],
                ],
            ],

            'compras' => [
                'nome' => 'compras',
                'nome_amigavel' => 'Compras',
                'descricao' => 'Módulo de gerenciamento de compras e solicitações',
                'icone' => 'shopping-cart',
                'ordem' => 2,
                'permissoes' => [
                    'acessar_modulo' => [
                        'nome' => 'compras.acessar_modulo',
                        'nome_amigavel' => 'Acessar Módulo de Compras',
                        'descricao' => 'Permite visualizar e acessar o módulo de compras',
                        'obrigatoria' => true,
                    ],
                    'dashboard' => [
                        'nome' => 'compras.dashboard',
                        'nome_amigavel' => 'Dashboard de Compras',
                        'descricao' => 'Visualizar painel de controle de compras',
                        'acoes' => ['visualizar'],
                    ],
                    'relatorios' => [
                        'nome' => 'compras.relatorios',
                        'nome_amigavel' => 'Relatórios de Compras',
                        'descricao' => 'Acessar todos os relatórios do módulo',
                        'acoes' => ['visualizar', 'exportar'],
                    ],
                ],
            ],

            'configuracoes' => [
                'nome' => 'configuracoes',
                'nome_amigavel' => 'Configurações',
                'descricao' => 'Módulo de configurações do sistema',
                'icone' => 'settings',
                'ordem' => 3,
                'permissoes' => [
                    'acessar_modulo' => [
                        'nome' => 'configuracoes.acessar_modulo',
                        'nome_amigavel' => 'Acessar Configurações',
                        'descricao' => 'Permite acessar configurações do sistema',
                        'obrigatoria' => true,
                    ],
                    'usuarios' => [
                        'nome' => 'configuracoes.usuarios',
                        'nome_amigavel' => 'Gerenciar Usuários',
                        'descricao' => 'Gerenciar usuários do sistema',
                        'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
                    ],
                    'permissoes' => [
                        'nome' => 'configuracoes.permissoes',
                        'nome_amigavel' => 'Gerenciar Permissões',
                        'descricao' => 'Gerenciar permissões e cargos',
                        'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
                    ],
                    'empresas_filiais' => [
                        'nome' => 'configuracoes.empresas_filiais',
                        'nome_amigavel' => 'Empresas e Filiais',
                        'descricao' => 'Gerenciar empresas e filiais',
                        'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
                    ],
                    'fornecedores' => [
                        'nome' => 'configuracoes.fornecedores',
                        'nome_amigavel' => 'Fornecedores',
                        'descricao' => 'Gerenciar cadastro de fornecedores',
                        'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
                    ],
                ],
            ],

            'checklist' => [
                'nome' => 'checklist',
                'nome_amigavel' => 'Checklist',
                'descricao' => 'Módulo de checklist de veículos',
                'icone' => 'clipboard-check',
                'ordem' => 4,
                'permissoes' => [
                    'acessar_modulo' => [
                        'nome' => 'checklist.acessar_modulo',
                        'nome_amigavel' => 'Acessar Módulo de Checklist',
                        'descricao' => 'Permite acessar o módulo de checklist',
                        'obrigatoria' => true,
                    ],
                ],
            ],

            'estoque' => [
                'nome' => 'estoque',
                'nome_amigavel' => 'Estoque',
                'descricao' => 'Módulo de controle de estoque',
                'icone' => 'warehouse',
                'ordem' => 5,
                'permissoes' => [
                    'acessar_modulo' => [
                        'nome' => 'estoque.acessar_modulo',
                        'nome_amigavel' => 'Acessar Módulo de Estoque',
                        'descricao' => 'Permite acessar o módulo de estoque',
                        'obrigatoria' => true,
                    ],
                    'produtos' => [
                        'nome' => 'estoque.produtos',
                        'nome_amigavel' => 'Gerenciar Produtos',
                        'descricao' => 'Gerenciar cadastro de produtos',
                        'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
                    ],
                    'movimentacao' => [
                        'nome' => 'estoque.movimentacao',
                        'nome_amigavel' => 'Movimentação de Estoque',
                        'descricao' => 'Gerenciar entradas e saídas de estoque',
                        'acoes' => ['visualizar', 'criar', 'editar'],
                    ],
                    'relatorios' => [
                        'nome' => 'estoque.relatorios',
                        'nome_amigavel' => 'Relatórios de Estoque',
                        'descricao' => 'Acessar relatórios de estoque',
                        'acoes' => ['visualizar', 'exportar'],
                    ],
                ],
            ],

            'imobilizados' => [
                'nome' => 'imobilizados',
                'nome_amigavel' => 'Imobilizados',
                'descricao' => 'Módulo de controle de ativos imobilizados',
                'icone' => 'briefcase',
                'ordem' => 6,
                'permissoes' => [
                    'acessar_modulo' => [
                        'nome' => 'imobilizados.acessar_modulo',
                        'nome_amigavel' => 'Acessar Módulo de Imobilizados',
                        'descricao' => 'Permite acessar o módulo de imobilizados',
                        'obrigatoria' => true,
                    ],
                ],
            ],

            'manutencao' => [
                'nome' => 'manutencao',
                'nome_amigavel' => 'Manutenção',
                'descricao' => 'Módulo de controle de manutenções',
                'icone' => 'tools',
                'ordem' => 7,
                'permissoes' => [
                    'acessar_modulo' => [
                        'nome' => 'manutencao.acessar_modulo',
                        'nome_amigavel' => 'Acessar Módulo de Manutenção',
                        'descricao' => 'Permite acessar o módulo de manutenção',
                        'obrigatoria' => true,
                    ],
                    'ordem_servico' => [
                        'nome' => 'manutencao.ordem_servico',
                        'nome_amigavel' => 'Ordens de Serviço',
                        'descricao' => 'Gerenciar ordens de serviço',
                        'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
                    ],
                    'preventivas' => [
                        'nome' => 'manutencao.preventivas',
                        'nome_amigavel' => 'Manutenções Preventivas',
                        'descricao' => 'Gerenciar manutenções preventivas',
                        'acoes' => ['visualizar', 'criar', 'editar'],
                    ],
                    'corretivas' => [
                        'nome' => 'manutencao.corretivas',
                        'nome_amigavel' => 'Manutenções Corretivas',
                        'descricao' => 'Gerenciar manutenções corretivas',
                        'acoes' => ['visualizar', 'criar', 'editar'],
                    ],
                    'relatorios' => [
                        'nome' => 'manutencao.relatorios',
                        'nome_amigavel' => 'Relatórios de Manutenção',
                        'descricao' => 'Acessar relatórios de manutenção',
                        'acoes' => ['visualizar', 'exportar'],
                    ],
                ],
            ],

            'pessoal' => [
                'nome' => 'pessoal',
                'nome_amigavel' => 'Pessoal',
                'descricao' => 'Módulo de gestão de pessoal e motoristas',
                'icone' => 'users',
                'ordem' => 8,
                'permissoes' => [
                    'acessar_modulo' => [
                        'nome' => 'pessoal.acessar_modulo',
                        'nome_amigavel' => 'Acessar Módulo de Pessoal',
                        'descricao' => 'Permite acessar o módulo de pessoal',
                        'obrigatoria' => true,
                    ],
                    'funcionarios' => [
                        'nome' => 'pessoal.funcionarios',
                        'nome_amigavel' => 'Gerenciar Funcionários',
                        'descricao' => 'Gerenciar cadastro de funcionários',
                        'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
                    ],
                    'motoristas' => [
                        'nome' => 'pessoal.motoristas',
                        'nome_amigavel' => 'Gerenciar Motoristas',
                        'descricao' => 'Gerenciar cadastro de motoristas',
                        'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
                    ],
                    'habilitacoes' => [
                        'nome' => 'pessoal.habilitacoes',
                        'nome_amigavel' => 'Controle de Habilitações',
                        'descricao' => 'Controlar habilitações de motoristas',
                        'acoes' => ['visualizar', 'criar', 'editar'],
                    ],
                    'relatorios' => [
                        'nome' => 'pessoal.relatorios',
                        'nome_amigavel' => 'Relatórios de Pessoal',
                        'descricao' => 'Acessar relatórios de pessoal',
                        'acoes' => ['visualizar', 'exportar'],
                    ],
                ],
            ],

            'pneus' => [
                'nome' => 'pneus',
                'nome_amigavel' => 'Pneus',
                'descricao' => 'Módulo de controle e gestão de pneus',
                'icone' => 'tire',
                'ordem' => 9,
                'permissoes' => [
                    'acessar_modulo' => [
                        'nome' => 'pneus.acessar_modulo',
                        'nome_amigavel' => 'Acessar Módulo de Pneus',
                        'descricao' => 'Permite acessar o módulo de pneus',
                        'obrigatoria' => true,
                    ],
                    'cadastro' => [
                        'nome' => 'pneus.cadastro',
                        'nome_amigavel' => 'Cadastro de Pneus',
                        'descricao' => 'Gerenciar cadastro de pneus',
                        'acoes' => ['visualizar', 'criar', 'editar'],
                    ],
                    'historico' => [
                        'nome' => 'pneus.historico',
                        'nome_amigavel' => 'Histórico de Vida dos Pneus',
                        'descricao' => 'Visualizar histórico completo de pneus',
                        'acoes' => ['visualizar'],
                    ],
                    'baixa' => [
                        'nome' => 'pneus.baixa',
                        'nome_amigavel' => 'Baixa de Pneus',
                        'descricao' => 'Realizar baixa/descarte de pneus',
                        'acoes' => ['visualizar', 'criar'],
                    ],
                    'calibragem' => [
                        'nome' => 'pneus.calibragem',
                        'nome_amigavel' => 'Calibragem de Pneus',
                        'descricao' => 'Registrar calibragem de pneus',
                        'acoes' => ['visualizar', 'criar'],
                    ],
                    'movimentacao' => [
                        'nome' => 'pneus.movimentacao',
                        'nome_amigavel' => 'Movimentação de Pneus',
                        'descricao' => 'Gerenciar movimentação de pneus',
                        'acoes' => ['visualizar', 'criar', 'editar'],
                    ],
                    'transferencia' => [
                        'nome' => 'pneus.transferencia',
                        'nome_amigavel' => 'Transferência de Pneus',
                        'descricao' => 'Transferir pneus entre filiais',
                        'acoes' => ['visualizar', 'criar'],
                    ],
                    'venda' => [
                        'nome' => 'pneus.venda',
                        'nome_amigavel' => 'Venda de Pneus',
                        'descricao' => 'Gerenciar vendas de pneus',
                        'acoes' => ['visualizar', 'criar', 'aprovar'],
                    ],
                    'relatorios' => [
                        'nome' => 'pneus.relatorios',
                        'nome_amigavel' => 'Relatórios de Pneus',
                        'descricao' => 'Acessar relatórios de pneus',
                        'acoes' => ['visualizar', 'exportar'],
                    ],
                ],
            ],

            'sinistro' => [
                'nome' => 'sinistro',
                'nome_amigavel' => 'Sinistros',
                'descricao' => 'Módulo de controle de sinistros',
                'icone' => 'alert-triangle',
                'ordem' => 10,
                'permissoes' => [
                    'acessar_modulo' => [
                        'nome' => 'sinistro.acessar_modulo',
                        'nome_amigavel' => 'Acessar Módulo de Sinistros',
                        'descricao' => 'Permite acessar o módulo de sinistros',
                        'obrigatoria' => true,
                    ],
                    'gerenciar' => [
                        'nome' => 'sinistro.gerenciar',
                        'nome_amigavel' => 'Gerenciar Sinistros',
                        'descricao' => 'Gerenciar registros de sinistros',
                        'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
                    ],
                    'relatorios' => [
                        'nome' => 'sinistro.relatorios',
                        'nome_amigavel' => 'Relatórios de Sinistros',
                        'descricao' => 'Acessar relatórios de sinistros',
                        'acoes' => ['visualizar', 'exportar'],
                    ],
                ],
            ],

            'veiculos' => [
                'nome' => 'veiculos',
                'nome_amigavel' => 'Veículos',
                'descricao' => 'Módulo de gerenciamento de veículos',
                'icone' => 'truck',
                'ordem' => 11,
                'permissoes' => [
                    'acessar_modulo' => [
                        'nome' => 'veiculos.acessar_modulo',
                        'nome_amigavel' => 'Acessar Módulo de Veículos',
                        'descricao' => 'Permite acessar o módulo de veículos',
                        'obrigatoria' => true,
                    ],
                    'cadastro' => [
                        'nome' => 'veiculos.cadastro',
                        'nome_amigavel' => 'Cadastro de Veículos',
                        'descricao' => 'Gerenciar cadastro de veículos',
                        'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
                    ],
                    'certificados' => [
                        'nome' => 'veiculos.certificados',
                        'nome_amigavel' => 'Certificados',
                        'descricao' => 'Gerenciar certificados de veículos',
                        'acoes' => ['visualizar', 'criar', 'editar'],
                    ],
                    'licencas' => [
                        'nome' => 'veiculos.licencas',
                        'nome_amigavel' => 'Controle de Licenças',
                        'descricao' => 'Gerenciar licenciamento, IPVA e seguro',
                        'acoes' => ['visualizar', 'criar', 'editar'],
                    ],
                    'multas' => [
                        'nome' => 'veiculos.multas',
                        'nome_amigavel' => 'Multas',
                        'descricao' => 'Gerenciar multas de veículos',
                        'acoes' => ['visualizar', 'criar', 'editar'],
                    ],
                    'relatorios' => [
                        'nome' => 'veiculos.relatorios',
                        'nome_amigavel' => 'Relatórios de Veículos',
                        'descricao' => 'Acessar relatórios de veículos',
                        'acoes' => ['visualizar', 'exportar'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Obter lista simples de módulos
     */
    public static function getModules(): array
    {
        $modules = self::getModulesStructure();
        return array_map(function ($module) {
            return [
                'nome' => $module['nome'],
                'nome_amigavel' => $module['nome_amigavel'],
                'descricao' => $module['descricao'],
                'icone' => $module['icone'] ?? null,
                'ordem' => $module['ordem'] ?? 999,
            ];
        }, $modules);
    }

    /**
     * Obter permissões de um módulo específico
     */
    public static function getModulePermissions(string $moduleName): array
    {
        $modules = self::getModulesStructure();
        return $modules[$moduleName]['permissoes'] ?? [];
    }

    /**
     * Gerar todas as permissões (expandindo ações)
     */
    public static function generateAllPermissions(): array
    {
        $allPermissions = [];
        $modules = self::getModulesStructure();

        foreach ($modules as $moduleKey => $module) {
            foreach ($module['permissoes'] as $permKey => $permission) {
                // Permissão base (obrigatória como acessar_modulo)
                if (isset($permission['obrigatoria']) && $permission['obrigatoria']) {
                    $allPermissions[] = [
                        'nome' => $permission['nome'],
                        'nome_amigavel' => $permission['nome_amigavel'],
                        'descricao' => $permission['descricao'],
                        'modulo' => $moduleKey,
                        'tipo' => 'acesso_modulo',
                    ];
                }
                // Permissões com ações
                elseif (isset($permission['acoes']) && is_array($permission['acoes'])) {
                    foreach ($permission['acoes'] as $acao) {
                        $nomePermissao = $permission['nome'] . '.' . $acao;
                        $allPermissions[] = [
                            'nome' => $nomePermissao,
                            'nome_amigavel' => ucfirst($acao) . ': ' . $permission['nome_amigavel'],
                            'descricao' => ucfirst($acao) . ' - ' . $permission['descricao'],
                            'modulo' => $moduleKey,
                            'funcionalidade' => $permKey,
                            'acao' => $acao,
                        ];
                    }
                }
            }
        }

        return $allPermissions;
    }

    /**
     * Criar todas as permissões no banco de dados
     */
    public static function syncPermissions(): array
    {
        $permissions = self::generateAllPermissions();
        $created = [];
        $existing = [];

        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate(
                ['name' => $perm['nome']],
                [
                    'guard_name' => 'web',
                    'description' => $perm['descricao'],
                ]
            );

            if ($permission->wasRecentlyCreated) {
                $created[] = $perm['nome'];
            } else {
                $existing[] = $perm['nome'];
            }
        }

        return [
            'created' => $created,
            'existing' => $existing,
            'total' => count($permissions),
        ];
    }

    /**
     * Obter permissões agrupadas por módulo (para exibição na interface)
     */
    public static function getPermissionsGroupedByModule(): array
    {
        $modules = self::getModulesStructure();
        $grouped = [];

        foreach ($modules as $moduleKey => $module) {
            $grouped[$moduleKey] = [
                'info' => [
                    'nome' => $module['nome'],
                    'nome_amigavel' => $module['nome_amigavel'],
                    'descricao' => $module['descricao'],
                    'icone' => $module['icone'] ?? null,
                    'ordem' => $module['ordem'] ?? 999,
                ],
                'permissoes' => [],
            ];

            foreach ($module['permissoes'] as $permKey => $permission) {
                $funcionalidade = [
                    'chave' => $permKey,
                    'nome_amigavel' => $permission['nome_amigavel'],
                    'descricao' => $permission['descricao'],
                    'permissoes' => [],
                ];

                if (isset($permission['obrigatoria']) && $permission['obrigatoria']) {
                    $funcionalidade['permissoes'][] = [
                        'nome' => $permission['nome'],
                        'nome_amigavel' => $permission['nome_amigavel'],
                        'obrigatoria' => true,
                    ];
                } elseif (isset($permission['acoes'])) {
                    foreach ($permission['acoes'] as $acao) {
                        $funcionalidade['permissoes'][] = [
                            'nome' => $permission['nome'] . '.' . $acao,
                            'nome_amigavel' => ucfirst($acao),
                            'acao' => $acao,
                        ];
                    }
                }

                $grouped[$moduleKey]['permissoes'][] = $funcionalidade;
            }
        }

        return $grouped;
    }
}