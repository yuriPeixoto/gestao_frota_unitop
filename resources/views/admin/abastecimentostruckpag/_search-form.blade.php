<form x-data="{ submitting: false }" @submit="submitting = true" method="GET"
    action="{{ route('admin.abastecimentostruckpag.onProcessarTruckPag') }}" class="space-y-4"
    hx-get="{{ route('admin.abastecimentostruckpag.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.input type="date" name="data_inicio_transacao" id="data_inicio_transacao" label="Data Inicial"
                value="{{ request('datatransacao') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final_transacao" id="data_final_transacao" label="Data Final"
                value="{{ request('data_final_transacao') }}" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.abastecimentostruckpag.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="submit" :disabled="submitting"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.integration />
                <span x-show="!submitting">Processar Abastecimento</span>
                <span x-show="submitting" class="flex items-center">
                    <x-ui.loading size="sm" message="" />
                    <span class="ml-2">Processando...</span>
                </span>
            </button>

            <!-- Overlay de loading -->
            <div x-show="submitting">
                <x-ui.loading fullscreen="true" message="Processando Abastecimentos..." />
            </div>

            <a href="{{ route('admin.abastecimentostruckpag.create') }}"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.truck class="h-4 w-4 mr-2 " />
                Reprocessar Integração ATS
            </a>
        </div>
    </div>
</form>
