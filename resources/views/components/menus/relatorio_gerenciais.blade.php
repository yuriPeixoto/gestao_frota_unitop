<div>
    <!-- Abastecimento Manual -->
    @can('ver_duracao_manutencoes_os')
    <a href="{{route('admin.relatorioduracaodasmanutencoes.index')}}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Duração das Manutenções por OS
    </a>
    @endcan

    @can('ver_relatorio_fornecedor_sem_nf')
    <a href="{{route('admin.relatoriofornecedorsemnf.index')}}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Relatorio de Fornecedor sem NFs
    </a>
    @endcan

    @can('ver_relatorio_recebimento_combustivel')
    <a href="{{route('admin.relatoriorecebimentocombustivel.index')}}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Relatorio de NFs Recebimento Combústivel
    </a>
    @endcan

    @can('ver_duracao_manutencoes_os')
    <a href="{{route('admin.relatorionfsmanutencaorealizadas.index')}}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Relatorio de NFs das Manutenções Realizadas
    </a>
    @endcan

    @can('ver_duracao_manutencoes_os')
    <a href="{{route('admin.relatorioabastecimentototais.index')}}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Relatorio de Abastecimentos Totais
    </a>
    @endcan

    @can('ver_duracao_manutencoes_os')
    <a href="{{route('admin.relatorioentradaprodutos.index')}}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Relatorio de Entradas de Produtos
    </a>
    @endcan

    @can('ver_duracao_manutencoes_os')
    <a href="{{route('admin.relatoriocustospordepartamento.index')}}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Relatorio de Custos Váriaveis por Departamento
    </a>
    @endcan

    @can('ver_duracao_manutencoes_os')
    <a href="{{route('admin.relatoriofechamentomensalcontroladoria.index')}}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Relatorio Fechamento Mensal Controladoria
    </a>
    @endcan

    @can('ver_duracao_manutencoes_os')
    <a href="{{route('admin.relatorioultimamovimentacaodespesas.index')}}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Relatorio de Ultima Movimentações de Despesas
    </a>
    @endcan

    @can('ver_duracao_manutencoes_os')
    <a href="{{route('admin.relatorioinventariopneus.index')}}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Relatorio de Inventario de Pneus
    </a>
    @endcan

</div>