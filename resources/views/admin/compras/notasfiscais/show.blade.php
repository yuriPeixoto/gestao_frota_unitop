<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalhes da Nota Fiscal Avulsa') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.notafiscalavulsa.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.chevron-left class="h-4 w-4 mr-2" /> Voltar
                </a>

                <a href="{{ route('admin.notafiscalavulsa.edit', $notaFiscal->id_nf_avulsa) }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.pencil class="h-4 w-4 mr-2" /> Editar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Informações da Nota Fiscal -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informações da Nota Fiscal</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">ID da Nota Fiscal</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $notaFiscal->id_nf_avulsa }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Data de Inclusão</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $notaFiscal->data_inclusao ?
                                $notaFiscal->data_inclusao->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Registrado por</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $notaFiscal->usuario ? $notaFiscal->usuario->name :
                                'Sistema' }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Número do Pedido</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $notaFiscal->numero_do_pedido ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Fornecedor</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $notaFiscal->fornecedor ?
                                $notaFiscal->fornecedor->nome_fornecedor : 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Número da Nota Fiscal</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $notaFiscal->numero_nf }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Série</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $notaFiscal->serie_nf ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Chave da Nota Fiscal</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $notaFiscal->chave_nf ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Data de Emissão</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $notaFiscal->data_emissao ?
                                $notaFiscal->data_emissao->format('d/m/Y') : 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Valor de Peças</p>
                            <p class="mt-1 text-sm text-gray-900">R$ {{ number_format($notaFiscal->valor_pecas ?? 0, 2,
                                ',', '.') }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Valor Total</p>
                            <p class="mt-1 text-sm text-gray-900">R$ {{ number_format($notaFiscal->valor_total_nf, 2,
                                ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Informações do Pedido de Compra -->
                @if($notaFiscal->pedidoCompra)
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informações do Pedido de Compra</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">ID do Pedido</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $notaFiscal->pedidoCompra->id_pedido_compras }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Data de Inclusão</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $notaFiscal->pedidoCompra->data_inclusao ?
                                $notaFiscal->pedidoCompra->data_inclusao->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($notaFiscal->pedidoCompra->status == 'aprovado') bg-green-100 text-green-800 
                                    @elseif($notaFiscal->pedidoCompra->status == 'pendente') bg-yellow-100 text-yellow-800 
                                    @elseif($notaFiscal->pedidoCompra->status == 'rejeitado') bg-red-100 text-red-800 
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($notaFiscal->pedidoCompra->status ?? 'N/A') }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Valor Total</p>
                            <p class="mt-1 text-sm text-gray-900">R$ {{
                                number_format($notaFiscal->pedidoCompra->valor_total, 2, ',', '.') }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Solicitante</p>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($notaFiscal->pedidoCompra->solicitacaoCompra &&
                                $notaFiscal->pedidoCompra->solicitacaoCompra->solicitante)
                                {{ $notaFiscal->pedidoCompra->solicitacaoCompra->solicitante->name }}
                                @else
                                N/A
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Comprador</p>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($notaFiscal->pedidoCompra->comprador)
                                {{ $notaFiscal->pedidoCompra->comprador->name }}
                                @else
                                N/A
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.compras.pedidos.show', $notaFiscal->pedidoCompra->id_pedido_compras) }}"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-900">
                            Ver detalhes completos do pedido
                            <x-icons.arrow-right class="h-4 w-4 ml-1" />
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>