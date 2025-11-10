<div>
    <!-- Pré-Cadastro -->
    <section>
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4">
                    </path>
                </svg>
                Pré-Cadastro
            </h3>
            <p class="mt-1 text-sm text-gray-600">Produtos em processo de pré-cadastro no sistema.</p>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
            <table class="tabelaPreCadastro min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Produto
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Data inclusão
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody id="tabelaPreCadastroBody" class="divide-y divide-gray-200 bg-white">
                    <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                </tbody>
            </table>
        </div>
    </section>

    <!-- Itens Solicitados -->
    <section class="mt-8">
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                    </path>
                </svg>
                Itens Solicitados
            </h3>
            <p class="mt-1 text-sm text-gray-600">Consulte e gerencie os itens da solicitação de compra.</p>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
            <div class="bg-indigo-600 px-6 py-4">
                <h4 class="text-lg font-medium leading-6 text-white">
                    Itens Solicitados
                </h4>
            </div>

            <div class="bg-gray-50 px-6 py-4">
                <div class="flex flex-col space-y-4 md:flex-row md:items-start md:space-x-4 md:space-y-0">
                    <div class="flex-1">
                        <x-forms.input name="solicitacao_compra_consulta" label="Cód. Solicitação de Compras"
                            value="{{ $cotacoes->solicitacoesCompra->id_solicitacoes_compras ?? '' }}" readonly />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="tabelaItemSolicitacao min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Filial
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Departamento
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Prioridade
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Situação Compra
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Observação
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Produto
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Quantidade<br>Solicitação
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Imagem Produto
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Descrição detalhada do <br> Produto/Serviço
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tabelaItemSolicitacaoBody" class="divide-y divide-gray-200 bg-white">
                        <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
