<x-slot name="header">
    <div class="flex w-full items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Aprovação Pedido') }}
        </h2>
    </div>
</x-slot>

<div class="border-b border-gray-200 bg-white p-4">
    <!-- Mensagens de Sucesso/Erro -->
    @if (session('success'))
        <div class="mb-6 rounded-r-lg border-l-4 border-green-400 bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-r-lg border-l-4 border-red-400 bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-r-lg border-l-4 border-red-400 bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Corrija os seguintes erros:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Formulário de Criação -->
    <form
        action="{{ isset($solicitacoes) ? route('admin.compras.aprovarpedido.update', $solicitacoes->id_validarcotacoes_compras) : route('admin.compras.aprovarpedido.store') }}"
        method="POST" enctype="multipart/form-data" id="formsolicitacoes">
        @csrf
        @if (isset($solicitacoes))
            @method('PUT')
        @endif

        <!-- Card Principal -->
        <div class="overflow-hidden rounded-lg bg-white shadow-lg">
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
                            Informações Básicas
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">Preencha as informações principais da solicitação.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <!-- Cód. Solicitação -->
                        <div>
                            <x-forms.input name="solicitacoes_compra_consulta" label="Cód. Solicitação de Compras"
                                :disabled="true" value="{{ $solicitacao->id_solicitacoes_compras ?? '' }}" readonly />
                        </div>

                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <!-- Comprador -->
                        <div>
                            <x-forms.input name="id_comprador" label="Comprador"
                                value="{{ $solicitacao->comprador->name ?? '' }}" readonly />
                        </div>

                        <!-- Departamento -->
                        <div>
                            <x-forms.input name="id_departamento" label="Departamento"
                                value="{{ $solicitacao->departamento->descricao_departamento ?? '' }}" readonly />
                        </div>

                        <!-- Prioridade -->
                        <div>
                            <x-forms.input name="prioridade" label="Prioridade"
                                value="{{ $solicitacao->prioridade ?? '' }}" readonly />
                        </div>
                    </div>
                </section>

                <!-- Cotações (sempre visíveis, preenchidas após processar) -->
                <section id="cotacoes-section">
                    <div class="mb-6 border-b border-gray-200 pb-4">
                        <h3 class="flex items-center text-lg font-semibold text-gray-900">
                            <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Cotações
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3" id="cotacoes-container">
                        <!-- Cotação 01 -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-500">Cód. Cotação:</h4>
                                    <input type="text" id="cotacao-01-codigo"
                                        class="w-20 border-0 bg-transparent p-0 text-right text-lg font-bold text-gray-900"
                                        readonly>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Cotação - 01</h3>
                                <p class="text-sm text-gray-600" id="cotacao-01-fornecedor"></p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <div class="overflow-hidden">
                                        <div
                                            class="grid grid-cols-5 gap-2 rounded-t bg-blue-100 p-2 text-xs font-medium text-gray-600">
                                            <div>Produto</div>
                                            <div class="text-center">Qtd</div>
                                            <div class="text-center">Vlr</div>
                                            <div class="text-center">Vlr.Bruto</div>
                                            <div class="text-center">Vlr com desconto</div>
                                        </div>
                                        <div id="cotacao-01-itens" class="min-h-[60px] rounded-b bg-blue-50">
                                            <div class="p-3 text-center text-sm text-gray-500">Nenhum registro foi
                                                encontrado</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cotação 02 -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-500">Cód. Cotação:</h4>
                                    <input type="text" id="cotacao-02-codigo"
                                        class="w-20 border-0 bg-transparent p-0 text-right text-lg font-bold text-gray-900"
                                        readonly>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Cotação - 02</h3>
                                <p class="text-sm text-gray-600" id="cotacao-02-fornecedor"></p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <div class="overflow-hidden">
                                        <div
                                            class="grid grid-cols-5 gap-2 rounded-t bg-blue-100 p-2 text-xs font-medium text-gray-600">
                                            <div>Produto</div>
                                            <div class="text-center">Qtd</div>
                                            <div class="text-center">Vlr</div>
                                            <div class="text-center">Vlr.Bruto</div>
                                            <div class="text-center">Vlr com desconto</div>
                                        </div>
                                        <div id="cotacao-02-itens" class="min-h-[60px] rounded-b bg-blue-50">
                                            <div class="p-3 text-center text-sm text-gray-500">Nenhum registro foi
                                                encontrado</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cotação 03 -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-500">Cód. Cotação:</h4>
                                    <input type="text" id="cotacao-03-codigo"
                                        class="w-20 border-0 bg-transparent p-0 text-right text-lg font-bold text-gray-900"
                                        readonly>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Cotação - 03</h3>
                                <p class="text-sm text-gray-600" id="cotacao-03-fornecedor"></p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <div class="overflow-hidden">
                                        <div
                                            class="grid grid-cols-5 gap-2 rounded-t bg-blue-100 p-2 text-xs font-medium text-gray-600">
                                            <div>Produto</div>
                                            <div class="text-center">Qtd</div>
                                            <div class="text-center">Vlr</div>
                                            <div class="text-center">Vlr.Bruto</div>
                                            <div class="text-center">Vlr com desconto</div>
                                        </div>
                                        <div id="cotacao-03-itens" class="min-h-[60px] rounded-b bg-blue-50">
                                            <div class="p-3 text-center text-sm text-gray-500">Nenhum registro foi
                                                encontrado</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-1">
                        <div class="mb-6 border-b border-gray-200 pb-4">
                            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Seleção de Cotação
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">Escolha como deseja aprovar a cotação.</p>
                        </div>

                        <div class="space-y-4">
                            <!-- Tipo de Aprovação -->
                            <div>
                                <label for="tipo_aprovacao" class="mb-2 block text-sm font-medium text-gray-700">
                                    Tipo de Aprovação:
                                </label>
                                <select name="tipo_aprovacao" id="tipo_aprovacao"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Selecione uma opção</option>
                                    <option value="menorValorFornecedor">Menor Valor Fornecedor</option>
                                    <option value="menorValorProdutos">Menor Valor Produtos</option>
                                    <option value="selecionarCotacao">Selecionar Cotação</option>
                                </select>
                            </div>

                            <!-- Select de Fornecedores -->
                            <div>
                                <label for="cotacao_selecionada" class="mb-2 block text-sm font-medium text-gray-700">
                                    Selecionar Fornecedor/Cotação:
                                </label>
                                <select name="cotacao_selecionada" id="cotacao_selecionada"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Selecione um fornecedor</option>
                                </select>
                                <p class="mt-2 text-xs text-gray-500">
                                    As opções serão carregadas após processar as cotações.
                                </p>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-2">
                                <div>
                                    <x-forms.smart-select name="filial_entrega" label="Filial de Entrega"
                                        :options="$filiais" :value="$solicitacao->filialEntrega->name ?? ''" />
                                </div>

                                <div>
                                    <x-forms.smart-select name="filial_faturamento" label="Filial de Faturamento"
                                        :options="$filiais" :value="$solicitacao->filialFaturamento->name ?? ''" />
                                </div>
                            </div>

                            <div>
                                <label for="observacoes"
                                    class="mb-2 block text-sm font-medium text-gray-700">Observações:</label>
                                <textarea name="observacoes" id="observacoes" rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
                        </div>
                    </div>
                </section>

            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-start space-x-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                <button type="button" onclick="aprovarCotacao()"
                    class="inline-flex cursor-pointer items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <x-icons.check class="mr-2 h-4 w-4" />
                    Aprovar Cotação
                </button>

                <a href="{{ route('admin.compras.aprovarpedido.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <x-icons.arrow-back class="mr-2 text-cyan-500" />
                    Voltar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Modal para Seleção de Cotação -->
