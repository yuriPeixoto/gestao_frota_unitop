<form method="GET" action="{{ route('admin.manutencaoservicosmecanico.index') }}" class="space-y-4"
    hx-get="{{ route('admin.manutencaoservicosmecanico.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="id_servico_mecanico" label="Cód. Servico Mecânico" clearable="true"
                value="{{ request('id_servico_mecanico') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione o fornecedor..."
                :options="$fornecedoresFrequentes" :searchUrl="route('admin.api.fornecedores.search')"
                :selected="request('id_fornecedor')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_veiculo" label="Placa" value="{{ request('id_veiculo') }}" />
        </div>

        <div>
            <x-forms.input name="id_os" label="Cód. O.S" value="{{ request('id_os') }}" />
        </div>

    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.manutencaoservicosmecanico.index') }}"
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

        <div>
            {{-- Usar o novo componente de botões de exportação --}}
            <x-ui.export-buttons route="admin.manutencaoservicosmecanico" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>
    </div>
</form>