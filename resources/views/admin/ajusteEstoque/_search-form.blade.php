<form method="GET" action="{{ route('admin.ajusteEstoque.index') }}" class="space-y-4"
    hx-get="{{ route('admin.ajusteEstoque.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <x-forms.input name="id_acerto_estoque" label="Código Acerto Estoque"
                value="{{ request('id_acerto_estoque') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inicial" label="Data Acerto Inicial"
                value="{{ request('data_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final" label="Data Acerto Final"
                value="{{ request('data_final') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a Filial..." :options="$filial"
                :selected="request('id_filial')" asyncSearch="true" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
            <x-forms.smart-select name="id_produto" label="Produto" :options="$produto" :selected_value="request('id_produto')"
                SearchUrl="{{ route('admin.api.produto.search') }}" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_tipo_acerto" label="Código Tipo Acerto" :options="$tipoAcerto" :selected="request('id_tipo_acerto')"
                asyncSearch="true" />
        </div>

        <div>
            <x-forms.input name="quantidade_acerto" label="Quantidade Acerto"
                value="{{ request('quantidade_acerto') }}" />
        </div>
    </div>


    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>

            <a href="{{ route('admin.ajusteEstoque.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>
        </div>

        <div>
            {{-- Usar o componente de botões de exportação --}}
            {{-- <x-ui.export-buttons route="admin.ajusteEstoque" :formats="['pdf', 'csv', 'xls', 'xml']" /> --}}
        </div>
    </div>
</form>
