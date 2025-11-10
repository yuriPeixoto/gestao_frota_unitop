<form method="GET" action="{{ route('admin.tipoveiculos.index') }}" class="space-y-4"
    hx-get="{{ route('admin.tipoveiculos.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <x-forms.input name="id" label="Cód. Tipo Veiculo" value="{{ request('id') }}" />
        </div>

        <div>
            <x-forms.smart-select name="descricao" label="Descricao Orgão" placeholder="Selecione a Descricao..."
                :options="$descricao" :selected="request('descricao')" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Inclusão"
                value="{{ request('data_inclusao') }}" />
        </div>

    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <x-forms.button href="{{ route('admin.tipoveiculos.index') }}" type="secondary" variant="outlined">
                <x-icons.trash class="h-4 w-4" />
                Limpar
            </x-forms.button>

            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>
        </div>
    </div>
</form>
