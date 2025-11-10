<div class="mt-6 overflow-x-auto relative min-h-[400px]">
    <div id="table-loading"
        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10 hidden">
        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
    </div>

    <div id="results-table" class="opacity-100 transition-opacity duration-300">
        <table class="min-w-[1200px] w-full text-sm text-left text-gray-700 divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Ações</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Código Devolução</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Data Inclusão</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Código Ordem Serviço</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Tipo Ordem Serviço</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Descrição Produto</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Quantidade</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Justificativa</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($devolucaoProdutos as $itemProdutos)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <x-tooltip content="Excluir">
                            <a href="#" onclick="onExcluir( {{ $itemProdutos->id_devolucao_produtos }})"
                                class="flex bg-red-600 rounded-full p-1 inline-flex items-center shadow-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <x-icons.trash class="w-4 h-4 text-white" />
                            </a>
                            {{-- <a
                                href="{{ route('admin.devolucaosaidaestoque.edit', $itemProdutos->id_devolucao_produtos) }}"
                                class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150"
                                title="Editar">
                                <x-icons.pencil class="h-3 w-3" />
                            </a> --}}
                        </x-tooltip>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $itemProdutos->id_devolucao_produtos }}
                    </td>
                    <td class="px-6 py-4 wrap text-sm text-gray-900">
                        {{ format_date($itemProdutos->data_inclusao) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        {{ $itemProdutos->id_ordem_servico }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $itemProdutos->ordemServico->tipoOrdemServico->descricao_tipo_ordem }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $itemProdutos->produto->descricao_produto }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $itemProdutos->quantidade }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $itemProdutos->justificativa }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        Nenhum produto encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $devolucaoProdutos->links() }}
        </div>
    </div>
</div>