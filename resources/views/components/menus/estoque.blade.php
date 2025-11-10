<div x-show="estoqueOpen" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" class="mt-2 space-y-2 pl-6">

    <!-- Painel de Controle - Estoque -->
    @can('ver_estoque')
    <a href="{{ route('admin.estoque.dashboard') }}"
        class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
        Painel de Controle - Estoque
    </a>
    @endcan

    <!-- Cadastro de Produtos -->
    @can(abilities: 'ver_produto')
    <a href="{{ route('admin.cadastroprodutosestoque.index') }}"
        class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
        Cadastro de Produtos
    </a>
    @endcan

    @can(abilities: 'ver_notas_fiscais')
    <a href="{{ route('admin.notafiscalentrada.index') }}"
        class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
        Nota Fiscal de entrada
    </a>
    @endcan

    <!-- Consulta de Requisições -->
    @can('ver_itensparacompra')
    <a href="{{ route('admin.itensparacompra.index') }}"
        class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
        Itens para compra
    </a>
    @endcan

    <!-- Consulta de Requisições -->
    @can('ver_requisicaomaterial')
    <a href="{{ route('admin.requisicaoMaterial.index') }}"
        class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
        Requisição de Materiais
    </a>
    @endcan

    <!-- Submenu Saida de Produtos -->
    <div x-data="{ subMenuSaidaProdutosOpen: false }">
        @php
        $hasSaidaProdutosPermissions =
        auth()->user()->can('ver_produtossolicitacoes') || auth()->user()->can('ver_devolucaomateriais');
        @endphp

        @if ($hasSaidaProdutosPermissions)
        <a href="#" @click="subMenuSaidaProdutosOpen = !subMenuSaidaProdutosOpen"
            class="flex items-center justify-between rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
            <span class="tracking-tight">Saida de Produtos</span>
            <svg x-bind:class="{ 'rotate-180': subMenuSaidaProdutosOpen }" class="ml-2 h-4 w-4 transition-transform"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>
        <div x-show="subMenuSaidaProdutosOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="mt-2 max-h-48 space-y-2 overflow-y-auto pl-4">
            <!-- Consulta de Requisições -->
            @can('ver_produtossolicitacoes')
            <a href="{{ route('admin.saidaprodutosestoque.index') }}"
                class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
                Consulta de Requisições
            </a>
            @endcan

            <!-- Devoluçoes -->
            @can('ver_devolucaomateriais')
            <a href="{{ route('admin.devolucaosaidaestoque.index') }}"
                class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
                Devoluções Saida de Estoque
            </a>
            @endcan
        </div>
        @endif
    </div>

    @can('ver_listagem')
    <a href="{{ route('admin.listapedidocompra.index') }}"
        class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
        Lista de Pedidos de <br> Compras
    </a>
    @endcan

    <!-- Ajuste Estoque -->
    @can('ver_ajusteestoque')
    <a href="{{ route('admin.ajusteEstoque.index') }}"
        class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
        Ajuste Estoque
    </a>
    @endcan

    <!-- Cadastro Estoque -->
    @can('ver_estoque')
    <a href="{{ route('admin.estoque.index') }}"
        class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
        Cadastro Estoque
    </a>
    @endcan

    <!-- Gerar código -->
    @can('ver_ajusteestoque')
    {{-- <a href="{{ route('admin.ajusteEstoque.codes') }}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Gerar código
    </a> --}}

    {{-- <a href="#" onclick="return alert('Em desenvolvimento');"
        class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
        Gerar código
    </a> --}}
    @endcan

    @can('ver_devolucoes')
    <a href="{{ route('admin.devolucoes.index') }}"
        class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
        Devoluções
    </a>
    @endcan

    <!-- Devolução de Requisição de Peças -->
    {{-- @can('ver_devolucaotransferenciaestoquerequisicao')
    <a href="{{ route('admin.devolucaoTransferenciaEntreEstoque.index') }}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Devoluções
    </a>
    @endcan --}}

    {{-- <a href="{{ route('admin.devolucaoTransferenciaEntreEstoque.index') }}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Devolução de Requisição de Peças
    </a> --}}

    {{-- <a href="{{ route('admin.transferenciaDiretaEstoqueList.index') }}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Devolução Transferência Direta Estoque
    </a> --}}

    {{-- <a href="{{ route('admin.devolucaoMateriaisMatriz.index') }}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Devolução de Materiais para Matriz
    </a> --}}

    <!-- Transferência Entre Estoques (Recebimento de Materiais) -->
    @can('ver_transferenciaestoque')
    <a href="{{ route('admin.transferenciaEntreEstoque.index') }}"
        class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
        Transferência Entre Estoques (Recebimento de Materiais)
    </a>
    @endcan

    <!-- Transferência Direta Estoque -->
    @can('ver_transferenciadiretaestoque')
    <a href="{{ route('admin.transferenciaDiretoEstoque.index') }}"
        class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
        Transferência Direta Estoque
    </a>
    @endcan

    @can('ver_graficoproduto')
    <a href="{{route('admin.consultaprodutografico.index')}}"
        class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
        Consulta Produto (Gráfico)
    </a>
    @endcan

    @if(auth()->user()->can('relatorio_estoque') || auth()->user()->can('ver_estoque'))
    <div x-data="{ estoquerelatorio: false }">
        <a href="#" @click="estoquerelatorio = !estoquerelatorio"
            class="flex items-center justify-between rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
            <span class="tracking-tight">Relatórios</span>
            <svg x-bind:class="{ 'rotate-180': estoquerelatorio }" class="ml-2 h-4 w-4 transition-transform" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>
        <div x-show="estoquerelatorio" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="mt-2 max-h-48 space-y-2 overflow-y-auto pl-4">

            {{-- Itens comentados - mantidos conforme original --}}

            <a href="{{ route('admin.consultaprodutostransferencia.index') }}"
                class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
                Consulta de Produtos Transferencia
            </a>
            <a href="{{ route('admin.relatoriofichacontroleestoque.index') }}"
                class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
                Ficha de Controle do Estoque
            </a>
            <a href="{{ route('admin.relatorioindicecoberturaestoque.index') }}"
                class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
                Indice Cobertura Estoque
            </a>
            <a href="{{ route('admin.relatoriosaidadepartamento.index') }}"
                class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
                Relatorio de Saídas por Departamento
            </a>
            <a href="{{ route('admin.relatorioconferenciarotativo.index') }}"
                class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
                Relatorio Conferência Rotativo Diário
            </a>
            <a href="{{ route('admin.relatorioprodutoscadastrados.index') }}"
                class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
                Relatorio Produtos Cadastrados
            </a>
            <a href="{{ route('admin.relatoriochecklistfornecedor.index') }}"
                class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
                Relatorio CheckList de Fornecedor
            </a>
            <a href="{{ route('admin.relatoriobaixaestoque.index') }}"
                class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
                Relatorio Baixa Estoque
            </a>
            <a href="{{ route('admin.relatorioprodutoemestoque.index') }}"
                class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
                Relatorio de Produtos em Estoque
            </a>
            <a href="{{ route('admin.relatoriohistoricotransferencia.index') }}"
                class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600">
                Relatorio Historico Transferencia por Requisição
            </a>
            {{-- <a href="{{route('admin.relatoriomaximoeminimo.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio de Estoque Máximo e Minimo
            </a> --}}
            {{-- <a href="#"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Produtos Cadastrados
            </a> --}}

        </div>
    </div>
    @endif
</div>