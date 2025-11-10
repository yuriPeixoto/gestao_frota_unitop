<form method="GET" action="{{ route('admin.listapedidocompra.index') }}" class="space-y-4"
    hx-get="{{ route('admin.listapedidocompra.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

        <!-- Número do Pedido -->
        <x-forms.input id="pedido" name="id_pedido_compras" label="N° Pedido:"
            value="{{ request('id_pedido_compras') }}" />

        <!-- Código da Solicitação -->
        <x-forms.input id="codigo" name="id_solicitacoes_compras" label="Cód. Solicitação:"
            value="{{ request('id_solicitacoes_compras') }}" />

        <!-- Data -->
        <x-forms.input name="data_inclusao" type="date" label="Data Inclusão" value="{{ request('data_inclusao') }}" />

        <!-- Fornecedor -->
        <x-forms.smart-select name="id_fornecedor" label="Fornecedor:" :options="$fornecedor" placeholder="Selecione..."
            :searchUrl="route('admin.api.fornecedores.search')" asyncSearch="true" />

        <!-- Situação -->
        <x-forms.smart-select name="situacao_pedido" label="Situação Pedido:" :options="$situacao"
            placeholder="Selecione..." asyncSearch="false" />

        <!-- Comprador -->
        <x-forms.smart-select name="id_comprador" label="Nome Comprador:" :options="$comprador"
            placeholder="Selecione..." :searchUrl="route('admin.api.users.search')" asyncSearch="true" />


    </div>

    <div class="flex justify-between mt-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            {{--
            <x-ui.export-buttons route="admin.listapedidocompra" :formats="['pdf', 'csv', 'xls', 'xml']" />--}}
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.listapedidocompra.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>
        </div>
    </div>
</form>