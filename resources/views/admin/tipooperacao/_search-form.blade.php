<form method="GET" action="{{ route('admin.tipooperacao.index') }}" class="space-y-4"
    hx-get="{{ route('admin.tipooperacao.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        <div>
            <x-forms.input name="id_tipo_operacao" label="Cód. Tipo Operação"
                value="{{ request('id_tipo_operacao') }}" />
        </div>
        <div>
            <x-forms.smart-select name="descricao_tipo_operacao" label="Descrição Tipo Operação"
                value="{{ request('descricao_tipo_operacao') }}" :options="$tipo" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.tipooperacao.index') }}"
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