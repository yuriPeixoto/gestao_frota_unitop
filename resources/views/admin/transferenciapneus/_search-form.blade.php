<form method="GET" action="{{ route('admin.transferenciapneus.index') }}" class="space-y-4"
    hx-get="{{ route('admin.transferenciapneus.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <x-forms.input name="id_transferencia_pneus" label="Código Transferência Pneus"
            value="{{ request('id_transferencia_pneus') }}" />

        <x-forms.input type="date" name="data_inclusao_inicial" label="Data Inclusão Inicial"
            value="{{ request('data_inclusao_inicial') }}" />

        <x-forms.input type="date" name="data_inclusao_final" label="Data Inclusão Final"
            value="{{ request('data_inclusao_final') }}" />

        <x-forms.smart-select name="id_filial" label="Filial" :options="$filiais" :selected="request('id_filial')" asyncSearch="false" />

        <x-forms.smart-select name="id_usario" label="Usuário" :options="$usuarios" :selected="request('id_usario')"
            asyncSearch="false" />
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.transferenciapneus.index') }}"
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
