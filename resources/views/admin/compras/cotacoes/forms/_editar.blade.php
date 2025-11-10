@php
    // supondo que $validarMapaCotacao, $departamento, $departamentoPermitidos, $os e $status foram passados pelo controller

    // Verificar se existem cotações vinculadas COM VALOR
    $temCotacoes = false;
    if (isset($cotacoesList) && $cotacoesList->count() > 0) {
        // Verifica se pelo menos uma cotação tem valor total ou valor total com desconto
        $temCotacoes = $cotacoesList->contains(function ($cotacao) {
            return ($cotacao->valor_total && $cotacao->valor_total > 0) ||
                ($cotacao->valor_total_desconto && $cotacao->valor_total_desconto > 0);
        });
    }

    if ($validarMapaCotacao) {
        $mostrarEnviar = 'hidden';
        $mostrarAprovacao = '';
    } else {
        if (
            $cotacoes->situacao_compra == 'INICIADA' ||
            $cotacoes->situacao_compra == 'Iniciada' ||
            $cotacoes->situacao_compra == 'COTAÇÕES RECUSADAS PELO GESTOR'
        ) {
            $mostrarEnviar = '';
            $mostrarAprovacao = 'hidden';
        } elseif ($cotacoes->situacao_compra == 'SOLICITAÇÃO VALIDADA PELO GESTOR') {
            $mostrarEnviar = 'hidden';
            $mostrarAprovacao = '';
        } else {
            $mostrarEnviar = 'hidden';
            $mostrarAprovacao = 'hidden';
        }
    }
@endphp

