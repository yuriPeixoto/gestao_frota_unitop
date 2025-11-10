<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Valor de Combustível por Bomba') }}
            </h2>
            <div class="flex items-center space-x-4">
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Valor de
                                    Combustível por Bomba</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Esta tela tem como finalidade exibir os registros de Valor de Combustível por Bomba.
                                    Os campos abaixo servem para realizar buscas nos registros!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search Form -->
                @include('admin.valorcombustiveis._search-form')

                <!-- Results Table with Loading -->
                <div class="mt-6 overflow-x-auto relative min-h-[400px]">
                    <!-- Loading indicator -->
                    <div id="table-loading"
                        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                    </div>

                    <!-- Actual results -->
                    <div id="results-table" class="opacity-0 transition-opacity duration-300">
                        @include('admin.valorcombustiveis._table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
                const tableLoading = document.getElementById('table-loading');
                const resultsTable = document.getElementById('results-table');
                
                // Função para verificar se a tabela está completamente carregada
                function checkTableReady() {
                    if (document.querySelectorAll('#results-table table tbody tr').length > 0) {
                        // Esconde o loading e mostra a tabela com uma pequena transição
                        setTimeout(function() {
                            tableLoading.style.opacity = '0';
                            resultsTable.classList.remove('opacity-0');
                            
                            // Remove completamente o loading após a transição
                            setTimeout(function() {
                                tableLoading.style.display = 'none';
                            }, 300);
                        }, 300);
                    } else {
                        // Tenta novamente em 100ms se ainda não estiver pronto
                        setTimeout(checkTableReady, 100);
                    }
                }
                
                // Inicia a verificação
                setTimeout(checkTableReady, 500);
                
                // Mostra loading quando o formulário de busca for submetido
                const searchForm = document.querySelector('form');
                if (searchForm) {
                    searchForm.addEventListener('submit', function() {
                        tableLoading.style.display = 'flex';
                        tableLoading.style.opacity = '1';
                        resultsTable.classList.add('opacity-0');
                    });
                }
                
                // Se estiver usando HTMX, intercepta os eventos
                document.body.addEventListener('htmx:beforeRequest', function(evt) {
                    if (evt.detail.target.id === 'results-table') {
                        tableLoading.style.display = 'flex';
                        tableLoading.style.opacity = '1';
                        resultsTable.classList.add('opacity-0');
                    }
                });
                
                document.body.addEventListener('htmx:afterRequest', function(evt) {
                    if (evt.detail.target.id === 'results-table') {
                        setTimeout(function() {
                            tableLoading.style.opacity = '0';
                            resultsTable.classList.remove('opacity-0');
                            
                            setTimeout(function() {
                                tableLoading.style.display = 'none';
                            }, 300);
                        }, 300);
                    }
                });
            });
    </script>
    @endpush
</x-app-layout>