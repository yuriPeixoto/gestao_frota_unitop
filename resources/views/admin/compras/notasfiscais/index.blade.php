<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notas Fiscais Avulsas') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.notafiscalavulsa.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nova Nota Fiscal
                </a>

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
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Notas Fiscais
                                    Avulsas</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Esta tela permite o gerenciamento de notas fiscais avulsas. Você pode filtrar,
                                    visualizar, editar e criar
                                    novas notas fiscais vinculadas a pedidos de compra.
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
                <form method="GET" action="{{ route('admin.notafiscalavulsa.index') }}" class="space-y-4">
                    {{-- Exibir mensagens de erro/confirmação --}}
                    <x-ui.export-message />

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-forms.input type="date" name="data_inclusao" label="Data Inclusão"
                                value="{{ request('data_inclusao') }}" />
                        </div>

                        <div>
                            <x-forms.input type="date" name="data_emissao" label="Data Emissão"
                                value="{{ request('data_emissao') }}" />
                        </div>

                        <div>
                            <x-forms.smart-select name="id_fornecedor" label="Fornecedor"
                                placeholder="Selecione o fornecedor..." :options="$fornecedoresFrequentes"
                                :searchUrl="route('admin.api.fornecedores.search')" :selected="request('id_fornecedor')"
                                asyncSearch="true" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-forms.input name="numero_nf" label="Número NF" value="{{ request('numero_nf') }}" />
                        </div>

                        <div>
                            <x-forms.input name="chave_nf" label="Chave NF" value="{{ request('chave_nf') }}" />
                        </div>

                        <div>
                            <x-forms.input name="numero_do_pedido" label="Número do Pedido"
                                value="{{ request('numero_do_pedido') }}" />
                        </div>
                    </div>

                    <div class="flex justify-between mt-4">
                        <div>
                            <x-ui.export-buttons route="admin.notafiscalavulsa" :formats="['pdf', 'csv', 'xls']" />
                        </div>

                        <div class="flex space-x-2">
                            <a href="{{ route('admin.notafiscalavulsa.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.trash class="h-4 w-4 mr-2" />
                                Limpar
                            </a>

                            <button type="submit"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Results Table with Loading -->
                <div class="mt-6 overflow-x-auto relative min-h-[400px]">
                    <!-- Loading indicator -->
                    <div id="table-loading"
                        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                    </div>

                    <!-- Actual results -->
                    <div id="results-table" class="opacity-0 transition-opacity duration-300">
                        <x-tables.table>
                            <x-tables.header>
                                <x-tables.head-cell>ID</x-tables.head-cell>
                                <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
                                <x-tables.head-cell>Pedido</x-tables.head-cell>
                                <x-tables.head-cell>Fornecedor</x-tables.head-cell>
                                <x-tables.head-cell>Número NF</x-tables.head-cell>
                                <x-tables.head-cell>Data Emissão</x-tables.head-cell>
                                <x-tables.head-cell>Valor Total</x-tables.head-cell>
                                <x-tables.head-cell>Ações</x-tables.head-cell>
                            </x-tables.header>

                            <x-tables.body>
                                @forelse ($notasFiscais as $index => $nota)
                                <x-tables.row :index="$index">
                                    <x-tables.cell>{{ $nota->id_nf_avulsa }}</x-tables.cell>
                                    <x-tables.cell nowrap>{{ $nota->data_inclusao?->format('d/m/Y H:i') }}
                                    </x-tables.cell>
                                    <x-tables.cell>{{ $nota->numero_do_pedido }}</x-tables.cell>
                                    <x-tables.cell nowrap>{{ $nota->fornecedor?->nome_fornecedor }}</x-tables.cell>
                                    <x-tables.cell>{{ $nota->numero_nf }}</x-tables.cell>
                                    <x-tables.cell nowrap>{{ $nota->data_emissao?->format('d/m/Y') }}</x-tables.cell>
                                    <x-tables.cell nowrap>R$ {{ number_format($nota->valor_total_nf, 2, ',', '.') }}
                                    </x-tables.cell>
                                    <x-tables.cell>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('admin.notafiscalavulsa.show', $nota->id_nf_avulsa) }}"
                                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                <x-icons.eye class="h-3 w-3" />
                                            </a>

                                            <a href="{{ route('admin.notafiscalavulsa.edit', $nota->id_nf_avulsa) }}"
                                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <x-icons.pencil class="h-3 w-3" />
                                            </a>

                                            @if (auth()->user()->can('excluir_nota_fiscal'))
                                            <button type="button" onclick="confirmarExclusao({{ $nota->id_nf_avulsa }})"
                                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <x-icons.trash class="h-3 w-3" />
                                            </button>
                                            @endif
                                        </div>
                                    </x-tables.cell>
                                </x-tables.row>
                                @empty
                                <x-tables.empty cols="8" message="Nenhuma nota fiscal encontrada" />
                                @endforelse
                            </x-tables.body>
                        </x-tables.table>

                        <div class="mt-4">
                            {{ $notasFiscais->links() }}
                        </div>
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
                    if (document.querySelectorAll('#results-table table tbody tr').length > 0 || 
                        document.querySelectorAll('#results-table .empty-message').length > 0) {
                        // Esconde o loading e mostra a tabela com uma pequena transição
                        setTimeout(function() {
                            if (tableLoading) tableLoading.style.opacity = '0';
                            if (resultsTable) resultsTable.classList.remove('opacity-0');
                            
                            // Remove completamente o loading após a transição
                            setTimeout(function() {
                                if (tableLoading) tableLoading.style.display = 'none';
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
                        if (tableLoading) {
                            tableLoading.style.display = 'flex';
                            tableLoading.style.opacity = '1';
                        }
                        if (resultsTable) resultsTable.classList.add('opacity-0');
                    });
                }
            });

            // Função para confirmar exclusão
            function confirmarExclusao(id) {
                if (confirm('Tem certeza que deseja excluir esta nota fiscal?')) {
                    // Criar token CSRF
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    // Fazer requisição para excluir
                    fetch(`/admin/notafiscalavulsa/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Recarregar página após exclusão
                            window.location.reload();
                        } else {
                            alert(data.message || 'Erro ao excluir nota fiscal');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao excluir nota fiscal');
                    });
                }
            }
    </script>
    @endpush
</x-app-layout>