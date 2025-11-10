<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Dashboard do Módulo de Compras') }}
            </h2>
            <div class="flex items-center space-x-4">
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="absolute right-0 mt-2 w-64 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="truncate text-sm font-medium leading-5 text-gray-900">Ajuda - Módulo de
                                    Compras</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Este dashboard exibe os indicadores principais de todo o módulo de compras,
                                    permitindo uma visão
                                    geral do processo. Você pode navegar para as diferentes seções através dos cards
                                    e gráficos disponíveis.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200 bg-white p-4">
            <div class="border-b border-gray-200 bg-white p-6">

                <!-- Cards de Resumo -->
                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-lg border bg-white p-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 rounded-full bg-indigo-100 p-3">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-gray-500">Solicitações Pendentes</h3>
                                <p class="text-lg font-semibold text-gray-900">{{ $solicitacoesPendentes ?? '0' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border bg-white p-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 rounded-full bg-yellow-100 p-3">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-gray-500">Pedidos Pendentes</h3>
                                <p class="text-lg font-semibold text-gray-900">{{ $pedidosPendentes ?? '0' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border bg-white p-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 rounded-full bg-green-100 p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-gray-500">Pedidos Aprovados</h3>
                                <p class="text-lg font-semibold text-gray-900">{{ $pedidosAprovados ?? '0' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border bg-white p-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 rounded-full bg-blue-100 p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-gray-500">Valor Total (Mês)</h3>
                                <p class="text-lg font-semibold text-gray-900">R$
                                    {{ number_format($valorTotalMes ?? 0, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulos Principais -->
                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <!-- Solicitações de Compra -->
                    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-indigo-50 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-indigo-800">Solicitações de Compra</h3>
                                <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <a href="{{ route('admin.compras.solicitacoes.create') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Nova Solicitação</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.compras.solicitacoes.peruser') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Solicitações do departamento</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                                <a href="{{ route('admin.compras.solicitacoes.pendentes') }}"
                                    class="rounpded-md flex items-center justify-between p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Aprovação de Solicitações</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                                <a href="{{ route('admin.compras.solicitacoes.index') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Todas as Solicitações</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Cotações -->
                    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-green-50 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-green-800">Cotações</h3>
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <a href="{{ route('admin.compras.cotacoes.index') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Cotação de Itens</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                                <a href="{{ route('admin.compras.validarcotacoes.index') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Validação de Cotações</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                                <a href="{{ route('admin.compras.aprovarpedido.index') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Aprovar Pedido</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                            </div>
                        </div>
                    </div>

                    <!-- Pedidos de Compra -->
                    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-yellow-50 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-yellow-800">Pedidos de Compra</h3>
                                <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <a href="{{ route('admin.compras.pedidos.index') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Todos os Pedidos</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.compras.pedidos.pendentes-aprovacao') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Aprovação de Pedidos</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.compras.pedidos.aprovados') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Pedidos Aprovados</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Orçamentos -->
                    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-orange-50 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-orange-800">Orçamentos</h3>
                                <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <a href="{{ route('admin.compras.orcamentos.index') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Todos os Orçamentos</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.compras.orcamentos.index') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Comparativo de Orçamentos</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Notas Fiscais -->
                    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-green-50 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-green-800">Notas Fiscais</h3>
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <a href="{{ route('admin.compras.avulsas.create') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Nova Nota Fiscal</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.compras.lancamento-notas.index') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Lançamento de Notas</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.compras.notas-lancadas.index') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Notas Lançadas</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.compras.pedidos-notas.index') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Consultar Pedidos e Notas</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulos Secundários -->
                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <!-- Fornecedores -->
                    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-blue-50 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-blue-800">Fornecedores</h3>
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <a href="{{ route('admin.compras.fornecedores.create') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Novo Fornecedor</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.compras.fornecedores.index') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Cadastro de Fornecedores</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Contratos -->
                    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-purple-50 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-purple-800">Contratos</h3>
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <a href="{{ route('admin.compras.contratos.create') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Novo Contrato</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.compras.contratos.index') }}"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Contratos Vigentes</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Relatórios -->
                    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-red-50 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-red-800">Relatórios</h3>
                                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <a href="#"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Compras por Período</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                <a href="#"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Compras por Fornecedor</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                <a href="#"
                                    class="flex items-center justify-between rounded-md p-2 transition duration-150 hover:bg-gray-50">
                                    <span class="text-gray-700">Compras por Departamento</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimas Atividades -->
                <div class="mt-6 overflow-hidden rounded-lg border bg-white shadow-sm">
                    <div class="border-b px-4 py-3">
                        <h3 class="text-lg font-medium text-gray-900">Últimas Atividades</h3>
                    </div>
                    <div class="p-4">
                        <ul class="divide-y divide-gray-200">
                            @forelse ($ultimasAtividades ?? [] as $atividade)
                                <li class="py-3">
                                    <div class="flex space-x-3">
                                        <div class="flex-shrink-0">
                                            <span
                                                class="@if ($atividade->tipo == 'solicitacao') bg-indigo-100 text-indigo-600
                                                @elseif($atividade->tipo == 'pedido') bg-yellow-100 text-yellow-600
                                                @elseif($atividade->tipo == 'fornecedor') bg-blue-100 text-blue-600
                                                @elseif($atividade->tipo == 'nota_fiscal') bg-green-100 text-green-600
                                                @else bg-gray-100 text-gray-600 @endif flex h-8 w-8 items-center justify-center rounded-full">
                                                @if ($atividade->tipo == 'solicitacao')
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                        </path>
                                                    </svg>
                                                @elseif($atividade->tipo == 'pedido')
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                        </path>
                                                    </svg>
                                                @elseif($atividade->tipo == 'fornecedor')
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                        </path>
                                                    </svg>
                                                @elseif($atividade->tipo == 'nota_fiscal')
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                        </path>
                                                    </svg>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm text-gray-800">
                                                {{ $atividade->descricao }}
                                            </p>
                                            <div class="mt-1 flex items-center">
                                                <p class="text-xs text-gray-500">
                                                    {{ $atividade->usuario->name ?? 'Sistema' }} •
                                                    {{ $atividade->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="py-3 text-center text-gray-500">
                                    Nenhuma atividade recente encontrada
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
