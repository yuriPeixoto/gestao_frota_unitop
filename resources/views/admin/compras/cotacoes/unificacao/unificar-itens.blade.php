<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Unificar Itens de Solicitações') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.compras.cotacoes.index') }}"
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

        <!-- Instruções -->
        <div class="mb-6 rounded-lg bg-blue-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Como funciona a unificação de itens:</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-inside list-disc space-y-1">
                            <li>Selecione itens de diferentes solicitações com situação <strong>INICIADA</strong></li>
                            <li>Apenas solicitações com <strong>comprador preenchido</strong> são exibidas</li>
                            <li>Todos os itens devem ser do <strong>mesmo tipo</strong> (produtos ou serviços)</li>
                            <li>Todos os itens devem ter o <strong>mesmo comprador</strong></li>
                            <li>Itens iguais serão consolidados (quantidades somadas)</li>
                            <li>Uma nova cotação será criada apenas com os itens selecionados</li>
                            <li>As solicitações originais <strong>não são alteradas</strong> - apenas os itens são
                                copiados</li>
                            <li>Todas as movimentações são registradas no sistema de auditoria</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="mb-6 rounded-lg bg-gray-50 p-4">
            <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-700">Filtros</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label for="filter-comprador" class="block text-sm font-medium text-gray-700">
                        Comprador
                    </label>
                    <select id="filter-comprador"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos os compradores</option>
                        @foreach ($compradores as $comprador)
                            <option value="{{ $comprador['value'] }}">{{ $comprador['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-tipo" class="block text-sm font-medium text-gray-700">
                        Tipo de Solicitação
                    </label>
                    <select id="filter-tipo"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos os tipos</option>
                        <option value="1">Produtos</option>
                        <option value="2">Serviços</option>
                    </select>
                </div>
                <div>
                    <label for="filter-solicitacao" class="block text-sm font-medium text-gray-700">
                        Código Solicitação
                    </label>
                    <input type="text" id="filter-solicitacao"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Digite o código...">
                </div>
                <div class="flex items-end">
                    <button type="button" id="btn-limpar-filtros"
                        class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Limpar Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Formulário -->
        <form method="POST" action="{{ route('admin.compras.cotacoes.unificar-itens') }}" id="form-unificar-itens">
            @csrf

            <!-- Configurações da Unificação -->
            <div class="mb-6 rounded-lg bg-white p-4 shadow">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Configurações da Unificação de Itens</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="observacao" class="block text-sm font-medium text-gray-700">
                            Observação da unificação (opcional)
                        </label>
                        <textarea id="observacao" name="observacao" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Descreva o motivo da unificação dos itens...">{{ old('observacao') }}</textarea>
                        <p class="mt-2 text-sm text-gray-500">
                            <strong>Nota:</strong> Apenas itens do mesmo tipo (produtos ou serviços) podem ser
                            unificados. O sistema detecta automaticamente o tipo.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botões de ação -->
            @if (!$itens->isEmpty())
                <div class="mb-6 mt-6 flex justify-end space-x-4">
                    <a href="{{ route('admin.compras.cotacoes.index') }}"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cancelar
                    </a>
                    <button type="submit" id="btn-unificar-itens"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        disabled>
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                            </path>
                        </svg>
                        Unificar Itens Selecionados
                    </button>
                </div>
            @endif

            <!-- Lista de itens -->
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            Itens de Solicitações
                            <span class="text-sm font-normal text-gray-500">
                                (Situação INICIADA com comprador preenchido)
                            </span>
                        </h3>
                        <div class="flex items-center space-x-4">
                            <button type="button" id="btn-selecionar-todos"
                                class="text-sm text-indigo-600 hover:text-indigo-900">
                                Selecionar Todos
                            </button>
                            <button type="button" id="btn-limpar-selecao"
                                class="text-sm text-gray-600 hover:text-gray-900">
                                Limpar Seleção
                            </button>
                            <div class="text-sm text-gray-500">
                                <span id="contador-selecionados">0</span> selecionados
                            </div>
                        </div>
                    </div>
                </div>

                @if ($itens->isEmpty())
                    <div class="p-6 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum item disponível</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Não há itens de solicitações com situação INICIADA e comprador preenchido para unificar.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <x-tables.table>
                            <x-tables.header>
                                <x-tables.head-cell>
                                    <input type="checkbox" id="select-all-checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </x-tables.head-cell>
                                <x-tables.head-cell>Código Solicitação</x-tables.head-cell>
                                <x-tables.head-cell>Comprador</x-tables.head-cell>
                                <x-tables.head-cell>Tipo</x-tables.head-cell>
                                <x-tables.head-cell>Item</x-tables.head-cell>
                                <x-tables.head-cell>Descrição</x-tables.head-cell>
                                <x-tables.head-cell>Quantidade</x-tables.head-cell>
                            </x-tables.header>
                            <x-tables.body>
                                @forelse ($itens as $index => $item)
                                    <x-tables.row :index="$index" class="item-row"
                                        data-comprador="{{ $item->solicitacaoCompra->comprador->name ?? 'N/A' }}"
                                        data-tipo="{{ $item->solicitacaoCompra->tipo_solicitacao }}"
                                        data-solicitacao="#{{ $item->solicitacaoCompra->id_solicitacoes_compras ?? 'N/A' }}">
                                        <x-tables.cell>
                                            <input type="checkbox" name="itens_ids[]"
                                                value="{{ $item->id_itens_solicitacoes }}"
                                                class="item-checkbox h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        </x-tables.cell>
                                        <x-tables.cell>
                                            #{{ $item->solicitacaoCompra->id_solicitacoes_compras ?? 'N/A' }}
                                        </x-tables.cell>
                                        <x-tables.cell>
                                            <span
                                                class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                                {{ $item->solicitacaoCompra->comprador->name ?? 'N/A' }}
                                            </span>
                                        </x-tables.cell>
                                        <x-tables.cell>
                                            @if ($item->solicitacaoCompra->tipo_solicitacao == 1)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                    Produtos
                                                </span>
                                            @elseif ($item->solicitacaoCompra->tipo_solicitacao == 2)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800">
                                                    Serviços
                                                </span>
                                            @endif
                                        </x-tables.cell>
                                        <x-tables.cell>
                                            {{ $item->id_produto ?? 'N/A' }}
                                        </x-tables.cell>
                                        <x-tables.cell>
                                            <div class="max-w-xs truncate"
                                                title="{{ $item->produto->descricao_produto }}">
                                                {{ $item->produto->descricao_produto }}
                                            </div>
                                        </x-tables.cell>
                                        <x-tables.cell>
                                            {{ number_format($item->quantidade_solicitada, 2, ',', '.') }}
                                        </x-tables.cell>
                                    </x-tables.row>
                                @empty
                                    <x-tables.empty cols="10" message="Nenhuma cotação encontrada" />
                                @endforelse
                            </x-tables.body>

                        </x-tables.table>
                    </div>

                    <!-- Paginação -->
                    <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        {{ $itens->links() }}
                    </div>

                @endif
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('form-unificar-itens');
                const btnUnificarItens = document.getElementById('btn-unificar-itens');
                const contadorSelecionados = document.getElementById('contador-selecionados');
                const btnSelecionarTodos = document.getElementById('btn-selecionar-todos');
                const btnLimparSelecao = document.getElementById('btn-limpar-selecao');
                const btnLimparFiltros = document.getElementById('btn-limpar-filtros');

                // Filtros
                const filterComprador = document.getElementById('filter-comprador');
                const filterTipo = document.getElementById('filter-tipo');
                const filterSolicitacao = document.getElementById('filter-solicitacao');

                // Função para obter checkboxes e rows atuais (por causa da paginação)
                function getElements() {
                    return {
                        checkboxes: document.querySelectorAll('.item-checkbox'),
                        selectAllCheckbox: document.getElementById('select-all-checkbox'),
                        itemRows: document.querySelectorAll('.item-row')
                    };
                }

                // Aplicar filtros
                function aplicarFiltros() {
                    const compradorSelecionado = filterComprador.value;
                    const tipoSelecionado = filterTipo.value;
                    const solicitacaoSelecionada = filterSolicitacao.value.toLowerCase();
                    const {
                        itemRows
                    } = getElements();

                    itemRows.forEach(row => {
                        const comprador = row.dataset.comprador;
                        const tipo = row.dataset.tipo;
                        const solicitacao = row.dataset.solicitacao;

                        let mostrar = true;

                        if (compradorSelecionado && comprador !== compradorSelecionado) {
                            mostrar = false;
                        }

                        if (tipoSelecionado && tipo !== tipoSelecionado) {
                            mostrar = false;
                        }

                        if (solicitacaoSelecionada && !solicitacao.toLowerCase().includes(
                                solicitacaoSelecionada)) {
                            mostrar = false;
                        }

                        row.style.display = mostrar ? '' : 'none';

                        // Desmarcar checkbox se esconder a linha
                        if (!mostrar) {
                            const checkbox = row.querySelector('.item-checkbox');
                            checkbox.checked = false;
                        }
                    });

                    atualizarContador();
                    verificarConsistencia();
                }

                // Event listeners para filtros
                filterComprador.addEventListener('change', aplicarFiltros);
                filterTipo.addEventListener('change', aplicarFiltros);
                filterSolicitacao.addEventListener('input', aplicarFiltros);

                btnLimparFiltros.addEventListener('click', function() {
                    filterComprador.value = '';
                    filterTipo.value = '';
                    filterSolicitacao.value = '';
                    aplicarFiltros();
                });

                // Função para atualizar contador
                function atualizarContador() {
                    const {
                        checkboxes
                    } = getElements();
                    const selecionados = Array.from(checkboxes).filter(cb => cb.checked).length;
                    contadorSelecionados.textContent = selecionados;

                    // Habilitar/desabilitar botão
                    btnUnificarItens.disabled = selecionados < 2;

                    if (selecionados < 2) {
                        btnUnificarItens.classList.add('opacity-50', 'cursor-not-allowed');
                    } else {
                        btnUnificarItens.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }

                // Função para verificar se os compradores e tipos são consistentes
                function verificarConsistencia() {
                    const {
                        checkboxes
                    } = getElements();
                    const checkboxesSelecionados = Array.from(checkboxes).filter(cb => cb.checked);

                    if (checkboxesSelecionados.length <= 1) {
                        return true;
                    }

                    const compradores = new Set();
                    const tipos = new Set();

                    checkboxesSelecionados.forEach(checkbox => {
                        const row = checkbox.closest('tr');
                        compradores.add(row.dataset.comprador);
                        tipos.add(row.dataset.tipo);
                    });

                    if (compradores.size > 1) {
                        // Mostrar alerta e desmarcar último checkbox selecionado
                        alert(
                            '⚠️ ATENÇÃO: Você selecionou itens com compradores diferentes. Apenas itens do mesmo comprador podem ser unificados.'
                        );

                        // Desmarcar o último checkbox selecionado
                        const ultimoCheckbox = checkboxesSelecionados[checkboxesSelecionados.length - 1];
                        ultimoCheckbox.checked = false;
                        atualizarContador();
                        return false;
                    }

                    if (tipos.size > 1) {
                        // Mostrar alerta e desmarcar último checkbox selecionado
                        alert(
                            '⚠️ ATENÇÃO: Você selecionou itens de tipos diferentes. Apenas itens do mesmo tipo (produtos ou serviços) podem ser unificados.'
                        );

                        // Desmarcar o último checkbox selecionado
                        const ultimoCheckbox = checkboxesSelecionados[checkboxesSelecionados.length - 1];
                        ultimoCheckbox.checked = false;
                        atualizarContador();
                        return false;
                    }

                    return true;
                }

                // Função para configurar event listeners dos checkboxes
                function configurarEventListeners() {
                    const {
                        checkboxes,
                        selectAllCheckbox
                    } = getElements();

                    // Event listeners para checkboxes individuais
                    checkboxes.forEach(checkbox => {
                        // Remove listener anterior se existir
                        checkbox.removeEventListener('change', handleCheckboxChange);
                        checkbox.addEventListener('change', handleCheckboxChange);
                    });

                    // Select all functionality
                    if (selectAllCheckbox) {
                        selectAllCheckbox.removeEventListener('change', handleSelectAllChange);
                        selectAllCheckbox.addEventListener('change', handleSelectAllChange);
                    }
                }

                function handleCheckboxChange() {
                    // Verificar consistência do comprador e tipo primeiro (se estiver marcando)
                    if (this.checked) {
                        // Verificar se viola as regras antes de atualizar contador
                        const validacao = verificarConsistenciaImediata(this);
                        if (!validacao) {
                            this.checked = false;
                            return;
                        }
                    }

                    atualizarContador();
                }

                // Função auxiliar para verificação imediata
                function verificarConsistenciaImediata(checkboxAtual) {
                    const {
                        checkboxes
                    } = getElements();
                    const checkboxesSelecionados = Array.from(checkboxes).filter(cb => cb.checked);

                    if (checkboxesSelecionados.length <= 1) {
                        return true;
                    }

                    const compradores = new Set();
                    const tipos = new Set();

                    checkboxesSelecionados.forEach(checkbox => {
                        const row = checkbox.closest('tr');
                        compradores.add(row.dataset.comprador);
                        tipos.add(row.dataset.tipo);
                    });

                    if (compradores.size > 1) {
                        alert(
                            '⚠️ ATENÇÃO: Você selecionou itens com compradores diferentes. Apenas itens do mesmo comprador podem ser unificados.'
                        );
                        return false;
                    }

                    if (tipos.size > 1) {
                        alert(
                            '⚠️ ATENÇÃO: Você selecionou itens de tipos diferentes. Apenas itens do mesmo tipo (produtos ou serviços) podem ser unificados.'
                        );
                        return false;
                    }

                    return true;
                }

                function handleSelectAllChange() {
                    const {
                        checkboxes
                    } = getElements();
                    const visibleCheckboxes = Array.from(checkboxes).filter(cb => {
                        const row = cb.closest('tr');
                        return row.style.display !== 'none';
                    });

                    visibleCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });

                    atualizarContador();
                    verificarConsistencia();
                }

                // Botões de seleção
                btnSelecionarTodos.addEventListener('click', function() {
                    const {
                        checkboxes,
                        selectAllCheckbox
                    } = getElements();
                    const visibleCheckboxes = Array.from(checkboxes).filter(cb => {
                        const row = cb.closest('tr');
                        return row.style.display !== 'none';
                    });

                    visibleCheckboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });

                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = true;
                    }
                    atualizarContador();
                    verificarConsistencia();
                });

                btnLimparSelecao.addEventListener('click', function() {
                    const {
                        checkboxes,
                        selectAllCheckbox
                    } = getElements();
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = false;
                    }
                    atualizarContador();
                });

                // Validação do formulário
                form.addEventListener('submit', function(e) {
                    const {
                        checkboxes
                    } = getElements();
                    const selecionados = Array.from(checkboxes).filter(cb => cb.checked).length;

                    if (selecionados < 2) {
                        e.preventDefault();
                        alert('Selecione pelo menos 2 itens para unificar.');
                        return;
                    }

                    if (!verificarConsistencia()) {
                        e.preventDefault();
                        alert(
                            'Todos os itens selecionados devem ter o mesmo comprador e ser do mesmo tipo (produtos ou serviços).'
                        );
                        return;
                    }

                    // Confirmação
                    const checkboxesSelecionados = Array.from(checkboxes).filter(cb => cb.checked);
                    const compradores = checkboxesSelecionados.map(cb => cb.closest('tr').dataset.comprador);
                    const tipos = checkboxesSelecionados.map(cb => cb.closest('tr').dataset.tipo);
                    const compradorUnico = [...new Set(compradores)][0];
                    const tipoUnico = [...new Set(tipos)][0];
                    const tipoTexto = tipoUnico === '1' ? 'produtos' : 'serviços';

                    const confirmMsg =
                        `Confirma a unificação de ${selecionados} itens do comprador "${compradorUnico}" (${tipoTexto})?\n\n` +
                        `Uma nova cotação será criada apenas com os itens selecionados.\n` +
                        `As solicitações originais não serão alteradas.`;

                    if (!confirm(confirmMsg)) {
                        e.preventDefault();
                    }
                });

                // Interceptar cliques em links de paginação para preservar filtros
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('a[href*="page="]');
                    if (link) {
                        const url = new URL(link.href);

                        // Adicionar filtros atuais à URL
                        if (filterComprador.value) {
                            url.searchParams.set('comprador', filterComprador.value);
                        }
                        if (filterTipo.value) {
                            url.searchParams.set('tipo', filterTipo.value);
                        }
                        if (filterSolicitacao.value) {
                            url.searchParams.set('solicitacao', filterSolicitacao.value);
                        }

                        link.href = url.toString();
                    }
                });

                // Aplicar filtros da URL ao carregar a página
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('comprador')) {
                    filterComprador.value = urlParams.get('comprador');
                }
                if (urlParams.get('tipo')) {
                    filterTipo.value = urlParams.get('tipo');
                }
                if (urlParams.get('solicitacao')) {
                    filterSolicitacao.value = urlParams.get('solicitacao');
                }

                // Configurar tudo inicialmente
                configurarEventListeners();
                aplicarFiltros();
                atualizarContador();

                // Observer para detectar mudanças no DOM (paginação AJAX)
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList') {
                            configurarEventListeners();
                            aplicarFiltros();
                            atualizarContador();
                        }
                    });
                });

                // Observar mudanças na tabela
                const tableBody = document.getElementById('itens-table-body');
                if (tableBody) {
                    observer.observe(tableBody, {
                        childList: true,
                        subtree: true
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
