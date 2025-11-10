<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Pedido Compras #{{ $pedido->numero }}
            </h2>
        </div>
    </x-slot>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200 bg-white p-4">
            <form id="pedidoForm" method="POST"
                action="{{ route('admin.compras.pedidos.update', $pedido->id_pedido_compras) }}">
                @csrf
                @method('PUT')
                <div class="overflow-hidden rounded-lg bg-white shadow-lg" x-data="pedidoForm()">
                    <div class="space-y-8 px-6 py-8">

                        <!-- Informações Básicas -->
                        <section>
                            <div class="mb-6 border-b border-gray-200 pb-4">
                                <h3 class="flex items-center text-lg font-semibold text-gray-900">
                                    <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Informações do Pedido
                                </h3>
                                <p class="mt-1 text-sm text-gray-600">Edite as informações do pedido de compra.</p>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <!-- Número do Pedido -->
                                <div>
                                    <label for="numero_pedido" class="block text-sm font-medium text-gray-700">Número do
                                        Pedido</label>
                                    <input type="text" id="numero_pedido" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $pedido->numero }}">
                                </div>

                                <!-- Solicitação de Compra (não editável) -->
                                <div>
                                    <label for="solicitacao" class="block text-sm font-medium text-gray-700">Solicitação
                                        de Compra</label>
                                    <input type="text" id="solicitacao" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $pedido->id_solicitacoes_compras ?? 'N/A' }}">
                                    <input type="hidden" name="id_solicitacoes_compras"
                                        value="{{ $pedido->id_solicitacoes_compras }}">
                                </div>

                                <!-- Tipo de Pedido (não editável) -->
                                <div>
                                    <label for="tipo_pedido" class="block text-sm font-medium text-gray-700">Tipo de
                                        Pedido</label>
                                    <input type="text" id="tipo_pedido" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $pedido->isProduto() ? 'Produto' : 'Serviço' }}">
                                    <input type="hidden" name="tipo_pedido" value="{{ $pedido->tipo_pedido }}">
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <!-- Fornecedor -->
                                <div>
                                    <x-forms.smart-select name="id_fornecedor" label="Fornecedor"
                                        placeholder="Selecione o fornecedor..." :options="$fornecedores" :searchUrl="route('admin.api.fornecedores.search')"
                                        :selected="old('id_fornecedor', $pedido->fornecedor->nome_fornecedor)" asyncSearch="true" required="true" />
                                </div>

                                <!-- Comprador (não editável) -->
                                <div>
                                    <label for="comprador"
                                        class="block text-sm font-medium text-gray-700">Comprador</label>
                                    <input type="text" id="comprador" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $pedido->comprador->name ?? 'N/A' }}">
                                    <input type="hidden" name="id_comprador" value="{{ $pedido->id_comprador }}">
                                </div>

                                <!-- Filial (não editável) -->
                                <div>
                                    <label for="filial" class="block text-sm font-medium text-gray-700">Filial</label>
                                    <input type="text" id="filial" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $pedido->filial->name ?? 'N/A' }}">
                                    <input type="hidden" name="id_filial" value="{{ $pedido->id_filial }}">
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Filial de Entrega -->
                                <div>
                                    <x-forms.smart-select name="filial_entrega" label="Filial de Entrega"
                                        placeholder="Selecione a filial de entrega..." :options="$filiais"
                                        :disabled="true" :selected="old('filial_entrega', $pedido->filialEntrega->name)" asyncSearch="false" required="true" />
                                </div>

                                <!-- Filial de Faturamento -->
                                <div>
                                    <x-forms.smart-select name="filial_faturamento" label="Filial de Faturamento"
                                        placeholder="Selecione a filial de faturamento..." :options="$filiais"
                                        :disabled="true" :selected="old('filial_faturamento', $pedido->filialFaturamento->name)" asyncSearch="false" required="true" />
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Valor Total (calculado) -->
                                <div>
                                    <label for="valor_total" class="block text-sm font-medium text-gray-700">Valor
                                        Total</label>
                                    <input type="text" id="valor_total" name="valor_total" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ number_format($pedido->valor_total, 2, ',', '.') }}"
                                        x-model="formatarMoeda(valorTotal)">
                                </div>
                            </div>
                        </section>

                        <!-- Observações -->
                        <section>
                            <div class="mb-6 border-b border-gray-200 pb-4">
                                <h3 class="flex items-center text-lg font-semibold text-gray-900">
                                    <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                    Observações
                                </h3>
                                <p class="mt-1 text-sm text-gray-600">Adicione informações complementares sobre o
                                    pedido.</p>
                            </div>

                            <div>
                                <label for="observacao_pedido"
                                    class="mb-2 block text-sm font-medium text-gray-700">Observações Gerais</label>
                                <textarea id="observacao_pedido" name="observacao_pedido" rows="4"
                                    placeholder="Descreva informações adicionais, requisitos especiais, prazos ou outras observações relevantes..."
                                    class="block w-full rounded-lg border-gray-300 shadow-sm transition duration-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('observacao_pedido', $pedido->observacao_pedido) }}</textarea>
                            </div>
                        </section>

                        <!-- Itens do Pedido -->
                        <section>
                            <!-- Tabela de Itens Adicionados -->
                            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <h5 class="text-lg font-medium text-gray-900">Itens do Pedido</h5>
                                        <span class="text-sm text-gray-500"
                                            x-text="`${itens.length} item(ns) adicionado(s)`"></span>
                                    </div>
                                </div>
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Item</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Descrição</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Qtd. Solicitada</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Unidade</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Valor Unit.</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Valor Total</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Valor Total com Desconto</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @forelse($pedido->itens as $item)
                                            <tr>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    {{ $item->produtos->descricao_produto }}
                                                </td>
                                                <td
                                                    class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                                    {{ $item ? number_format($item->quantidade_produtos, 2, ',', '.') : '-' }}
                                                </td>
                                                <td
                                                    class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                                    {{ $item->unidade_produto }}</td>
                                                <td
                                                    class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                                    {{ $item ? number_format($item->valor_produto, 2, ',', '.') : '-' }}
                                                </td>
                                                <td
                                                    class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                                    {{ $item ? number_format($item->valor_total, 2, ',', '.') : '-' }}
                                                </td>
                                                <td
                                                    class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                                    {{ $item ? number_format($item->valor_total_desconto, 2, ',', '.') : '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9"
                                                    class="px-6 py-4 text-center text-sm text-gray-500">
                                                    Nenhum item encontrado
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Botões de Ação -->
                        <div class="flex justify-end space-x-4 border-t border-gray-200 pt-6">
                            <a href="{{ route('admin.compras.pedidos.index') }}"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Voltar
                            </a>

                            <button type="submit" id="submit-button" @click="validarFormulario"
                                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Atualizar Pedido
                            </button>
                        </div>
                    </div>
                </div>
        </div>
        </form>
    </div>

    @push('scripts')
        @include('admin.compras.pedidos._scripts')
    @endpush
</x-app-layout>
