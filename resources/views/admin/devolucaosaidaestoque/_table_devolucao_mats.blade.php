<div class="mt-6 overflow-x-auto relative min-h-[400px]">
    <div id="table-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10 hidden">
        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
    </div>

    <div id="results-table" class="opacity-100 transition-opacity duration-300">
        <table class="min-w-[1200px] w-full text-sm text-left text-gray-700 divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Ações</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Código Devolução</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Código Solicitação
                    </th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Filial</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Produto</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Quantidade</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Justificativa</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Data Inclusão</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($devolucaoMateriais as $itemMateriais)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <x-tooltip content="Excluir">
                                <a href="#"
                                    onclick="onExcluir( {{ $itemMateriais->id_devolucao_materiais }}, 'mat')"
                                    class="flex bg-red-600 rounded-full p-1 inline-flex items-center shadow-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="w-4 h-4 text-white" />
                                </a>
                            </x-tooltip>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ $itemMateriais->id_devolucao_materiais }}
                        </td>
                        <td class="px-6 py-4 wrap text-sm text-gray-900 text-center">
                            {{ $itemMateriais->id_relacaosolicitacoespecas }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $itemMateriais->filial->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $itemMateriais->produto->descricao_produto }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ $itemMateriais->quantidade }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $itemMateriais->justificativa }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ format_date($itemMateriais->data_inclusao) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Nenhum produto encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $devolucaoMateriais->links() }}
        </div>
    </div>
</div>
