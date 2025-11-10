<div class="space-y-4">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />
    <form id="filterForm" method="GET" action="{{ route('admin.relatoriocompraevendaveiculo.index') }}">

        {{-- Formato front relatório --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-forms.smart-select name="placa" label="Placa:" placeholder="Selecionar" :options="$placa"
                value="{{ request('placa') }}" :searchUrl="route('admin.api.veiculo.search')" asyncSearch="false" />

            <x-forms.input name="chassi" label="Chassi:" value="{{ request('chassi') }}" />

            <x-forms.input name="renavam" label="Renavam:" value="{{ request('renavam') }}" />

            <x-forms.input type="date" name="ano_fabricacao" label="Ano Fabricacao:"
                value="{{ request('ano_fabricacao') }}" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <x-forms.smart-select name="id_filial" label="Filial:" placeholder="Selecionar" :options="$filial"
                value="{{ request('id_filial') }}" />

            <x-forms.smart-select name="descricao_departamento" label="Departamento:" placeholder="Selecionar"
                :options="$departamento" value="{{ request('descricao_departamento') }}"
                :searchUrl="route('admin.api.departamento.search')" asyncSearch="false" />

            <x-forms.smart-select name="descricao_modelo_veiculo" label="Modelo Veiculo:" placeholder="Selecionar"
                :options="$modelo" value="{{ request('descricao_modelo_veiculo') }}" />

            <x-forms.input type="date" name="data_compra" label="Data Compra:" value="{{ request('data_compra') }}" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">


            <x-forms.input type="date" name="data_venda" label="Data Venda:" value="{{ request('data_venda') }}" />


        </div>
    </form>
    {{-- Formato Ações botão limpar - pdf excel --}}
    <div class="flex justify-between mt-4">
        <div></div>

        <div class="flex space-x-2">

            <a href="{{ route('admin.relatoriocompraevendaveiculo.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            {{-- Usar o novo componente de botões de exportação --}}
            <button type="submit" form="filterForm"
                formaction="{{ route('admin.relatoriocompraevendaveiculo.exportPdf') }}" formtarget="_blank" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded
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
                formaction="{{ route('admin.relatoriocompraevendaveiculo.exportXls') }}" formtarget="_blank" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded
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