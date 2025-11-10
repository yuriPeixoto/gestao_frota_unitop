<div class="space-y-4">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    {{-- Formato front relatório --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <x-forms.input type="date" name="data_inclusao" label="Data Inicial:" value="{{ request('data_inclusao') }}" />

        <x-forms.input type="date" name="data_final" label="Data Final:" value="{{ request('data_final') }}" />

        <x-forms.smart-select name="id_tipo_combustivel" label="Tipo Combustivel:" placeholder="Selecionar"
            value="{{ request('id_tipo_combustivel') }}" asyncSearch="false" :options="$tipo" />

        <x-forms.smart-select name="id_filial" label="Filial:" placeholder="Selecionar"
            value="{{ request('id_filial') }}" asyncSearch="false" :options="$filial" />
        {{-- :searchUrl="route('admin.api.veiculo.search')" --}}

        <x-forms.smart-select name="id_departamento" label="Departamento:" placeholder="Selecionar"
            value="{{ request('id_departamento') }}" asyncSearch="false" :options="$departamento" />

        <x-forms.smart-select name="id_veiculo" label="Placa:" placeholder="Selecionar"
            value="{{ request('id_veiculo') }}" asyncSearch="false" :options="$placa" />

        <x-forms.smart-select name="id_tipo_abastecimento" label="Tipo Abastecimento:" placeholder="Selecionar"
            value="{{ request('id_tipo_abastecimento') }}" asyncSearch="false" />

    </div>

    {{-- Formato Ações botão limpar - pdf excel --}}
    <div class="flex justify-between mt-4">
        <div></div>

        <div class="flex space-x-2">

            <a href="{{ route('admin.relatorioabastecimentototais.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            {{-- <button type="button" x-on:click="$store.relatorioabastecimentototais.gerarPdf()"
                :disabled="$store.relatorioabastecimentototais.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatorioabastecimentototais.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatorioabastecimentototais.loading" class="h-4 w-4 mr-2" />
                <span x-text="$store.relatorioabastecimentototais.loading ? 'Gerando...' : 'Buscar PDF'"></span>
            </button> --}}

            <button type="button" x-on:click="$store.relatorioabastecimentototais.gerarExcel()"
                :disabled="$store.relatorioabastecimentototais.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatorioabastecimentototais.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatorioabastecimentototais.loading" class="h-4 w-4 mr-2" />
                <span x-text="$store.relatorioabastecimentototais.loading ? 'Gerando...' : 'Buscar Excel'"></span>
            </button>

        </div>
    </div>
</div>