<div class="space-y-4">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    {{-- Formato front relatório --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-forms.smart-select name="id_mot_unitop" label="Motorista:" value="{{ request('id_mot_unitop') }}"
            :options="$motorista" />
        <x-forms.smart-select name="placa_sascar" label="Placa:" value="{{ request('placa_sascar') }}"
            :options="$placa" />
    </div>


    {{-- Formato Ações botão limpar - pdf excel --}}
    <div class="flex justify-between mt-4">
        <div></div>

        <div class="flex space-x-2">

            <a href="{{ route('admin.relatoriovaloresexcedentes.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="button" x-on:click="$store.relatoriovaloresexcedentes.gerarPdf()"
                :disabled="$store.relatoriovaloresexcedentes.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatoriovaloresexcedentes.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatoriovaloresexcedentes.loading" class="h-4 w-4 mr-2" />
                <span x-text="$store.relatoriovaloresexcedentes.loading ? 'Gerando...' : 'Gerar PDF'"></span>
            </button>

            {{-- <button type="button" x-on:click="$store.relatoriovaloresexcedentes.gerarRelatorioPrevia()"
                :disabled="$store.relatoriovaloresexcedentes.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatoriovaloresexcedentes.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatoriovaloresexcedentes.loading" class="h-4 w-4 mr-2" />
                <span
                    x-text="$store.relatoriovaloresexcedentes.loading ? 'Gerando...' : ' Extrato Motorista/RH Excel'"></span>
            </button> --}}

            <button type="button" x-on:click="$store.relatoriovaloresexcedentes.gerarExcel()"
                :disabled="$store.relatoriovaloresexcedentes.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatoriovaloresexcedentes.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatoriovaloresexcedentes.loading" class="h-4 w-4 mr-2" />
                <span x-text="$store.relatoriovaloresexcedentes.loading ? 'Gerando...' : 'Gerar Excel'"></span>
            </button>

        </div>
    </div>
</div>