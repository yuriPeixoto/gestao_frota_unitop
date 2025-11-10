<form method="GET" action="{{ route('admin.compras.pedidos.index') }}" class="space-y-4"
    hx-get="{{ route('admin.compras.pedidos.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-forms.input name="numero" label="Número do Pedido" data-filter="exact" value="{{ request('numero') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Inicial" value="{{ request('data_inclusao') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_alteracao" label="Data Alteração" value="{{ request('data_alteracao') }}" />
        </div>

        <div>
            <x-forms.select name="situacao" label="Status" :options="[
                '' => 'Todos',
                'pendente' => 'Pendente',
                'aprovado' => 'Aprovado',
                'finalizado' => 'Finalizado',
                'pre_pedido' => 'Pré-Pedido',
            ]" :selected="request('situacao')" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione o fornecedor..."
                :options="$fornecedoresFrequentes ?? []" :searchUrl="route('admin.api.fornecedores.search')"
                :selected="request('id_fornecedor')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_solicitacao" label="Solicitação" placeholder="Selecione a solicitação..."
                :options="$solicitacoesFrequentes ?? []" :searchUrl="route('admin.api.solicitacoes.search')"
                :selected="request('id_solicitacao')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..."
                :options="$filiais ?? []" :selected="request('id_filial')" asyncSearch="false" />
        </div>

        <div>
            <x-forms.smart-select name="id_comprador" label="Comprador" placeholder="Selecione o comprador..."
                :options="$compradores ?? []" :selected="request('id_comprador')" asyncSearch="false" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div>
            {{-- Usar o componente de botões de exportação --}}
            <x-ui.export-buttons route="admin.compras.pedidos" :formats="['pdf', 'csv', 'xls']" />
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.compras.pedidos.index') }}"
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
