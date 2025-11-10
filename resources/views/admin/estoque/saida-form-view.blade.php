<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Registrar Saída de Estoque') }}
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

                    <!-- Alerta -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Atenção: Somente produtos com quantidade disponível são exibidos nesta lista. A
                                    saída será registrada e não poderá ser desfeita.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Formulário de Saída -->
                    <form action="{{ route('admin.estoque.registrar-saida', $estoque->id_estoque) }}" method="POST"
                        class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="id_produto" class="block text-sm font-medium text-gray-700">Produto</label>
                                <select name="id_produto" id="id_produto" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Selecione um produto</option>
                                    @foreach($produtos as $produto)
                                    <option value="{{ $produto['value'] }}"
                                        data-quantidade="{{ $produto['quantidade'] ?? 0 }}">
                                        {{ $produto['label'] }}
                                    </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500" id="disponibilidade-info">Selecione um produto
                                    para ver a disponibilidade</p>
                            </div>

                            <div>
                                <label for="quantidade"
                                    class="block text-sm font-medium text-gray-700">Quantidade</label>
                                <input type="number" name="quantidade" id="quantidade" min="0.01" step="0.01" required
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <p class="mt-1 text-sm text-red-500 hidden" id="quantidade-erro">A quantidade solicitada
                                    excede a disponível!</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="destino" class="block text-sm font-medium text-gray-700">Destino</label>
                                <select name="destino" id="destino" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    onchange="toggleReferenceFields()">
                                    @foreach($destinos as $destino)
                                    <option value="{{ $destino['value'] }}">{{ $destino['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="referenceFields" class="hidden">
                                <label for="id_referencia"
                                    class="block text-sm font-medium text-gray-700">Referência</label>
                                <input type="text" name="id_referencia" id="id_referencia"
                                    placeholder="ID da requisição, ordem de serviço, etc."
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div>
                            <label for="observacao" class="block text-sm font-medium text-gray-700">Observação</label>
                            <textarea name="observacao" id="observacao" rows="3"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" id="submit-button"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Registrar Saída
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let quantidadeDisponivel = 0;
        
        function toggleReferenceFields() {
            const destino = document.getElementById('destino').value;
            const referenceFields = document.getElementById('referenceFields');
            
            if (destino === 'requisicao' || destino === 'ordem_servico') {
                referenceFields.classList.remove('hidden');
            } else {
                referenceFields.classList.add('hidden');
                document.getElementById('id_referencia').value = '';
            }
        }
        
        function atualizarDisponibilidade() {
            const produtoSelect = document.getElementById('id_produto');
            const selectedOption = produtoSelect.options[produtoSelect.selectedIndex];
            
            if (selectedOption.value) {
                quantidadeDisponivel = parseFloat(selectedOption.getAttribute('data-quantidade'));
                document.getElementById('disponibilidade-info').textContent = 
                    `Disponível: ${quantidadeDisponivel.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            } else {
                quantidadeDisponivel = 0;
                document.getElementById('disponibilidade-info').textContent = 'Selecione um produto para ver a disponibilidade';
            }
            
            validarQuantidade();
        }
        
        function validarQuantidade() {
            const quantidade = parseFloat(document.getElementById('quantidade').value) || 0;
            const quantidadeErro = document.getElementById('quantidade-erro');
            const submitButton = document.getElementById('submit-button');
            
            if (quantidade > quantidadeDisponivel) {
                quantidadeErro.classList.remove('hidden');
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                quantidadeErro.classList.add('hidden');
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
        
        // Executar ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            toggleReferenceFields();
            
            // Adicionar evento para quando o produto for alterado
            document.getElementById('id_produto').addEventListener('change', atualizarDisponibilidade);
            
            // Adicionar evento para quando a quantidade for alterada
            document.getElementById('quantidade').addEventListener('input', validarQuantidade);
            
            // Adicionar evento para quando o destino for alterado
            document.getElementById('destino').addEventListener('change', toggleReferenceFields);
            
            // Verificar disponibilidade inicial
            atualizarDisponibilidade();
        });
        
        // Validar o formulário antes de enviar
        document.querySelector('form').addEventListener('submit', function(e) {
            const quantidade = parseFloat(document.getElementById('quantidade').value) || 0;
            
            if (quantidade <= 0) {
                e.preventDefault();
                alert('A quantidade deve ser maior que zero!');
                return false;
            }
            
            if (quantidade > quantidadeDisponivel) {
                e.preventDefault();
                alert('A quantidade solicitada excede a disponível!');
                return false;
            }
            
            return true;
        });
    </script>
    @endpush
</x-app-layout>