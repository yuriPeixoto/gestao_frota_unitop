<div x-show="abastecimentosOpen" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-2 mt-2">

    <!-- Abastecimento Manual -->
    @can('ver_abastecimentomanual')
        <a href="{{ route('admin.abastecimentomanual.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Abastecimento Manual
        </a>
    @endcan

    <!-- Listar Abastecimentos -->
    @can('ver_abastecimentoatstruckpagmanual')
        <a href="{{ route('admin.abastecimentosatstruckpagmanual.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Listar Abastecimentos
        </a>
    @endcan


    <!-- Valor de Combustível por Bomba -->
    @can('ver_valorcombustivelterceiro')
        <a href="{{ route('admin.valorcombustiveis.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Valor de Combustível por Bomba
        </a>
    @endcan

    @if (auth()->user()->can('ver_abastecimentosfaturamento') || auth()->user()->can('ver_abastecimentos_faturamento'))
        <a href="{{ route('admin.abastecimentosfaturamento.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Faturamento Abastecimento
        </a>
    @endif

    <!-- Encerrantes Abastecimento -->
    @can('ver_encerrante')
        <a href="{{ route('admin.encerrantes.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Encerrantes Abastecimento
        </a>
    @endcan

    <!-- Indicadores de Abastecimento (Link externo - sempre visível se módulo acessível) -->
    <a href="https://carvalima.unitopconsultoria.com.br/bi/" target="_blank"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Indicadores de Abastecimento
    </a>

    <!-- Saldo Estoque Combustível -->
    @can('ver_estoquecombustivel')
        <a href="{{ route('admin.estoque-combustivel.dashboard') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Saldo Estoque Combustível
        </a>
    @endcan

    <!-- Submenu Cadastros -->
    <div x-data="{ cadastrosOpen: false }">
        @php
            $hasCadastrosPermissions =
                auth()->user()->can('ver_bomba') ||
                auth()->user()->can('ver_permissaokmmanual') ||
                auth()->user()->can('ver_afericao_bomba') ||
                auth()->user()->can('ver_afericaobomba') ||
                auth()->user()->can('ver_entradaafericaoabastecimento') ||
                auth()->user()->can('ver_ajustekmabastecimento') ||
                auth()->user()->can('ver_ajuste_km_abastecimento') ||
                auth()->user()->can('ver_abastecimentosfaturamento') ||
                auth()->user()->can('ver_abastecimentos_faturamento') ||
                auth()->user()->can('ver_recebimentocombustivel') ||
                auth()->user()->can('ver_tanque') ||
                auth()->user()->can('ver_valorcombustivelterceiro');
        @endphp

        @if ($hasCadastrosPermissions)
            <a href="#" @click="cadastrosOpen = !cadastrosOpen"
                class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                <span class="tracking-tight">Cadastros</span>
                <svg x-bind:class="{ 'rotate-180': cadastrosOpen }" class="w-4 h-4 ml-2 transition-transform"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </a>

            <div x-show="cadastrosOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95" class="pl-4 mt-2 max-h-48 overflow-y-auto space-y-2">

                <!-- Ajuste do Km Manual -->
                @can('ver_ajustekmabastecimento')
                    <a href="{{ route('admin.ajustekm.index') }}"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                        Ajuste do Km Manual
                    </a>
                @endcan

                <!-- Controle de Veículos (Lançamento de Km Manual) -->
                @can('ver_permissaokmmanual')
                    <a href="{{ route('admin.permissaokmmanuals.index') }}"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                        Controle de Veículos (Lançamento de Km Manual)
                    </a>
                @endcan

                <!-- Entrada por Aferição de Bomba -->
                @if (auth()->user()->can('ver_afericaobomba') || auth()->user()->can('ver_afericao_bomba'))
                    <a href="{{ route('admin.afericaobombas.index') }}"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                        Entrada por Aferição de Bomba
                    </a>
                @endif

                <!-- Informar o KM do Abastecimento -->
                @can('ver_ajustekmabastecimento')
                    {{-- <a href="{{ route('admin.ajustekm.informar-km') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Informar o KM do Abastecimento
            </a> --}}
                    <!-- Link temporariamente removido conforme solicitação -->
                @endcan

                <!-- Faturamento Abastecimento -->


                <!-- Recebimento Combustíveis -->
                @can('ver_recebimentocombustivel')
                    <a href="{{ route('admin.recebimentocombustiveis.index') }}"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                        Recebimento Combustíveis
                    </a>
                @endcan

                <!-- Tanques -->
                @can('ver_tanque')
                    <a href="{{ route('admin.tanques.index') }}"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                        Tanques
                    </a>
                @endcan


            </div>
        @endif
    </div>

    <!-- Inconsistências -->
    @if (auth()->user()->can('ver_inconsistenciaats') || auth()->user()->can('ver_inconsistenciatruckpag'))
        <a href="{{ route('admin.inconsistencias.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Inconsistências
        </a>
    @endif

    <!-- Reprocessar Integrações -->
    @if (auth()->user()->can('ver_abastecimentointegracao') ||
            auth()->user()->can('editar_abastecimentointegracao') ||
            auth()->user()->can('ver_reprocessar_integracao') ||
            auth()->user()->can('editar_reprocessar_integracao'))
        <a href="{{ route('admin.reprocessar.index') }}"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Reprocessar Integrações
        </a>
    @endif

    <div x-data="{ abastecimentosrelatorios: false }">
        @php
            $hasRelatoriosPermissions =
                auth()->user()->can('relatorio_abastecimento') ||
                auth()->user()->can('ver_abastecimento_manual_relatorio') ||
                auth()->user()->can('ver_abastecimento_placa_totalizado') ||
                auth()->user()->can('ver_ajustekmabastecimento') ||
                auth()->user()->can('ver_abastecimento_equipamento') ||
                auth()->user()->can('ver_extrato_abastecimento_terceiros') ||
                auth()->user()->can('ver_fechamento_abastecimento_media') ||
                auth()->user()->can('ver_reprocessar_integracao') ||
                auth()->user()->can('ver_encerrante') ||
                auth()->user()->can('ver_faturamento_abastecimento');
        @endphp

        @if ($hasRelatoriosPermissions)
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

                @if (auth()->user()->can('relatorio_abastecimento') || auth()->user()->can('ver_abastecimento_manual_relatorio'))
                    <a href="{{ route('admin.abastecimentomanualrelatorio.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.abastecimentomanualrelatorio.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Abastecimentos (TruckPag, ATS e Carvalima)
                    </a>
                @endif

                @can('ver_abastecimento_placa_totalizado')
                    <a href="{{ route('admin.abastecimentoplacatotalizado.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.abastecimentoplacatotalizado.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Abastecimento por Placa Totalizado
                    </a>
                @endcan

                <a href=" {{ route('admin.abastecimentoporbomposto.index') }}"
                    class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                    Abastecimento por Bomba/Posto

                </a>

                @can('ver_ajustekmabastecimento')
                    <a href="{{ route('admin.consultarlancamentoskmmanual.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.consultarlancamentoskmmanual.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Consultar Lançamentos Km Manual
                    </a>
                @endcan

                @can('ver_abastecimento_equipamento')
                    <a href="{{ route('admin.abastecimentoequipamento.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.abastecimentoequipamento.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Controle de Abastecimento por Equipamento
                    </a>
                @endcan

                @can('ver_extrato_abastecimento_terceiros')
                    <a href="{{ route('admin.extratoabastecimentoterceiros.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.extratoabastecimentoterceiros.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Extrato Abastecimento de Terceiros
                    </a>
                @endcan

                @can('ver_fechamento_abastecimento_media')
                    <a href="{{ route('admin.fechamentoabastecimentomedia.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.fechamentoabastecimentomedia.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Fechamento Abastecimento Média
                    </a>
                @endcan

                @can('ver_reprocessar_integracao')
                    <a href="{{ route('admin.integracao486Ssw.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.integracao486Ssw.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Integração 486Ssw
                    </a>
                @endcan

                @can('ver_encerrante')
                    <a href="{{ route('admin.listagemencerrantes.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.listagemencerrantes.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Listagem de Encerrantes
                    </a>
                @endcan

                @can('ver_ajustekmabastecimento')
                    <a href="{{ route('admin.listagemkmhistorico.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.listagemkmhistorico.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Listagem de Km Histórico
                    </a>
                @endcan

                @can('ver_faturamento_abastecimento')
                    <a href="{{ route('admin.faturamentoabastecimento.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.faturamentoabastecimento.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Listagem de Faturamento de Abastecimento
                    </a>
                @endcan
            </div>
        @endif
    </div>
</div>
