<form method="GET" action="{{ route('admin.fornecedores.index') }}" class="space-y-4"
    hx-get="{{ route('admin.fornecedores.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="id_fornecedor" label="Cód. Fornecedor" value="{{ request('id_fornecedor') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Inclusão Inicial"
                value="{{ request('data_inclusao') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final_inclusao" label="Data Inclusão Final"
                value="{{ request('data_final_inclusao') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_fornecedor" label="Nome Fornecedor" placeholder="Digite para buscar..."
                :searchUrl="route('admin.api.fornecedores.search')" :selected="request('id_fornecedor')"
                asyncSearch="true" minSearchLength="2" :options="$forn" />
        </div>

        <div>
            <x-forms.smart-select name="id_tipo_fornecedor" label="Tipo Fornecedor"
                value="{{ request('id_tipo_fornecedor') }}" :options="$tipo" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial" label="Filial" value="{{ request('id_filial') }}"
                :options="$filial" />
        </div>

        <div>
            <x-forms.smart-select name="id_uf" label="UF" value="{{ request('id_uf') }}" :options="$uf" />
        </div>

        <div>
            <x-forms.input name="cnpj" label="CNPJ" value="{{ request('cnpj') }}" />
        </div>

        <div>
            <x-forms.smart-select name="is_ativo" label="Ativo" placeholder="Selecione..." :options="$ativo"
                :selected="request('is_ativo')" />
        </div>

    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.fornecedores.index') }}"
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