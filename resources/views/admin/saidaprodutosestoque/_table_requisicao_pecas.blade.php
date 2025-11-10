<div class="relative mt-6 min-h-[400px] overflow-x-auto">
    <div id="table-loading" class="absolute inset-0 z-10 flex hidden items-center justify-center bg-white bg-opacity-80">
        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
    </div>

    <div id="results-table" class="opacity-100 transition-opacity duration-300">
        <table class="w-full min-w-[1200px] divide-y divide-gray-200 text-left text-sm text-gray-700">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Ações</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Código Solicitação</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Ordem de Serviço</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Filial Manutenção</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Departamento</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Usuário Solicitação</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Situação</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Data Inclusão</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Data Alteração</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Estoquistas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($relacaoPecas as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium">
                            <div class="relative inline-block">
                                <button onclick="toggleDropdown()"
                                    class="dropdown-button flex items-center space-x-2 rounded border bg-white px-4 py-2 shadow">
                                    <x-icons.gear class="h-4 w-4" />
                                    <span>Ações</span>
                                </button>
                                <ul
                                    class="dropdown-menu absolute left-0 z-50 mt-2 hidden w-48 rounded border bg-white shadow-lg">
                                    <li>
                                        <a href="{{ route('admin.saidaprodutosestoque.edit', $item->id_solicitacao_pecas) }}"
                                            class="block flex items-center px-4 py-2 hover:bg-gray-100">
                                            <x-icons.cubes class="mr-2 h-4 w-4 text-blue-600" />
                                            Baixar Itens
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" onclick="imprimirReqPecas({{ $item->id_solicitacao_pecas }})"
                                            class="block flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                            <x-icons.pdf-doc class="mr-2 h-4 w-4 text-red-600" />
                                            Imprimir
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#"
                                            onclick="visualizarProdutos({{ $item->id_solicitacao_pecas }})"
                                            class="block flex items-center px-4 py-2 hover:bg-gray-100">
                                            <x-icons.dolly-flatbed class="mr-2 h-4 w-4 text-cyan-600" />
                                            Visualizar Produtos
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->id_solicitacao_pecas }}
                        </td>
                        <td class="wrap px-6 py-4 text-sm text-gray-900">
                            {{ $item->id_orderm_servico }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->id_filial_manutencao }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->departamentoPecas->descricao_departamento ?? '' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->pessoalAbertura->nome ?? ($item->id_usuario_abertura ?? '') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->situacao }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ format_date($item->data_inclusao) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ format_date($item->data_alteracao) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->usuario_estoque ?? ($item->pessoalEstoque->nome ?? $item->id_usuario_estoque) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="px-6 py-4 text-center text-sm text-gray-500">
                            Nenhum produto encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $relacaoPecas->links() }}
        </div>
    </div>
</div>