<div id="modal-selecionar-cotacao" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
        <!-- Overlay do modal -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Conteúdo do modal -->
        <div
            class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-7xl sm:align-middle">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="w-full">
                        <div class="mb-6 flex items-center justify-between">
                            <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                                📋 Listagem de Itens Cotações
                            </h3>
                            <button type="button" onclick="fecharModalCotacoes()"
                                class="text-2xl text-gray-400 hover:text-gray-600">
                                ❌
                            </button>
                        </div>

                        <!-- Tabela de Cotações -->
                        <div class="overflow-x-auto rounded-lg border border-gray-300 bg-white">
                            <table id="tabela-cotacoes-modal" class="min-w-full text-sm">
                                <thead class="bg-blue-50">
                                    <tr>
                                        <th
                                            class="border border-gray-300 px-2 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">
                                            <input type="checkbox" id="select-all-cotacoes"
                                                onchange="selecionarTodasCotacoes()"
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        </th>
                                        <th
                                            class="border border-gray-300 px-2 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-700">
                                            Cód.<br>Cotações<br>Itens</th>
                                        <th
                                            class="border border-gray-300 px-2 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">
                                            Fornecedor</th>
                                        <th
                                            class="border border-gray-300 px-2 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">
                                            Descrição Produto</th>
                                        <th
                                            class="border border-gray-300 px-2 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-700">
                                            Unidade</th>
                                        <th
                                            class="border border-gray-300 px-2 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-700">
                                            Quantidade<br>Solicitada</th>
                                        <th
                                            class="border border-gray-300 px-2 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-700">
                                            Quantidade<br>Fornecedor</th>
                                        <th
                                            class="border border-gray-300 px-2 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-700">
                                            Valor<br>Unitário</th>
                                        <th
                                            class="border border-gray-300 px-2 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-700">
                                            Valor<br>Item</th>
                                        <th
                                            class="border border-gray-300 px-2 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-700">
                                            Valor Desconto<br>Item</th>
                                        <th
                                            class="border border-gray-300 px-2 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-700">
                                            % desconto<br>item</th>
                                    </tr>
                                </thead>
                                <tbody id="corpo-tabela-cotacoes" class="divide-y divide-gray-200 bg-white">
                                    <!-- Conteúdo será preenchido via JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Loading -->
                        <div id="loading-cotacoes" class="hidden py-8 text-center">
                            <div class="inline-block h-8 w-8 animate-spin rounded-full border-b-2 border-blue-600">
                            </div>
                            <p class="mt-2 text-gray-600">Carregando cotações...</p>
                        </div>

                        <!-- Paginação -->
                        <div id="paginacao-modal" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Mostrando <span id="itens-inicio">0</span> a <span id="itens-fim">0</span> de <span
                                    id="total-itens">0</span> itens
                            </div>
                            <div class="flex items-center space-x-2">
                                <button type="button" onclick="paginaAnterior()" id="btn-anterior"
                                    class="rounded bg-gray-200 px-3 py-1 text-xs hover:bg-gray-300 disabled:cursor-not-allowed disabled:opacity-50">
                                    ← Anterior
                                </button>
                                <span id="pagina-atual-info" class="px-3 py-1 text-xs">Página 1</span>
                                <button type="button" onclick="proximaPagina()" id="btn-proximo"
                                    class="rounded bg-gray-200 px-3 py-1 text-xs hover:bg-gray-300 disabled:cursor-not-allowed disabled:opacity-50">
                                    Próximo →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões do modal -->
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="button" onclick="gerarPedidosComItensSelecionados()"
                    class="inline-flex w-full justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                    🗂️ Gerar Pedidos com os Itens Selecionados
                </button>
                <button type="button" onclick="fecharModalCotacoes()"
                    class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @include('admin.compras.aprovarpedido._scripts')
@endpush
