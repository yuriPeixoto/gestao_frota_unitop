<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Unificar Cotações') }}
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
                    <h3 class="text-sm font-medium text-blue-800">Como funciona a unificação de cotações:</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-inside list-disc space-y-1">
                            <li>Selecione pelo menos 2 cotações para unificar</li>
                            <li>Apenas cotações com situação <strong>INICIADA</strong> e <strong>comprador
                                    preenchido</strong> podem ser unificadas</li>
                            <li>Todas as cotações devem ter o <strong>mesmo comprador</strong></li>
                            <li>Todas as cotações devem ser do <strong>mesmo tipo</strong> (produtos ou serviços)</li>
                            <li>Itens iguais serão consolidados (quantidades somadas)</li>
                            <li>As cotações originais serão finalizadas automaticamente</li>
                            <li>Uma nova cotação será criada com todos os itens selecionados</li>
                            <li>A nova cotação será marcada como <strong>unificada</strong> e poderá ser desmembrada
                            </li>
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
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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
                        Tipo de Cotação
                    </label>
                    <select id="filter-tipo"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos os tipos</option>
                        <option value="1">Produtos</option>
                        <option value="2">Serviços</option>
                    </select>
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
        <form method="POST" action="{{ route('admin.compras.cotacoes.unificar') }}" id="form-unificar-cotacoes">
            @csrf

            <!-- Configurações da Unificação -->
            <div class="mb-6 rounded-lg bg-white p-4 shadow">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Configurações da Unificação</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="observacao" class="block text-sm font-medium text-gray-700">
                            Observação da unificação (opcional)
                        </label>
                        <textarea id="observacao" name="observacao" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Descreva o motivo da unificação das cotações...">{{ old('observacao') }}</textarea>
                        <p class="mt-2 text-sm text-gray-500">
                            <strong>Nota:</strong> Apenas cotações do mesmo tipo (produtos ou serviços) podem ser
                            unificadas. O sistema detecta automaticamente o tipo.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botões de ação -->
            @if (!$cotacoes->isEmpty())
                <div class="mb-6 mt-6 flex justify-end space-x-4">
                    <a href="{{ route('admin.compras.cotacoes.index') }}"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cancelar
                    </a>
                    <button type="submit" id="btn-unificar"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        disabled>
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                            </path>
                        </svg>
                        Unificar Cotações Selecionadas
                    </button>
                </div>
            @endif

            <!-- Lista de cotações -->
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            Cotações Disponíveis
                            <span class="text-sm font-normal text-gray-500">
                                (Situação INICIADA com comprador preenchido)
                            </span>
                        </h3>
                        <div class="flex items-center space-x-4">
                            <button type="button" id="btn-selecionar-todas"
                                class="text-sm text-indigo-600 hover:text-indigo-900">
                                Selecionar Todas
                            </button>
                            <button type="button" id="btn-limpar-selecao"
                                class="text-sm text-gray-600 hover:text-gray-900">
                                Limpar Seleção
                            </button>
                            <div class="text-sm text-gray-500">
                                <span id="contador-selecionadas">0</span> selecionadas
                            </div>
                        </div>
                    </div>
                </div>

                @if ($cotacoes->isEmpty())
                    <div class="p-6 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma cotação disponível</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Não há cotações com situação INICIADA e comprador preenchido para unificar.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        <input type="checkbox" id="select-all-checkbox"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Código
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Data
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Comprador
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Tipo
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Departamento
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Filial
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Situação
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white" id="cotacoes-table-body">
                                @foreach ($cotacoes as $cotacao)
                                    <tr class="cotacao-row hover:bg-gray-50"
                                        data-comprador="{{ $cotacao->id_comprador }}"
                                        data-comprador-nome="{{ $cotacao->comprador->name ?? 'N/A' }}"
                                        data-tipo="{{ $cotacao->tipo_solicitacao }}">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <input type="checkbox" name="cotacoes_ids[]"
                                                value="{{ $cotacao->id_solicitacoes_compras }}"
                                                class="cotacao-checkbox h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                            #{{ $cotacao->id_solicitacoes_compras }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ $cotacao->data_inclusao ? $cotacao->data_inclusao->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            <span
                                                class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                                {{ $cotacao->comprador->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            @if ($cotacao->tipo_solicitacao == 1)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                    Produtos
                                                </span>
                                            @elseif ($cotacao->tipo_solicitacao == 2)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800">
                                                    Serviços
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ $cotacao->departamento->descricao_departamento ?? 'N/A' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ $cotacao->filial->name ?? 'N/A' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                                {{ $cotacao->situacao_compra }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        {{ $cotacoes->links() }}
                    </div>

                @endif
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('form-unificar-cotacoes');
                const btnUnificar = document.getElementById('btn-unificar');
                const contadorSelecionadas = document.getElementById('contador-selecionadas');
                const btnSelecionarTodas = document.getElementById('btn-selecionar-todas');
                const btnLimparSelecao = document.getElementById('btn-limpar-selecao');
                const btnLimparFiltros = document.getElementById('btn-limpar-filtros');

                // Filtros
                const filterComprador = document.getElementById('filter-comprador');
                const filterTipo = document.getElementById('filter-tipo');

                // Função para obter checkboxes e rows atuais (por causa da paginação)
                function getElements() {
                    return {
                        checkboxes: document.querySelectorAll('.cotacao-checkbox'),
                        selectAllCheckbox: document.getElementById('select-all-checkbox'),
                        cotacaoRows: document.querySelectorAll('.cotacao-row')
                    };
                }

                // Aplicar filtros
                function aplicarFiltros() {
                    const compradorSelecionado = filterComprador.value;
                    const tipoSelecionado = filterTipo.value;
                    const {
                        cotacaoRows
                    } = getElements();

                    cotacaoRows.forEach(row => {
                        const comprador = row.dataset.comprador;
                        const tipo = row.dataset.tipo;

                        let mostrar = true;

                        if (compradorSelecionado && comprador !== compradorSelecionado) {
                            mostrar = false;
                        }

                        if (tipoSelecionado && tipo !== tipoSelecionado) {
                            mostrar = false;
                        }

                        row.style.display = mostrar ? '' : 'none';

                        // Desmarcar checkbox se esconder a linha
                        if (!mostrar) {
                            const checkbox = row.querySelector('.cotacao-checkbox');
                            checkbox.checked = false;
                        }
                    });

                    atualizarContador();
                    verificarCompradorConsistente();
                }

                // Event listeners para filtros
                filterComprador.addEventListener('change', aplicarFiltros);
                filterTipo.addEventListener('change', aplicarFiltros);

                btnLimparFiltros.addEventListener('click', function() {
                    filterComprador.value = '';
                    filterTipo.value = '';
                    aplicarFiltros();
                });

                // Função para atualizar contador
                function atualizarContador() {
                    const {
                        checkboxes
                    } = getElements();
                    const selecionadas = Array.from(checkboxes).filter(cb => cb.checked).length;
                    contadorSelecionadas.textContent = selecionadas;

                    // Habilitar/desabilitar botão
                    btnUnificar.disabled = selecionadas < 2;

                    if (selecionadas < 2) {
                        btnUnificar.classList.add('opacity-50', 'cursor-not-allowed');
                    } else {
                        btnUnificar.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }

                // Função para verificar se os compradores são consistentes
                function verificarCompradorConsistente() {
                    const {
                        checkboxes
                    } = getElements();
                    const checkboxesSelecionados = Array.from(checkboxes).filter(cb => cb.checked);
                    const compradores = new Set();

                    checkboxesSelecionados.forEach(checkbox => {
                        const row = checkbox.closest('tr');
                        compradores.add(row.dataset.comprador);
                    });

                    if (compradores.size > 1) {
                        // Mostrar alerta
                        if (checkboxesSelecionados.length > 1) {
                            alert(
                                '⚠️ ATENÇÃO: Você selecionou cotações com compradores diferentes. Apenas cotações do mesmo comprador podem ser unificadas.'
                            );
                        }
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
                    atualizarContador();

                    // Verificar consistência do comprador
                    if (this.checked) {
                        setTimeout(() => verificarCompradorConsistente(), 100);
                    }
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
                    verificarCompradorConsistente();
                }

                // Botões de seleção
                btnSelecionarTodas.addEventListener('click', function() {
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
                    verificarCompradorConsistente();
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
                    const selecionadas = Array.from(checkboxes).filter(cb => cb.checked).length;

                    if (selecionadas < 2) {
                        e.preventDefault();
                        alert('Selecione pelo menos 2 cotações para unificar.');
                        return;
                    }

                    if (!verificarCompradorConsistente()) {
                        e.preventDefault();
                        alert('Todas as cotações selecionadas devem ter o mesmo comprador.');
                        return;
                    }

                    // Confirmação
                    const checkboxesSelecionados = Array.from(checkboxes).filter(cb => cb.checked);
                    const compradores = checkboxesSelecionados.map(cb => cb.closest('tr').dataset.comprador);
                    const nomeComprador = checkboxesSelecionados[0].closest('tr').dataset.compradorNome;

                    const confirmMsg =
                        `Confirma a unificação de ${selecionadas} cotações do comprador "${nomeComprador}"?\n\n` +
                        `As cotações originais serão finalizadas e uma nova cotação será criada.\n` +
                        `O sistema detectará automaticamente o tipo (produtos ou serviços).`;

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
                const tableBody = document.getElementById('cotacoes-table-body');
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
