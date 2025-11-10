<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listar Abastecimentos') }}
            </h2>
            <div class="flex items-center space-x-4">
                {{-- Botão de Ajuda - Sempre visível --}}
                <x-help-icon title="Ajuda - Abastecimentos ATS/Truckpag/Manual"
                    content="Esta tela exibe os registros de abastecimentos originados dos sistemas ATS, Truckpag e lançamentos manuais. Use os filtros abaixo para pesquisar registros específicos." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                @if($ultimosProcessamentos)
                <div class="mb-6 bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-blue-800 mb-2">Últimos Processamentos</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Último Abastecimento Truckpag:</span>
                                {{ $ultimosProcessamentos->ultimo_abastecimento_truck_pag ?
                                \Carbon\Carbon::parse($ultimosProcessamentos->ultimo_abastecimento_truck_pag)->format('d/m/Y
                                H:i:s') : 'Nenhum' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Último Abastecimento ATS:</span>
                                {{ $ultimosProcessamentos->ultimo_abastecimento_integracao ?
                                \Carbon\Carbon::parse($ultimosProcessamentos->ultimo_abastecimento_integracao)->format('d/m/Y
                                H:i:s') : 'Nenhum' }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Search Form -->
                @include('admin.abastecimentosatstruckpagmanual._search-form')

                <!-- Results Table -->
                <div class="mt-6 overflow-x-auto relative min-h-[400px]">
                    <!-- Loading indicator -->
                    {{-- <div id="table-loading"
                        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                    </div> --}}

                    <!-- Actual results -->
                    @include('admin.abastecimentosatstruckpagmanual._table')
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
                        // Esconde o loading
                        tableLoading.style.display = 'none';
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
                    });
                }
                
                // Se estiver usando HTMX, intercepta os eventos
                document.body.addEventListener('htmx:beforeRequest', function(evt) {
                    if (evt.detail.target.id === 'results-table') {
                        tableLoading.style.display = 'flex';
                    }
                });
                
                document.body.addEventListener('htmx:afterRequest', function(evt) {
                    if (evt.detail.target.id === 'results-table') {
                        tableLoading.style.display = 'none';
                    }
                });
            });

            function enviarParaInconsistencia(id) {
                if (confirm('Deseja realmente enviar este abastecimento para inconsistência?')) {
                    fetch(`/admin/abastecimentosatstruckpagmanual/${id}/enviar-inconsistencia`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert(data.message || 'Erro ao enviar para inconsistência');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro ao processar a requisição');
                    });
                }
            }
    </script>
    @endpush
</x-app-layout>