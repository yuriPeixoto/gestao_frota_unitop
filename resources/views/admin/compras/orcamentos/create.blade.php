<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Novo Orçamento') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.compras.orcamentos.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.chevron-left class="h-4 w-4 mr-2" /> Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Formulário de Criação de Orçamento -->
                    <form action="{{ route('admin.compras.orcamentos.store') }}" method="POST">
                        @csrf

                        <!-- Campos do Formulário -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="id_pedido" class="block text-sm font-medium text-gray-700">Pedido de
                                    Compra</label>
                                <select id="id_pedido" name="id_pedido" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @foreach($pedidosCompra as $pedido)
                                    <option value="{{ $pedido->id }}" {{ $pedidoCompra->id == $pedido->id ? 'selected' :
                                        '' }}>
                                        {{ $pedido->numero_pedido }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="id_fornecedor"
                                    class="block text-sm font-medium text-gray-700">Fornecedor</label>
                                <select id="id_fornecedor" name="id_fornecedor" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @foreach($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="data_orcamento" class="block text-sm font-medium text-gray-700">Data do
                                    Orçamento</label>
                                <input type="date" id="data_orcamento" name="data_orcamento" required
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label for="prazo_entrega" class="block text-sm font-medium text-gray-700">Prazo de
                                    Entrega</label>
                                <input type="number" id="prazo_entrega" name="prazo_entrega" min="1" required
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label for="validade" class="block text-sm font-medium text-gray-700">Validade</label>
                                <input type="date" id="validade" name="validade" required
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label for="observacao"
                                    class="block text-sm font-medium text-gray-700">Observação</label>
                                <textarea id="observacao" name="observacao" rows="3"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                            </div>
                        </div>

                        <!-- Itens do Orçamento -->
                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-gray-900">Itens do Orçamento</h3>

                            <div id="itens-orcamento">
                                @foreach($pedidoCompra->itens as $index => $itemPedido)
                                <div class="mt-4">
                                    <input type="hidden" name="itens[{{ $index }}][id_item_pedido]"
                                        value="{{ $itemPedido->id }}">
                                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Item</label>
                                            <input type="text" readonly value="{{ $itemPedido->descricao }}"
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-100">
                                        </div>

                                        <div>
                                            <label for="itens[{{ $index }}][quantidade]"
                                                class="block text-sm font-medium text-gray-700">Quantidade</label>
                                            <input type="number" id="itens[{{ $index }}][quantidade]"
                                                name="itens[{{ $index }}][quantidade]" min="1" step="1"
                                                value="{{ $itemPedido->quantidade }}" required
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </div>

                                        <div>
                                            <label for="itens[{{ $index }}][valor_unitario]"
                                                class="block text-sm font-medium text-gray-700">Valor Unitário</label>
                                            <input type="number" id="itens[{{ $index }}][valor_unitario]"
                                                name="itens[{{ $index }}][valor_unitario]" min="0" step="0.01" required
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Valor Total</label>
                                            <input type="number" readonly
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-100 valor-total">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="mt-6">
                            <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Salvar Orçamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    // Calcula o valor total de cada item ao digitar o valor unitário
        document.querySelectorAll('input[name$="[valor_unitario]"]').forEach(input => {
            input.addEventListener('input', function() {
                const quantidade = this.parentNode.parentNode.querySelector('input[name$="[quantidade]"]').value;
                const valorUnitario = this.value;
                const valorTotal = quantidade * valorUnitario;
                this.parentNode.parentNode.querySelector('.valor-total').value = valorTotal.toFixed(2);
            });
        });
</script>
@endpush