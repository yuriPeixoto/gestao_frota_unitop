<form method="GET" action="{{ route('admin.manutencaokmveiculocomodato.index') }}" class="space-y-4"
    hx-get="{{ route('admin.manutencaokmveiculocomodato.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div>
            <x-forms.input name="id_km_comodato" label="Cód. Km Comodato" value="{{ request('id_km_comodato') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_veiculo" label="Veículo" :searchUrl="route('admin.veiculos.search')" :options="$veiculosFrequentes"
                selected="{{ request('id_veiculo') }}" />
        </div>

        <div>
            <x-forms.input name="km_realizacao" label="Km Realizado" value="{{ request('km_realizacao') }}" />
        </div>

        <div>
            <x-forms.input name="horimetro" label="Horimetro" value="{{ request('horimetro') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao_inicial" label="Data Inclusão Inicio"
                value="{{ request('data_inclusao_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao_fim" label="Data Inclusão Fim"
                value="{{ request('data_inclusao_fim') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_alteracao_inicial" label="Data Alteração Inicio"
                value="{{ request('data_alteracao_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_alteracao_fim" label="Data Alteração Fim"
                value="{{ request('data_alteracao_fim') }}" />
        </div>



    </div>

    <div class="flex justify-between mt-4">

        <div class="flex space-x-2">
            <a href="{{ route('admin.manutencaokmveiculocomodato.index') }}"
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
            <x-ui.export-buttons route="admin.manutencaokmveiculocomodato" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>
    </div>
</form>
