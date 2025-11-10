<form method="GET" action="{{ route('admin.abastecimentomanual.index') }}" class="space-y-4"
    hx-get="{{ route('admin.abastecimentomanual.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="id_abastecimento" label="Cód. Abastecimento"
                value="{{ request('id_abastecimento') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Inclusão Inicial"
                value="{{ request('data_inclusao') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final_abastecimento" label="Data Inclusão Final"
                value="{{ request('data_final_abastecimento') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inicial_abastecimento" label="Data Abastecimento Inicial"
                value="{{ request('data_inicial_abastecimento') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_fim_abastecimento" label="Data Abastecimento Final"
                value="{{ request('data_fim_abastecimento') }}" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-forms.smart-select name="id_veiculo" label="Veículo" placeholder="Selecione o veículo..."
                :options="$veiculosFrequentes" :searchUrl="route('admin.api.veiculos.search')"
                :selected="request('id_veiculo')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione o fornecedor..."
                :options="$fornecedoresFrequentes" :searchUrl="route('admin.api.fornecedores.search')"
                :selected="request('id_fornecedor')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.input name="numero_nota_fiscal" label="Nº Nota Fiscal"
                value="{{ request('numero_nota_fiscal') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..."
                :options="$filiais" :selected="request('id_filial')" asyncSearch="false" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div>
            {{-- Usar o novo componente de botões de exportação --}}
            <x-ui.export-buttons route="admin.abastecimentomanual" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.abastecimentomanual.index') }}"
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