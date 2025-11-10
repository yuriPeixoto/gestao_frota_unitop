<script>
    // ======================================================
    // INICIALIZAÇÃO GERAL DO DOCUMENTO
    // ======================================================
    document.addEventListener('DOMContentLoaded', function() {

        // Inicializar manipulação da tabela
        initTableLoading();

        // Inicializar eventos de armazenamento local
        initLocalStorageEvents();

        // Inicializar gerenciamento de seleções Alpine
        initSelectionManagement();
    });

    // ======================================================
    // GESTÃO DE LOADING DA TABELA DE RESULTADOS
    // ======================================================
    function initTableLoading() {
        const loadingElement = document.getElementById('table-loading');
        const resultsElement = document.getElementById('results-table');

        if (loadingElement && resultsElement) {

            // Esconder o loading e mostrar os resultados após carregamento da página
            setTimeout(function() {
                loadingElement.style.display = 'none';
                resultsElement.classList.remove('opacity-0');
                resultsElement.classList.add('opacity-100');

                // Importante: após exibir os resultados, atualize Alpine para refletir as seleções
                updateAlpineState();
            }, 300);

            // Lidar com eventos HTMX
            document.body.addEventListener('htmx:beforeRequest', function(event) {
                if (event.detail.target &&
                    (event.detail.target.id === 'results-table' ||
                        event.detail.target.closest('#results-table'))) {
                    console.log('HTMX request iniciada - mostrando loading');
                    loadingElement.style.display = 'flex';
                    resultsElement.classList.add('opacity-0');
                }
            });

            document.body.addEventListener('htmx:afterSwap', function(event) {
                if (event.detail.target &&
                    (event.detail.target.id === 'results-table' ||
                        event.detail.target.closest('#results-table'))) {
                    console.log('HTMX swap concluído - escondendo loading');
                    loadingElement.style.display = 'none';
                    resultsElement.classList.remove('opacity-0');
                    resultsElement.classList.add('opacity-100');

                    // Após swap HTMX, atualize Alpine e seleções
                    updateAlpineState();
                }
            });

            // Backup em caso de falha no HTMX
            document.body.addEventListener('htmx:responseError', function(event) {
                console.log('HTMX erro - escondendo loading');
                loadingElement.style.display = 'none';
                resultsElement.classList.remove('opacity-0');
                resultsElement.classList.add('opacity-100');
            });
        } else {
            console.log('Elementos de loading/resultado não encontrados');
        }
    }

    // ======================================================
    // FUNÇÕES DE EVENTOS DE ARMAZENAMENTO LOCAL
    // ======================================================
    function initLocalStorageEvents() {
        // Manter o comportamento original de localStorage
        const originalSetItem = localStorage.setItem;
        const originalRemoveItem = localStorage.removeItem;

        // Sobrescrever setItem para disparar evento personalizado
        localStorage.setItem = function(key, value) {
            const oldValue = localStorage.getItem(key);

            // Chamar o método original
            originalSetItem.call(localStorage, key, value);

            // Disparar eventos personalizados
            const storageEvent = new StorageEvent('storage-change', {
                key: key,
                oldValue: oldValue,
                newValue: value,
                storageArea: localStorage
            });

            window.dispatchEvent(storageEvent);
            window.dispatchEvent(new CustomEvent('storage-update', {
                detail: {
                    key,
                    oldValue,
                    newValue: value
                }
            }));
        };

        // Sobrescrever removeItem para disparar evento personalizado
        localStorage.removeItem = function(key) {
            const oldValue = localStorage.getItem(key);

            // Chamar o método original
            originalRemoveItem.call(localStorage, key);

            // Disparar eventos personalizados
            const storageEvent = new StorageEvent('storage-change', {
                key: key,
                oldValue: oldValue,
                newValue: null,
                storageArea: localStorage
            });

            window.dispatchEvent(storageEvent);
            window.dispatchEvent(new CustomEvent('storage-update', {
                detail: {
                    key,
                    oldValue,
                    newValue: null
                }
            }));

            // Log para debug
            console.log(`localStorage.removeItem: ${key}`);
        };

        // Escutar eventos nativos de armazenamento (em caso de outras abas)
        window.addEventListener('storage', function(e) {
            console.log('storage event (cross-tab)', e);
            if (e.key === 'selectedRows') {
                updateAlpineState();
            }
        });

        // Escutar evento personalizado
        window.addEventListener('storage-update', function(e) {
            updateAlpineState();
        });
    }

    // ======================================================
    // GERENCIAMENTO DE SELEÇÕES NO ALPINE
    // ======================================================
    function initSelectionManagement() {
        // Garantir que o global Alpine existe
        if (typeof window.Alpine === 'undefined') {
            console.warn('Alpine.js não está disponível. Algumas funcionalidades podem não funcionar corretamente.');
            return;
        }

        // Inicializar seleções na carga inicial
        updateAlpineState();
    }

    // Função para atualizar o estado Alpine e as seleções visuais
    function updateAlpineState() {
        // Garantir que todas as linhas selecionáveis sejam corretamente marcadas
        updateRowsSelectedStatus();

        // Atualizar o Alpine.js para refletir mudanças
        if (window.Alpine && window.Alpine.initialised) {
            console.log('Atualizando componentes Alpine');

            // Avaliar todos os elementos Alpine que têm data binding com selectedRows
            document.querySelectorAll('[x-data]').forEach(element => {
                // Forçar Alpine a reavaliar este componente
                if (window.Alpine.discoverComponents) {
                    window.Alpine.discoverComponents(element);
                    window.Alpine.initializeComponent(element);
                } else if (window.Alpine.initTree) {
                    // Para Alpine 3.x
                    window.Alpine.initTree(element);
                }
            });
        }
    }

    // Atualiza visualmente as linhas selecionadas
    function updateRowsSelectedStatus() {
        const selectedIds = JSON.parse(localStorage.getItem('selectedRows') || '[]');

        // Verificar cada linha da tabela e atualizar seu estado de seleção
        document.querySelectorAll('tr[data-id]').forEach(row => {
            const id = row.getAttribute('data-id');
            const checkbox = row.querySelector('input[type="checkbox"]');

            if (selectedIds.includes(id)) {
                row.classList.add('bg-gray-100');
                if (checkbox) checkbox.checked = true;
            } else {
                row.classList.remove('bg-gray-100');
                if (checkbox) checkbox.checked = false;
            }
        });

        // Atualizar também o estado do checkbox "Selecionar Todos"
        const checkboxSelecionarTodos = document.querySelector('thead input[type="checkbox"]');

        if (checkboxSelecionarTodos) {
            // Obter IDs das linhas na página atual
            const idsNaPaginaAtual = Array.from(document.querySelectorAll('tr[data-id]'))
                .map(row => row.getAttribute('data-id'))
                .filter(id => id && /^\d+$/.test(id));

            // Verificar se todos estão selecionados
            const todosSelecionados = idsNaPaginaAtual.length > 0 &&
                idsNaPaginaAtual.every(id => selectedIds.includes(id));

            // Verificar se há seleção parcial
            const selecaoParcial = !todosSelecionados &&
                idsNaPaginaAtual.some(id => selectedIds.includes(id));

            // Atualizar estado do checkbox
            checkboxSelecionarTodos.checked = todosSelecionados;
            checkboxSelecionarTodos.indeterminate = selecaoParcial;
        }
    }

    // ======================================================
    // FUNÇÕES UTILITÁRIAS
    // ======================================================
    function confirmarFaturamento() {
        showAlert({
            title: 'Atenção!',
            message: 'Confirma o faturamento dos abastecimentos selecionados?',
            type: 'warning',
            buttonText: 'Sim, faturar',
            cancelButtonText: 'Cancelar',
            onConfirm: () => document.getElementById('abastecimentoFaturamentoForm').submit()
        });
    }

    function limparSelecoes() {
        localStorage.removeItem('selectedRows');
        updateAlpineState();
    }

    function faturarSelecionados() {
        const selectedRows = JSON.parse(localStorage.getItem('selectedRows') || '[]');

        if (selectedRows.length === 0) {
            showAlert({
                title: 'Atenção!',
                message: 'Nenhum item selecionado para faturamento.',
                type: 'warning'
            });
            return;
        }

        const ids = selectedRows.join(',');
        window.location.href = `/admin/abastecimentosfaturamento/create?ids=${ids}`;
    }

    // Função de debug para exibir o estado atual de seleção
    function debugSelectionState() {
        const selectedIds = JSON.parse(localStorage.getItem('selectedRows') || '[]');
        console.log('Estado atual de seleção:', selectedIds);

        const activeIndicator = document.querySelector('.bg-indigo-50.border.border-indigo-200');
        console.log('Indicador de seleção ativo?', activeIndicator && window.getComputedStyle(activeIndicator)
            .display !== 'none');

        return {
            selectedIds,
            count: selectedIds.length,
            indicatorActive: activeIndicator && window.getComputedStyle(activeIndicator).display !== 'none'
        };
    }
</script>
