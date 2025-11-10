<div x-show="imobilizadosOpen" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-2 mt-2">

    <!-- Cadastro Imobilizados -->
    @can('ver_relacaoimobilizado')
    <a href="{{ route('admin.cadastroimobilizado.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.cadastroimobilizado.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Cadastro Imobilizados
    </a>
    @endcan

    <!-- Estoque Imobilizados -->
    @can('ver_relacaoimobilizado')
    <a href="{{ route('admin.estoqueimobilizado.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.estoqueimobilizado.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Estoque Imobilizados
    </a>
    @endcan

    <!-- Descarte Imobilizados -->
    @can('ver_relacaoimobilizado')
    <a href="{{ route('admin.descarteimobilizado.index') }}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Descarte Imobilizados
    </a>
    @endcan

    <!-- Produtos Imobilizados -->
    @can('ver_tipoimobilizado')
    <a href="{{ route('admin.produtosimobilizados.index') }}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Produtos Imobilizados
    </a>
    @endcan

    <!-- Submenu Transferencia -->
    <div x-data="{ transferenciaImobilizadoOpen: false }">
        @php
        $hasTransferenciaImobilizadoPermissions = auth()->user()->can('ver_relacaoimobilizado');
        @endphp

        @if($hasTransferenciaImobilizadoPermissions)
        <a href="#" @click="transferenciaImobilizadoOpen = !transferenciaImobilizadoOpen"
            class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            <span class="tracking-tight">Transferencia</span>
            <svg x-bind:class="{ 'rotate-180': transferenciaImobilizadoOpen }" class="w-4 h-4 ml-2 transition-transform"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>

        <div x-show="transferenciaImobilizadoOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 mt-2 max-h-48 overflow-y-auto space-y-2">

            <!-- Transferência Imobilizados Veiculos -->
            @can('ver_relacaoimobilizado')
            <a href="{{ route('admin.transfimobilizadoveiculo.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Transferência Imobilizados Veiculos
            </a>
            @endcan

            <!-- Devoluçãos Imobilizados Veiculos -->
            @can('ver_relacaoimobilizado')
            <a href="{{ route('admin.devolucaoimobilizadoveiculo.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Devoluçãos Imobilizados Veiculos
            </a>
            @endcan
        </div>
        @endif
    </div>

    <!-- Submenu Requisições -->
    <div x-data="{ requisicoesOpen: false }">
        @php
        $hasRequisicoesPermissions = auth()->user()->can('ver_relacaoimobilizado') ||
        auth()->user()->can('ver_vrequisicaoprodutoos') ||
        auth()->user()->can('ver_relacaosolicitacaoespecas');
        @endphp

        @if($hasRequisicoesPermissions)
        <a href="#" @click="requisicoesOpen = !requisicoesOpen"
            class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            <span class="tracking-tight">Requisições</span>
            <svg x-bind:class="{ 'rotate-180': requisicoesOpen }" class="w-4 h-4 ml-2 transition-transform" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>

        <div x-show="requisicoesOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 mt-2 max-h-48 overflow-y-auto space-y-2">

            <!-- Submenu Solicitações -->
            <div x-data="{ solicitacoesOpen: false }">
                @php
                $hasSolicitacoesPermissions = auth()->user()->can('ver_relacaoimobilizado') ||
                auth()->user()->can('ver_vrequisicaoprodutoos');
                @endphp

                @if($hasSolicitacoesPermissions)
                <a href="#" @click="solicitacoesOpen = !solicitacoesOpen"
                    class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                    <span class="tracking-tight">Solicitações</span>
                    <svg x-bind:class="{ 'rotate-180': solicitacoesOpen }" class="w-4 h-4 ml-2 transition-transform"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </a>

                <div x-show="solicitacoesOpen" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="pl-4 mt-2 max-h-48 overflow-y-auto space-y-2">

                    <!-- Aprovar Requisição de Imobilizados -->
                    @can('ver_relacaoimobilizado')
                    <a href="{{ route('admin.aprovacaorelacaoimobilizado.index') }}"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                        Aprovar Requisição de Imobilizados
                    </a>
                    @endcan

                    <!-- Aprovar Requisição de Imobilizados (Gestor) -->
                    @can('ver_relacaoimobilizado')
                    <a href="{{ route('admin.aprovacaoimobilizadogestor.index') }}"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                        Aprovar Requisição de Imobilizados (Gestor)
                    </a>
                    @endcan

                    <!-- Solicitações de Imobilizados -->
                    @can('ver_vrequisicaoprodutoos')
                    <a href="{{ route('admin.requisicaoimobilizados.index') }}"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                        Solicitações de Imobilizados
                    </a>
                    @endcan
                </div>
                @endif
            </div>

            <!-- Saida de Imobilizados -->
            @can('ver_relacaoimobilizado')
            <a href="{{ route('admin.saidarelacaoimobilizado.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Saida de Imobilizados
            </a>
            @endcan

            <!-- Recebimento Imobilizados -->
            @can('ver_relacaoimobilizado')
            <a href="{{ route('admin.recebimentoimobilizado.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Recebimento Imobilizados
            </a>
            @endcan
        </div>
        @endif
    </div>

    <!-- Submenu Ordem de Serviço -->
    <div x-data="{ solicitacoesOpen: false }">
        @php
        $hasOrdemServicoPermissions = auth()->user()->can('ver_tipomanutencaoimobilizado');
        @endphp

        @if($hasOrdemServicoPermissions)
        <a href="#" @click="solicitacoesOpen = !solicitacoesOpen"
            class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            <span class="tracking-tight">Ordem de Serviço</span>
            <svg x-bind:class="{ 'rotate-180': solicitacoesOpen }" class="w-4 h-4 ml-2 transition-transform" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>

        <div x-show="solicitacoesOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 mt-2 max-h-48 overflow-y-auto space-y-2">

            <!-- Ordem de Serviço Imobilizado -->
            @can('ver_tipomanutencaoimobilizado')
            <a href="{{ route('admin.ordemservicoimobilizado.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Ordem de Serviço Imobilizado
            </a>
            @endcan
        </div>
        @endif
    </div>

    <div x-data="{ imobilizadorelatorio: false }">
        <a href="#" @click="imobilizadorelatorio = !imobilizadorelatorio"
            class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            <span class="tracking-tight">Relatórios</span>
            <svg x-bind:class="{ 'rotate-180': imobilizadorelatorio }" class="w-4 h-4 ml-2 transition-transform"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>
        <div x-show="imobilizadorelatorio" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 mt-2 max-h-48 overflow-y-auto space-y-2">

            {{-- Itens comentados - mantidos conforme original --}}


            <a href="{{route('admin.relatorioprodutoimobilizado.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório Produtos Imobilizados
            </a>
            <a href="{{route('admin.relatorioorigembaixas.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório Origem Baixas de Peças
            </a>
            <a href="{{route('admin.relatoriohistoricoimobilizado.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório Historico Imobilizados
            </a>
        </div>
    </div>
</div>