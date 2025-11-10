<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Módulos do Sistema de Gestão de Frota
    |--------------------------------------------------------------------------
    |
    | Configuração de todos os módulos disponíveis no sistema, organizados
    | por status de desenvolvimento e implementação.
    |
    */

    'modules' => [
        // PRODUÇÃO - Módulos totalmente funcionais
        [
            'name' => 'Abastecimento',
            'description' => 'Controle de combustível da frota',
            'status' => 'production',
            'icon' => 'fuel',
            'route' => 'admin.abastecimentomanual.index',
            'permission' => 'ver_abastecimento',
            'order' => 1,
        ],

        // HOMOLOGAÇÃO - Módulos em teste
        [
            'name' => 'Configurações',
            'description' => 'Filiais e configurações do sistema',
            'status' => 'production',
            'icon' => 'cog',
            'route' => 'admin.configuracoes.index',
            'permission' => 'ver_configuracoes',
            'order' => 2,
        ],
        [
            'name' => 'Imobilizados',
            'description' => 'Gestão de ativos imobilizados',
            'status' => 'production',
            'icon' => 'building-office',
            'route' => 'admin.imobilizados.index',
            'permission' => 'ver_relacaoimobilizado',
            'order' => 3,
        ],
        [
            'name' => 'Manutenção',
            'description' => 'Controle de manutenções preventivas e corretivas',
            'status' => 'production',
            'icon' => 'wrench-screwdriver',
            'route' => 'admin.manutencao.index',
            'permission' => 'ver_manutencao',
            'order' => 4,
        ],
        [
            'name' => 'Pessoal',
            'description' => 'Pessoas e fornecedores',
            'status' => 'production',
            'icon' => 'users',
            'route' => 'admin.pessoal.index',
            'permission' => 'ver_pessoal',
            'order' => 5,
        ],
        [
            'name' => 'Pneus',
            'description' => 'Gestão e controle de pneus',
            'status' => 'production',
            'icon' => 'circle',
            'route' => 'admin.pneus.index',
            'permission' => 'ver_pneu',
            'order' => 6,
        ],
        [
            'name' => 'Sinistros',
            'description' => 'Registros de sinistros e ocorrências',
            'status' => 'production',
            'icon' => 'exclamation-triangle',
            'route' => 'admin.sinistros.index',
            'permission' => 'ver_sinistro',
            'order' => 7,
        ],
        [
            'name' => 'Veículos',
            'description' => 'Gestão da frota de veículos',
            'status' => 'production',
            'icon' => 'truck',
            'route' => 'admin.veiculos.index',
            'permission' => 'ver_veiculo',
            'order' => 8,
        ],

        // DESENVOLVIMENTO - Módulos em construção
        [
            'name' => 'Compras',
            'description' => 'Sistema de compras e solicitações',
            'status' => 'development',
            'icon' => 'shopping-cart',
            'route' => 'admin.compras.index',
            'permission' => 'ver_solicitacaocompras',
            'order' => 9,
        ],
        [
            'name' => 'Checklist',
            'description' => 'Checklists de vistoria e inspeção',
            'status' => 'production',
            'icon' => 'clipboard-document-check',
            'route' => 'admin.checklist.index',
            'permission' => 'ver_checklist',
            'order' => 10,
        ],
        [
            'name' => 'Estoque',
            'description' => 'Controle de estoque de peças e materiais',
            'status' => 'production',
            'icon' => 'archive-box',
            'route' => 'admin.estoque.index',
            'permission' => 'ver_estoque',
            'order' => 11,
        ],
        [
            'name' => 'Vencimentários',
            'description' => 'Controle de documentos e certificados',
            'status' => 'production',
            'icon' => 'calendar-days',
            'route' => 'admin.vencimentarios.index',
            'permission' => 'ver_certificadoveiculos',
            'order' => 12,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Status
    |--------------------------------------------------------------------------
    |
    | Definições visuais para cada status de módulo
    |
    */

    'status_config' => [
        'production' => [
            'label' => 'Produção',
            'bg_color' => 'bg-green-50',
            'border_color' => 'border-green-200',
            'text_color' => 'text-green-800',
            'icon_bg' => 'bg-green-100',
            'icon_color' => 'text-green-600',
            'hover' => 'hover:shadow-lg hover:bg-green-100',
            'badge_bg' => 'bg-green-100',
            'badge_text' => 'text-green-800',
        ],
        'staging' => [
            'label' => 'Homologação',
            'bg_color' => 'bg-gray-50',
            'border_color' => 'border-gray-200',
            'text_color' => 'text-gray-700',
            'icon_bg' => 'bg-gray-100',
            'icon_color' => 'text-gray-600',
            'hover' => 'hover:shadow-md hover:bg-gray-100',
            'badge_bg' => 'bg-gray-100',
            'badge_text' => 'text-gray-700',
        ],
        'development' => [
            'label' => 'Desenvolvimento',
            'bg_color' => 'bg-blue-50',
            'border_color' => 'border-blue-200',
            'text_color' => 'text-blue-700',
            'icon_bg' => 'bg-blue-100',
            'icon_color' => 'text-blue-600',
            'hover' => 'hover:shadow-md hover:bg-blue-100',
            'badge_bg' => 'bg-blue-100',
            'badge_text' => 'text-blue-700',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ícones dos Módulos
    |--------------------------------------------------------------------------
    |
    | Mapeamento de ícones Heroicons para cada tipo de módulo
    |
    */

    'icons' => [
        'fuel' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119.993zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
        'shopping-cart' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z',
        'cog' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'clipboard-document-check' => 'M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75',
        'archive-box' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
        'building-office' => 'M3.75 21h16.5M4.5 3h15l-.75 18h-13.5L4.5 3zM6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M15 6.75h.75m-.75 3h.75m-.75 3h.75M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
        'wrench-screwdriver' => 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z',
        'users' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'circle' => 'M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'exclamation-triangle' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 15.126zM12 15.75h.007v.008H12v-.008z',
        'truck' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m6.75 4.5v-3a1.875 1.875 0 011.875-1.875h4.5V12m0 6.75h.75a.375.375 0 00.375-.375V12m-1.125 0h1.125m-1.125 0v1.5m0-1.5v-1.5a1.875 1.875 0 011.875-1.875M3 7.5a2.25 2.25 0 012.25-2.25h13.5a2.25 2.25 0 012.25 2.25v6.75a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 14.25V7.5z',
        'calendar-days' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5m-9-6h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 4.5h.008v.008H12v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
    ],
];
