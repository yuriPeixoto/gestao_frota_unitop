<form method="GET" action="{{ route('admin.premiosuperacao.index') }}" class="space-y-4"
    hx-get="{{ route('admin.premiosuperacao.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 sm:grid-cols-5 md:grid-cols-4 gap-4">
        <div>
            <x-forms.input name="id_premio_superacao" label="Código:" value="{{ request('id_premio_superacao') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inicial:" label="Data Inicial:"
                value="{{ request('data_inicial') }}" />
        </div>
        <div>
            <x-forms.input type="date" name="data_final" label="Data Final:" value="{{ request('data_final') }}" />
        </div>
        <div>
            <x-forms.smart-select name="id_user" label="Usuário:" value="{{ request('id_user') }}" :options="$user" />
        </div>
        <div>
            <x-forms.smart-select name="situacao" label="Situação:" value="{{ request('situacao') }}"
                :options="$situacao" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.premiosuperacao.index') }}"
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