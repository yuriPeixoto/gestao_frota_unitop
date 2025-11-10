<div>
    <!-- Dados do Fornecedor -->
    <section>
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                </svg>
                Dados Fornecedor
            </h3>
            <p class="mt-1 text-sm text-gray-600">Selecione o fornecedor e configure os dados para geração da cotação.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione o fornecedor..."
                    :options="$fornecedor" :searchUrl="route('admin.api.fornecedores.search')" asyncSearch="true" :selected="old('id_fornecedor', $transferenciaImobilizadoVeiculo->id_fornecedor ?? '')"
                    onSelectCallback="preencherDadosFornecedor" />
            </div>

            <div>
                <x-forms.input name="nome_contato" label="Nome do contato"
                    value="{{ $cotacoes->solicitacaoCompra->nome_contato ?? '' }}" />
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <x-forms.input name="email" label="Email" value="{{ $cotacoes->solicitacaoCompra->email ?? '' }}" />
            </div>

            <div class="flex items-end">
                <a onclick="incluirCotacao()"
                    class="inline-flex cursor-pointer items-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium leading-4 text-white shadow-sm transition duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Gerar Cotação
                </a>
            </div>
        </div>
    </section>

    <!-- Itens Disponíveis para Cotação -->
    <section class="mt-8">
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                    </path>
                </svg>
                Itens da Solicitação
            </h3>
            <p class="mt-1 text-sm text-gray-600">Visualize os itens que serão incluídos na cotação.</p>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
            <table class="tabelaHistorico min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Código Solicitações<br>Compras
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Código
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Serviço
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Quantidade Solicitada
                        </th>
                    </tr>
                </thead>
                <tbody id="tabelaHistoricoBody" class="divide-y divide-gray-200 bg-white">
                    <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                </tbody>
            </table>
        </div>
    </section>

    <!-- Cotações Geradas -->
    <section class="mt-8">
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Cotações já Geradas
            </h3>
            <p class="mt-1 text-sm text-gray-600">Lista das cotações enviadas para fornecedores.</p>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
            <div class="bg-indigo-600 px-6 py-4">
                <h4 class="text-lg font-medium leading-6 text-white">
                    Lista de Arquivos Cotações
                </h4>
            </div>

            <div class="bg-gray-50 px-6 py-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                    <x-forms.input name="id_solicitacoes_compras" label="Cód. Solicitação de Compras"
                        value="{{ $cotacoes->solicitacoesCompra->id_solicitacoes_compras ?? '' }}" readonly />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="tabelaCotacao min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Ações
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Código Solicitações<br>Compras
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Nome do Fornecedor
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Email
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Telefone fornecedor
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Nome contato
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tabelaCotacaoBody" class="divide-y divide-gray-200 bg-white">
                        <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
