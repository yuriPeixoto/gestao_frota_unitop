<form method="GET" action="{{ route('admin.ajustekm.index') }}" class="space-y-4"
    hx-get="{{ route('admin.ajustekm.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <x-forms.input name="id_ajuste_km_abastecimento" label="Cód. km Abastecimento"
                value="{{ request('id_ajuste_km_abastecimento') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inicial" label="Data Inicial" value="{{ request('data_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final" label="Data Final" value="{{ request('data_final') }}" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-forms.smart-select name="id_veiculo" label="Veículo" placeholder="Selecione o veículo..."
                :options="$veiculosFrequentes" :searchUrl="route('admin.api.veiculos.search')"
                :selected="request('id_veiculo')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.select name="tipo_combustivel" label="Tipo de Combustível"
                :options="$tiposCombustivel->pluck('descricao', 'descricao')"
                selected="{{ request('tipo_combustivel') }}" emptyOption="Selecione um tipo de combustível" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div>
            {{-- Usar o componente de botões de exportação --}}
            <x-ui.export-buttons route="admin.ajustekm" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.ajustekm.index') }}"
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