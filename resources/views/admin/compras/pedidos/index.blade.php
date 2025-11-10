<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Pedidos de Compra') }}
            </h2>
        </div>
    </x-slot>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200 bg-white p-4">
            <div class="border-b border-gray-200 bg-white p-6">
                <!-- Search Form -->
                @include('admin.compras.pedidos._search-form')

                <!-- Results Table with Loading -->
                <div class="relative mt-6 min-h-[400px] overflow-x-auto">
                    <!-- Loading indicator -->
                    <div id="table-loading"
                        class="absolute inset-0 z-10 flex items-center justify-center bg-white bg-opacity-80">
                        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                    </div>

                    <!-- Actual results -->
                    <div id="results-table" class="opacity-0 transition-opacity duration-300">
                        @include('admin.compras.pedidos._table')
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

            function confirmarAprovacao(id) {
                if (confirm('Tem certeza que deseja aprovar este pedido de compra?')) {
                    // Criar formulário dinamicamente e enviar
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/compras/pedidos/${id}/aprovar`;

                    // CSRF Token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            }

            function confirmarCancelamento(id) {
                const motivo = prompt('Informe o motivo do cancelamento:');
                if (motivo) {
                    // Criar formulário dinamicamente e enviar
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/compras/pedidos/${id}/cancelar`;

                    // CSRF Token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);

                    // Motivo do cancelamento
                    const motivoInput = document.createElement('input');
                    motivoInput.type = 'hidden';
                    motivoInput.name = 'motivo_cancelamento';
                    motivoInput.value = motivo;
                    form.appendChild(motivoInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            }
        </script>
    @endpush
</x-app-layout>
