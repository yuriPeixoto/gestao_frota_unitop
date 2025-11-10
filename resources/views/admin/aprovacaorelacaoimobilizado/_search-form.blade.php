<form method="GET" action="{{ route('admin.aprovacaorelacaoimobilizado.index') }}" class="space-y-4"
    hx-get="{{ route('admin.aprovacaorelacaoimobilizado.index') }}" hx-target="#results-table"
    hx-select="#results-table" hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>
            {{-- Cod. Descarte Imobilizados --}}
            <x-forms.input name="id_relacao_imobilizados" label="Cód. aprovação Imobilizados"
                value="{{ request('id_relacao_imobilizados') }}" />
        </div>
    </div>

    <div class="flex justify-between mt-4">

        <div class="flex space-x-2">
            <a href="{{ route('admin.aprovacaorelacaoimobilizado.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="submit" id="search-input"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>
        </div>
    </div>
</form>