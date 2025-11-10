<div class="space-y-4">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />
    <form id="filterForm" method="GET" action="{{ route('admin.relatorioconferenciarotativo.index') }}">

        {{-- Formato front relatório --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <x-forms.input type="date" name="data_baixa" label="Data Baixa:" value="{{ request('data_baixa') }}" />

            <x-forms.smart-select name="id_filial" label="Filial:" placeholder="Selecionar" :options="$filial"
                :value="request('id_filial', 1)" disabled />



            <x-forms.smart-select name="id_estoque" label="Estoque:" placeholder="Selecionar" :options="$estoque"
                value="{{ request('id_estoque') }}" />

        </div>
    </form>
    {{-- Formato Ações botão limpar - pdf excel --}}
    <div class="flex justify-between mt-4">
        <div></div>

        <div class="flex space-x-2">

            <a href="{{ route('admin.relatorioconferenciarotativo.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            {{-- Usar o novo componente de botões de exportação --}}
            <button type="submit" form="filterForm"
                formaction="{{ route('admin.relatorioconferenciarotativo.exportPdf') }}" formtarget="_blank" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded
           text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2
           focus:ring-indigo-500 export-btn">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7v0a3 3 0 116 0v0" />
                </svg>
                PDF
            </button>

            {{-- Usar o novo componente de botões de exportação --}}
            <button type="submit" form="filterForm"
                formaction="{{ route('admin.relatorioconferenciarotativo.exportXls') }}" formtarget="_blank" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded
           text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2
           focus:ring-indigo-500 export-btn">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-green-700" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                XLS
            </button>

        </div>
    </div>
</div>