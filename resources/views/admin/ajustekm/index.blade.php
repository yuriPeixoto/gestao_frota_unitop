<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ajuste KM Abastecimento') }}
            </h2>
            <div class="flex items-center space-x-4">
                {{-- Botão Criar - Protegido por Permissão --}}
                @can('criar_ajustekmabastecimento')
                <a href="{{ route('admin.ajustekm.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Cadastrar Ajuste KM
                </a>
                @endcan

                {{-- Botão de Ajuda - Sempre visível --}}
                <x-help-icon title="Ajuda - Ajuste KM Abastecimento"
                    content="Está tela tem como finalidade exibir os registros de Ajustes de KM para os Abastecimentos. Os campos abaixo servem para realizar buscas nos registros lançados!" />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search Form -->
                @include('admin.ajustekm._search-form')

                <!-- Results Table with Loading -->
                <div class="mt-6 overflow-x-auto relative min-h-[400px]">
                    <!-- Loading indicator -->
                    <div id="table-loading"
                        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                    </div>

                    <!-- Actual results -->
                    <div id="results-table" class="opacity-0 transition-opacity duration-300">
                        @include('admin.ajustekm._table')
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