<form method="GET" action="#" class="space-y-4">
    {{-- Linha de Filtros --}}
    <div class="flex w-full gap-2">

        {{-- Código da Transferência --}}
        <div class="w-full">
            <x-forms.input name="id_transferencia_direta_estoque" label="Cód. Transferência"
                value="{{ request('id_transferencia_direta_estoque') }}" />
        </div>
        {{-- Status --}}
        <div class="w-full">
            <x-forms.select name="status" label="Status" placeholder="Selecionar Status" :options="$status"
                :selected="request('status')" />
        </div>


    </div>
    <div class="flex w-full gap-2">

        <div class="w-full">
            <x-forms.input type="date" name="data_inclusao" label="Data Inicial"
                value="{{ request('data_inclusao') }}" />
        </div>

        {{-- Data Final --}}
        <div class="w-full">
            <x-forms.input type="date" name="data_final" label="Data Final" value="{{ request('data_final') }}" />
        </div>
    </div>

    {{-- Botões --}}
    <div class="flex justify-between mt-4">
        <div>
            {{-- Usar o novo componente de botões de exportação --}}
            <a href="{{ route('admin.transferenciaDiretoEstoque.pdf', request()->only(['status', 'data_inclusao', 'data_final', 'id_transferencia_direta_estoque'])) }}"
                class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7v0a3 3 0 116 0v0" />
                </svg>
                PDF
            </a>

            {{-- Usar o novo componente de botões de exportação --}}
            <a href="{{ route('admin.export.csv', request()->only(['status', 'data_inclusao', 'data_final', 'id_transferencia_direta_estoque']))}}"
                class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-green-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                CSV
            </a>

            {{-- Usar o novo componente de botões de exportação --}}
            <a href="{{ route('admin.export.xls', request()->only(['status', 'data_inclusao', 'data_final', 'id_transferencia_direta_estoque'])) }}"
                class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-green-700" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                XLS
            </a>

            {{-- Usar o novo componente de botões de exportação --}}
            <a href="{{route('admin.export.xml', request()->only(['status', 'data_inclusao', 'data_final', 'id_transferencia_direta_estoque']))}}"
                class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                XML
            </a>
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.transferenciaDiretoEstoque.index') }}"
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