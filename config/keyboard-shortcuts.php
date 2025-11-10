<?php

return [
    // GRUPO 1-100: ABASTECIMENTOS
    'abastecimentos' => [
        'title' => 'Abastecimentos',
        'items' => [
            '1' => ['title' => 'Abastecimento Manual', 'url' => '/admin/abastecimentomanual'],
            '2' => ['title' => 'Abastecimentos (ATS/Truckpag/Manual)', 'url' => '/admin/abastecimentosatstruckpagmanual'],
            '3' => ['title' => 'Ajuste do Km Manual', 'url' => '/admin/ajustekm'],
            '4' => ['title' => 'Saldo Estoque Combustível', 'url' => '/admin/estoque-combustivel/dashboard'],
            '5' => ['title' => 'Bombas Abastecimento', 'url' => '/admin/bombas'],
            '6' => ['title' => 'Entrada por Aferição de Bomba', 'url' => '/admin/afericaobombas'],
            '7' => ['title' => 'Faturamento Abastecimento', 'url' => '/admin/abastecimentosfaturamento'],
            '8' => ['title' => 'Meta por Tipo de Equipamento', 'url' => '/admin/metatipoequipamentos'],
            '9' => ['title' => 'Recebimento Combustíveis', 'url' => '/admin/recebimentocombustiveis'],
            '10' => ['title' => 'Tanques', 'url' => '/admin/tanques'],
            '11' => ['title' => 'Valor de Combustível por Bomba', 'url' => '/admin/valorcombustiveis'],
            '12' => ['title' => 'Inconsistências', 'url' => '/admin/inconsistencias'],
        ],
    ],

    // GRUPO 101-200: CERTIFICADOS
    'certificados' => [
        'title' => 'Certificados',
        'items' => [
            '101' => ['title' => 'AETs - Autorização Especiais de Trânsito', 'url' => '/admin/autorizacoesesptransitos'],
            '102' => ['title' => 'Cadastro de Tipo de Certificados', 'url' => '/admin/tipocertificados'],
            '103' => ['title' => 'Cronotacógrafos', 'url' => '/admin/cronotacografos'],
            '104' => ['title' => 'Teste de Frio', 'url' => '/admin/testefrios'],
            '105' => ['title' => 'Teste de Fumaça', 'url' => '/admin/testefumacas'],
        ],
    ],

    // GRUPO 201-300: CHECKLIST
    'checklist' => [
        'title' => 'Checklist',
        'items' => [
            '201' => ['title' => 'Checklist Respostas', 'url' => '/admin/checklistResposta'],
            '202' => ['title' => 'Tipo Checklist', 'url' => '/admin/tipoChecklist'],
            '203' => ['title' => 'Checklist', 'url' => '/admin/checklist'],
        ],
    ],

    // GRUPO 301-400: CONTROLE DE LICENÇAS
    'controlelicencas' => [
        'title' => 'Controle de Licenças',
        'items' => [
            '301' => ['title' => 'Cadastro Licenciamento de Veículos', 'url' => '/admin/licenciamentoveiculos'],
            '302' => ['title' => 'Cadastro IPVA', 'url' => '/admin/ipvaveiculos'],
            '303' => ['title' => 'Cadastro Seguro Obrigatório', 'url' => '/admin/seguroobrigatorioveiculos'],
            '304' => ['title' => 'Lançamentos em Lote IPVA/Licenciamento/Seguro Obrigatório', 'url' => '/admin/lancipvalicenciamentoseguros/create'],
        ],
    ],

    // GRUPO 401-500: ESTOQUE
    'estoque' => [
        'title' => 'Estoque',
        'items' => [
            '401' => ['title' => 'Ajuste Estoque', 'url' => '/admin/ajusteEstoque'],
            '402' => ['title' => 'Cadastro Estoque', 'url' => '/admin/estoque'],
            '403' => ['title' => 'Gerar código', 'url' => '/admin/ajusteEstoque/codes'],
            '404' => ['title' => 'Devolução de Requisição de Peças', 'url' => '/admin/devolucaoTransferenciaEntreEstoque'],
            '405' => ['title' => 'Devolução Transferência Direta Estoque', 'url' => '/admin/transferenciaDiretaEstoqueList'],
            '406' => ['title' => 'Devolução de Materiais para Matriz', 'url' => '/admin/devolucaoMateriaisMatriz'],
            '407' => ['title' => 'Transferência Entre Estoques', 'url' => '/admin/transferenciaEntreEstoque'],
            '408' => ['title' => 'Transferência Direta Estoque', 'url' => '/admin/transferenciaDiretaEstoqueController'],
        ],
    ],

    // GRUPO 701-800: MULTAS
    'multas' => [
        'title' => 'Multas',
        'items' => [
            '701' => ['title' => 'Classificação de Multas', 'url' => '/admin/classificacaomultas'],
            '702' => ['title' => 'Multas', 'url' => '/admin/multas'],
        ],
    ],

    // GRUPO 801-900: PESSOAL
    'pessoal' => [
        'title' => 'Pessoal',
        'items' => [
            '801' => ['title' => 'Pessoal', 'url' => '/admin/pessoas'],
            '802' => ['title' => 'Fornecedor', 'url' => '/admin/fornecedores'],
        ],
    ],

    // GRUPO 901-1000: PNEUS
    'pneus' => [
        'title' => 'Pneus',
        'items' => [
            '901' => ['title' => 'Cadastro de Pneus', 'url' => '/admin/pneus'],
            '902' => ['title' => 'Contagem de Pneus', 'url' => '/admin/contagempneus'],
            '903' => ['title' => 'Envio de Pneus para Manutenção', 'url' => '/admin/manutencaopneus'],
            '904' => ['title' => 'Entrada de Pneus da Manutenção', 'url' => '/admin/manutencaopneusentrada'],
            '905' => ['title' => 'Movimentação do Pneu', 'url' => '/admin/movimentacaopneus'],
            '906' => ['title' => 'Aprovação da Venda de Pneus', 'url' => '/admin/requisicaopneusvendas'],
            '907' => ['title' => 'Saída de Pneus para Venda', 'url' => '/admin/manutencaopneus'],
            '908' => ['title' => 'Cadastro de Descarte de Pneus', 'url' => '/admin/descartepneus'],
            '909' => ['title' => 'Transferência Pneus', 'url' => '/admin/transferenciapneus'],
        ],
    ],

    // GRUPO 1001-1100: SINISTROS
    'sinistros' => [
        'title' => 'Sinistros',
        'items' => [
            '1001' => ['title' => 'Sinistros', 'url' => '/admin/sinistros'],
        ],
    ],

    // GRUPO 1101-1200: VEÍCULOS
    'veiculos' => [
        'title' => 'Veículos',
        'items' => [
            '1101' => ['title' => 'Cadastro de Veículos', 'url' => '/admin/veiculos'],
            '1102' => ['title' => 'Licenciamento Veículos', 'url' => '/admin/licenciamentoveiculos'],
        ],
    ],

    // GRUPO 1201-1300: CONFIGURAÇÕES
    'configuracoes' => [
        'title' => 'Configurações',
        'items' => [
            '1201' => ['title' => 'Filiais', 'url' => '/admin/filiais'],
            '1202' => ['title' => 'Cargos', 'url' => '/admin/cargos'],
            '1203' => ['title' => 'Usuários', 'url' => '/admin/usuarios'],
            '1204' => ['title' => 'Listagem de Usuários x Departamentos', 'url' => '/admin/usuarios/list-with-departments'],
            '1205' => ['title' => 'Log de Atividades', 'url' => '/admin/log-atividades'],
            '1206' => ['title' => 'Permissões', 'url' => '/admin/permissoes'],
            '1207' => ['title' => 'Tipo Checklist', 'url' => '/admin/tipoChecklist'],
            '1208' => ['title' => 'Tipos de Acerto de Estoque', 'url' => '/admin/tipoacertoestoque'],
            '1209' => ['title' => 'Tipos de Borracha Pneus', 'url' => '/admin/tipoborrachapneus'],
            '1210' => ['title' => 'Tipos de Categorias de Veículos', 'url' => '/admin/tipocategorias'],
            '1211' => ['title' => 'Tipos de Subcategorias de Veículos', 'url' => '/admin/subcategoriaveiculos'],
            '1212' => ['title' => 'Tipos de Combustíveis', 'url' => '/admin/tipocombustiveis'],
            '1213' => ['title' => 'Tipos de Equipamentos', 'url' => '/admin/tipoequipamentos'],
            '1214' => ['title' => 'Tipos de Desenho Pneus', 'url' => '/admin/tipodesenhopneus'],
            '1215' => ['title' => 'Tipos de Dimensão Pneus', 'url' => '/admin/tipodimensaopneus'],
            '1216' => ['title' => 'Tipos de Fornecedores', 'url' => '/admin/tipofornecedores'],
            '1217' => ['title' => 'Tipos de Imobilizados', 'url' => '/admin/tipoimobilizados'],
            '1218' => ['title' => 'Tipos de Manutenção', 'url' => '/admin/tipomanutencoes'],
            '1219' => ['title' => 'Tipos de Motivo de Sinistros', 'url' => '/admin/tipomotivosinistros'],
            '1220' => ['title' => 'Tipos de Reforma de Pneus', 'url' => '/admin/tiporeformapneus'],
            '1221' => ['title' => 'Tipos de Ocorrência', 'url' => '/admin/tipoocorrencias'],
            '1222' => ['title' => 'Tipos de Orgão de Sinistros', 'url' => '/admin/tipoorgaosinistros'],
            '1223' => ['title' => 'Tipos de Pessoal', 'url' => '/admin/tipopessoal'],
            '1224' => ['title' => 'Tipos de Veículos', 'url' => '/admin/tipoveiculos'],
            '1225' => ['title' => 'Tipos de Departamentos', 'url' => '/admin/departamentos'],
        ],
    ],
];
