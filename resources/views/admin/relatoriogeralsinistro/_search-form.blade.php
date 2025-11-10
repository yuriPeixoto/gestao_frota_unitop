<div class="space-y-4">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    {{-- Formato front relatório --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <x-forms.input type="date" name="data_inclusao" label="Data Inicial:" value="{{ request('data_inclusao') }}" />

        <x-forms.input type="date" name="data_final" label="Data Final:" value="{{ request('data_final') }}" />

        <x-forms.smart-select name="id_motorista" label="Motorista:" placeholder="Selecionar" :options="$motorista"
            value="{{ request('id_motorista') }}" :searchUrl="route('admin.api.motorista.search')"
            asyncSearch="false" />

        <x-forms.smart-select name="id_sinistro" label="Cód. Sinistro:" placeholder="Selecionar" :options="$sinistro"
            value="{{ request('id_sinistro') }}" :searchUrl="route('admin.api.sinistro.search')" asyncSearch="false" />

        <x-forms.smart-select name="id_veiculo" label="Placa:" placeholder="Selecionar" :options="$placa"
            value="{{ request('id_veiculo') }}" :searchUrl="route('admin.api.veiculo.search')" asyncSearch="false" />

        <x-forms.smart-select name="id_filial" label="Filial:" placeholder="Selecionar" :options="$filial"
            value="{{ request('id_filial') }}" />

    </div>

    {{-- Formato Ações botão limpar - pdf excel --}}
    <div class="flex justify-between mt-4">
        <div></div>

        <div class="flex space-x-2">

            <a href="{{ route('admin.relatoriogeralsinistro.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="button" x-on:click="$store.relatoriogeralsinistro.gerarPdf()"
                :disabled="$store.relatoriogeralsinistro.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatoriogeralsinistro.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatoriogeralsinistro.loading" class="h-4 w-4 mr-2" />
                <span x-text="$store.relatoriogeralsinistro.loading ? 'Gerando...' : 'Buscar PDF'"></span>
            </button>

            {{-- <button type="button" x-on:click="$store.relatoriogeralsinistro.gerarPdfTotalizado()"
                :disabled="$store.relatoriogeralsinistro.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatoriogeralsinistro.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatoriogeralsinistro.loading" class="h-4 w-4 mr-2" />
                <span x-text="$store.relatoriogeralsinistro.loading ? 'Gerando...' : 'Buscar Totalizado'"></span>
            </button> --}}

            <button type="button" x-on:click="$store.relatoriogeralsinistro.gerarExcel()"
                :disabled="$store.relatoriogeralsinistro.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatoriogeralsinistro.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatoriogeralsinistro.loading" class="h-4 w-4 mr-2" />
                <span x-text="$store.relatoriogeralsinistro.loading ? 'Gerando...' : 'Buscar Excel'"></span>
            </button>

        </div>
    </div>
</div>