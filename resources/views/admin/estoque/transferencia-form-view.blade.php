<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Transferência entre Estoques') }}
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
                    <!-- Informações dos Estoques -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Estoque de Origem -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-medium text-gray-900 mb-2">Estoque de Origem</h3>
                            <div class="space-y-2">
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

                        <!-- Estoque de Destino (a ser selecionado) -->
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h3 class="font-medium text-yellow-900 mb-2">Estoque de Destino</h3>
                            <p class="text-sm text-yellow-700 mb-4">
                                Selecione abaixo o estoque para onde os produtos serão transferidos.
                            </p>
                        </div>
                    </div>

                    <!-- Alerta -->
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    A transferência de produtos entre estoques será registrada como uma saída do estoque
                                    de origem e uma entrada no estoque de destino.
                                    Somente produtos com quantidade disponível são exibidos na lista.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Formulário de Transferência -->
                    <form action="{{ route('admin.estoque.registrar-transferencia', $estoque->id_estoque) }}"
                        method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="id_estoque_destino" class="block text-sm font-medium text-gray-700">Estoque
                                    de Destino</label>
                                <select name="id_estoque_destino" id="id_estoque_destino" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Selecione o estoque de destino</option>
                                    @foreach($outrosEstoques as $estoqueDestino)
                                    <option value="{{ $estoqueDestino['value'] }}">{{ $estoqueDestino['label'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="id_produto" class="block text-sm font-medium text-gray-700">Produto a
                                    Transferir</label>
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
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="quantidade" class="block text-sm font-medium text-gray-700">Quantidade a
                                    Transferir</label>
                                <input type="number" name="quantidade" id="quantidade" min="0.01" step="0.01" required
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <p class="mt-1 text-sm text-red-500 hidden" id="quantidade-erro">A quantidade solicitada
                                    excede a disponível!</p>
                            </div>

                            <div class="flex items-end">
                                <div
                                    class="h-10 w-full bg-yellow-50 rounded-md border border-yellow-200 flex items-center justify-center">
                                    <span class="text-yellow-800 text-sm" id="transferencia-info">
                                        Selecione um produto e quantidade para ver detalhes da transferência
                                    </span>
                                </div>
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
                                Realizar Transferência
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
        let produtoSelecionado = '';
        
        function atualizarDisponibilidade() {
            const produtoSelect = document.getElementById('id_produto');
            const selectedOption = produtoSelect.options[produtoSelect.selectedIndex];
            
            if (selectedOption.value) {
                quantidadeDisponivel = parseFloat(selectedOption.getAttribute('data-quantidade'));
                produtoSelecionado = selectedOption.textContent.trim();
                document.getElementById('disponibilidade-info').textContent = 
                    `Disponível: ${quantidadeDisponivel.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            } else {
                quantidadeDisponivel = 0;
                produtoSelecionado = '';
                document.getElementById('disponibilidade-info').textContent = 'Selecione um produto para ver a disponibilidade';
            }
            
            validarQuantidade();
            atualizarInfoTransferencia();
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
            
            atualizarInfoTransferencia();
        }
        
        function atualizarInfoTransferencia() {
            const quantidade = parseFloat(document.getElementById('quantidade').value) || 0;
            const estoqueDestinoSelect = document.getElementById('id_estoque_destino');
            const estoqueDestino = estoqueDestinoSelect.options[estoqueDestinoSelect.selectedIndex]?.textContent || 'não selecionado';
            
            if (produtoSelecionado && quantidade > 0 && estoqueDestino !== 'não selecionado') {
                document.getElementById('transferencia-info').textContent = 
                    `Transferir ${quantidade.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} unidades de "${produtoSelecionado.substring(0, 20)}${produtoSelecionado.length > 20 ? '...' : ''}" para ${estoqueDestino}`;
            } else {
                document.getElementById('transferencia-info').textContent = 
                    'Selecione um produto, quantidade e estoque de destino para ver detalhes da transferência';
            }
        }
        
        // Executar ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            // Adicionar eventos
            document.getElementById('id_produto').addEventListener('change', atualizarDisponibilidade);
            document.getElementById('quantidade').addEventListener('input', validarQuantidade);
            document.getElementById('id_estoque_destino').addEventListener('change', atualizarInfoTransferencia);
            
            // Verificar disponibilidade inicial
            atualizarDisponibilidade();
        });
        
        // Validar o formulário antes de enviar
        document.querySelector('form').addEventListener('submit', function(e) {
            const quantidade = parseFloat(document.getElementById('quantidade').value) || 0;
            const idEstoqueDestino = document.getElementById('id_estoque_destino').value;
            
            if (!idEstoqueDestino) {
                e.preventDefault();
                alert('Selecione um estoque de destino!');
                return false;
            }
            
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