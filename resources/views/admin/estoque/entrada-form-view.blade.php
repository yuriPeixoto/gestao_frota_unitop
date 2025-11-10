<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Registrar Entrada em Estoque') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.estoque.itens', $estoque->id_estoque) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Informações do Estoque -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="font-medium text-gray-900 mb-2">Informações do Estoque</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">ID do Estoque:</span>
                                <p class="font-medium">{{ $estoque->id_estoque }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Descrição:</span>
                                <p class="font-medium">{{ $estoque->descricao_estoque }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Filial:</span>
                                <p class="font-medium">{{ $estoque->filial->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Formulário de Entrada -->
                    <form action="{{ route('admin.estoque.registrar-entrada', $estoque->id_estoque) }}" method="POST"
                        class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="id_produto" class="block text-sm font-medium text-gray-700">Produto</label>
                                <select name="id_produto" id="id_produto" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Selecione um produto</option>
                                    @foreach($produtos as $produto)
                                    <option value="{{ $produto['value'] }}" {{ request('id_produto')==$produto['value']
                                        ? 'selected' : '' }}>
                                        {{ $produto['label'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="quantidade"
                                    class="block text-sm font-medium text-gray-700">Quantidade</label>
                                <input type="number" name="quantidade" id="quantidade" min="0.01" step="0.01" required
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="origem" class="block text-sm font-medium text-gray-700">Origem</label>
                                <select name="origem" id="origem" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    onchange="toggleReferenceFields()">
                                    @foreach($origens as $origem)
                                    <option value="{{ $origem['value'] }}">{{ $origem['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="referenceFields" class="hidden">
                                <label for="id_referencia" class="block text-sm font-medium text-gray-700">Pedido de
                                    Compra</label>
                                <select name="id_referencia" id="id_referencia"
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Selecione um pedido (opcional)</option>
                                    @foreach($pedidosCompra as $pedido)
                                    <option value="{{ $pedido['value'] }}">{{ $pedido['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="observacao" class="block text-sm font-medium text-gray-700">Observação</label>
                            <textarea name="observacao" id="observacao" rows="3"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Registrar Entrada
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleReferenceFields() {
            const origem = document.getElementById('origem').value;
            const referenceFields = document.getElementById('referenceFields');
            
            if (origem === 'compra') {
                referenceFields.classList.remove('hidden');
            } else {
                referenceFields.classList.add('hidden');
                document.getElementById('id_referencia').value = '';
            }
        }
        
        // Executar ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            toggleReferenceFields();
            
            // Se o produto já estiver pré-selecionado via URL, buscar informações
            const idProduto = document.getElementById('id_produto').value;
            if (idProduto) {
                buscarInfoProduto(idProduto);
            }
            
            // Adicionar evento para quando o produto for alterado
            document.getElementById('id_produto').addEventListener('change', function() {
                buscarInfoProduto(this.value);
            });
        });
        
        // Função para buscar informações do produto
        function buscarInfoProduto(idProduto) {
            if (!idProduto) return;
            
            fetch(`/admin/api/produtos/${idProduto}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Informações do produto:', data);
                    // Aqui você pode exibir informações adicionais do produto se necessário
                })
                .catch(error => {
                    console.error('Erro ao buscar informações do produto:', error);
                });
        }
    </script>
    @endpush
</x-app-layout>