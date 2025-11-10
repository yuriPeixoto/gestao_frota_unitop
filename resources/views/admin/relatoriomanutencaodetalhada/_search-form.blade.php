<div class="space-y-4">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    {{-- Formato front relatório --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <!-- Filial -->
        <x-forms.smart-select name="id_filial" label="Filial:" placeholder="Selecionar" :options="$filiais"
            value="{{ request('id_filial') }}" />

        <!-- Departamento -->
        <x-forms.smart-select name="id_departamento" label="Departamento:" placeholder="Selecionar"
            :options="$departamentos" value="{{ request('id_departamento') }}" />

        <!-- MP's / Manutenção -->
        <x-forms.smart-select name="id_manutencao" label="MP's:" placeholder="Selecionar" :options="$manutencoes"
            value="{{ request('id_manutencao') }}" />

        <!-- Data inclusão inicial -->
        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Inclusão Inicial:"
                value="{{ request('data_inclusao') }}" />
        </div>

        <!-- Data inclusão final -->
        <div>
            <x-forms.input type="date" name="data_final" label="Data Inclusão Final:"
                value="{{ request('data_final') }}" />
        </div>

        <!-- Placa -->
        <x-forms.smart-select name="id_veiculo" label="Placa:" placeholder="Selecionar" :options="$veiculos"
            value="{{ request('id_veiculo') }}" :searchUrl="route('admin.api.veiculos.search')" asyncSearch="false" />

        <!-- Categoria -->
        <x-forms.smart-select name="id_categoria" label="Categoria:" placeholder="Selecionar" :options="$categorias"
            value="{{ request('id_categoria') }}" />

        <!-- Tipo da Ordem de Serviço -->
        <x-forms.smart-select name="id_tipo_ordem_servico" label="Tipo da Ordem de Serviço:" placeholder="Selecionar"
            :options="$tiposOS" value="{{ request('id_tipo_ordem_servico') }}" />

        <!-- Número da Ordem de Serviço -->
        <x-forms.smart-select name="id_ordem_servico" label="N° Ordem de Serviço:" placeholder="Selecionar"
            :options="$nf" value="{{ request('id_ordem_servico') }}" :searchUrl="route('admin.api.ordemservico.search')"
            asyncSearch="false" />

    </div>

    {{-- Formato Ações botão limpar - pdf excel --}}
    <div class="flex justify-between mt-4">
        <div></div>

        <div class="flex space-x-2">

            <a href="{{ route('admin.relatoriomanutencaodetalhada.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="button" x-on:click="$store.relatorioManutencaoDetalhada.gerarPdf()"
                :disabled="$store.relatorioManutencaoDetalhada.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatorioManutencaoDetalhada.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatorioManutencaoDetalhada.loading" class="h-4 w-4 mr-2" />
                <span x-text="$store.relatorioManutencaoDetalhada.loading ? 'Gerando...' : 'Buscar PDF'"></span>
            </button>

            <button type="button" x-on:click="$store.relatorioManutencaoDetalhada.gerarExcel()"
                :disabled="$store.relatorioManutencaoDetalhada.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatorioManutencaoDetalhada.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatorioManutencaoDetalhada.loading" class="h-4 w-4 mr-2" />
                <span x-text="$store.relatorioManutencaoDetalhada.loading ? 'Gerando...' : 'Buscar Excel'"></span>
            </button>

        </div>
    </div>
</div>