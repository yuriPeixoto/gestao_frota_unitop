<form method="GET" action="{{ route('admin.relacaodespesasveiculos.index') }}" class="space-y-4"
    hx-get="{{ route('admin.relacaodespesasveiculos.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">

        <div>
            <x-forms.input name="id_despesas_veiculos" label="Código Despesas"
                value="{{ request('id_despesas_veiculos') }}" />
        </div>

        <div>
            <x-forms.input name="numero_nf" label="N. da NF" value="{{ request('numero_nf') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_veiculo" label="Veículo" placeholder="Selecione o veículo..."
                :options="$referenceDatas['veiculosFrequentes']" :searchUrl="route('admin.api.veiculos.search')" :selected="request('id_veiculo')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione o fornecedor..."
                :options="$referenceDatas['fornecedoresFrequentes']" :searchUrl="route('admin.api.fornecedores.search')" :selected="request('id_fornecedor')" asyncSearch="true" />
        </div>

    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.relacaodespesasveiculos.index') }}"
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
