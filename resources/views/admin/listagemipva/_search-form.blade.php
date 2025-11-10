<form method="GET" action="{{ route('admin.listagemipva.index') }}" class="space-y-4"
    hx-get="{{ route('admin.listagemipva.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>
            <x-forms.smart-select id="placa" name="placa[]" label="Placa" placeholder="Selecione a(s) placa(s)..."
                :options="$placa"  :multiple="true" asyncSearch="false" />
        </div>

        <div>
            {{-- Renavam --}}
            <x-forms.smart-select name="renavam" label="Renavam"
                placeholder="Selecione o renavam..." :options="$renavam"
                asyncSearch="false" />
        </div>

    </div>

    <div class="flex justify-between mt-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <x-ui.export-buttons route="admin.listagemipva" :formats="['pdf', 'csv', 'xls', 'xml']" />

            <div>
                <button type="button" onclick="baixarBoletosLote()"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7v0a3 3 0 116 0v0" />
                    </svg>
                    Boletos
                </button>            
            </div>

        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.listagemipva.index') }}"
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