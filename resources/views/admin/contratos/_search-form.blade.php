<form method="GET" action="{{ route('admin.contratos.index') }}" class="space-y-4"
    hx-get="{{ route('admin.contratos.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

        <div>
            {{-- Cod. Descarte Imobilizados --}}
            <x-forms.smart-select name="id_contrato_forn" label="Cód. Imobilizados"
                placeholder="Selecione o codigo descarte imobilizado..." :options="[]" asyncSearch="false" />
        </div>
    </div>

    <div class="mt-4 flex justify-between">
        <div>
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.contratos.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <x-icons.trash class="mr-2 h-4 w-4" />
                Limpar
            </a>

            <button type="submit"
                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <x-icons.magnifying-glass class="mr-2 h-4 w-4" />
                Buscar
            </button>
        </div>
    </div>
</form>
