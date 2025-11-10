<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="{{ asset('images/logoM.svg') }}" type="image/svg+xml">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- External Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Virtual Select CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/virtual-select-plugin/dist/virtual-select.min.css">
    <!-- Virtual Select JS -->
    <script src="https://cdn.jsdelivr.net/npm/virtual-select-plugin/dist/virtual-select.min.js" defer></script>

    <!-- Alpine.js x-cloak style -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/custom.js'])

    <!-- Bladewind -->
    <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/bladewind-ui.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
</head>

@if (session('success') || session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif
            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: '{{ session('error') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif
        });
    </script>
@endif

<body class="font-sans antialiased">
    <x-ui.keyboard-shortcuts />
    <div class="min-h-screen bg-gray-100">
        <div x-data="{
            // Estado da sidebar com persistência no localStorage
            sidebarOpen: localStorage.getItem('sidebar-open') !== 'false',
        
            // Estado dos menus com persistência
            abastecimentosOpen: localStorage.getItem('menu-abastecimentos') === 'true',
            comprasOpen: localStorage.getItem('menu-compras') === 'true',
            configOpen: localStorage.getItem('menu-config') === 'true',
            checklistOpen: localStorage.getItem('menu-checklist') === 'true',
            estoqueOpen: localStorage.getItem('menu-estoque') === 'true',
            imobilizadosOpen: localStorage.getItem('menu-imobilizados') === 'true',
            manutencaoOpen: localStorage.getItem('menu-manutencao') === 'true',
            pessoalOpen: localStorage.getItem('menu-pessoal') === 'true',
            pneusOpen: localStorage.getItem('menu-pneus') === 'true',
            sinistrosOpen: localStorage.getItem('menu-sinistros') === 'true',
            veiculosOpen: localStorage.getItem('menu-veiculos') === 'true',
            ticketsOpen: localStorage.getItem('menu-tickets') === 'true',
        
            // Função para alternar sidebar
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
                localStorage.setItem('sidebar-open', this.sidebarOpen);
            },
        
            // Função para alternar estado do menu e salvar no localStorage
            toggleMenu(menuName) {
                const isCurrentlyOpen = this[menuName + 'Open'];
        
                // Alternar o menu atual imediatamente
                this[menuName + 'Open'] = !this[menuName + 'Open'];
                localStorage.setItem('menu-' + menuName.toLowerCase(), this[menuName + 'Open']);
        
                // Se o menu atual foi aberto, fechar todos os outros de forma assíncrona
                if (!isCurrentlyOpen) {
                    setTimeout(() => {
                        // Lista de todos os menus
                        const allMenus = [
                            'abastecimentos', 'compras', 'config', 'checklist', 'estoque',
                            'imobilizados', 'manutencao', 'pessoal', 'pneus', 'sinistros',
                            'veiculos', 'tickets'
                        ];
        
                        // Fechar todos os outros menus
                        allMenus.forEach(menu => {
                            if (menu !== menuName && this[menu + 'Open']) {
                                this[menu + 'Open'] = false;
                                localStorage.setItem('menu-' + menu.toLowerCase(), false);
                            }
                        });
                    }, 10);
                }
            }
        }" @toggle-sidebar.window="toggleSidebar()"
            @toggle-sidebar-from-header.window="toggleSidebar()" class="flex min-h-screen bg-gray-100">
            <!-- Sidebar -->
            <aside class="border-r border-gray-200 bg-white transition-all duration-300 ease-in-out overflow-hidden"
                :class="sidebarOpen ? 'w-64' : 'w-0'">

                <div class="flex h-16 flex-shrink-0 items-center justify-between border-b bg-white px-6 w-64">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <div class="flex shrink-0 items-center">
                            <a href="{{ route('admin.dashboard') }}">
                                <img src="{{ asset('images/logoM.svg') }}" alt="Logo Unitop"
                                    class="h-10 w-auto max-w-[157px] transition-all sm:h-14 dark:brightness-100" />
                            </a>
                        </div>
                    </div>
                    <!-- Close sidebar button -->
                    <button @click="toggleSidebar()"
                        class="text-gray-500 hover:text-gray-700 focus:outline-none transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links com Verificação de Permissões -->
                <nav class="mt-6 flex-1 space-y-2 overflow-hidden">
                    <div class="h-full space-y-2 overflow-y-auto overflow-x-hidden px-4"
                        style="max-height: calc(100vh - 8rem);">
                        <!-- Dashboard -->
                        <a href="{{ route('admin.dashboard') }}"
                            class="{{ request()->routeIs('admin.dashboard') ? 'text-indigo-600 bg-indigo-50 rounded-lg' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200' }} flex items-center px-4 py-2.5 text-sm font-medium">
                            <x-icons.modules.dashboard />
                            Dashboard
                        </a>

                        <!-- Notificações -->
                        <a href="{{ route('notifications.index') }}"
                            class="{{ request()->routeIs('notifications.*') ? 'text-indigo-600 bg-indigo-50 rounded-lg' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200' }} flex items-center px-4 py-2.5 text-sm font-medium relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Notificações
                            @php
                                $userUnreadCount = Auth::user()->unreadNotificationsCount();
                            @endphp
                            @if ($userUnreadCount > 0)
                                <span
                                    class="ml-auto inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                                    {{ $userUnreadCount > 99 ? '99+' : $userUnreadCount }}
                                </span>
                            @endif
                        </a>

                        @php
                            use App\Helpers\PermissionHelper;
                        @endphp

                        <!-- Abastecimentos -->
                        @if (PermissionHelper::hasModuleAccess('abastecimentos'))
                            <div class="relative">
                                <button @click.prevent="toggleMenu('abastecimentos')"
                                    class="{{ request()->routeIs('admin.abastecimentoequipamento.*', 'admin.abastecimentomanual.*', 'admin.abastecimentomanualrelatorio.*', 'admin.abastecimentoplacatotalizado.*', 'admin.abastecimentosatstruckpagmanual.*', 'admin.abastecimentosfaturamento.*', 'admin.abastecimentostruckpag.*', 'admin.estoque-combustivel.*', 'admin.tipocombustiveis.*', 'admin.valorcombustiveis.*', 'admin.encerrantes.*', 'admin.listagemencerrantes.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                    <x-icons.modules.abastecimentos />
                                    Abastecimentos
                                    <svg x-bind:class="{ 'rotate-180': abastecimentosOpen }"
                                        class="ml-auto h-4 w-4 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                @include('components.menus.abastecimentos')
                            </div>
                        @endif

                        <!-- Compras -->
                        @if (PermissionHelper::hasModuleAccess('compras'))
                            <div class="relative">
                                <button @click.prevent="toggleMenu('compras')"
                                    class="{{ request()->routeIs('admin.compras.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                    <x-icons.modules.compras />
                                    Compras
                                    <svg x-bind:class="{ 'rotate-180': comprasOpen }"
                                        class="ml-auto h-4 w-4 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                @include('components.menus.compras')
                            </div>
                        @endif

                        <!-- Configurações -->
                        @if (PermissionHelper::hasModuleAccess('configuracoes'))
                            <div class="relative">
                                <button @click.prevent="toggleMenu('config')"
                                    class="{{ request()->routeIs('admin.configuracoes.*', 'admin.users.*', 'admin.permissions.*', 'admin.roles.*', 'admin.empresa.*', 'admin.filiais.*', 'admin.setores.*', 'admin.fornecedores.*', 'admin.bancos.*', 'admin.centroscusto.*', 'admin.contascontabeis.*', 'admin.tiposcombustivel.*', 'admin.tiposcalculo.*', 'admin.condicaopagamento.*', 'admin.formasPagamentos.*', 'admin.classificacaoContabil.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                    <x-icons.modules.configuracoes />
                                    Configurações
                                    <svg x-bind:class="{ 'rotate-180': configOpen }"
                                        class="ml-auto h-4 w-4 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                @include('components.menus.configuracoes')
                            </div>
                        @endif

                        <!-- Checklist -->
                        @if (PermissionHelper::hasModuleAccess('checklist'))
                            <div class="relative">
                                <button @click.prevent="toggleMenu('checklist')"
                                    class="{{ request()->routeIs('admin.checklist.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                    <x-icons.modules.checklist />
                                    Checklist
                                    <svg x-bind:class="{ 'rotate-180': checklistOpen }"
                                        class="ml-auto h-4 w-4 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                @include('components.menus.checklist')
                            </div>
                        @endif

                        <!-- Estoque -->
                        @if (PermissionHelper::hasModuleAccess('estoque'))
                            <div class="relative">
                                <button @click.prevent="toggleMenu('estoque')"
                                    class="{{ request()->routeIs('admin.estoque.*', 'admin.produtos.*', 'admin.movimentacao.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                    <x-icons.modules.estoque />
                                    Estoque
                                    <svg x-bind:class="{ 'rotate-180': estoqueOpen }"
                                        class="ml-auto h-4 w-4 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                @include('components.menus.estoque')
                            </div>
                        @endif

                        <!-- Imobilizados -->
                        @if (PermissionHelper::hasModuleAccess('imobilizados'))
                            <div class="relative">
                                <button @click.prevent="toggleMenu('imobilizados')"
                                    class="{{ request()->routeIs('admin.imobilizados.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                    <x-icons.modules.imobilizados />
                                    Imobilizados
                                    <svg x-bind:class="{ 'rotate-180': imobilizadosOpen }"
                                        class="ml-auto h-4 w-4 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                @include('components.menus.imobilizados')
                            </div>
                        @endif

                        <!-- Manutenção -->
                        @if (PermissionHelper::hasModuleAccess('manutencao'))
                            <div class="relative">
                                <button @click.prevent="toggleMenu('manutencao')"
                                    class="{{ request()->routeIs('admin.manutencao.*', 'admin.ordemServico.*', 'admin.preventivas.*', 'admin.corretivas.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                    <x-icons.modules.manutencao />
                                    Manutenção
                                    <svg x-bind:class="{ 'rotate-180': manutencaoOpen }"
                                        class="ml-auto h-4 w-4 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                @include('components.menus.manutencao')
                            </div>
                        @endif

                        <!-- Pessoal -->
                        @if (PermissionHelper::hasModuleAccess('pessoal'))
                            <div class="relative">
                                <button @click.prevent="toggleMenu('pessoal')"
                                    class="{{ request()->routeIs('admin.funcionarios.*', 'admin.motoristas.*', 'admin.habilitacoes.*', 'admin.cargos.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                    <x-icons.modules.pessoal />
                                    Pessoal
                                    <svg x-bind:class="{ 'rotate-180': pessoalOpen }"
                                        class="ml-auto h-4 w-4 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                @include('components.menus.pessoal')
                            </div>
                        @endif

                        <!-- Pneus -->
                        @if (PermissionHelper::hasModuleAccess('pneus'))
                            <div class="relative">
                                <button @click.prevent="toggleMenu('pneus')"
                                    class="{{ request()->routeIs('admin.pneus.*', 'admin.pneuhistorico.*', 'admin.descartepneus.*', 'admin.calibragempneus.*', 'admin.saidaPneus.*', 'admin.contagempneus.*', 'admin.manutencaopneusentrada.*', 'admin.manutencaopneus.*', 'admin.movimentacaopneus.*', 'admin.transferenciapneus.*', 'admin.requisicaopneusvendas.*', 'admin.requisicaopneusvendassaida.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                    <x-icons.modules.pneus />
                                    Pneus
                                    <svg x-bind:class="{ 'rotate-180': pneusOpen }"
                                        class="ml-auto h-4 w-4 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                @include('components.menus.pneus')
                            </div>
                        @endif

                        <!-- Sinistros -->
                        @if (PermissionHelper::hasModuleAccess('sinistro'))
                            <div class="relative">
                                <button @click.prevent="toggleMenu('sinistros')"
                                    class="{{ request()->routeIs('admin.sinistros.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                    <x-icons.modules.sinistros />
                                    Sinistros
                                    <svg x-bind:class="{ 'rotate-180': sinistrosOpen }"
                                        class="ml-auto h-4 w-4 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                @include('components.menus.sinistros')
                            </div>
                        @endif

                        <!-- Veículos -->
                        @if (PermissionHelper::hasModuleAccess('veiculos'))
                            <div class="relative">
                                <button @click.prevent="toggleMenu('veiculos')"
                                    class="{{ request()->routeIs('admin.veiculos.*', 'admin.api.veiculos.*', 'admin.modeloveiculos.*', 'admin.tipoveiculos.*', 'admin.subcategoriaveiculos.*', 'admin.atrelamentoveiculos.*', 'admin.transfimobilizadoveiculo.*', 'admin.devolucaoimobilizadoveiculo.*', 'admin.licenciamentoveiculos.*', 'admin.licenciamentos.*', 'admin.lancipvalicenciamentoseguros.*', 'admin.ipvaveiculos.*', 'admin.listagemipva.*', 'admin.seguroobrigatorio.*', 'admin.multas.*', 'admin.listagemmultas.*', 'admin.classificacaomultas.*', 'admin.dashboard-multas.*', 'admin.testefumacas.*', 'admin.tipocertificados.*', 'admin.historicomanutencaoveiculo.*', 'admin.relacaodespesasveiculos.*', 'admin.manutencaokmveiculocomodato.*', 'admin.cadastroimobilizado.*', 'admin.cadastroveiculovencimentario.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                    <x-icons.modules.veiculos />
                                    Veículos
                                    <svg x-bind:class="{ 'rotate-180': veiculosOpen }"
                                        class="ml-auto h-4 w-4 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                @include('components.menus.veiculos')
                            </div>
                        @endif

                        <!-- Tickets de Suporte -->
                        @if (PermissionHelper::hasModuleAccess('tickets'))
                            <div class="relative">
                                <button @click.prevent="toggleMenu('tickets')"
                                    class="{{ request()->routeIs('tickets.*', 'quality.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                    <x-icons.modules.tickets />
                                    Chamados
                                    <svg x-bind:class="{ 'rotate-180': ticketsOpen }"
                                        class="ml-auto h-4 w-4 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="ticketsOpen" x-transition class="mt-2 space-y-1 pl-4">
                                    <a href="{{ route('tickets.index') }}"
                                        class="{{ request()->routeIs('tickets.index') ? 'text-indigo-600 bg-indigo-50 rounded-lg font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg' }} flex items-center px-4 py-2 text-sm">
                                        <i class="fas fa-list mr-3 w-5 text-xs"></i>
                                        Meus Chamados
                                    </a>
                                    <a href="{{ route('tickets.create') }}"
                                        class="{{ request()->routeIs('tickets.create') ? 'text-indigo-600 bg-indigo-50 rounded-lg font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg' }} flex items-center px-4 py-2 text-sm">
                                        <i class="fas fa-plus mr-3 w-5 text-xs"></i>
                                        Novo Chamado
                                    </a>
                                    @can('tickets.quality_review')
                                        <a href="{{ route('quality.index') }}"
                                            class="{{ request()->routeIs('quality.*') ? 'text-indigo-600 bg-indigo-50 rounded-lg font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg' }} flex items-center px-4 py-2 text-sm">
                                            <i class="fas fa-check-circle mr-3 w-5 text-xs"></i>
                                            Dashboard Qualidade
                                        </a>
                                    @endcan
                                </div>
                            </div>
                            <!-- main blade -->
                            <div x-data="{ openMenu: null, toggleMenu(menu) { this.openMenu = this.openMenu === menu ? null : menu } }">
                                @if (PermissionHelper::hasModuleAccess('relatorio_gerenciais'))
                                    <div class="relative">
                                        <button @click.prevent="toggleMenu('relatorio_gerenciais')"
                                            class="{{ request()->routeIs('admin.relatorio_gerenciais.*')
                                                ? 'text-indigo-600 bg-indigo-50'
                                                : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                            <x-icons.document-text />
                                            Relatórios Gerenciais
                                            <svg x-bind:class="{ 'rotate-180': openMenu === 'relatorio_gerenciais' }"
                                                class="ml-auto h-4 w-4 transition-transform" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- aqui mostra o submenu -->
                                        <div x-show="openMenu === 'relatorio_gerenciais'" x-transition
                                            class="mt-2 space-y-1 pl-8">
                                            @include('components.menus.relatorio_gerenciais')
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div x-data="{ openMenu: null, toggleMenu(menu) { this.openMenu = this.openMenu === menu ? null : menu } }">
                                @if (PermissionHelper::hasModuleAccess('relatorio_premiacao'))
                                    <div class="relative">
                                        <button @click.prevent="toggleMenu('relatorio_premiacao')"
                                            class="{{ request()->routeIs('admin.relatorio_premiacao.*')
                                                ? 'text-indigo-600 bg-indigo-50'
                                                : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" class="bi bi-award" viewBox="0 0 16 16">
                                                <path
                                                    d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702z" />
                                                <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1z" />
                                            </svg>
                                            Prêmios Carvalima
                                            <svg x-bind:class="{ 'rotate-180': openMenu === 'relatorio_premiacao' }"
                                                class="ml-auto h-4 w-4 transition-transform" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- aqui mostra o submenu -->
                                        <div x-show="openMenu === 'relatorio_premiacao'" x-transition
                                            class="mt-2 space-y-1 pl-8">
                                            @include('components.menus.relatorio_premiacao')
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if (config('app.debug'))
                            <!-- Debug - Módulos Acessíveis -->
                            <div class="mt-4 rounded bg-gray-100 p-2 text-xs">
                                <strong>Módulos Acessíveis:</strong><br>
                                @foreach (PermissionHelper::getUserAccessibleModules() as $module)
                                    <span
                                        class="mb-1 mr-1 inline-block rounded bg-blue-200 px-2 py-1 text-blue-800">{{ $module }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="flex flex-1 flex-col transition-all duration-300 ease-in-out min-w-0">
                <!-- Top Navigation -->
                <div class="h-16 border-b border-gray-200 bg-white">
                    <div class="px-4 sm:px-6 lg:px-8">
                        <div class="flex h-16 justify-between">
                            <div class="flex items-center gap-2">
                                <!-- Mobile menu button -->
                                <button @click="sidebarOpen = true"
                                    class="px-3 text-gray-500 focus:outline-none lg:hidden">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>

                                <!-- Desktop toggle button - Hamburguer -->
                                <button @click="toggleSidebar()"
                                    class="hidden lg:flex items-center justify-center px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md focus:outline-none transition-colors duration-200"
                                    :title="sidebarOpen ? 'Esconder Menu' : 'Mostrar Menu'">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>
                            </div>

                            <!-- User Profile Dropdown -->
                            <div x-data="{ userDropdownOpen: false, notificationDropdownOpen: false }" class="relative flex items-center gap-4 pr-4">
                                <!-- Notifications Bell -->
                                <div class="relative">
                                    <button @click="notificationDropdownOpen = !notificationDropdownOpen"
                                        class="relative rounded-full p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        <span id="notification-badge"
                                            class="absolute right-0 top-0 inline-flex hidden -translate-y-1/2 translate-x-1/2 transform items-center justify-center rounded-full bg-red-600 px-2 py-1 text-xs font-bold leading-none text-white">
                                            0
                                        </span>
                                    </button>

                                    <!-- Notifications Dropdown -->
                                    <div x-show="notificationDropdownOpen"
                                        @click.away="notificationDropdownOpen = false"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="absolute right-0 z-50 mt-2 max-h-96 w-96 overflow-hidden rounded-lg bg-white shadow-xl"
                                        style="display: none;">
                                        <div class="flex items-center justify-between border-b border-gray-200 p-3">
                                            <h3 class="font-semibold text-gray-900">Notificações</h3>
                                            <button id="mark-all-read"
                                                class="text-xs text-blue-600 hover:text-blue-800">
                                                Marcar todas como lidas
                                            </button>
                                        </div>
                                        <div id="notification-dropdown" class="max-h-80 overflow-y-auto">
                                            <div id="notification-list">
                                                <div class="p-4 text-center text-gray-500">
                                                    <svg class="mx-auto mb-2 h-8 w-8 animate-spin text-gray-400"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    <p class="mt-2 text-sm">Carregando...</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="border-t border-gray-200 p-3">
                                            <a href="{{ route('notifications.index') }}"
                                                class="block text-center text-sm text-blue-600 hover:text-blue-800">
                                                Ver todas as notificações
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                {{-- @php
                                use App\Models\VFilial;
                                $user = Auth::user();

                                $filiais = $user->is_superuser
                                ? VFilial::orderBy('name')->get()
                                : $user->filiais()->orderBy('name')->get();
                                @endphp

                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                            <div>Filial: {{ $user->filial?->name ?? 'Nenhuma' }}</div>
                                            <div class="ml-1">
                                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        @forelse ($filiais as $filial)
                                        <x-dropdown-link href="#"
                                            onclick="event.preventDefault(); document.getElementById('trocar-filial-{{ $filial->id }}').submit();">
                                            {{ $filial->name }}
                                        </x-dropdown-link>

                                        <form id="trocar-filial-{{ $filial->id }}"
                                            action="{{ route('admin.trocafilial.filial') }}" method="POST"
                                            class="hidden">
                                            @csrf
                                            <input type="hidden" name="filial_id" value="{{ $filial->id }}">
                                        </form>
                                        @empty
                                        <div class="px-4 py-2 text-gray-500 text-sm">
                                            Nenhuma filial disponível
                                        </div>
                                        @endforelse
                                    </x-slot>
                                </x-dropdown> --}}

                                <div>
                                    <div class="mr-4 text-right">
                                        <div class="text-sm font-medium text-gray-700">
                                            {{ GetterFilial('nome') }}
                                        </div>
                                    </div>
                                </div>

                                <!-- User Avatar -->
                                <div @click="userDropdownOpen = !userDropdownOpen" class="cursor-pointer">
                                    <img class="h-14 w-14 rounded-full object-cover"
                                        src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}">
                                </div>

                                <!-- Dropdown Menu -->
                                <div x-show="userDropdownOpen" @click.away="userDropdownOpen = false" x-cloak
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute right-0 top-full z-50 mt-2 w-48 rounded-md bg-white py-1 shadow-lg">
                                    <a href="{{ route('profile.edit') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                            Sair
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page Content -->
                <main class="flex-1 overflow-x-hidden bg-gray-100">
                    <!-- Page Heading -->
                    @if (isset($header))
                        <header class="bg-white shadow-md">
                            <div class="mx-auto max-w-full px-4 py-2 sm:px-6 lg:px-8">
                                <div class="flex items-center gap-3">
                                    <!-- Botão Toggle Sidebar - Global -->
                                    <button @click="toggleSidebar()"
                                        class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md focus:outline-none transition-colors duration-200 flex-shrink-0"
                                        :title="sidebarOpen ? 'Esconder Menu' : 'Mostrar Menu'">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                    </button>

                                    <!-- Conteúdo do Header -->
                                    <div class="flex-1">
                                        {{ $header }}
                                    </div>
                                </div>
                            </div>
                        </header>
                    @endif

                    <div class="overflow-auto p-4 sm:p-6 lg:p-8">
                        {{ $slot ?? '' }}
                    </div>
                </main>
            </div>
        </div>
    </div>

    <x-ui.confirmation-modal icon="⚠️" iconClass="text-yellow-500" />

    <!-- Portal para componentes dinâmicos -->
    <div id="portal-root" class="relative z-[10000]"></div>

    @stack('scripts')

    <!-- Application Scripts -->
    <script>
        // CSRF Token Auto-Refresh System
        /**
         * Sistema de Auto-Refresh do CSRF Token
         * Previne erro 419 Page Expired
         */
        (function() {
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            let isRefreshing = false;

            // Função para atualizar o CSRF token
            function refreshCSRFToken() {
                if (isRefreshing) return Promise.resolve(csrfToken);

                isRefreshing = true;

                return fetch('/dashboard/current-time', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        // Extract CSRF token from response headers if available
                        const newToken = response.headers.get('X-CSRF-TOKEN');
                        if (newToken) {
                            updateCSRFToken(newToken);
                        }
                        return response.json();
                    })
                    .catch(error => {
                        console.warn('Erro ao atualizar CSRF token:', error);
                        return {
                            error: true
                        };
                    })
                    .finally(() => {
                        isRefreshing = false;
                    });
            }

            // Função para atualizar o token em todos os lugares
            function updateCSRFToken(newToken) {
                csrfToken = newToken;

                // Atualizar meta tag
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', newToken);
                }

                // Atualizar todos os inputs hidden com nome _token
                document.querySelectorAll('input[name="_token"]').forEach(input => {
                    input.value = newToken;
                });

                // Atualizar headers do axios se disponível
                if (typeof window.axios !== 'undefined') {
                    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
                }

                console.log('CSRF Token atualizado:', newToken.substring(0, 10) + '...');
            }

            // Interceptar requisições AJAX para adicionar CSRF token
            function setupAjaxInterceptors() {
                // Para fetch API
                const originalFetch = window.fetch;
                window.fetch = function(url, options = {}) {
                    // Adicionar CSRF token se for uma requisição POST/PUT/PATCH/DELETE
                    if (options.method && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(options.method
                            .toUpperCase())) {
                        options.headers = options.headers || {};

                        if (!options.headers['X-CSRF-TOKEN']) {
                            options.headers['X-CSRF-TOKEN'] = csrfToken;
                        }
                    }

                    return originalFetch(url, options).catch(error => {
                        // Se erro 419, tentar renovar token e repetir requisição
                        if (error.status === 419 || error.message.includes('CSRF')) {
                            return refreshCSRFToken().then(() => {
                                options.headers['X-CSRF-TOKEN'] = csrfToken;
                                return originalFetch(url, options);
                            });
                        }
                        throw error;
                    });
                };

                // Para jQuery AJAX se disponível
                if (typeof $ !== 'undefined' && $.ajaxSetup) {
                    $.ajaxSetup({
                        beforeSend: function(xhr, settings) {
                            if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                                xhr.setRequestHeader("X-CSRF-TOKEN", csrfToken);
                            }
                        }
                    });
                }
            }

            // Renovar token periodicamente (a cada 30 minutos)
            function startTokenRefreshTimer() {
                setInterval(() => {
                    refreshCSRFToken();
                }, 30 * 60 * 1000); // 30 minutos
            }

            // Verificar se sessão ainda é válida
            function checkSessionStatus() {
                fetch('/dashboard/current-time', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (response.status === 419 || response.status === 401) {
                            // Sessão expirada, redirecionar para login
                            window.location.href = '/login';
                        }
                    })
                    .catch(error => {
                        if (error.status === 419 || error.status === 401) {
                            window.location.href = '/login';
                        }
                    });
            }

            // Inicializar sistema
            document.addEventListener('DOMContentLoaded', function() {
                setupAjaxInterceptors();
                startTokenRefreshTimer();

                // Verificar status da sessão a cada 5 minutos
                setInterval(checkSessionStatus, 5 * 60 * 1000);

                console.log('Sistema de CSRF Token Auto-Refresh inicializado');
            });

            // Atualizar token antes da página ser fechada/recarregada
            window.addEventListener('beforeunload', function() {
                if (!isRefreshing) {
                    navigator.sendBeacon('/dashboard/current-time');
                }
            });

            // Expor função globalmente para uso manual
            window.refreshCSRFToken = refreshCSRFToken;
        })();
    </script>

    {{-- SISTEMA DE CAPTURA DO SMART-SELECT --}}
    {{-- Colocar APÓS os scripts dos componentes --}}
    <script>
        /**
         * Sistema de Captura de Seleções do Smart-Select - VERSÃO CORRIGIDA
         * Corrige erro de estrutura circular e melhora a captura de dados
         */

        window.SmartSelectListener = {
            // Armazenar callbacks registrados
            callbacks: new Map(),

            // Armazenar último valor de cada select
            lastValues: new Map(),

            // Flag para debug
            debug: false,

            /**
             * Registra um listener para um smart-select específico
             */
            listen(selectName, callback, options = {}) {
                if (!selectName || typeof callback !== 'function') {
                    console.error('SmartSelectListener: Nome do select e callback são obrigatórios');
                    return;
                }

                const config = {
                    immediate: false,
                    trackChanges: true,
                    includeObjects: true,
                    ...options
                };

                this.callbacks.set(selectName, {
                    callback,
                    config
                });
                this._setupListeners(selectName);

                if (config.immediate) {
                    this._executeCallback(selectName, this._getCurrentValue(selectName), 'immediate');
                }

                this._log(`Listener registrado para: ${selectName}`);
            },

            /**
             * Remove um listener
             */
            unlisten(selectName) {
                this.callbacks.delete(selectName);
                this.lastValues.delete(selectName);
                this._log(`Listener removido para: ${selectName}`);
            },

            /**
             * Obtém o valor atual de um smart-select
             */
            getValue(selectName) {
                return this._getCurrentValue(selectName);
            },

            /**
             * Obtém todos os valores atuais
             */
            getAllValues() {
                const values = {};
                document.querySelectorAll('[x-data*="asyncSearchableSelect"]').forEach(el => {
                    const nameAttr = el.querySelector('input[type="hidden"]')?.name;
                    if (nameAttr) {
                        const cleanName = nameAttr.replace('[]', '');
                        values[cleanName] = this._getCurrentValue(cleanName);
                    }
                });
                return values;
            },

            /**
             * Configura múltiplos listeners
             */
            _setupListeners(selectName) {
                // 1. Event listener customizado do componente
                document.addEventListener('select-change', (event) => {
                    if (event.detail && event.detail.name === selectName) {
                        this._handleSelection(selectName, event.detail, 'select-change');
                    }
                });

                // 2. Event listener específico por nome
                window.addEventListener(`${selectName}:selected`, (event) => {
                    if (event.detail) {
                        this._handleSelection(selectName, event.detail, 'named-event');
                    }
                });

                // 3. Observer de mudanças no DOM (input hidden)
                this._observeHiddenInput(selectName);
            },

            /**
             * Observer para mudanças no input hidden - CORRIGIDO
             */
            _observeHiddenInput(selectName) {
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                            const input = mutation.target;
                            if (input.name === selectName || input.name === `${selectName}[]`) {
                                this._handleInputChange(selectName, 'input-observer');
                            }
                        }

                        if (mutation.type === 'childList') {
                            mutation.addedNodes.forEach(node => {
                                if (node.nodeType === 1 && node.name &&
                                    (node.name === selectName || node.name ===
                                        `${selectName}[]`)) {
                                    this._handleInputChange(selectName, 'input-added');
                                }
                            });
                            mutation.removedNodes.forEach(node => {
                                if (node.nodeType === 1 && node.name &&
                                    (node.name === selectName || node.name ===
                                        `${selectName}[]`)) {
                                    this._handleInputChange(selectName, 'input-removed');
                                }
                            });
                        }
                    });
                });

                // Observar mudanças nos inputs hidden existentes
                document.querySelectorAll(`input[name="${selectName}"], input[name="${selectName}[]"]`).forEach(
                    input => {
                        observer.observe(input, {
                            attributes: true,
                            attributeFilter: ['value']
                        });
                        if (input.parentNode) {
                            observer.observe(input.parentNode, {
                                childList: true
                            });
                        }
                    });

                // Observer global para novos inputs
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            },

            /**
             * Manipula seleção capturada - CORRIGIDO
             */
            _handleSelection(selectName, data, source) {
                const config = this.callbacks.get(selectName)?.config;
                if (!config) return;

                // Normalizar dados para evitar estruturas circulares
                const normalizedData = this._normalizeData(data, selectName);

                // Verificar se houve mudança real
                const lastValue = this.lastValues.get(selectName);
                if (config.trackChanges && this._isEqual(normalizedData, lastValue)) {
                    return;
                }

                // Atualizar último valor usando cópia profunda segura
                this.lastValues.set(selectName, this._safeDeepCopy(normalizedData));

                // Executar callback
                this._executeCallback(selectName, normalizedData, source);
            },

            /**
             * Manipula mudanças no input hidden
             */
            _handleInputChange(selectName, source) {
                const currentValue = this._getCurrentValue(selectName);
                this._handleSelection(selectName, currentValue, source);
            },

            /**
             * Executa o callback registrado
             */
            _executeCallback(selectName, data, source) {
                const registered = this.callbacks.get(selectName);
                if (!registered) return;

                try {
                    this._log(`Executando callback para ${selectName} (fonte: ${source})`, data);
                    registered.callback(data, source);
                } catch (error) {
                    console.error(`Erro no callback de ${selectName}:`, error);
                }
            },

            /**
             * Obtém valor atual de um smart-select - MELHORADO
             */
            _getCurrentValue(selectName) {
                const data = {
                    name: selectName,
                    value: null,
                    values: [],
                    label: null,
                    labels: [],
                    object: null,
                    objects: [],
                    multiple: false
                };

                // 1. Buscar pelos inputs hidden
                const singleInput = document.querySelector(`input[name="${selectName}"]`);
                const multipleInputs = document.querySelectorAll(`input[name="${selectName}[]"]`);

                if (multipleInputs.length > 0) {
                    data.multiple = true;
                    data.values = Array.from(multipleInputs)
                        .map(input => input.value)
                        .filter(v => v !== '' && v !== null && v !== undefined);
                } else if (singleInput && singleInput.value) {
                    data.multiple = false;
                    data.values = [singleInput.value];
                }

                // 2. Tentar obter dados do Alpine.js de forma segura
                const alpineElement = document.querySelector(
                    `[x-data*="asyncSearchableSelect"][x-data*="'${selectName}'"]`);
                if (alpineElement) {
                    try {
                        if (alpineElement._x_dataStack && alpineElement._x_dataStack[0]) {
                            const alpineData = alpineElement._x_dataStack[0];

                            if (alpineData.selectedValues && Array.isArray(alpineData.selectedValues)) {
                                data.values = [...alpineData.selectedValues];
                            }
                            if (alpineData.selectedLabels && Array.isArray(alpineData.selectedLabels)) {
                                data.labels = [...alpineData.selectedLabels];
                            }
                            if (alpineData.selectedObjects && Array.isArray(alpineData.selectedObjects)) {
                                // Copiar objetos sem referências circulares
                                data.objects = alpineData.selectedObjects.map(obj => this._sanitizeObject(obj));
                            }
                            if (typeof alpineData.multiple !== 'undefined') {
                                data.multiple = alpineData.multiple;
                            }
                        }
                    } catch (e) {
                        this._log(`Erro ao acessar dados Alpine para ${selectName}:`, e.message);
                    }
                }

                // 3. Definir propriedades simples para retrocompatibilidade
                data.value = data.multiple ? data.values : (data.values[0] || null);
                data.label = data.multiple ? data.labels : (data.labels[0] || null);
                data.object = data.multiple ? data.objects : (data.objects[0] || null);

                return data;
            },

            /**
             * Normaliza dados para evitar estruturas circulares
             */
            _normalizeData(data, selectName) {
                if (!data) return {
                    name: selectName,
                    value: null,
                    values: [],
                    multiple: false
                };

                const normalized = {
                    name: data.name || selectName,
                    value: data.value || null,
                    values: Array.isArray(data.values) ? [...data.values] : (data.value ? [data.value] : []),
                    label: data.label || null,
                    labels: Array.isArray(data.labels) ? [...data.labels] : (data.label ? [data.label] : []),
                    object: data.object ? this._sanitizeObject(data.object) : null,
                    objects: Array.isArray(data.objects) ? data.objects.map(obj => this._sanitizeObject(obj)) : [],
                    multiple: Boolean(data.multiple)
                };

                // Garantir consistência
                if (!normalized.multiple && normalized.values.length > 0) {
                    normalized.value = normalized.values[0];
                    normalized.label = normalized.labels[0] || null;
                    normalized.object = normalized.objects[0] || null;
                }

                return normalized;
            },

            /**
             * Remove propriedades problemáticas de objetos
             */
            _sanitizeObject(obj) {
                if (!obj || typeof obj !== 'object') return obj;

                const sanitized = {};
                for (const [key, value] of Object.entries(obj)) {
                    // Pular propriedades que podem causar referências circulares
                    if (key.startsWith('_x_') || key.startsWith('$') ||
                        typeof value === 'function' || value instanceof HTMLElement) {
                        continue;
                    }

                    if (value && typeof value === 'object' && !Array.isArray(value)) {
                        // Para objetos aninhados, fazer cópia rasa
                        sanitized[key] = {
                            ...value
                        };
                    } else {
                        sanitized[key] = value;
                    }
                }
                return sanitized;
            },

            /**
             * Cópia profunda segura sem referências circulares
             */
            _safeDeepCopy(obj) {
                try {
                    return JSON.parse(JSON.stringify(obj));
                } catch (e) {
                    // Se falhar, fazer cópia rasa
                    return {
                        ...obj
                    };
                }
            },

            /**
             * Compara dois valores de forma segura - CORRIGIDO
             */
            _isEqual(a, b) {
                if (a === b) return true;
                if (!a || !b) return false;

                try {
                    // Comparar propriedades importantes
                    return (
                        a.name === b.name &&
                        JSON.stringify(a.values || []) === JSON.stringify(b.values || []) &&
                        JSON.stringify(a.labels || []) === JSON.stringify(b.labels || []) &&
                        a.multiple === b.multiple
                    );
                } catch (e) {
                    // Fallback para comparação simples
                    return false;
                }
            },

            /**
             * Log interno
             */
            _log(message, data = null) {
                if (this.debug) {
                    console.log(`[SmartSelectListener] ${message}`, data);
                }
            },

            /**
             * Ativa/desativa debug
             */
            setDebug(enabled) {
                this.debug = enabled;
                console.log(`SmartSelectListener debug: ${enabled ? 'ATIVADO' : 'DESATIVADO'}`);
            }
        };

        // ===== FUNÇÕES DE CONVENIÊNCIA =====
        window.onSmartSelectChange = function(selectName, callback, options = {}) {
            return SmartSelectListener.listen(selectName, callback, options);
        };

        window.getSmartSelectValue = function(selectName) {
            return SmartSelectListener.getValue(selectName);
        };

        window.onMultipleSmartSelectChange = function(listeners, options = {}) {
            Object.entries(listeners).forEach(([selectName, callback]) => {
                SmartSelectListener.listen(selectName, callback, options);
            });
        };

        // ===== AUTO-INICIALIZAÇÃO =====
        document.addEventListener('DOMContentLoaded', function() {
            console.log('SmartSelectListener CORRIGIDO inicializado e pronto para uso!');

            if (window.SmartSelectAutoDetect) {
                document.querySelectorAll('[x-data*="asyncSearchableSelect"]').forEach(el => {
                    const hiddenInput = el.querySelector('input[type="hidden"]');
                    if (hiddenInput) {
                        const selectName = hiddenInput.name.replace('[]', '');
                        console.log(`Smart-select detectado automaticamente: ${selectName}`);

                        if (typeof window.onAnySmartSelectChange === 'function') {
                            SmartSelectListener.listen(selectName, window.onAnySmartSelectChange);
                        }
                    }
                });
            }
        });
    </script>

    <script>
        /**
         * SISTEMA PARA DEFINIR VALORES NO SMART-SELECT
         * Múltiplas formas de definir valores programaticamente
         */

        // ===== FUNÇÃO PRINCIPAL PARA DEFINIR VALORES =====
        window.setSmartSelectValue = function(selectName, value, options = {}) {
            const config = {
                triggerEvents: true, // Disparar eventos de mudança
                updateLabel: true, // Atualizar label automaticamente
                forceUpdate: false, // Forçar atualização mesmo se valor for igual
                findByValue: true, // Buscar opção por valor
                findByLabel: false, // Buscar opção por label
                ...options
            };

            try {
                // Encontrar o elemento Alpine.js
                const alpineElement = document.querySelector(
                    `[x-data*="asyncSearchableSelect"][x-data*="'${selectName}'"]`);
                if (!alpineElement) {
                    console.error(`Smart-select "${selectName}" não encontrado`);
                    return false;
                }

                // Acessar dados do Alpine.js
                if (!alpineElement._x_dataStack || !alpineElement._x_dataStack[0]) {
                    console.error(`Dados Alpine.js não encontrados para "${selectName}"`);
                    return false;
                }

                const alpineData = alpineElement._x_dataStack[0];

                // Verificar se é seleção múltipla
                const isMultiple = alpineData.multiple || false;

                if (isMultiple) {
                    return setMultipleValues(alpineData, value, config, selectName);
                } else {
                    return setSingleValue(alpineData, value, config, selectName);
                }

            } catch (error) {
                console.error(`Erro ao definir valor para "${selectName}":`, error);
                return false;
            }
        };

        // ===== SELEÇÃO ÚNICA =====
        function setSingleValue(alpineData, value, config, selectName) {
            // Se valor é null/undefined, limpar seleção
            if (value === null || value === undefined || value === '') {
                clearSmartSelect(alpineData, selectName);
                return true;
            }

            // Encontrar a opção correspondente
            const option = findOption(alpineData, value, config);
            if (!option) {
                // Criar opção temporária se não encontrar
                if (config.createIfNotFound) {
                    const tempOption = createTempOption(value, config);
                    setOptionValues(alpineData, tempOption, false, config, selectName);
                    return true;
                }
                return false;
            }

            // Definir valores
            setOptionValues(alpineData, option, false, config, selectName);
            return true;
        }

        // ===== SELEÇÃO MÚLTIPLA =====
        function setMultipleValues(alpineData, values, config, selectName) {
            // Normalizar valores para array
            const valueArray = Array.isArray(values) ? values : [values];

            // Se array vazio, limpar seleção
            if (valueArray.length === 0) {
                clearSmartSelect(alpineData, selectName);
                return true;
            }

            const selectedOptions = [];
            const notFoundValues = [];

            // Encontrar todas as opções
            valueArray.forEach(value => {
                const option = findOption(alpineData, value, config);
                if (option) {
                    selectedOptions.push(option);
                } else {
                    notFoundValues.push(value);

                    // Criar opção temporária se configurado
                    if (config.createIfNotFound) {
                        selectedOptions.push(createTempOption(value, config));
                    }
                }
            });

            if (notFoundValues.length > 0) {
                console.warn(`Opções não encontradas em "${selectName}":`, notFoundValues);
            }

            // Definir valores múltiplos
            setMultipleOptionValues(alpineData, selectedOptions, config, selectName);
            return selectedOptions.length > 0;
        }

        // ===== FUNÇÕES AUXILIARES =====

        // Encontrar opção por valor ou label
        function findOption(alpineData, searchValue, config) {
            if (!alpineData.options || !Array.isArray(alpineData.options)) {
                return null;
            }

            return alpineData.options.find(option => {
                const optionValue = alpineData.getOptionValue(option);
                const optionText = alpineData.getOptionText(option);

                if (config.findByValue && String(optionValue) === String(searchValue)) {
                    return true;
                }

                if (config.findByLabel && String(optionText) === String(searchValue)) {
                    return true;
                }

                return false;
            });
        }

        // Criar opção temporária
        function createTempOption(value, config) {
            const option = {};
            option[config.valueField || 'value'] = value;
            option[config.textField || 'label'] = config.tempLabel || String(value);
            return option;
        }

        // Definir valores para seleção única
        function setOptionValues(alpineData, option, isMultiple, config, selectName) {
            const value = alpineData.getOptionValue(option);
            const label = alpineData.getOptionText(option);

            // Atualizar arrays do Alpine
            alpineData.selectedValues = [value];
            alpineData.selectedLabels = [label];
            alpineData.selectedObjects = [option];
            alpineData.selectedObjectsJson = JSON.stringify([option]);

            // Disparar eventos se configurado
            if (config.triggerEvents) {
                alpineData.dispatchSelectionEvent(option);
            }

        }

        // Definir valores para seleção múltipla
        function setMultipleOptionValues(alpineData, options, config, selectName) {
            const values = options.map(opt => alpineData.getOptionValue(opt));
            const labels = options.map(opt => alpineData.getOptionText(opt));

            // Atualizar arrays do Alpine
            alpineData.selectedValues = values;
            alpineData.selectedLabels = labels;
            alpineData.selectedObjects = options;
            alpineData.selectedObjectsJson = JSON.stringify(options);

            // Disparar eventos para cada opção se configurado
            if (config.triggerEvents) {
                options.forEach(option => alpineData.dispatchSelectionEvent(option));
            }

        }

        // Limpar seleção
        function clearSmartSelect(selectName) {
            try {
                const matches = Array.from(document.querySelectorAll('[x-data*="asyncSearchableSelect"]'));
                if (matches.length === 0) {
                    console.warn(`clearSmartSelect: nenhum componente asyncSearchableSelect encontrado`);
                }

                let clearedAny = false;

                matches.forEach(el => {
                    const alpine = el._x_dataStack && el._x_dataStack[0];
                    if (!alpine) return;

                    // Comparar pelo nome configurado no componente
                    if (String(alpine.name) !== String(selectName)) return;

                    clearedAny = true;

                    // Limpa estado do Alpine
                    alpine.selectedValues = [];
                    alpine.selectedLabels = [];
                    alpine.selectedObjects = [];
                    alpine.selectedObjectsJson = '[]';
                    alpine.search = '';
                    alpine.highlightIndex = -1;

                    // Limpa inputs hidden dentro desta instância
                    const singleInput = el.querySelector(`input[name="${selectName}"]`);
                    if (singleInput) {
                        singleInput.value = '';
                        singleInput.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                        singleInput.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }

                    const multipleInputs = Array.from(el.querySelectorAll(`input[name="${selectName}[]"]`));
                    multipleInputs.forEach(input => {
                        try {
                            input.value = '';
                            input.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                            input.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                            // remover para evitar inputs fantasmas
                            input.remove();
                        } catch (e) {
                            console.warn('clearSmartSelect: erro ao limpar input múltiplo', e);
                        }
                    });

                    // Forçar atualização reativa
                    if (typeof alpine.$nextTick === 'function') {
                        alpine.$nextTick(() => {
                            alpine.selectedValues = [...alpine.selectedValues];
                        });
                    }

                    // Disparar evento local do componente (se existir)
                    if (typeof alpine.dispatchSelectionEvent === 'function') {
                        try {
                            alpine.dispatchSelectionEvent({
                                name: selectName,
                                value: null,
                                label: '',
                                object: {},
                                objects: []
                            });
                        } catch (e) {
                            console.warn('clearSmartSelect: erro dispatchSelectionEvent', e);
                        }
                    }
                });

                // Evento global para compatibilidade com listeners externos
                window.dispatchEvent(new CustomEvent('smart-select:set-value', {
                    detail: {
                        name: selectName,
                        value: null,
                        label: ''
                    }
                }));

                return clearedAny;
            } catch (error) {
                console.error('clearSmartSelect: erro inesperado', error);
                return false;
            }
        }

        // ===== FUNÇÕES DE CONVENIÊNCIA =====

        // Definir valor por label
        window.setSmartSelectByLabel = function(selectName, label, options = {}) {
            return setSmartSelectValue(selectName, label, {
                ...options,
                findByLabel: true,
                findByValue: false
            });
        };

        // Adicionar valor à seleção múltipla
        window.addToSmartSelect = function(selectName, value, options = {}) {
            const currentValue = getSmartSelectValue(selectName);

            if (currentValue.multiple) {
                const newValues = [...currentValue.values, value];
                return setSmartSelectValue(selectName, newValues, options);
            } else {
                console.warn(`Smart-select "${selectName}" não é múltiplo`);
                return false;
            }
        };

        // Remover valor da seleção múltipla
        window.removeFromSmartSelect = function(selectName, value) {
            const currentValue = getSmartSelectValue(selectName);

            if (currentValue.multiple) {
                const newValues = currentValue.values.filter(v => String(v) !== String(value));
                return setSmartSelectValue(selectName, newValues);
            } else {
                console.warn(`Smart-select "${selectName}" não é múltiplo`);
                return false;
            }
        };

        // Limpar smart-select
        window.clearSmartSelect = function(selectName) {

            try {
                // Encontrar o elemento Alpine.js
                const alpineElement = document.querySelector(
                    `[x-data*="asyncSearchableSelect"][x-data*="'${selectName}'"]`);
                if (!alpineElement) {
                    console.error(`❌ Smart-select "${selectName}" não encontrado`);
                    return false;
                }

                // Acessar dados do Alpine.js
                if (!alpineElement._x_dataStack || !alpineElement._x_dataStack[0]) {
                    console.error(`❌ Dados Alpine.js não encontrados para "${selectName}"`);
                    return false;
                }

                const alpineData = alpineElement._x_dataStack[0];

                // LIMPAR DIRETAMENTE OS DADOS DO ALPINE
                alpineData.selectedValues = [];
                alpineData.selectedLabels = [];
                alpineData.selectedObjects = [];
                alpineData.selectedObjectsJson = '[]';

                // LIMPAR INPUTS HIDDEN
                // Para seleção única
                const singleInput = document.querySelector(`input[name="${selectName}"]`);
                if (singleInput) {
                    singleInput.value = '';
                }

                // Para seleção múltipla
                const multipleInputs = document.querySelectorAll(`input[name="${selectName}[]"]`);
                multipleInputs.forEach(input => {
                    input.remove(); // Remover todos os inputs múltiplos
                });

                // FORÇAR ATUALIZAÇÃO VISUAL DO ALPINE
                // Disparar reatividade do Alpine.js
                if (typeof alpineData.$nextTick === 'function') {
                    alpineData.$nextTick(() => {
                        // Força re-render
                        alpineData.selectedValues = [...alpineData.selectedValues];
                    });
                }

                // DISPARAR EVENTO DE MUDANÇA SE NECESSÁRIO
                if (typeof alpineData.dispatchSelectionEvent === 'function') {
                    alpineData.dispatchSelectionEvent({
                        [alpineData.valueField || 'value']: null,
                        [alpineData.textField || 'label']: null
                    });
                }

                return true;

            } catch (error) {
                return false;
            }
        };

        // Alternar valor na seleção múltipla
        window.toggleSmartSelectValue = function(selectName, value) {
            const currentValue = getSmartSelectValue(selectName);

            if (currentValue.multiple) {
                if (currentValue.values.includes(String(value))) {
                    return removeFromSmartSelect(selectName, value);
                } else {
                    return addToSmartSelect(selectName, value);
                }
            } else {
                // Para seleção única, alternar entre valor e null
                if (String(currentValue.value) === String(value)) {
                    return clearSmartSelect(selectName);
                } else {
                    return setSmartSelectValue(selectName, value);
                }
            }
        };

        // Verificar se valor está selecionado
        window.isValueSelected = function(selectName, value) {
            const currentValue = getSmartSelectValue(selectName);

            if (currentValue.multiple) {
                return currentValue.values.some(v => String(v) === String(value));
            } else {
                return String(currentValue.value) === String(value);
            }
        };

        // ===== FUNÇÕES DE ATUALIZAÇÃO DE OPÇÕES =====

        // Atualizar opções do smart-select
        window.updateSmartSelectOptions = function(selectName, newOptions, preserveSelection = true) {
            try {
                const alpineElement = document.querySelector(
                    `[x-data*="asyncSearchableSelect"][x-data*="'${selectName}'"]`);
                if (!alpineElement || !alpineElement._x_dataStack) {
                    console.error(`Smart-select "${selectName}" não encontrado`);
                    return false;
                }

                const alpineData = alpineElement._x_dataStack[0];
                const currentValues = preserveSelection ? [...alpineData.selectedValues] : [];

                // Atualizar opções
                alpineData.options = newOptions;

                // Revalidar seleção atual se preserveSelection = true
                if (preserveSelection && currentValues.length > 0) {
                    setTimeout(() => {
                        setSmartSelectValue(selectName, alpineData.multiple ? currentValues : currentValues[
                            0], {
                            triggerEvents: false
                        });
                    }, 100);
                }

                return true;

            } catch (error) {
                return false;
            }
        };

        // Adicionar opção às existentes
        window.addSmartSelectOption = function(selectName, newOption) {
            try {
                const alpineElement = document.querySelector(
                    `[x-data*="asyncSearchableSelect"][x-data*="'${selectName}'"]`);
                if (!alpineElement || !alpineElement._x_dataStack) return false;

                const alpineData = alpineElement._x_dataStack[0];
                alpineData.options.push(newOption);

                return true;

            } catch (error) {
                return false;
            }
        };
    </script>
</body>

</html>
