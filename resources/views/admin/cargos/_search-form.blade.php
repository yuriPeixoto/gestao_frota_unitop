<form method="GET" action="{{ route('admin.cargos.index') }}" class="space-y-4"
    hx-get="{{ route('admin.cargos.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div>
            <x-forms.input name="id" label="ID" value="{{ request('id') }}" />
        </div>

        <div>
            <x-forms.input name="name" label="Cargo" value="{{ request('name') }}" />
        </div>

        <div>
            <x-forms.input name="description" label="Descrição" value="{{ request('description') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_de_criacao" label="Data de criação - Inicio"
                value="{{ request('data_de_criacao') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_de_criacao_final" label="Data de criação - Final"
                value="{{ request('data_de_criacao_final') }}" />
        </div>

        <div>
            <label for="is_ativo" class="block text-sm font-medium text-gray-700">Ativo</label>
            <select name="is_ativo"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todos</option>
                <option value="true" {{ request('is_ativo')=='true' ? 'selected' : '' }}>Ativo</option>
                <option value="false" {{ request('is_ativo')=='false' ? 'selected' : '' }}>Inativo</option>
            </select>
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>

            <a href="{{ route('admin.cargos.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>
        </div>
    </div>
</form>