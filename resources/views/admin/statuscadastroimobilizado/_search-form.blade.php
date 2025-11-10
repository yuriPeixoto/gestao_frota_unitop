<form method="GET" action="{{ route('admin.statuscadastroimobilizado.index') }}" class="space-y-4"
    hx-get="{{ route('admin.statuscadastroimobilizado.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="id" label="Código Status" value="{{ request('id') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao_inicial" label="Data Inclusão Inicial"
                value="{{ request('data_inclusao_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao_final" label="Data Inclusão Final"
                value="{{ request('data_inclusao_final') }}" />
        </div>

        <div>
            <x-forms.input name="descricao" label="Descrição Tipo Solicitação" value="{{ request('descricao') }}" />
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

                <x-forms.button type="secondary" href="{{ route('admin.statuscadastroimobilizado.index') }}"
                    variant="outlined">
                    <x-icons.trash class="h-4 w-4" />
                    Limpar
                </x-forms.button>
            </div>
        </div>
    </div>
</form>