<div>
    <!-- Seção de Controles de Ação -->
    <section>
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                    </path>
                </svg>
                Controles de Ação
            </h3>
            <p class="mt-1 text-sm text-gray-600">Ações disponíveis para gerenciar o status da solicitação.</p>
        </div>

        <div class="flex flex-wrap gap-3 rounded-lg bg-gray-50 p-4">
            <button type="button"
                onclick="event.preventDefault(); event.stopPropagation(); const result = mudarStatusSolicitante({{ $cotacoes->id_solicitacoes_compras }}, '{{ $cotacoes->situacao_compra }}'); console.log('🔍 RESULTADO DA FUNÇÃO:', result); return false;"
                aria-label="Enviar para Solicitante" id="botaoenviarsolicitante" {{ !$temCotacoes ? 'disabled' : '' }}
                class="{{ $mostrarEnviar }} {{ !$temCotacoes ? 'opacity-50 cursor-not-allowed hover:bg-white' : '' }} inline-flex cursor-pointer items-center whitespace-nowrap rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                {{ !$temCotacoes ? 'title="Não há cotações vinculadas a esta solicitação"' : '' }}>
                <svg class="mr-2 h-4 w-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Enviar para Solicitante</span>
            </button>

            <button type="button"
                onclick="event.preventDefault(); event.stopPropagation(); const result = mudarStatusSolicitante({{ $cotacoes->id_solicitacoes_compras }}, '{{ $cotacoes->situacao_compra }}', '{{ $cotacoes->grupo_despesa }}'); console.log('🔍 RESULTADO DA FUNÇÃO:', result); return false;"
                aria-label="Gerar Mapa e Enviar para Aprovação" id="botaoaprovacao"
                class="{{ $mostrarAprovacao }} inline-flex cursor-pointer items-center whitespace-nowrap rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="mr-2 h-4 w-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Gerar Mapa e Enviar para Aprovação</span>
            </button>
        </div>
    </section>

    <!-- Lista de Arquivos Cotações -->
    <section class="mt-8">
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Lista de Arquivos Cotações
            </h3>
            <p class="mt-1 text-sm text-gray-600">Gerencie os arquivos e documentos relacionados às cotações recebidas.
            </p>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
            <div class="bg-indigo-600 px-6 py-4">
                <h4 class="text-lg font-medium leading-6 text-white">
                    Lista de Arquivos Cotações
                </h4>
            </div>

            <div class="bg-gray-50 px-6 py-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                    <x-forms.input name="nome_contato" label="Cód. Solicitação de Compras"
                        value="{{ $cotacoes->solicitacoesCompra->id_solicitacoes_compras ?? '' }}" readonly />
                </div>
            </div>
        </div>
    </section>

    <!-- Orçamentos -->
    <section class="mt-8">
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                    </path>
                </svg>
                Orçamentos Recebidos
            </h3>
            <p class="mt-1 text-sm text-gray-600">Visualize e edite os orçamentos recebidos dos fornecedores.</p>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
            <div class="bg-gray-200 px-6 py-4">
                <h4 class="text-lg font-medium leading-6 text-gray-900">
                    Orçamentos
                </h4>
            </div>

            <div class="overflow-x-auto">
                <table class="tabelaOrcamento min-w-full divide-y divide-gray-200">
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
                                Nome contato
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Valor total sem desconto
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Valor total com desconto
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Data de Entrega
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tabelaOrcamentoBody" class="divide-y divide-gray-200 bg-white">
                        <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<x-bladewind.modal name="editarForm" cancel_button_label="" ok_button_label="" title="Editar Orçamento Recebido"
    size="xl">
    <!-- Informações da Cotação -->
    <div class="mb-6 rounded-lg bg-gray-50 p-4">
        <div class="mb-2 grid grid-cols-1 gap-4 rounded-lg bg-gray-50 p-4 md:grid-cols-5">

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Código Cotação:</label>
                <div class="cotacao-info rounded bg-gray-200 px-3 py-2 sm:text-sm">-</div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Código Solicitação:</label>
                <div class="solicitacao-info rounded bg-gray-200 px-3 py-2 sm:text-sm">-</div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Comprador:</label>
                <div class="comprador-info rounded bg-gray-200 px-3 py-2 sm:text-sm">-</div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Nome Fornecedor:</label>
                <div class="fornecedor-info rounded bg-gray-200 px-3 py-2 sm:text-sm">-</div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Nome do Contato:</label>
                <div class="contato-info rounded bg-gray-200 px-3 py-2 sm:text-sm">-</div>
            </div>

        </div>

        <div class="mb-2 grid grid-cols-1 gap-4 rounded-lg bg-gray-50 p-4 md:grid-cols-5">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Email:</label>
                <div class="email-info rounded bg-gray-200 px-3 py-2 sm:text-sm">-</div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Telefone:</label>
                <div class="tefone-info rounded bg-gray-200 px-3 py-2 sm:text-sm">-</div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Data de Entrega:</label>
                <input type="date" id="data_entrega" name="data_entrega"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <div>
                <x-forms.input name="condicao_pag" type="number" label="Condição de Pagamento" id="condicao_pag"
                    name="condicao_pag" value="" />
            </div>
        </div>

        <hr class="mb-1 mt-1">

        <div class="mb-2 grid grid-cols-1 gap-4 rounded-lg bg-gray-50 p-4 md:grid-cols-5">

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Código Produto:</label>
                <div class="produto-info rounded bg-gray-200 px-3 py-2 sm:text-sm">-</div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Descrição Produto:</label>
                <div class="descricao-info rounded bg-gray-200 px-3 py-2 sm:text-sm">-</div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Unidade:</label>
                <div class="unidade-info rounded bg-gray-200 px-3 py-2 sm:text-sm">-</div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Quantidade Solicitada:</label>
                <div class="quantidade-info rounded bg-gray-200 px-3 py-2 sm:text-sm" id="quantidadeSolicitada">-
                </div>
            </div>
            <div>
                <x-forms.input name="quantidade_fornecedor" label="Quantidade Fornecedor" value=""
                    placeholder="0" step="1" />
            </div>
        </div>

        <div class="mb-2 grid grid-cols-1 gap-4 rounded-lg bg-gray-50 p-4 md:grid-cols-4">
            <div>
                <x-forms.input name="valorunitario" label="Valor Unitário" value="" placeholder="0,00"
                    step="0.01" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Valor Total:</label>
                <div class="valor-info rounded bg-gray-200 px-3 py-2 sm:text-sm">0,00</div>
            </div>
            <div>
                <x-forms.input name="valor_desconto" label="Total c/ Desconto" value="" placeholder="0,00"
                    step="0.01" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Valor desconto:</label>
                <div class="valor-desconto-info rounded bg-gray-200 px-3 py-2 sm:text-sm">0,00</div>
            </div>
        </div>

        <!-- Botão para salvar alterações do item -->
        <div class="mb-4 flex justify-start">
            <button type="button" onclick="salvarAlteracaoItem()"
                class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                    </path>
                </svg>
                Salvar Alterações do Item
            </button>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-4 rounded-lg bg-gray-50 p-4">
            <div class="col-span-full overflow-x-auto">
                <table class="tabelaOrcamentoItens w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col"
                                class="w-12 px-2 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Ação
                            </th>
                            <th scope="col"
                                class="w-20 px-2 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Cód.<br>Produto
                            </th>
                            <th scope="col"
                                class="w-48 px-2 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Descrição<br>produto
                            </th>
                            <th scope="col"
                                class="w-16 px-2 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Unidade
                            </th>
                            <th scope="col"
                                class="w-16 px-2 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Quantidade
                            </th>
                            <th scope="col"
                                class="w-20 px-2 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Condição<br>Pagamento
                            </th>
                            <th scope="col"
                                class="w-20 px-2 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Quantidade<br>Fornecedor
                            </th>
                            <th scope="col"
                                class="w-20 px-2 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Valor<br>Unitário
                            </th>
                            <th scope="col"
                                class="w-24 px-2 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Total s/<br>Desconto
                            </th>
                            <th scope="col"
                                class="w-24 px-2 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Total c/<br>Desconto
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tabelaOrcamentoItensBody" class="divide-y divide-gray-200 bg-white">
                        <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="flex justify-end">
            <button type="button" onclick="salvarEdicao()"
                class="mt-4 rounded-md bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                Salvar</button>
        </div>
    </div>
</x-bladewind.modal>
