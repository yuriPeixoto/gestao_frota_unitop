<!-- Modal de Visualização do Produto -->
<div id="modalVisualizarProduto"
    class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-gray-600 bg-opacity-50">
    <div class="relative top-20 mx-auto w-full max-w-md rounded-md border bg-white p-5 shadow-lg">
        <div class="mt-3">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Visualizar Produto</h3>
                <button type="button" onclick="fecharModalVisualizarProduto()"
                    class="text-gray-400 transition-colors hover:text-gray-600">
                    <span class="sr-only">Fechar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div id="conteudoProduto" class="text-center">
                <div id="loadingProduto" class="flex h-32 items-center justify-center">
                    <div class="h-8 w-8 animate-spin rounded-full border-b-2 border-indigo-600"></div>
                </div>

                <div id="dadosProduto" class="hidden">
                    <div class="mb-4">
                        <img id="imagemProduto" src="" alt="Imagem do produto"
                            class="mx-auto h-auto max-h-64 max-w-full rounded-lg border shadow-md">
                    </div>
                    <div class="space-y-2 rounded-lg bg-gray-50 p-4 text-left">
                        <p><strong>Código:</strong> <span id="codigoProduto" class="text-gray-700"></span></p>
                        <p><strong>Descrição:</strong> <span id="descricaoProduto" class="text-gray-700"></span></p>
                        <p><strong>Estoque:</strong> <span id="estoqueProduto" class="text-gray-700"></span></p>
                    </div>
                </div>

                <div id="erroProduto" class="hidden p-4 text-red-600">
                    <div class="flex items-center justify-center">
                        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.704-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                        <p>Erro ao carregar informações do produto</p>
                    </div>
                </div>

                <div id="semImagemProduto" class="hidden">
                    <div class="flex h-32 flex-col items-center justify-center rounded-lg bg-gray-100 text-gray-500">
                        <svg class="mb-2 h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <p class="text-sm">Imagem não disponível</p>
                    </div>
                    <div class="mt-4 space-y-2 rounded-lg bg-gray-50 p-4 text-left">
                        <p><strong>Código:</strong> <span id="codigoProdutoSemImagem" class="text-gray-700"></span>
                        </p>
                        <p><strong>Descrição:</strong> <span id="descricaoProdutoSemImagem"
                                class="text-gray-700"></span></p>
                        <p><strong>Estoque:</strong> <span id="estoqueProdutoSemImagem" class="text-gray-700"></span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" onclick="fecharModalVisualizarProduto()"
                    class="rounded-md bg-gray-300 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Disponibilidade do Produto -->
<div id="modalDisponibilidade"
    class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-gray-600 bg-opacity-50">
    <div class="relative top-10 mx-auto w-full max-w-4xl rounded-md border bg-white p-5 shadow-lg">
        <div class="mt-3">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Disponibilidade do Produto</h3>
                <button type="button" onclick="fecharModalDisponibilidade()"
                    class="text-gray-400 transition-colors hover:text-gray-600">
                    <span class="sr-only">Fechar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div id="conteudoDisponibilidade">
                <div id="loadingDisponibilidade" class="flex h-32 items-center justify-center">
                    <div class="h-8 w-8 animate-spin rounded-full border-b-2 border-indigo-600"></div>
                </div>

                <div id="dadosDisponibilidade" class="hidden">
                    <!-- Informações do produto -->
                    <div class="mb-6 rounded-lg bg-gray-50 p-4">
                        <h4 class="text-md mb-2 font-semibold text-gray-800">Produto Selecionado</h4>
                        <div class="grid grid-cols-1 gap-2 text-sm md:grid-cols-3">
                            <div><strong>Código:</strong> <span id="codigoProdutoDisp" class="text-gray-700"></span>
                            </div>
                            <div><strong>Descrição:</strong> <span id="descricaoProdutoDisp"
                                    class="text-gray-700"></span></div>
                        </div>
                    </div>

                    <!-- Resumo -->
                    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-lg bg-blue-50 p-4 text-center">
                            <div class="text-2xl font-bold text-blue-600" id="quantidadeTotal">0</div>
                            <div class="text-sm text-blue-800">Quantidade Total</div>
                        </div>
                        <div class="rounded-lg bg-green-50 p-4 text-center">
                            <div class="text-2xl font-bold text-green-600" id="filiaisComEstoque">0</div>
                            <div class="text-sm text-green-800">Filiais com Estoque</div>
                        </div>
                        <div class="rounded-lg bg-yellow-50 p-4 text-center">
                            <div class="text-lg font-bold text-yellow-600" id="valorMedioGeral">R$ 0,00</div>
                            <div class="text-sm text-yellow-800">Valor Médio Geral</div>
                        </div>
                    </div>

                    <!-- Tabela de disponibilidade -->
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                            <h5 class="text-lg font-medium text-gray-900">Disponibilidade por Filial</h5>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Filial
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Estoque
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Quantidade
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Valor Médio
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Localização
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tabelaDisponibilidade" class="divide-y divide-gray-200 bg-white">
                                    <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="erroDisponibilidade" class="hidden p-4 text-red-600">
                    <div class="flex items-center justify-center">
                        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.704-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                        <p id="mensagemErroDisponibilidade">Erro ao carregar disponibilidade do produto</p>
                    </div>
                </div>

                <div id="semDisponibilidade" class="hidden p-8 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-500">
                        <svg class="mb-4 h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-lg font-medium">Produto sem estoque</p>
                        <p class="text-sm">Este produto não possui estoque disponível em nenhuma filial</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" onclick="fecharModalDisponibilidade()"
                    class="rounded-md bg-gray-300 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Transferência de Estoque -->
<div id="modalTransferencia"
    class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-gray-600 bg-opacity-50">
    <div class="relative top-20 mx-auto w-full max-w-md rounded-md border bg-white p-5 shadow-lg">
        <div class="mt-3">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Adicionar para Transferência</h3>
                <button type="button" onclick="fecharModalTransferencia()"
                    class="text-gray-400 transition-colors hover:text-gray-600">
                    <span class="sr-only">Fechar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="formTransferencia" onsubmit="processarTransferencia(event)">
                <!-- Informações do produto -->
                <div class="mb-4 rounded-lg bg-gray-50 p-3">
                    <h4 class="mb-2 text-sm font-semibold text-gray-800">Produto</h4>
                    <p id="descricaoProdutoTransf" class="text-sm text-gray-700"></p>
                </div>

                <!-- Filial de origem -->
                <div class="mb-4">
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Filial de Origem
                    </label>
                    <input type="text" id="filialOrigem" readonly
                        class="w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-gray-700">
                </div>

                <!-- Quantidade -->
                <div class="mb-4">
                    <label for="quantidadeTransf" class="mb-1 block text-sm font-medium text-gray-700">
                        Quantidade para Transferência *
                    </label>
                    <input type="number" id="quantidadeTransf" name="quantidade" min="1" step="1"
                        required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Disponível: <span id="quantidadeDisponivel">0</span></p>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fecharModalTransferencia()"
                        class="rounded-md bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancelar
                    </button>
                    <button type="submit" id="btnTransferencia"
                        class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Adicionar para Transferência
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
