<div class="space-y-4">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    {{-- Formato front relatório --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <!-- Filial -->
        <x-forms.smart-select name="id" label="Filial:" placeholder="Selecionar" :options="$filiais"
            value="{{ request('id') }}" />


        <!-- Placa -->
        <x-forms.smart-select name="id_veiculo" label="Placa:" placeholder="Selecionar" :options="$veiculos"
            value="{{ request('id_veiculo') }}" :searchUrl="route('admin.api.veiculos.search')" asyncSearch="false" />

        <!-- MP's / Manutenção -->
        <x-forms.smart-select name="id_manutencao" label="Descrição Manutenção:" placeholder="Selecionar"
            :options="$manutencoes" value="{{ request('id_manutencao') }}" />

        <!-- Categoria -->
        <x-forms.smart-select name="id_categoria" label="Categoria:" placeholder="Selecionar" :options="$categorias"
            value="{{ request('id_categoria') }}" />

    </div>

    {{-- Formato Ações botão limpar - pdf excel --}}
    <div class="flex justify-between mt-4">
        <div></div>

        <div class="flex space-x-2">

            <a href="{{ route('admin.relatoriomanutencaovencidas.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="button" x-on:click="$store.relatorioManutencaoVencidas.gerarPdf()"
                :disabled="$store.relatorioManutencaoVencidas.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatorioManutencaoVencidas.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatorioManutencaoVencidas.loading" class="h-4 w-4 mr-2" />
                <span x-text="$store.relatorioManutencaoVencidas.loading ? 'Gerando...' : 'Buscar PDF'"></span>
            </button>

            <button type="button" x-on:click="$store.relatorioManutencaoVencidas.gerarExcel()"
                :disabled="$store.relatorioManutencaoVencidas.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatorioManutencaoVencidas.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatorioManutencaoVencidas.loading" class="h-4 w-4 mr-2" />
                <span x-text="$store.relatorioManutencaoVencidas.loading ? 'Gerando...' : 'Buscar Excel'"></span>
            </button>

        </div>
    </div>
</div>