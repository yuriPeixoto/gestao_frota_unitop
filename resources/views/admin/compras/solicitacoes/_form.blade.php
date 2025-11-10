<x-slot name="header">
    <div class="flex w-full items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Nova Solicitação de Compra') }}
        </h2>
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.compras.solicitacoes.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar para a Lista
            </a>
        </div>
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
        action="{{ isset($solicitacao) ? route('admin.compras.solicitacoes.update', $solicitacao->id_solicitacoes_compras) : route('admin.compras.solicitacoes.store') }}"
        method="POST" enctype="multipart/form-data" id="formSolicitacao">
        @csrf
        @if (isset($solicitacao))
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

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <!-- Departamento -->
                        <div>
                            {{-- Usuario --}}
                            <label for="id_usuario" class="block text-sm font-medium text-gray-700">Usuário</label>

                            <!-- Input visível com o nome do usuário (somente leitura) -->
                            <input type="text" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ $transferenciaImobilizadoVeiculo->user->name ?? auth()->user()->name }}">

                            <!-- Input oculto com o ID do usuário, que será enviado no form -->
                            <input type="hidden" name="id_usuario"
                                value="{{ $transferenciaImobilizadoVeiculo->user->id ?? auth()->user()->id }}">
                        </div>

                        <div>
                            {{-- Departamento --}}
                            <label for="id_departamento"
                                class="block text-sm font-medium text-gray-700">Departamento</label>

                            <!-- Input visível com o nome do usuário (somente leitura) -->
                            <input type="text" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ auth()->user()->departamento->descricao_departamento ?? '' }}">

                            <!-- Input oculto com o ID do usuário, que será enviado no form -->
                            <input type="hidden" name="id_departamento"
                                value="{{ auth()->user()->departamento_id ?? '' }}">
                        </div>

                        <!-- Filial -->
                        <div class="space-y-1">
                            <x-forms.smart-select name="id_filial" label="Filial da Solicitação" placeholder="Filial"
                                required="true" :options="$filiais" :selected="old('id_filial', $solicitacao->id_filial ?? '')" placeholder="Selecione a filial" />
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <!-- Prioridade -->
                        <div class="space-y-1">
                            <label for="prioridade" class="block text-sm font-medium text-gray-700">
                                Prioridade <span class="text-red-500">*</span>
                            </label>
                            <select id="prioridade" name="prioridade" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm transition duration-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="" {{ old('prioridade') == '' ? 'selected' : '' }}>Selecione a
                                    prioridade</option>
                                <option value="BAIXA"
                                    {{ old('prioridade', $solicitacao->prioridade ?? '') == 'BAIXA' ? 'selected' : '' }}>
                                    Baixa</option>
                                <option value="MEDIA"
                                    {{ old('prioridade', $solicitacao->prioridade ?? '') == 'MEDIA' ? 'selected' : '' }}>
                                    Média</option>
                                <option value="ALTA"
                                    {{ old('prioridade', $solicitacao->prioridade ?? '') == 'ALTA' ? 'selected' : '' }}>
                                    Alta</option>
                            </select>
                        </div>

                        <!-- Filial de Entrega -->
                        <div class="space-y-1">
                            <x-forms.smart-select name="filial_entrega" label="Filial de Entrega" placeholder="Filial"
                                required="true" :options="$filiais" :selected="old('filial_entrega', $solicitacao->filial_entrega ?? '')"
                                placeholder="Selecione a filial" />
                        </div>

                        <!-- Filial de Faturamento -->
                        <div class="space-y-1">
                            <x-forms.smart-select name="filial_faturamento" label="Filial de Faturamento"
                                placeholder="Filial" required="true" :options="$filiais" :selected="old('filial_faturamento', $solicitacao->filial_faturamento ?? '')"
                                placeholder="Selecione a filial" />
                        </div>

                        <!-- Tipo de Solicitação -->
                        <div class="space-y-1">
                            <x-forms.select name="tipo_solicitacao" label="Tipo de Solicitação" :options="$tipo"
                                :disabled="isset($solicitacao->tipo_solicitacao)" valueField="codigo" textField="descricao" :selected="old('tipo_solicitacao', $solicitacao->tipo_solicitacao ?? '')"
                                required="true" />
                        </div>

                        {{-- Tipo de Despesa --}}
                        <div class="space-y-1">
                            <x-forms.smart-select name="grupo_despesa" label="Grupo de Despesa" required="true"
                                :options="$grupoDespesa" :selected="old('grupo_despesa', $solicitacao->id_grupo_despesas ?? '')" placeholder="Selecione o grupo de despesa" />
                        </div>
                    </div>
                </section>

                <!-- Preferências de Fornecedor -->
                <section>
                    <div class="mb-6 border-b border-gray-200 pb-4">
                        <h3 class="flex items-center text-lg font-semibold text-gray-900">
                            <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                            Preferências de Fornecedor
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">Configure as preferências para seleção de fornecedores.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <!-- Opções de Contrato -->
                        <div id="opcoes-contrato" class="rounded-lg bg-gray-50 p-6" style="display: none;">
                            <h4 class="mb-4 text-sm font-medium text-gray-900">Opções de Contrato</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm text-gray-700">Abastecimento Estoque:</label>
                                    <div class="inline-flex rounded-md shadow-sm" role="group">
                                        <!-- Campo oculto para garantir que sempre seja enviado o valor 1 -->
                                        <input type="hidden" name="is_aplicacao_direta" value="1">

                                        <!-- Radio button Sim - sempre marcado e desabilitado -->
                                        <input type="radio" class="hidden" name="is_aplicacao_direta_visual"
                                            id="is_aplicacao_direta_sim" value="1" checked disabled>
                                        <label for="is_aplicacao_direta_sim"
                                            class="cursor-default rounded-l-md border border-gray-300 bg-gray-500 px-4 py-2 text-sm font-medium text-white">
                                            Sim
                                        </label>

                                        <!-- Radio button Não - sempre desmarcado e desabilitado -->
                                        <input type="radio" class="hidden" name="is_aplicacao_direta_visual"
                                            id="is_aplicacao_direta_nao" value="0" disabled>
                                        <label for="is_aplicacao_direta_nao"
                                            class="cursor-default rounded-r-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 opacity-50">
                                            Não
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fornecedor Preferencial -->
                        <div class="space-y-1">
                            <x-forms.smart-select name="id_fornecedor" label="Fornecedor Preferencial"
                                :options="$fornecedores->map(function ($item) {
                                    return [
                                        'value' => $item->id_fornecedor,
                                        'label' => $item->nome_fornecedor,
                                    ];
                                })" :selected="old('id_fornecedor', $solicitacao->id_fornecedor ?? '')" placeholder="Selecione um fornecedor" />
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
                        <p class="mt-1 text-sm text-gray-600">Adicione informações complementares sobre a solicitação.
                        </p>
                    </div>

                    <div>
                        <label for="observacao" class="mb-2 block text-sm font-medium text-gray-700">
                            Observações Gerais
                        </label>
                        <textarea id="observacao" name="observacao" rows="4"
                            placeholder="Descreva informações adicionais, requisitos especiais, prazos ou outras observações relevantes..."
                            class="block w-full rounded-lg border-gray-300 shadow-sm transition duration-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('observacao', $solicitacao->observacao ?? '') }}</textarea>
                    </div>
                </section>

                <!-- Divisor Visual -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-white px-3 text-lg font-medium text-gray-900">Itens da Solicitação</span>
                    </div>
                </div>

                <!-- Itens da Solicitação -->
                <section>
                    <!-- Header da Seção -->
                    <div id="header-adicionar-itens"
                        class="mb-6 rounded-lg border border-indigo-200 bg-gradient-to-r from-indigo-50 to-blue-50 p-6">
                        <div class="mb-4 flex items-center">
                            <svg class="mr-2 h-6 w-6 text-indigo-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <h4 class="text-lg font-medium text-gray-900">Adicionar Itens</h4>
                        </div>
                        <p class="text-sm text-gray-600">Os itens disponíveis são controlados pelo Tipo de Pedido
                            selecionado acima.</p>
                    </div>

                    <!-- Seção Produto - Layout Melhorado -->
                    <div id="secao-produto" class="hidden">
                        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                            <!-- Header da Seção -->
                            <div
                                class="rounded-t-lg border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4">
                                <h4 class="flex items-center text-lg font-semibold text-gray-800">
                                    <svg class="mr-2 h-5 w-5 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    Adicionar Produto
                                </h4>
                            </div>

                            <!-- Conteúdo da Seção -->
                            <div class="space-y-6 p-6">
                                <!-- Informações do Produto -->
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div>
                                        <x-forms.smart-select name="id_produtos" label="Produto"
                                            placeholder="Selecione o produto..." :options="$produtos" required="false"
                                            :searchUrl="route('admin.api.produtos.search')" asyncSearch="true" />
                                    </div>

                                    <div>
                                        <label for="unidade"
                                            class="mb-1 block text-sm font-medium text-gray-700">Unidade</label>
                                        <select name="unidade" id="unidade"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <!-- Opções serão preenchidas automaticamente via JavaScript -->
                                        </select>
                                    </div>
                                    <div>
                                        <x-forms.input name="quantidade_solicitada" type="number" label="Quantidade"
                                            value="{{ old('quantidade', $solicitacao->quantidade ?? '') }}" />
                                    </div>
                                </div>

                                <!-- Justificativa e Arquivo -->
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label for="observacao_item"
                                            class="mb-2 block text-sm font-medium text-gray-700">
                                            Descrição detalhada do Produto
                                        </label>
                                        <textarea name="observacao_item" placeholder="Descreva a observação para o item..." rows="4"
                                            class="w-full rounded-lg border-gray-300 shadow-sm transition duration-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>

                                    <div>
                                        <label for="justificativa_iten_solicitacao"
                                            class="mb-2 block text-sm font-medium text-gray-700">
                                            Justificativa
                                        </label>
                                        <textarea name="justificativa_iten_solicitacao" placeholder="Descreva a justificativa para a solicitação..."
                                            rows="4"
                                            class="w-full rounded-lg border-gray-300 shadow-sm transition duration-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>

                                    <!-- Campo para Anexar Imagem do Produto -->
                                    <div>
                                        <label for="imagem_produto"
                                            class="mb-2 block text-sm font-medium text-gray-700">
                                            Anexar Imagem do Produto
                                            <span class="text-xs text-gray-500">(Opcional - máx. 2MB)</span>
                                        </label>
                                        <div class="flex items-center space-x-3">
                                            <input type="file" id="imagem_produto" name="imagem_produto"
                                                accept="image/jpeg,image/png,image/gif,image/webp"
                                                class="block w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900 focus:border-indigo-500 focus:outline-none">
                                            <button type="button" onclick="limparImagemProduto()"
                                                class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                Limpar
                                            </button>
                                        </div>
                                        <div id="preview-imagem-produto" class="mt-2 hidden">
                                            <img id="img-preview-produto" src="" alt="Preview"
                                                class="max-h-32 max-w-32 rounded-md border border-gray-300 object-cover">
                                        </div>
                                        <p class="text-xs text-gray-500">Formatos aceitos: JPG, PNG, GIF, WEBP</p>
                                    </div>
                                </div>

                                <!-- Botões de Ação -->
                                <div class="flex justify-between border-t border-gray-200 pt-4">
                                    <button type="button" onclick="abrirModalPreCadastro()"
                                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-medium text-gray-700 shadow-sm transition duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        Pré-Cadastro
                                    </button>

                                    <button type="button" onclick="adicionarProduto()"
                                        class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-6 py-3 text-sm font-medium text-white shadow-sm transition duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Adicionar Produto
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Campo hidden para armazenar os produtos -->
                        <input type="hidden" name="produtos" id="produtos_json" value="[]">

                        <!-- Tabela de Produtos -->
                        <div class="mt-6">
                            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                    <h5 class="text-lg font-medium text-gray-900">Produtos Adicionados</h5>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Produtos</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Data Inclusão</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Descrição</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Unidade</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Quantidade</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Observação do produto</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Justificativa do produto</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Anexo</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabelaProdutosBody" class="divide-y divide-gray-200 bg-white">
                                            <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção Serviço - Layout Melhorado -->
                    <div id="secao-servico" class="hidden">
                        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                            <!-- Header da Seção -->
                            <div
                                class="rounded-t-lg border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4">
                                <h4 class="flex items-center text-lg font-semibold text-gray-800">
                                    <svg class="mr-2 h-5 w-5 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Adicionar Serviço
                                </h4>
                            </div>

                            <!-- Conteúdo da Seção -->
                            <div class="space-y-6 p-6">
                                <!-- Informações do Serviço -->
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <x-forms.smart-select name="id_servico" label="Serviço"
                                            placeholder="Selecione o serviço..." :options="$servicos" required="false"
                                            :searchUrl="route('admin.api.servicos.search')" asyncSearch="true" />
                                    </div>

                                    <div>
                                        <x-forms.input name="quantidade_solicitada_servico" type="number"
                                            label="Quantidade"
                                            value="{{ old('quantidade', $solicitacao->quantidade_solicitada ?? '') }}" />
                                    </div>

                                </div>

                                <!-- Justificativa e Arquivo -->
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label for="observacao_item_servico"
                                            class="mb-2 block text-sm font-medium text-gray-700">
                                            Observação do serviço
                                        </label>
                                        <textarea name="observacao_item_servico" placeholder="Descreva a observação para a solicitação..." rows="4"
                                            class="w-full rounded-lg border-gray-300 shadow-sm transition duration-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                    <div>
                                        <label for="justificativa_iten_solicitacao_servico"
                                            class="mb-2 block text-sm font-medium text-gray-700">
                                            Justificativa do item
                                        </label>
                                        <textarea name="justificativa_iten_solicitacao_servico" placeholder="Descreva a justificativa para a solicitação..."
                                            rows="4"
                                            class="w-full rounded-lg border-gray-300 shadow-sm transition duration-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                </div>

                                <!-- Campo para Anexar Imagem do Serviço -->
                                <div>
                                    <label for="imagem_servico" class="mb-2 block text-sm font-medium text-gray-700">
                                        Anexar Imagem do Serviço
                                        <span class="text-xs text-gray-500">(Opcional - máx. 2MB)</span>
                                    </label>
                                    <div class="flex items-center space-x-3">
                                        <input type="file" id="imagem_servico" name="imagem_servico"
                                            accept="image/jpeg,image/png,image/gif,image/webp"
                                            class="block w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900 focus:border-indigo-500 focus:outline-none">
                                        <button type="button" onclick="limparImagemServico()"
                                            class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            Limpar
                                        </button>
                                    </div>
                                    <div id="preview-imagem-servico" class="mt-2 hidden">
                                        <img id="img-preview-servico" src="" alt="Preview"
                                            class="max-h-32 max-w-32 rounded-md border border-gray-300 object-cover">
                                    </div>
                                    <p class="text-xs text-gray-500">Formatos aceitos: JPG, PNG, GIF, WEBP</p>
                                </div>

                                <!-- Botão Adicionar -->
                                <div class="flex justify-end border-t border-gray-200 pt-4">
                                    <button type="button" onclick="adicionarServico()"
                                        class="inline-flex items-center rounded-lg border border-transparent bg-green-600 px-6 py-3 text-sm font-medium text-white shadow-sm transition duration-200 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Adicionar Serviço
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Campo hidden para armazenar os serviços -->
                        <input type="hidden" name="servicos" id="servicos_json" value="[]">

                        <!-- Tabela de Serviços -->
                        <div class="mt-6">
                            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                    <h5 class="text-lg font-medium text-gray-900">Serviços Adicionados</h5>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Serviços</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Data Inclusão</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Descrição</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Quantidade</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Observação do item</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Justificativa do item</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Anexo</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabelaServicosBody" class="divide-y divide-gray-200 bg-white">
                                            <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-end space-x-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                <a href="{{ route('admin.compras.solicitacoes.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-6 py-2 text-sm font-medium text-white shadow-sm transition duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                        </path>
                    </svg>
                    Salvar Solicitação
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Modal de Confirmação -->
<x-ui.modal-confirmacao id="modal-confirmar-cadastro" titulo="Confirmar Cadastro"
    mensagem="Tem certeza que deseja cadastrar esta solicitação de compra?" textoBotaoConfirmar="Confirmar"
    textoBotaoCancelar="Cancelar" tipo="pergunta" :aberto="false" />

