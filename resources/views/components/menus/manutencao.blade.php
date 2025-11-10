<div x-show="manutencaoOpen" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-2 mt-2">

    @can('ver_manutencao')
        <a href="https://app.powerbi.com/view?r=eyJrIjoiOGFlN2VmNmItNTY5Mi00YjBlLTkyN2ItYTM3NjRjMDVlMDNjIiwidCI6IjE5YWNlNzk0LWUxYWItNDU4ZC1hZjY4LTRlN2M0NTFlZGJhMiJ9&pageName=7571d5448a05099109b9"
            target="_blank"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Indicadores de Ordem de Serviço
        </a>
    @endcan

    <a href="https://carvalima.unitopconsultoria.com.br/manutencao" target="_blank"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Indicadores de Manutenção
    </a>

    <!-- Relatorios -->
    <div x-data="{ abastecimentosrelatorios: false }">
        <a href="#" @click="abastecimentosrelatorios = !abastecimentosrelatorios"
            class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            <span class="tracking-tight">Relatórios</span>
            <svg x-bind:class="{ 'rotate-180': abastecimentosrelatorios }" class="w-4 h-4 ml-2 transition-transform"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>
        <div x-show="abastecimentosrelatorios" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 mt-2 max-h-48 overflow-y-auto space-y-2">

            {{-- Itens comentados - mantidos conforme original --}}


            <a href="{{ route('admin.fornecedorescomissionadosrelatorio.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Fornecedores Comissionados
            </a>

            <a href=" {{ route('admin.historicomanutencaoveiculo.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Histórico de Manutenções do Veículo
            </a>

            <a href=" {{ route('admin.relatoriochecklist.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório Tabela Checklist
            </a>

            <a href=" {{ route('admin.relatorionotafiscalexterna.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório de Notas Fiscais Externas
            </a>

            <a href=" {{ route('admin.relatoriomanutencaodetalhada.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório de Manutenções Detalhadas Por O.S
            </a>

            <a href=" {{ route('admin.relatoriomanutencaovencidas.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório de Manutenções Preventivas
            </a>

            <a href=" {{ route('admin.relatoriosinteticonfos.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório Sintético de Notas Fiscais por O.S
            </a>

            <a href="{{ route('admin.relatoriogeralchecklist.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório Geral de Checklist
            </a>

            <a href=" {{ route('admin.relatorioordemservicostatus.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório de Ordem de Serviços por Status

            </a>

            <a href=" {{ route('admin.relatoriopecasutilizadasos.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório de Peças Utilizadas Por O.S

            </a>

            <a href=" {{ route('admin.relatorioservicosutilizadasos.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório de Serviços Utilizadas Por O.S
            </a>
            <!---->
        </div>
    </div>

    <!-- Submenu Pré-O.S -->
    <div x-data="{ subMenuManutencaoPreOs: false }">
        @php
            $hasPreOsPermissions = auth()->user()->can('ver_preordemservico');
        @endphp

        @if ($hasPreOsPermissions)
            <!-- Listagem Pré-O.S Nova -->
            @can('ver_preordemservico')
                <a href="{{ route('admin.manutencaopreordemserviconova.index') }}"
                    class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                    Pré-O.S
                </a>
            @endcan

            {{-- @can('ver_preordemservico')
        <a href="{{ route('admin.manutencaopreordemservicofinalizada.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Listagem Pré-O.S Finalizadas
        </a>
        @endcan --}}
        @endif
    </div>

    <!-- Listagem Ordem de Serviço -->
    @can('ver_ordemservico')
        <a href="{{ route('admin.ordemservicos.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Ordem de Serviço - O.S
        </a>
    @endcan

    <!-- Listagem Ordem de Serviço Canceladas -->
    {{-- @can('ver_ordemservico')
    <a href="{{ route('admin.ordemservicocanceladas.index') }}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Listagem Ordem de Serviço Canceladas
    </a>
    @endcan --}}

    <!-- Listagem O.S Preventiva Auxiliares -->
    @can('ver_ordemservico')
        <a href="{{ route('admin.ordemservicoauxiliares.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Listagem O.S Preventiva Auxiliares
        </a>
    @endcan

    <a href="{{ route('admin.manutencaoservicos.index') }}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Listagem Serviços
    </a>

    <!-- Monitoramento das Manutenções -->
    @can('ver_vcontrolemanutencaofrota')
        <a href="{{ route('admin.monitoramentoDasManutencoes.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Monitoramento das Manutenções
        </a>
    @endcan



    {{-- @can('ver_servico')
    <a href="#"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Serviços
    </a>
    @endcan --}}

    <!-- Controle de Manutenção Frota -->
    {{-- @can('ver_vcontrolemanutencaofrota')
        <a href="{{ route('admin.controlemanutancaofrota.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Consulta de Manutenção
        </a>
    @endcan --}}

    <!-- Km Veículos em Comodato -->
    @can('ver_kmcomotado')
        <a href="{{ route('admin.manutencaokmveiculocomodato.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Km Veículos em Comodato
        </a>
    @endcan

    <!-- Serviços Mecânicos -->
    @can('ver_servicosmecanico')
        <a href="{{ route('admin.manutencaoservicosmecanico.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Serviços Mecânicos
        </a>
    @endcan

    <!-- Submenu Cadastros -->
    <div x-data="{ subMenuManutencaoCadastros: false }">
        @php
            $hasManutencaoCadastrosPermissions =
                auth()->user()->can('ver_nfordemservico') || auth()->user()->can('ver_statusordemservico');
        @endphp

        {{-- @if ($hasManutencaoCadastrosPermissions)
        <a href="#" @click="subMenuManutencaoCadastros = !subMenuManutencaoCadastros"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Cadastros
        </a>

        <div x-show="subMenuManutencaoCadastros" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 space-y-2 mt-2">

            <!-- Notas Fiscais Rateio -->
            @can('ver_nfordemservico')
            <a href="{{ route('admin.listagemoslacamentoservicorateio.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Notas Fiscais Rateio
            </a>
            @endcan


        </div>
        @endif --}}
    </div>

    <!-- Submenu Manutenções -->
    <div x-data="{ subMenuManutencaoPreOs: false }">
        @php
            $hasManutencaoGestaoPermissions =
                auth()->user()->can('ver_categoriaplanejamentomanutencao') ||
                auth()->user()->can('ver_servicos') ||
                auth()->user()->can('ver_servicoxfornecedor') ||
                auth()->user()->can('ver_despesasveiculos');
        @endphp

        @if ($hasManutencaoGestaoPermissions)
            <a href="#" @click="subMenuManutencaoPreOs = !subMenuManutencaoPreOs"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Manutenções
            </a>

            <div x-show="subMenuManutencaoPreOs" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95" class="pl-4 space-y-2 mt-2">

                <!-- Configuração Manutenção X Categoria -->
                @can('ver_categoriaplanejamentomanutencao')
                    <a href="{{ route('admin.manutencaocategoria.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.manutencaocategoria.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Configuração Manutenção X Categoria
                    </a>
                @endcan

                <!-- Manutenção X Serviços -->
                @can('ver_servicos')
                    <a href="{{ route('admin.manutencaoservico.index') }}"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                        Manutenção X Serviços
                    </a>
                @endcan

                <!-- Serviço X Fornecedor -->
                @can('ver_servicoxfornecedor')
                    <a href="{{ route('admin.servicofornecedor.index') }}"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                        Serviço X Fornecedor
                    </a>
                @endcan

                <!-- Relação de Despesas Veículos -->
                {{-- @can('ver_despesasveiculos')
            <a href="{{ route('admin.relacaodespesasveiculos.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relação de Despesas Veículos
            </a>
            @endcan --}}
            </div>
        @endif
    </div>
</div>
