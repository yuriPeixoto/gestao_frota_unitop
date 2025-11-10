<div x-show="pneusOpen" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-2 mt-2" x-data="{
        submenuMovPneuOpen: false,
        submenuVendaPneuOpen: false,

        toggleSubmenu(submenu) {
            if (submenu === 'submenuMovPneu') {
                this.submenuMovPneuOpen = !this.submenuMovPneuOpen;
            } else if (submenu === 'submenuVendaPneu') {
                this.submenuVendaPneuOpen = !this.submenuVendaPneuOpen;
            }
        }
    }">

    <!-- Baixa de Pneus -->
    @can('ver_descartepneus')
    <a href="{{ route('admin.descartepneus.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.descartepneus.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Baixa de Pneus
    </a>
    @endcan

    <!-- Cadastro de Pneus -->
    @can('ver_pneu')
    <a href="{{ route('admin.pneus.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.pneus.index') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Cadastro de Pneus
    </a>
    @endcan

    <a href="{{ route('admin.calibragempneus.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.calibragempneus.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Calibragem de Pneus
    </a>

    <!-- Histórico de Vida dos Pneus -->
    @can('ver_pneu')
    <a href="{{ route('admin.pneuhistorico.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.pneuhistorico.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        {{--<svg class="w-4 h-4 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            </path>
        </svg>--}}
        Histórico de Vida dos Pneus
    </a>
    @endcan

    <!-- Listagem de Saída de Pneus -->
    @can('ver_requisicaopneu')
    <a href="{{ route('admin.saidaPneus.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.saidaPneus.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Listagem de Saída de Pneus
    </a>
    @endcan

    <!-- Submenu Movimentação de Pneus -->
    <div>
        @php
        $hasMovPneuPermissions =
        auth()->user()->can('ver_contagempneu') ||
        auth()->user()->can('ver_manutencaopneus') ||
        auth()->user()->can('ver_manutencaopneusentrada');
        @endphp

        @if ($hasMovPneuPermissions)
        <a href="#" @click="toggleSubmenu('submenuMovPneu')"
            class="flex items-center justify-between px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.contagempneus.*', 'admin.manutencaopneusentrada.*', 'admin.manutencaopneus.*', 'admin.pneus.borracharia.*', 'admin.movimentacaopneus.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
            <span class="tracking-tight">Movimentação de Pneus</span>
            <svg x-bind:class="{ 'rotate-180': submenuMovPneuOpen }" class="w-4 h-4 ml-2 transition-transform"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>

        <div x-show="submenuMovPneuOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 space-y-2 mt-2">

            <!-- Contagem de Pneus -->
            @can('ver_contagempneu')
            <a href="{{ route('admin.contagempneus.index') }}"
                class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.contagempneus.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                Contagem de Pneus
            </a>
            @endcan

            <!-- Entrada de Pneus da Manutenção -->
            {{-- @can('ver_manutencaopneusentrada')
            <a href="{{ route('admin.manutencaopneusentrada.index') }}"
                class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.manutencaopneusentrada.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                Entrada de Pneus da Manutenção
            </a>
            @endcan

            <!-- Envio de Pneus para Manutenção -->
            @can('ver_manutencaopneus')
            <a href="{{ route('admin.manutencaopneus.index') }}"
                class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.manutencaopneus.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                Envio de Pneus para Manutenção
            </a>
            @endcan --}}

            <!-- Listagem OS - Borracharia -->
            <a href="{{ route('admin.pneus.borracharia.index') }}"
                class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.pneus.borracharia.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                Listagem OS - Borracharia
            </a>

            <!-- Movimentação do Pneu -->
            @can('ver_manutencaopneus')
            <a href="{{ route('admin.movimentacaopneus.index') }}"
                class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.movimentacaopneus.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                Movimentação do Pneu
            </a>
            @endcan

            <!-- Pneus em Depósito -->
            <a href="{{ route('admin.pneusdeposito.index') }}"
                class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.pneusdeposito.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                Pneus em Depósito
            </a>
        </div>
        @endif
    </div>

    <!-- Transferência Pneus -->
    @can('ver_transferenciapneus')
    <a href="{{ route('admin.transferenciapneus.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.transferenciapneus.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Transferência Pneus
    </a>
    @endcan

    <!-- Submenu Venda Pneus -->
    <div>
        @php
        $hasVendaPneuPermissions = auth()->user()->can('ver_requisicaopneu');
        @endphp

        @if ($hasVendaPneuPermissions)
        <a href="#" @click="toggleSubmenu('submenuVendaPneu')"
            class="flex items-center justify-between px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.requisicaopneusvendas.*', 'admin.requisicaopneusvendassaida.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
            <span class="tracking-tight">Venda Pneus</span>
            <svg x-bind:class="{ 'rotate-180': submenuVendaPneuOpen }" class="w-4 h-4 ml-2 transition-transform"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>

        <div x-show="submenuVendaPneuOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 space-y-2 mt-2">

            <!-- Aprovação da Venda de Pneus -->
            @can('ver_requisicaopneu')
            <a href="{{ route('admin.requisicaopneusvendas.index') }}"
                class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.requisicaopneusvendas.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                Aprovação da Venda de Pneus
            </a>
            @endcan

            <!-- Saída de Pneus para Venda -->
            @can('ver_requisicaopneu')
            <a href="{{ route('admin.requisicaopneusvendassaida.index') }}"
                class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.requisicaopneusvendassaida.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                Saída de Pneus para Venda
            </a>
            @endcan
        </div>
        @endif
    </div>

    @can('ver_manutencaopneus')
    <a href="{{route('admin.envioerecebimentopneus.index')}}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Envio e Recebimento Pneus para Manutenção
    </a>
    @endcan

    <div x-data="{ pneusrelatorio: false }">
        <a href="#" @click="pneusrelatorio = !pneusrelatorio"
            class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            <span class="tracking-tight">Relatórios</span>
            <svg x-bind:class="{ 'rotate-180': pneusrelatorio }" class="w-4 h-4 ml-2 transition-transform" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>
        <div x-show="pneusrelatorio" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 mt-2 max-h-48 overflow-y-auto space-y-2">

            {{-- Itens comentados - mantidos conforme original --}}


            <a href="{{ route('admin.relatorioquantidadepneusporfilial.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório Quantidade Pneu por Filial
            </a>

            <a href="{{ route('admin.relatoriocontroleemovimentacaodeestoquedospneus.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Controle e Movimentação de Estoque dos Pneus
            </a>

            <a href="{{ route('admin.relatorioentradadepneumanutencao.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Entrada de Pneus para Manutenção
            </a>

            <a href="{{route('admin.relatoriolistagempneusdescartados.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Listagem de Pneus Descartados
            </a>

            <a href="{{route('admin.relatoriolistagempneusmanutencao.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Listagem de Pneus Manutenção
            </a>

            <a href="{{route('admin.relatoriopneusnaoaplicado.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio de Veiculo com Pneus não Aplicados
            </a>

            <a href="{{route('admin.relatoriopneusestoque.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Pneus em Estoque
            </a>

            <a href="{{route('admin.relatoriopneusstatus.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Pneus por Status
            </a>

            <a href="{{route('admin.relatoriopneusaplicado.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Pneus Aplicados
            </a>

            <a href="{{route('admin.relatoriocalibracao.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio de Calibração
            </a>

            <a href="{{route('admin.relatoriodehistoricomovimentacaopneus.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio de Historico de Movimentação do Pneu
            </a>


            <a href="{{route('admin.relatoriorequisicaopneusfinalizadas.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio de Requisições de Pneus Finalizadas
            </a>


            <a href="{{route('admin.relatoriovendapneus.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio de Vendas de Pneus
            </a>


            <a href="{{route('admin.relatoriorodiziopneus.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio Rodizio Pneu
            </a>

            {{-- <a href="{{route('admin.relatorioinventariopneusaplicados.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio de Inventario de Pneus Aplicados
            </a> --}}

        </div>
    </div>
</div>