<form method="GET" action="{{ route('admin.manutencaopneus.index') }}" class="space-y-4"
    hx-get="{{ route('admin.manutencaopneus.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">

        <x-forms.input name="id_manutencao_pneu" label="Código Manutenção Pneu"
            value="{{ request('id_manutecao_pneu') }}" />

        <x-forms.input type="date" name="data_inclusao" label="Data Inicial" value="{{ request('data_inclusao') }}" />
        <x-forms.input type="date" name="data_final" label="Data Final" value="{{ request('data_final') }}" />

        <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione o modelo..." :options="$filiais"
            :selected="request('id_filial')" asyncSearch="true" />

        <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione o usuário..."
            :options="$fornecedoresFrequentes" :searchUrl="route('admin.api.fornecedores.search')"
            :selected="request('id_fornecedor')" asyncSearch="true" />

    </div>

    <div class="flex justify-between mt-4">
        <div>
            {{-- Usar o novo componente de botões de exportação --}}
            {{--
            <x-ui.export-buttons route="admin.abastecimentomanual" :formats="['pdf', 'csv', 'xls', 'xml']" /> --}}
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.manutencaopneus.index') }}"
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