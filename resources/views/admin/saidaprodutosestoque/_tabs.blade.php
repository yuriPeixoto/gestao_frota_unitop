<div class="mt-6">
    <div class="mx-auto">
        <!-- Botões das abas -->
        <div class="flex space-x-1">
            <button type="button"
                class="tablink text-md rounded-t-lg bg-white px-3 py-1 text-gray-700 transition-colors hover:bg-gray-200 hover:text-black"
                onclick="openTab(event, 'Aba1')">
                <x-icons.square-3-stack-3d class="mr-1 inline-block h-4 w-4" />
                Consulta de Requisição de Materiais
            </button>
            <button type="button"
                class="tablink text-md rounded-t-lg bg-white px-3 py-1 text-gray-700 transition-colors hover:bg-gray-200 hover:text-black"
                onclick="openTab(event, 'Aba2')">
                <x-icons.square-3-stack-3d class="mr-1 inline-block h-4 w-4" />
                Consulta de Requisição de Materiais Transferências
            </button>
            <button type="button"
                class="tablink text-md rounded-t-lg bg-white px-3 py-1 text-gray-700 transition-colors hover:bg-gray-200 hover:text-black"
                onclick="openTab(event, 'Aba3')">
                <x-icons.square-3-stack-3d class="mr-1 inline-block h-4 w-4" />
                Consulta Requisição de Peças por O.S.
            </button>
        </div>
    </div>

    <!-- Conteúdo das abas -->
    <div id="Aba1" class="tabcontent rounded-b-lg bg-white p-6 shadow-lg">
        @include('admin.saidaprodutosestoque._search-form_requisicao_materiais')
        @include('admin.saidaprodutosestoque._table_requisicao_materiais')
    </div>

    <div id="Aba2" class="tabcontent hidden rounded-b-lg bg-white p-6 shadow-lg">
        @include('admin.saidaprodutosestoque._search-form_requisicao_transferencias')
        @include('admin.saidaprodutosestoque._table_requisicao_transferencias')
    </div>

    <div id="Aba3" class="tabcontent hidden rounded-b-lg bg-white p-6 shadow-lg">
        @include('admin.saidaprodutosestoque._search-form_requisicao_pecas')
        @include('admin.saidaprodutosestoque._table_requisicao_pecas')
    </div>

    <x-bladewind.modal name="vizualizar-produtos" size="omg" cancel_button_label="" ok_button_label="Ok"
        title="Produtos">
        <x-tables.table>
            <x-tables.header id="tabelaBodyProdutos">
                <x-tables.head-cell>Código Requisição Solicitações</x-tables.head-cell>
                <x-tables.head-cell>Produtos</x-tables.head-cell>
                <x-tables.head-cell class="text-center">Unidade Produto</x-tables.head-cell>
                <x-tables.head-cell class="text-center">Quantidade de Produtos</x-tables.head-cell>
                <x-tables.head-cell class="text-center">Quantidade Baixa</x-tables.head-cell>
                <x-tables.head-cell class="text-center">Quantidade Estoque</x-tables.head-cell>
                <x-tables.head-cell>Localização</x-tables.head-cell>
                <x-tables.head-cell>Data Baixa</x-tables.head-cell>
            </x-tables.header>

            <x-tables.body id="tabelaBodyProdutos"></x-tables.body>

        </x-tables.table>

    </x-bladewind.modal>
</div>