<!-- Modal de Pré-Cadastro de Produto -->
<div id="modal-pre-cadastro" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center px-4 py-6 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <div
            class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:align-middle">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <!-- Header do Modal -->
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900">
                        <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Pré-Cadastro de Produto
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">Cadastre um novo produto que será disponibilizado para
                        seleção.</p>
                </div>

                <!-- Formulário do Modal -->
                <form id="form-pre-cadastro">
                    @csrf
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Estoque -->
                        <div>
                            <label for="id_estoque_produto" class="block text-sm font-medium text-gray-700">
                                Estoque <span class="text-red-500">*</span>
                            </label>
                            <select id="id_estoque_produto" name="id_estoque_produto" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecione o estoque...</option>
                                <!-- Opções serão carregadas via JavaScript -->
                            </select>
                        </div>

                        <!-- Filial -->
                        <div>
                            <label for="id_filial_produto" class="block text-sm font-medium text-gray-700">
                                Filial <span class="text-red-500">*</span>
                            </label>
                            <select id="id_filial_produto" name="id_filial" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecione a filial...</option>
                                @foreach ($filiais as $filial)
                                    <option value="{{ $filial['value'] }}">{{ $filial['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Descrição do Produto -->
                        <div class="md:col-span-2">
                            <label for="descricao_produto" class="block text-sm font-medium text-gray-700">
                                Descrição do Produto <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="descricao_produto" name="descricao_produto" required
                                placeholder="Digite a descrição do produto..."
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- Unidade -->
                        <div>
                            <label for="id_unidade_produto" class="block text-sm font-medium text-gray-700">
                                Unidade <span class="text-red-500">*</span>
                            </label>
                            <select id="id_unidade_produto" name="id_unidade_produto" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecione a unidade...</option>
                                <!-- Opções serão carregadas via JavaScript -->
                            </select>
                        </div>

                        <!-- Grupo -->
                        <div>
                            <label for="id_grupo_servico" class="block text-sm font-medium text-gray-700">
                                Grupo <span class="text-red-500">*</span>
                            </label>
                            <select id="id_grupo_servico" name="id_grupo_servico" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecione o grupo...</option>
                                <!-- Opções serão carregadas via JavaScript -->
                            </select>
                        </div>

                        <!-- Quantidade -->
                        <div>
                            <label for="quantidade_solicitada_modal" class="block text-sm font-medium text-gray-700">
                                Quantidade <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="quantidade_solicitada_modal" name="quantidade_solicitada"
                                required min="1" step="0.01" value="1"
                                placeholder="Digite a quantidade..."
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </form>
            </div>

            <!-- Botões do Modal -->
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="button" onclick="salvarPreCadastro()"
                    class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">
                    Salvar Produto
                </button>
                <button type="button" onclick="fecharModalPreCadastro()"
                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @include('admin.compras.solicitacoes._scripts')
@endpush
