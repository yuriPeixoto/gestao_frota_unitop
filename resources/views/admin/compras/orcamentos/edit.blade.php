<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Orçamento') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.compras.orcamentos.show', $orcamento->id_orcamento) }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.chevron-left class="h-4 w-4 mr-2" /> Cancelar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Alertas -->
                    @if(session('success'))
                    <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                        <p>{{ session('success') }}</p>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                        <p class="font-bold">Por favor, corrija os seguintes erros:</p>
                        <ul>
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Formulário de Edição de Orçamento -->
                    <form action="{{ route('admin.compras.orcamentos.update', $orcamento->id_orcamento) }}"
                        method="POST" id="orcamentoForm" x-data="orcamentoForm()" x-init="inicializar()">
                        @csrf
                        @method('PUT')

                        <!-- Cabeçalho do Pedido -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Informações do Pedido</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Número do Pedido</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900">
                                        {{ $orcamento->pedidoCompra->numero_pedido }}
                                    </p>
                                    <input type="hidden" name="id_pedido" value="{{ $orcamento->id_pedido }}">
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Solicitante</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ $orcamento->pedidoCompra->solicitacaoCompra->solicitante->name ?? 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Departamento</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{
                                        $orcamento->pedidoCompra->solicitacaoCompra->departamento->descricao_departamento
                                        ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Dados do Orçamento -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Dados do Orçamento</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="id_fornecedor"
                                        class="block text-sm font-medium text-gray-700">Fornecedor</label>
                                    <select id="id_fornecedor" name="id_fornecedor" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Selecione um fornecedor</option>
                                        @foreach($fornecedores as $fornecedor)
                                        <option value="{{ $fornecedor->id_fornecedor }}" {{ old('id_fornecedor',
                                            $orcamento->id_fornecedor) == $fornecedor->id_fornecedor ? 'selected' : ''
                                            }}>
                                            {{ $fornecedor->nome_fornecedor }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('id_fornecedor')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="data_orcamento" class="block text-sm font-medium text-gray-700">Data do
                                        Orçamento</label>
                                    <input type="date" id="data_orcamento" name="data_orcamento" required
                                        value="{{ old('data_orcamento', $orcamento->data_orcamento ? $orcamento->data_orcamento->format('Y-m-d') : '') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('data_orcamento')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="prazo_entrega" class="block text-sm font-medium text-gray-700">Prazo de
                                        Entrega (dias)</label>
                                    <input type="number" id="prazo_entrega" name="prazo_entrega" min="1" required
                                        value="{{ old('prazo_entrega', $orcamento->prazo_entrega) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('prazo_entrega')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="validade"
                                        class="block text-sm font-medium text-gray-700">Validade</label>
                                    <input type="date" id="validade" name="validade" required
                                        value="{{ old('validade', $orcamento->validade ? $orcamento->validade->format('Y-m-d') : '') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('validade')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-3">
                                    <label for="observacao"
                                        class="block text-sm font-medium text-gray-700">Observações</label>
                                    <textarea id="observacao" name="observacao" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('observacao', $orcamento->observacao) }}</textarea>
                                    @error('observacao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Itens do Pedido -->
                        <div class="bg-white overflow-hidden border border-gray-200 shadow-sm rounded-lg mb-6">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-800">Itens do Orçamento</h3>
                            </div>
                            <div class="p-4">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Item
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Quantidade
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Valor Unitário
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Valor Total
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($orcamento->pedidoCompra->itens as $index => $itemPedido)
                                        @php
                                        $itemOrcamento = $orcamento->itens->firstWhere('id_item_pedido',
                                        $itemPedido->id_item_pedido);
                                        $valor_unitario = old("itens.$index.valor_unitario",
                                        $itemOrcamento ? $itemOrcamento->valor_unitario : '');
                                        $quantidade = old("itens.$index.quantidade",
                                        $itemOrcamento ? $itemOrcamento->quantidade : $itemPedido->quantidade);
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $itemPedido->descricao }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $itemPedido->observacao ?? '' }}
                                                </div>
                                                <input type="hidden" name="itens[{{ $index }}][id_item_pedido]"
                                                    value="{{ $itemPedido->id_item_pedido }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number" name="itens[{{ $index }}][quantidade]"
                                                    x-model="itens[{{ $index }}].quantidade"
                                                    @input="calcularTotal({{ $index }})" min="0.01" step="0.01" required
                                                    value="{{ $quantidade }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number" name="itens[{{ $index }}][valor_unitario]"
                                                    x-model="itens[{{ $index }}].valor_unitario"
                                                    @input="calcularTotal({{ $index }})" min="0.01" step="0.01" required
                                                    value="{{ $valor_unitario }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="text" x-model="itens[{{ $index }}].valor_total" readonly
                                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="3"
                                                class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                                Valor Total do Orçamento
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="text" x-model="valorTotal" readonly
                                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-bold">
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="flex justify-end space-x-3 mt-6">
                            <a href="{{ route('admin.compras.orcamentos.show', $orcamento->id_orcamento) }}"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancelar
                            </a>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function orcamentoForm() {
            return {
                itens: @json(collect($orcamento->pedidoCompra->itens)->map(function($itemPedido, $index) use ($orcamento) {
                    $itemOrcamento = $orcamento->itens->firstWhere('id_item_pedido', $itemPedido->id_item_pedido);
                    $valorUnitario = $itemOrcamento ? $itemOrcamento->valor_unitario : 0;
                    $quantidade = $itemOrcamento ? $itemOrcamento->quantidade : $itemPedido->quantidade;
                    
                    return [
                        'id' => $itemPedido->id_item_pedido,
                        'quantidade' => $quantidade,
                        'valor_unitario' => $valorUnitario,
                        'valor_total' => number_format($valorUnitario * $quantidade, 2, '.', '')
                    ];
                })),
                valorTotal: 0,
                
                inicializar() {
                    this.calcularTotalGeral();
                },
                
                calcularTotal(index) {
                    const quantidade = parseFloat(this.itens[index].quantidade) || 0;
                    const valorUnitario = parseFloat(this.itens[index].valor_unitario) || 0;
                    this.itens[index].valor_total = (quantidade * valorUnitario).toFixed(2);
                    this.calcularTotalGeral();
                },
                
                calcularTotalGeral() {
                    let total = 0;
                    for (let i = 0; i < this.itens.length; i++) {
                        total += parseFloat(this.itens[i].valor_total) || 0;
                    }
                    this.valorTotal = total.toFixed(2);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>