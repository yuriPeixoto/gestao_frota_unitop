<form method="GET" action="{{ route('admin.listagemoslacamentoservicorateio.index') }}" class="space-y-4"
    hx-get="{{ route('admin.listagemoslacamentoservicorateio.index') }}" hx-target="#results-table"
    hx-select="#results-table" hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        <div>
            <x-forms.input name="id_nota_fiscal_servico" label="Cód. Serviço"
                value="{{ request('id_nota_fiscal_servico') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_fornecedor" label="Fornecedor" :options="$fornecedoresFrequentes" :searchUrl="route('admin.fornecedores.search')"
                :selected="request('id_fornecedor')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.input name="numero_nf" label="Número NF" value="{{ request('numero_nf') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_servico" label="Data do Servico"
                value="{{ request('data_servico') }}" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.listagemoslacamentoservicorateio.index') }}"
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
