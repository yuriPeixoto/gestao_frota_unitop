<form method="GET" action="{{ route('admin.cadastroprodutosestoque.index') }}" class="space-y-4"
    hx-get="{{ route('admin.cadastroprodutosestoque.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="id_produto" label="Cód. Produto" value="{{ request('id_produto') }}" />
        </div>

        <div>
            <x-forms.input type="text" name="descricao" label="Descrição" value="{{ request('descricao') }}" />
        </div>

        <div>
            <x-forms.input type="text" name="cod_fabricante_" label="Código do Fabricante"
                value="{{ request('cod_fabricante_') }}" />
        </div>

        <div>
            <x-forms.input type="text" name="cod_alternativo_1_" label="Código do Alternativo 1"
                value="{{ request('cod_alternativo_1_') }}" />
        </div>

        <div>
            <x-forms.input type="text" name="cod_alternativo_2_" label="Código do Alternativo 2"
                value="{{ request('cod_alternativo_2_') }}" />
        </div>
    </div>

    <div class="flex justify-between mt-4">


        <div class="flex space-x-2">
            <a href="{{ route('admin.cadastroprodutosestoque.index') }}"
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
