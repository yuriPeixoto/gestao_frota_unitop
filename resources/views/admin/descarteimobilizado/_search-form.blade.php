<form method="GET" action="{{ route('admin.descarteimobilizado.index') }}" class="space-y-4"
    hx-get="{{ route('admin.descarteimobilizado.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div>
            {{-- Cod. Descarte Imobilizados --}}
            <x-forms.input name="id_descarte_imobilizados" label="Cód. Descarte Imobilizados"
                value="{{ request('id_descarte_imobilizados') }}" />
        </div>

        <div>

            {{-- Produto --}}
            <x-forms.smart-select name="id_produtos_imobilizados" label="Produtos Imobilizados"
                placeholder="Selecione o produto..." :options="$produtosImobilizados"
                :searchUrl="route('admin.api.produtosimobilizados.search')" asyncSearch="true" />
        </div>

        <div>

            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..."
                :options="$filiais" :selected="request('id_filial')" asyncSearch="false" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div>
            <x-ui.export-buttons route="admin.descarteimobilizado" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.descarteimobilizado.index') }}"
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