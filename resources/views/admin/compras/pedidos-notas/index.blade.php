<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center w-full gap-2">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 truncate">
                {{ __('Consulta de Pedidos e Notas Fiscais') }}
            </h2>

            <!-- Botão de ajuda -->
            <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                <button @click="helpOpen = !helpOpen" type="button"
                    class="p-1.5 sm:p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>

                <!-- Dropdown Ajuda -->
                <div x-show="helpOpen" @click.away="helpOpen = false"
                    class="origin-top-right absolute right-0 mt-2 w-64 sm:w-72 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 p-3 text-sm"
                    x-transition>
                    <p class="font-medium text-gray-900">Ajuda - Consulta</p>
                    <p class="mt-1 text-gray-600 text-xs leading-5">
                        Pesquise pedidos de compra e notas fiscais usando filtros como número do pedido, fornecedor,
                        OS, placa e mais.
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200 space-y-6">

            <!-- Formulário de busca (pode ser colapsável no mobile) -->
            <div class="bg-gray-50 p-4 rounded-md shadow-inner">
                @include('admin.compras.pedidos-notas._search-form')
            </div>

            <!-- Tabela de resultados -->
            <div class="relative min-h-[400px]">
                <!-- Loader -->
                <div id="table-loading"
                    class="hidden absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                    <x-ui.loading message="Carregando..." color="primary" size="lg" />
                </div>

                <!-- Conteúdo da tabela -->
                <div
                    class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 rounded-md border">
                    @include('admin.compras.pedidos-notas._table')
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @include('admin.compras.pedidos-notas._scripts')
    @endpush
</x-app-layout>