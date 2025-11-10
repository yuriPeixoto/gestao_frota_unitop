<form method="GET" action="{{ route('admin.tipoorgaosinistros.index') }}" class="space-y-4"
    hx-get="{{ route('admin.tipoorgaosinistros.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <x-forms.input name="id_tipo_orgao" label="Código Tipo Orgão" 
                value="{{ request('id_tipo_orgao') }}" />
        </div>

        <div>
            <x-forms.smart-select name="descricao_tipo_orgao" label="Descricao Orgão" placeholder="Selecione o Orgão..."
                :options="$descricao_tipo_orgao" :selected="request('descricao_tipo_orgao')" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Inclusão"
                value="{{ request('data_inclusao') }}" />
        </div>
    </div>

    <div class="flex justify-between mt-4">

        <div>
            <div class="flex space-x-2">
                <button type="submit"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                    Buscar
                </button>

                <a href="{{ route('admin.tipoorgaosinistros.index') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.trash class="h-4 w-4 mr-2" />
                    Limpar
                </a>
            </div>
        </div>
    </div>
</form>
