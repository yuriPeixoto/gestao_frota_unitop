<script>
    // ======================================================
    // FUNÇÃO DE ABAS - DEFINIDA IMEDIATAMENTE PARA TESTES
    // ======================================================
    function openTabSimple(evt, tabName) {
        // Esconder todas as abas
        const allTabs = document.querySelectorAll('.tabcontent');
        allTabs.forEach(tab => tab.classList.add('hidden'));

        // Mostrar aba selecionada
        const targetTab = document.getElementById(tabName);
        if (targetTab) {
            targetTab.classList.remove('hidden');
        }

        // Atualizar botões
        const allButtons = document.querySelectorAll('.tablink');
        allButtons.forEach(btn => {
            btn.classList.remove('bg-blue-500', 'text-white');
            btn.classList.add('bg-gray-200', 'text-gray-700');
        });

        // Ativar botão atual
        if (evt && evt.currentTarget) {
            evt.currentTarget.classList.remove('bg-gray-200', 'text-gray-700');
            evt.currentTarget.classList.add('bg-blue-500', 'text-white');
        }

        // *** PERSISTÊNCIA DE ABA ATIVA ***
        // Salvar a aba ativa no localStorage para manter após refresh da página
        try {
            localStorage.setItem('cotacoes_aba_ativa', tabName);
        } catch (error) {
            console.warn('Não foi possível salvar a aba ativa no localStorage:', error);
        }

        // Carregar itens automaticamente quando abrir a aba de itens
        if (tabName === 'Itens') {
            setTimeout(() => {
                buscarItemSolicitacao();
            }, 100);
        }
    }

    // Expor globalmente para teste
    window.openTab = openTabSimple;
    window.openTabSimple = openTabSimple;

    // Versão de fallback ainda mais simples
    window.openTabFallback = function(evt, tabName) {
        console.warn('Usando fallback openTabFallback para:', tabName);

        // Versão mais direta sem dependências
        try {
            document.querySelectorAll('.tabcontent').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.tablink').forEach(el => {
                el.classList.remove('bg-blue-500', 'text-white');
                el.classList.add('bg-gray-200', 'text-gray-700');
            });

            const target = document.getElementById(tabName);
            if (target) {
                target.style.display = 'block';
                target.classList.remove('hidden');
            }

            if (evt && evt.target) {
                evt.target.classList.remove('bg-gray-200', 'text-gray-700');
                evt.target.classList.add('bg-blue-500', 'text-white');
            }

            // *** PERSISTÊNCIA DE ABA ATIVA ***
            // Salvar a aba ativa no localStorage para manter após refresh da página
            try {
                localStorage.setItem('cotacoes_aba_ativa', tabName);
            } catch (error) {
                console.warn('Não foi possível salvar a aba ativa no localStorage:', error);
            }
        } catch (error) {
            console.error('Erro no fallback:', error);
        }
    };

    // ======================================================
    // INICIALIZAÇÃO GERAL DO DOCUMENTO E DADOS
    // ======================================================
    window.itemSolicitacaoCompra = window.itemSolicitacaoCompra || @json($itemSolicitacaoCompra ?? []);
    window.cotacoesList = window.cotacoesList || @json($cotacoesList ?? []);
    window.descricaoItem = window.descricaoItem || @json($descricaoItem ?? []);
    window.cotacoesItens = window.cotacoesItens || @json($cotacoesItens ?? []);

    // ======================================================
    // CACHE DE ELEMENTOS DOM PARA PERFORMANCE
    // ======================================================
    const DOMCache = {
        elements: new Map(),

        get(selector) {
            if (!this.elements.has(selector)) {
                this.elements.set(selector, document.querySelector(selector));
            }
            return this.elements.get(selector);
        },

        getAll(selector) {
            const cacheKey = `all_${selector}`;
            if (!this.elements.has(cacheKey)) {
                this.elements.set(cacheKey, document.querySelectorAll(selector));
            }
            return this.elements.get(cacheKey);
        },

        clear() {
            this.elements.clear();
        },

        refresh(selector) {
            this.elements.delete(selector);
            this.elements.delete(`all_${selector}`);
            return this.get(selector);
        }
    };

    // ======================================================
    // UTILITÁRIOS OTIMIZADOS
    // ======================================================
    const Utils = {
        // Formatador monetário com cache
        formatBR: (function() {
            const formatter = new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            return (valor) => {
                const num = typeof valor === 'number' ? valor : parseFloat(String(valor).replace(/,/g,
                    '.')) || 0;
                return formatter.format(num);
            };
        })(),

        // Formatador de data com cache
        formatDate: (function() {
            const cache = new Map();
            return (dateStr) => {
                if (cache.has(dateStr)) return cache.get(dateStr);

                const date = new Date(dateStr);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                const result = `${day}/${month}/${year}`;

                cache.set(dateStr, result);
                return result;
            };
        })(),

        // Obter valor numérico otimizado
        getNumericValue(valorFormatado) {
            if (!valorFormatado) return 0;
            return parseFloat(valorFormatado.replace(/\./g, '').replace(',', '.')) || 0;
        },

        // Debounce otimizado
        debounce(func, wait = 150) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Notificações centralizadas
        showNotification(title, message, type = 'info') {
            if (typeof window.showNotification === 'function') {
                window.showNotification(title, message, type);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title,
                    text: message,
                    icon: type,
                    confirmButtonText: 'OK'
                });
            } else {
                alert(`${title}: ${message}`);
            }
        },

        // CSRF Token
        getCSRFToken() {
            return DOMCache.get('meta[name="csrf-token"]')?.getAttribute('content') || '';
        }
    };

    // ======================================================
    // FUNÇÕES OTIMIZADAS DE INTERFACE (DEFINIDAS GLOBALMENTE)
    // ======================================================
    function initTabs() {
        setTimeout(() => {
            // *** RESTAURAÇÃO DE ABA ATIVA APÓS REFRESH ***
            // Verificar se existe uma aba salva no localStorage
            let abaAtiva = null;
            try {
                abaAtiva = localStorage.getItem('cotacoes_aba_ativa');
            } catch (error) {
                console.warn('Não foi possível acessar localStorage:', error);
            }

            // Se existe uma aba salva e o elemento existe, ativar essa aba
            if (abaAtiva && document.getElementById(abaAtiva)) {
                // Encontrar o botão correspondente à aba salva
                const buttons = document.querySelectorAll('.tablink');
                let targetButton = null;

                buttons.forEach(button => {
                    const onclick = button.getAttribute('onclick');
                    if (onclick && onclick.includes(abaAtiva)) {
                        targetButton = button;
                    }
                });

                if (targetButton) {
                    // Simular clique no botão da aba salva
                    const event = {
                        currentTarget: targetButton
                    };
                    openTabSimple(event, abaAtiva);

                    // Se a aba for a de itens, carregar automaticamente
                    const buttonText = targetButton.textContent?.trim();
                    if (buttonText === 'Itens') {
                        setTimeout(() => {
                            buscarItemSolicitacao();
                        }, 200);
                    }
                    return;
                }
            }

            // Fallback: ativar a primeira aba se não houver aba salva ou se ela não existir
            const firstTab = document.querySelector(".tablink");
            if (firstTab) {
                firstTab.click();

                // Se a primeira aba for a de itens, carregar automaticamente
                const firstTabText = firstTab.textContent?.trim();
                if (firstTabText === 'Itens') {
                    setTimeout(() => {
                        buscarItemSolicitacao();
                    }, 200);
                }
            } else {
                console.error('Nenhum botão .tablink encontrado'); // Debug
            }
        }, 100);
    }

    // Expor imediatamente no window para garantir disponibilidade
    window.initTabs = initTabs;

    document.addEventListener('DOMContentLoaded', function() {

        // ======================================================
        // INICIALIZAÇÃO OTIMIZADA
        // ======================================================

        // Adicionar estilos CSS de forma otimizada
        if (!document.getElementById('cotacoes-styles')) {
            const style = document.createElement('style');
            style.id = 'cotacoes-styles';
            style.textContent = `
                /* Estilos gerais para inputs com texto mais escuro */
                input, select, textarea {
                    color: #1f2937 !important; /* text-gray-800 */
                    font-weight: 500 !important;
                }
                input::placeholder {
                    color: #6b7280 !important; /* text-gray-500 */
                }

                /* Estilos para tabelas com texto mais escuro */
                table th, table td {
                    color: #111827 !important; /* text-gray-900 */
                    font-weight: 500 !important;
                }
                table thead th {
                    color: #374151 !important; /* text-gray-700 */
                    font-weight: 600 !important;
                }

                /* Estilos específicos para campos readonly/info */
                .cotacao-info, .solicitacao-info, .comprador-info,
                .fornecedor-info, .contato-info, .email-info,
                .tefone-info, .produto-info, .descricao-info,
                .unidade-info, .quantidade-info, .valor-info,
                .valor-desconto-info {
                    color: #1f2937 !important; /* text-gray-800 */
                    font-weight: 600 !important;
                }

                /* Estilos para campos monetários */
                input[name="valorunitario"], input[name="valor_desconto"] {
                    text-align: right;
                    font-family: 'Courier New', monospace;
                    font-weight: bold;
                    color: #1f2937 !important; /* text-gray-800 */
                }
                input[name="valorunitario"]:focus, input[name="valor_desconto"]:focus {
                    background-color: #f0f9ff;
                    border-color: #0ea5e9;
                    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
                }
                .valor-info, .valor-desconto-info { transition: background-color 0.3s ease; }
                #tabelaOrcamentoItensBody tr {
                    transition: background-color 0.3s ease, transform 0.3s ease;
                }
                #tabelaOrcamentoItensBody tr.bg-green-100 {
                    background-color: #dcfce7 !important;
                    border: 2px solid #22c55e;
                }
                .item-saved { animation: pulseSuccess 0.6s ease-in-out; }
                @keyframes pulseSuccess {
                    0% { transform: scale(1); background-color: transparent; }
                    50% { transform: scale(1.02); background-color: #dcfce7; }
                    100% { transform: scale(1); background-color: transparent; }
                }

                /* Animação de spin para loading */
                .animate-spin {
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }

        // Inicializar componentes principais
        initTableLoading();
        initTabs();
        populateAllTables();
        initCalculations();

        // Carregar itens automaticamente se tivermos uma solicitação de compra
        setTimeout(() => {
            const solicitacaoInput = DOMCache.get('input[name="solicitacao_compra_consulta"]');
            if (solicitacaoInput && solicitacaoInput.value) {
                buscarItemSolicitacao();
            }
        }, 500);
    });

    function initTableLoading() {
        const loadingElement = DOMCache.get('#table-loading');
        const resultsElement = DOMCache.get('#results-table');

        if (!loadingElement || !resultsElement) return;

        // Preparar elementos de forma otimizada
        requestAnimationFrame(() => {
            if (resultsElement && resultsElement.classList) {
                resultsElement.classList.add('opacity-0');
            }
            setTimeout(() => {
                if (loadingElement) {
                    loadingElement.style.display = 'none';
                }
                if (resultsElement && resultsElement.classList) {
                    resultsElement.classList.remove('opacity-0');
                    resultsElement.classList.add('opacity-100');
                }
            }, 300);
        });

        // Event delegation para eventos HTMX
        document.body.addEventListener('htmx:beforeRequest', handleHTMXBefore);
        document.body.addEventListener('htmx:afterSwap', handleHTMXAfter);
        document.body.addEventListener('htmx:responseError', handleHTMXError);

        function handleHTMXBefore(event) {
            if (isTableTarget(event.detail.target)) {
                requestAnimationFrame(() => {
                    if (loadingElement) {
                        loadingElement.style.display = 'flex';
                    }
                    if (resultsElement && resultsElement.classList) {
                        resultsElement.classList.add('opacity-0');
                    }
                });
            }
        }

        function handleHTMXAfter(event) {
            if (isTableTarget(event.detail.target)) {
                if (loadingElement) {
                    loadingElement.style.display = 'none';
                }
                if (resultsElement && resultsElement.classList) {
                    resultsElement.classList.remove('opacity-0');
                    resultsElement.classList.add('opacity-100');
                }
            }
        }

        function handleHTMXError(event) {
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            if (resultsElement && resultsElement.classList) {
                resultsElement.classList.remove('opacity-0');
                resultsElement.classList.add('opacity-100');
            }
        }

        function isTableTarget(target) {
            return target && (target.id === 'results-table' || target.closest('#results-table'));
        }
    }

    // ======================================================
    // SISTEMA DE FORMATAÇÃO MONETÁRIA OTIMIZADO
    // ======================================================
    const MoneyFormatter = {
        // Formatação em tempo real otimizada
        formatRealTime(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor === '') {
                input.value = '';
                return;
            }
            let numero = parseInt(valor) / 100;
            input.value = Utils.formatBR(numero);
        },

        // Handler unificado para campos monetários
        createMoneyHandler(calculateCallback) {
            const allowedKeys = [8, 9, 13, 46, 35, 36, 37, 38, 39, 40];

            return {
                input: (e) => {
                    this.formatRealTime(e.target);
                    if (calculateCallback) calculateCallback();
                },

                keydown: (e) => {
                    const isNumber = (e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <=
                        105);
                    if (!allowedKeys.includes(e.keyCode) && !isNumber && !e.ctrlKey) {
                        e.preventDefault();
                    }
                },

                focus: (e) => {
                    setTimeout(() => e.target.select(), 50);
                }
            };
        },

        // Inicialização otimizada dos campos monetários
        init(calculateCallback) {
            const valorUnitario = DOMCache.get('input[name="valorunitario"]');
            const valorDesconto = DOMCache.get('input[name="valor_desconto"]');

            [valorUnitario, valorDesconto].forEach(input => {
                if (!input) return;

                const handlers = this.createMoneyHandler(calculateCallback);

                // Remover listeners existentes (se houver)
                ['input', 'keydown', 'focus'].forEach(event => {
                    input.removeEventListener(event, input._handlers?.[event]);
                });

                // Adicionar novos handlers
                input._handlers = handlers;
                input.addEventListener('input', handlers.input);
                input.addEventListener('keydown', handlers.keydown);
                input.addEventListener('focus', handlers.focus);
            });
        }
    };

    // ======================================================
    // SISTEMA DE CÁLCULOS AUTOMÁTICOS OTIMIZADO
    // ======================================================
    const Calculator = {
        // Elementos em cache para performance
        elements: null,

        // Inicializar cache de elementos
        initElements() {
            this.elements = {
                quantidade: DOMCache.get('input[name="quantidade_fornecedor"]'),
                valorUnitario: DOMCache.get('input[name="valorunitario"]'),
                valorDesconto: DOMCache.get('input[name="valor_desconto"]'),
                valorInfo: DOMCache.get('.valor-info'),
                valorDescontoInfo: DOMCache.get('.valor-desconto-info')
            };
        },

        // Cálculo otimizado com batch de atualizações DOM
        calculate() {
            if (!this.elements) this.initElements();

            const {
                quantidade,
                valorUnitario,
                valorDesconto,
                valorInfo,
                valorDescontoInfo
            } = this.elements;

            if (!quantidade || !valorUnitario) return;

            // Realizar cálculos em lote
            const qtd = parseFloat(quantidade.value) || 0;
            const unitario = Utils.getNumericValue(valorUnitario.value);
            const desconto = Utils.getNumericValue(valorDesconto?.value);

            const valorTotal = qtd * unitario;
            const valorFinal = Math.abs(desconto - valorTotal);

            // Preparar valores formatados
            const formatadoTotal = Utils.formatBR(valorTotal);
            const formatadoFinal = Utils.formatBR(valorFinal);

            // Batch de atualizações DOM
            requestAnimationFrame(() => {
                if (valorInfo) {
                    valorInfo.textContent = formatadoTotal;
                    valorInfo.classList.add('bg-yellow-100');
                }

                if (valorDescontoInfo) {
                    valorDescontoInfo.textContent = formatadoFinal;
                    valorDescontoInfo.classList.add('bg-yellow-100');
                }

                // Remover destaque após delay
                setTimeout(() => {
                    valorInfo?.classList.remove('bg-yellow-100');
                    valorDescontoInfo?.classList.remove('bg-yellow-100');
                }, 500);
            });
        },

        // Inicialização otimizada
        init() {
            this.initElements();

            // Versão otimizada com debounce
            const debouncedCalculate = Utils.debounce(this.calculate.bind(this), 150);

            // Inicializar formatação monetária
            MoneyFormatter.init(debouncedCalculate);

            // Listener para quantidade
            if (this.elements.quantidade) {
                this.elements.quantidade.removeEventListener('input', this.calculate);
                this.elements.quantidade.addEventListener('input', debouncedCalculate);
                this.elements.quantidade.addEventListener('change', this.calculate.bind(this));
            }

            // Cálculo inicial
            setTimeout(() => this.calculate(), 0);
        }
    };

    // Função para inicializar tudo
    function initCalculations() {
        Calculator.init();
    }

    // ======================================================
    // SISTEMA DE TABELAS OTIMIZADO
    // ======================================================
    const TableManager = {
        // Função genérica para popular tabelas com performance otimizada
        populateTable(tableId, data, rowBuilder, emptyMessage = 'Nenhum item encontrado') {
            const tbody = DOMCache.get(`#${tableId}`);
            if (!tbody) return;

            // Usar DocumentFragment para melhor performance
            const fragment = document.createDocumentFragment();

            if (data && data.length > 0) {
                data.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = rowBuilder(item, index);
                    fragment.appendChild(tr);
                });
            } else {
                const tr = document.createElement('tr');
                tr.innerHTML =
                    `<td colspan="100%" class="px-6 py-4 text-center text-gray-500">${emptyMessage}</td>`;
                fragment.appendChild(tr);
            }

            // Batch DOM update
            requestAnimationFrame(() => {
                tbody.innerHTML = '';
                tbody.appendChild(fragment);
            });
        },

        // Tabela de pré-cadastro otimizada
        populatePreCadastro() {
            this.populateTable('tabelaPreCadastroBody', window.itemSolicitacaoCompra, (item) => `
                <td class="px-6 py-4">Cód. ${item.id_produto} - ${window.descricaoItem[item.id_produto] ?? item.descricao}</td>
                <td class="px-6 py-4">${Utils.formatDate(item.data_inclusao)}</td>
                <td class="px-6 py-4">
                    ${item.produto && item.produto.pre_cadastro ?
                        `<a href="/admin/cadastroprodutosestoque/${item.id_produto}/editar"
                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar
                        </a>` :
                        '<span class="text-gray-400 text-sm">-</span>'
                    }
                </td>
            `);
        },

        // Tabela de histórico otimizada
        populateHistorico() {
            this.populateTable('tabelaHistoricoBody', window.itemSolicitacaoCompra, (item) => `
                <td class="px-6 py-4">${item.id_solicitacao_compra}</td>
                <td class="px-6 py-4">${item.id_produto}</td>
                <td class="px-6 py-4">${window.descricaoItem[item.id_produto]}</td>
                <td class="px-6 py-4">${item.quantidade_solicitada}</td>
            `);
        },

        // Tabela de item solicitação otimizada
        populateItemSolicitacao() {
            this.populateTable('tabelaItemSolicitacaoBody', window.buscaItemSolicitacao, (item) => `
                <td class="px-6 py-4">${item.id_filial}</td>
                <td class="px-6 py-4">${item.departamento}</td>
                <td class="px-6 py-4">${item.prioridade}</td>
                <td class="px-6 py-4">${item.situacao}</td>
                <td class="px-6 py-4">${item.observacao}</td>
                <td class="px-6 py-4">${window.descricaoItem[item.produto] ?? item.produto}</td>
                <td class="px-6 py-4">${item.quantidade}</td>
                <td class="px-6 py-4">${item.imagem}</td>
                <td class="px-6 py-4">${item.observacao_item}</td>
            `);
        },

        // Tabela de cotação otimizada
        populateCotacao() {
            this.populateTable('tabelaCotacaoBody', window.cotacoesList, (item) => `
                <td class="px-6 py-4">
                    <a x-on:click="$store.utils.imprimirCotacoes(${item.id_solicitacoes_compras})"
                        title="Imprimir Cotação"
                        class="bg-blue-500 hover:bg-blue-600 text-white p-1.5 rounded text-xs shadow hover:shadow-md transition-all duration-200 inline-flex items-center justify-center cursor-pointer">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        <span>Imprimir</span>
                    </a>
                </td>
                <td class="px-6 py-4">${item.id_cotacoes}</td>
                <td class="px-6 py-4">${item.nome_fornecedor}</td>
                <td class="px-6 py-4">${item.email ?? ''}</td>
                <td class="px-6 py-4">${item.telefone_fornecedor ?? ''}</td>
                <td class="px-6 py-4">${item.nome_contato ?? ''}</td>
            `, 'Nenhuma cotação encontrada');
        },

        // Tabela de orçamento otimizada
        populateOrcamento() {
            this.populateTable('tabelaOrcamentoBody', window.cotacoesList, (item) => `
                <td class="px-6 py-4">
                    <a onclick="testarCotacao(${item.id_cotacoes})" title="Editar Cotação"
                        class="bg-blue-500 hover:bg-blue-600 text-white p-1.5 rounded text-xs shadow hover:shadow-md transition-all duration-200 inline-flex items-center justify-center cursor-pointer">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span>Editar</span>
                    </a>
                </td>
                <td class="px-6 py-4">${item.id_solicitacoes_compras}</td>
                <td class="px-6 py-4">${item.nome_fornecedor}</td>
                <td class="px-6 py-4">${item.nome_contato ?? ''}</td>
                <td class="px-6 py-4">${item.valor_total ? Utils.formatBR(item.valor_total) : '0,00'}</td>
                <td class="px-6 py-4">${item.valor_total_desconto ? Utils.formatBR(item.valor_total_desconto) : '0,00'}</td>
                <td class="px-6 py-4">${item.data_entrega ? Utils.formatDate(item.data_entrega) : '-'}</td>
            `);
        },

        // Tabela de itens do orçamento otimizada
        populateOrcamentoItens(cotacaoId) {
            const itensCotacao = window.cotacoesItens.filter(item => item.id_cotacao == cotacaoId);
            window.itensCotacaoAtual = itensCotacao;

            this.populateTable('tabelaOrcamentoItensBody', itensCotacao, (item, index) => {
                const valorUnitarioStr = Utils.formatBR(parseFloat(item.valorunitario) || 0);
                const valorItemStr = Utils.formatBR(parseFloat(item.valor_item) || 0);
                const valorDescontoStr = Utils.formatBR(parseFloat(item.valor_desconto) || 0);

                return `
                    <td class="px-2 py-2">
                        <a onclick="editarItemCotacao(window.itensCotacaoAtual[${index}])" title="Editar Item"
                            class="bg-blue-500 hover:bg-blue-600 text-white p-1.5 rounded text-xs shadow hover:shadow-md transition-all duration-200 inline-flex items-center justify-center cursor-pointer">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                    </td>
                    <td class="px-2 py-2 text-xs">${item.id_produto ?? '-'}</td>
                    <td class="px-2 py-2 text-xs max-w-48 break-words">${item.descricao_produto ?? '-'}</td>
                    <td class="px-2 py-2 text-xs">${item.descricao_unidade ?? '-'}</td>
                    <td class="px-2 py-2 text-xs">${item.quantidade_solicitada ?? '-'}</td>
                    <td class="px-2 py-2 text-xs">${item.condicao_pag ?? '-'}</td>
                    <td class="px-2 py-2 text-xs">${item.quantidade_fornecedor ?? '0'}</td>
                    <td class="px-2 py-2 text-xs">${valorUnitarioStr}</td>
                    <td class="px-2 py-2 text-xs">${valorItemStr}</td>
                    <td class="px-2 py-2 text-xs">${valorDescontoStr}</td>
                `;
            }, 'Nenhum item encontrado para esta cotação');
        }
    };

    // Função para popular todas as tabelas
    function populateAllTables() {
        requestAnimationFrame(() => {
            TableManager.populatePreCadastro();
            TableManager.populateHistorico();
            TableManager.populateCotacao();
            TableManager.populateOrcamento();
        });
    }

    // ======================================================
    // SISTEMA UNIFICADO DE REQUISIÇÕES AJAX
    // ======================================================
    const AjaxManager = {
        // Configuração padrão
        defaultOptions: {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        },

        // Função principal para requisições
        async request(url, options = {}) {
            const config = {
                ...this.defaultOptions,
                ...options
            };

            // Para FormData, não incluir Content-Type nos headers padrão
            if (options.body instanceof FormData) {
                config.headers = {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': Utils.getCSRFToken(),
                    ...options.headers
                };
            } else {
                config.headers = {
                    ...this.defaultOptions.headers,
                    'X-CSRF-TOKEN': Utils.getCSRFToken(),
                    ...options.headers
                };
            }

            try {
                const response = await fetch(url, config);

                if (!response.ok) {
                    let errorMessage = `Erro HTTP ${response.status}: ${response.statusText}`;
                    try {
                        const errorData = await response.json();
                        errorMessage = errorData.message || errorData.error || errorMessage;
                    } catch (e) {
                        // Se não conseguir fazer parse do JSON, usa mensagem padrão
                    }
                    throw new Error(errorMessage);
                }

                return await response.json();
            } catch (error) {
                console.error('💥 Erro na requisição:', error);
                Utils.showNotification('Erro', `Erro ao acessar ${url}: ${error.message}`, 'error');
                throw error;
            }
        },

        // Requisição GET
        get(url, params = {}) {
            const queryString = Object.keys(params).length ?
                '?' + new URLSearchParams(params).toString() : '';
            return this.request(url + queryString);
        },

        // Requisição POST
        post(url, data = {}) {
            return this.request(url, {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },

        // Requisição POST com FormData
        postForm(url, formData) {
            const headers = {
                'X-CSRF-TOKEN': Utils.getCSRFToken(),
                'X-Requested-With': 'XMLHttpRequest'
                // Não definir Content-Type para FormData (deixa o browser definir)
            };

            // Para FormData, removemos o Content-Type dos headers padrão
            return this.request(url, {
                method: 'POST',
                body: formData,
                headers
            });
        }
    };

    // ======================================================
    // FUNÇÕES DE NEGÓCIO OTIMIZADAS
    // ======================================================
    async function buscarItemSolicitacao() {
        const inputEl = DOMCache.get('input[name="solicitacao_compra_consulta"]');
        if (!inputEl) return;

        const solicitacaoCompraConsulta = inputEl.value.trim();
        if (!solicitacaoCompraConsulta) {
            Utils.showNotification('Aviso', 'Informe o id da solicitação', 'warning');
            return;
        }

        try {
            const data = await AjaxManager.get('{{ route('admin.compras.cotacoes.buscaritem') }}', {
                solicitacaoCompraConsulta
            });

            window.buscaItemSolicitacao = Array.isArray(data) ? data : (data ? [data] : []);
            TableManager.populateItemSolicitacao();
        } catch (error) {
            // Erro já tratado no AjaxManager
        }
    }

    async function incluirCotacao() {
        // *** PREVENÇÃO DE MÚLTIPLOS CLIQUES ***
        // Desabilitar o botão para evitar múltiplos cliques
        const botaoGerar = document.querySelector('a[onclick="incluirCotacao()"]');
        if (botaoGerar) {
            botaoGerar.style.pointerEvents = 'none';
            botaoGerar.style.opacity = '0.6';
            botaoGerar.innerHTML = `
                <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processando...
            `;
        }

        // Função para restaurar o botão
        const restaurarBotao = () => {
            if (botaoGerar) {
                botaoGerar.style.pointerEvents = 'auto';
                botaoGerar.style.opacity = '1';
                botaoGerar.innerHTML = `
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Gerar Cotação
                `;
            }
        };

        // Buscar elementos do formulário com debug
        const solicitacao = DOMCache.get('[name="id_solicitacoes_compras"]');
        const fornecedor = DOMCache.get('[name="id_fornecedor"]');
        const nomeContato = DOMCache.get('input[name="nome_contato"]');
        const email = DOMCache.get('input[name="email"]');

        // Validações dos campos obrigatórios
        if (!solicitacao || !solicitacao.value) {
            restaurarBotao();
            Utils.showNotification('Erro', 'Selecione uma solicitação de compra', 'error');
            console.error('❌ Solicitação não selecionada'); // Debug
            return;
        }

        if (!fornecedor || !fornecedor.value) {
            restaurarBotao();
            Utils.showNotification('Erro', 'Selecione um fornecedor', 'error');
            console.error('❌ Fornecedor não selecionado'); // Debug
            return;
        }

        const dadosFormulario = {
            solicitacao: solicitacao.value,
            fornecedor: fornecedor.value,
            nomeContato: nomeContato?.value || '',
            email: email?.value || ''
        };

        const formData = new FormData();
        formData.append('solicitacao', dadosFormulario.solicitacao);
        formData.append('fornecedor', dadosFormulario.fornecedor);
        formData.append('nome_contato', dadosFormulario.nomeContato);
        formData.append('email', dadosFormulario.email);

        try {
            // *** MOSTRAR LOADING PERSONALIZADO ***
            // Mostrar loading no estilo SweetAlert2
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Gerando cotação...',
                    text: 'Por favor, aguarde enquanto a cotação está sendo processada.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => Swal.showLoading()
                });
            } else {
                Utils.showNotification('Info', 'Incluindo cotação...', 'info');
            }

            // Tentar primeiro com fetch direto para debug
            const testUrl = '{{ route('admin.compras.cotacoes.incluircotacao') }}';

            const response = await fetch(testUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': Utils.getCSRFToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // *** FECHAR LOADING E MOSTRAR RESULTADO ***
            // ESTRATÉGIA: Para sucesso, manter o loading ativo até o reload terminar
            // Para erro, fechar o loading e mostrar mensagem de erro

            // Verificar diferentes formatos de resposta
            if (data.success === true) {
                // Para sucesso, não fechar o loading - deixar até o reload terminar
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else if (data.notification) {
                if (data.notification.type === 'success') {
                    // Para sucesso, não fechar o loading - deixar até o reload terminar
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    // Só mostrar erro se não for sucesso
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: data.notification.title,
                            text: data.notification.message,
                            showConfirmButton: true
                        }).then(() => {
                            restaurarBotao();
                        });
                    } else {
                        Utils.showNotification(
                            data.notification.title,
                            data.notification.message,
                            data.notification.type
                        );
                        restaurarBotao();
                    }
                }
            } else if (data.title && data.message) {
                if (data.success) {
                    // Para sucesso, não fechar o loading - deixar até o reload terminar
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    // Só mostrar erro se não for sucesso
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: data.title,
                            text: data.message,
                            showConfirmButton: true
                        }).then(() => {
                            restaurarBotao();
                        });
                    } else {
                        Utils.showNotification(data.title, data.message, 'error');
                        restaurarBotao();
                    }
                }
            } else {
                // Resposta inesperada - assumir sucesso e fazer reload
                console.warn('⚠️ Formato de resposta inesperado:', data);
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }

        } catch (error) {
            console.error('❌ Erro ao incluir cotação:', error); // Debug

            // *** FECHAR LOADING E MOSTRAR ERRO ***
            if (typeof Swal !== 'undefined') {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: `Erro ao incluir cotação: ${error.message}`,
                    showConfirmButton: true
                });
            } else {
                Utils.showNotification('Erro', `Erro ao incluir cotação: ${error.message}`, 'error');
            }

            // Restaurar o botão em caso de erro
            restaurarBotao();
        }
    }

    async function buscarDadosCotacao(idCotacao) {
        if (!idCotacao) {
            throw new Error('ID da cotação é obrigatório');
        }

        return await AjaxManager.get(`/admin/compras/cotacoes/single/${idCotacao}`);
    }


    function mudarStatusSolicitante(idSolicitacao, situacaoCompra, grupoDespesa = null) {

        // Validar se existem cotações vinculadas COM VALOR
        if (!window.cotacoesList || window.cotacoesList.length === 0) {
            if (typeof showNotification === 'function') {
                showNotification('Atenção',
                    'Não é possível enviar para o solicitante pois não há cotações vinculadas a esta solicitação.',
                    'warning');
            } else {
                alert('Não é possível enviar para o solicitante pois não há cotações vinculadas a esta solicitação.');
            }
            return false;
        }

        // Verificar se pelo menos uma cotação tem valor
        const temCotacaoComValor = window.cotacoesList.some(cotacao => {
            return (cotacao.valor_total && parseFloat(cotacao.valor_total) > 0) ||
                (cotacao.valor_total_desconto && parseFloat(cotacao.valor_total_desconto) > 0);
        });

        if (!temCotacaoComValor) {
            if (typeof showNotification === 'function') {
                showNotification('Atenção',
                    'Não é possível enviar para o solicitante pois não há cotações com valor preenchido.',
                    'warning');
            } else {
                alert('Não é possível enviar para o solicitante pois não há cotações com valor preenchido.');
            }
            return false;
        }

        const confirmacao = confirm('Atenção: você deseja enviar essa solicitação para o SOLICITANTE?');

        if (!confirmacao) {
            return false; // Retorna false explicitamente para indicar cancelamento
        }

        if (!idSolicitacao) {
            console.error('❌ ID DA SOLICITAÇÃO NÃO FORNECIDO');
            Utils.showNotification('Erro', 'ID da solicitação é obrigatório', 'error');
            return;
        }

        let formData = new FormData();
        formData.append('id_solicitacoes_compras', idSolicitacao);
        formData.append('situacao_compra', situacaoCompra);
        if (grupoDespesa) {
            formData.append('grupo_despesa', grupoDespesa);
        }


        const url = '{{ route('admin.compras.cotacoes.mudarstatussolicitante') }}';

        fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': Utils.getCSRFToken(),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    console.error('❌ RESPOSTA NÃO OK - Status:', response.status);
                    return response.json().then(err => {
                        console.error('❌ ERRO DETALHADO:', err);
                        throw err;
                    });
                }
                return response.json();
            })
            .then(data => {
                if (typeof showNotification === 'function' && data.notification) {
                    showNotification(
                        data.notification.title,
                        data.notification.message,
                        data.notification.type
                    );
                } else if (data.notification) {
                    alert(data.notification.message);
                } else if (data.success) {
                    if (typeof showNotification === 'function') {
                        showNotification('Sucesso', 'Operação realizada com sucesso!', 'success');
                    } else {
                        alert('Operação realizada com sucesso!');
                    }
                }

                // Verifica se há redirect na resposta, senão vai para o índice
                const redirectUrl = data.redirect || '/admin/compras/cotacoes';

                // REDIRECIONAMENTO IMEDIATO para evitar interceptação
                window.location.href = redirectUrl;
            })
            .catch(error => {
                console.error('💥 ERRO CAPTURADO NO CATCH:', error);
                console.error('💥 TIPO DO ERRO:', typeof error);
                console.error('💥 NOME DO ERRO:', error.name);
                console.error('💥 MENSAGEM DO ERRO:', error.message);
                console.error('💥 STACK DO ERRO:', error.stack);
                console.error('💥 ERRO COMPLETO:', error);

                const message =
                    error?.notification?.message ||
                    error?.message ||
                    'Ocorreu um erro ao processar a solicitação';

                console.error('💥 MENSAGEM FINAL DE ERRO:', message);

                if (typeof showNotification === 'function') {
                    showNotification('Erro', message, 'error');
                } else {
                    alert(message);
                }
            });
    }

    async function mudarStatus(idSolicitacao, situacaoCompra, grupoDespesa) {
        if (!confirm('Atenção: você deseja enviar essa solicitação para o SOLICITANTE?')) {
            return;
        }

        if (!idSolicitacao) {
            Utils.showNotification('Erro', 'ID da solicitação é obrigatório', 'error');
            return;
        }

        try {
            const data = await AjaxManager.post('{{ route('admin.compras.cotacoes.mudarstatus') }}', {
                id_solicitacoes_compras: idSolicitacao,
                situacao_compra: situacaoCompra,
                grupo_despesa: grupoDespesa
            });

            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            }
        } catch (error) {
            // Erro já tratado no AjaxManager
        }
    }

    async function enviarCotacoes() {
        const idSolicitacoesCompras = DOMCache.get('input[name="id_solicitacoes_compras"]')?.value;
        const filialEntrega = DOMCache.get('input[name="filial_entrega"]')?.value;
        const filialFaturamento = DOMCache.get('input[name="filial_faturamento"]')?.value;

        if (!idSolicitacoesCompras) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'ID da solicitação de compra não encontrado.'
                });
            } else {
                Utils.showNotification('Erro', 'ID da solicitação de compra não encontrado.', 'error');
            }
            return;
        }

        // Mostrar loading
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Enviando cotações...',
                text: 'Por favor, aguarde enquanto os emails são enviados.',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => Swal.showLoading()
            });
        }

        const formData = new FormData();
        formData.append('id_solicitacoes_compras', idSolicitacoesCompras);
        if (filialEntrega) formData.append('filial_entrega', filialEntrega);
        if (filialFaturamento) formData.append('filial_faturamento', filialFaturamento);

        try {
            const data = await AjaxManager.postForm('{{ route('admin.compras.cotacoes.enviar') }}', formData);

            if (typeof Swal !== 'undefined') {
                Swal.close();
                Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? 'Sucesso!' : 'Erro!',
                    text: data.message || (data.success ? 'Cotações enviadas com sucesso!' :
                        'Erro ao enviar cotações.'),
                    showConfirmButton: true
                });
            } else {
                Utils.showNotification(
                    data.success ? 'Sucesso' : 'Erro',
                    data.message || (data.success ? 'Cotações enviadas!' : 'Erro ao enviar cotações.'),
                    data.success ? 'success' : 'error'
                );
            }
        } catch (error) {
            if (typeof Swal !== 'undefined') {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro de comunicação com o servidor. Tente novamente.',
                    showConfirmButton: true
                });
            }
        }
    }

    // ======================================================
    // FUNÇÕES DE COTAÇÕES OTIMIZADAS
    // ======================================================
    function testarCotacao(idCotacao) {
        carregarCotacao(idCotacao);
    }

    // ======================================================
    // FUNÇÃO PARA SALVAR ALTERAÇÕES LOCAIS DO ITEM (SEM ENVIAR PARA BANCO)
    // ======================================================
    function salvarAlteracaoItem() {
        if (!window.itemAtualEdicao) {
            console.warn('⚠️ Nenhum item selecionado para edição'); // Debug
            Utils.showNotification('Aviso', 'Nenhum item selecionado para edição', 'warning');
            return;
        }

        try {
            // Coletar valores dos campos editáveis
            const quantidadeFornecedor = DOMCache.get('input[name="quantidade_fornecedor"]')?.value || '';
            const valorUnitario = DOMCache.get('input[name="valorunitario"]')?.value || '';
            const valorDesconto = DOMCache.get('input[name="valor_desconto"]')?.value || '';

            const quantidadeSolicitada = window.itemAtualEdicao.quantidade_solicitada || 0;

            // Atualizar o objeto do item atual na memória
            if (quantidadeFornecedor) {
                window.itemAtualEdicao.quantidade_fornecedor = quantidadeFornecedor;
            }

            if (valorUnitario) {
                const valorNumerico = Utils.getNumericValue(valorUnitario);
                window.itemAtualEdicao.valorunitario = valorNumerico;
            }

            if (valorDesconto) {
                const descontoNumerico = Utils.getNumericValue(valorDesconto);
                window.itemAtualEdicao.valor_desconto = descontoNumerico;
            }

            // Recalcular valores
            const qtd = parseFloat(window.itemAtualEdicao.quantidade_fornecedor) || 0;
            const unitario = parseFloat(window.itemAtualEdicao.valorunitario) || 0;
            const desconto = parseFloat(window.itemAtualEdicao.valor_desconto) || 0;

            window.itemAtualEdicao.valor_item = qtd * unitario;

            // Atualizar tabela de itens com os novos valores
            if (window.cotacaoAtual && window.cotacaoAtual.id_cotacoes) {
                TableManager.populateOrcamentoItens(window.cotacaoAtual.id_cotacoes);
            }

            if (!unitario) {
                Utils.showNotification('Aviso',
                    'Atenção: O valor unitário do fornecedor deve ser informado!', 'info');
                return;
            }

            if (quantidadeSolicitada != qtd) {
                Utils.showNotification('Aviso',
                    'Atenção: A quantidade do fornecedor difere da quantidade solicitada!', 'info');
            } else {
                Utils.showNotification('Sucesso', 'Alterações do item salvas localmente!', 'success');
            }


            // Destacar visualmente que foi salvo
            const containerEdicao = DOMCache.get('.mb-6.grid.grid-cols-2.gap-4.rounded-lg.bg-gray-50.p-4');
            if (containerEdicao && containerEdicao.classList) {
                containerEdicao.classList.add('ring-2', 'ring-green-500', 'ring-opacity-50', 'bg-green-50');
                setTimeout(() => {
                    if (containerEdicao.classList) {
                        containerEdicao.classList.remove('ring-2', 'ring-green-500', 'ring-opacity-50',
                            'bg-green-50');
                    }
                }, 2000);
            }

        } catch (error) {
            console.error('❌ Erro ao salvar alterações locais:', error); // Debug
            Utils.showNotification('Erro', 'Erro ao salvar alterações do item', 'error');
        }
    }

    async function salvarEdicao() {

        const dadosModal = coletarDadosModal();

        const dataEntrega = dadosModal.data_entrega;
        if (!dataEntrega) {
            Utils.showNotification('Erro', 'Data de entrega é obrigatória', 'error');
            return;
        }

        const condicaoPag = dadosModal.condicao_pag;
        if (!condicaoPag) {
            Utils.showNotification('Erro', 'Condição de pagamento é obrigatória', 'error');
            return;
        }

        if (!window.cotacaoAtual) {
            console.error('❌ Nenhuma cotação selecionada'); // Debug
            Utils.showNotification('Erro', 'Nenhuma cotação selecionada para edição', 'error');
            return;
        }

        try {
            // *** MOSTRAR LOADING PERSONALIZADO ***
            // Mostrar loading no estilo SweetAlert2
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Salvando alterações...',
                    text: 'Por favor, aguarde enquanto as alterações estão sendo salvas.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => Swal.showLoading()
                });
            } else {
                Utils.showNotification('Info', 'Salvando alterações...', 'info');
            }

            const data = await AjaxManager.post('{{ route('admin.compras.cotacoes.salvaritenscotacao') }}',
                dadosModal);

            // *** MANTER LOADING ATIVO DURANTE O RELOAD ***
            // Para sucesso, manter o loading ativo até o reload terminar
            // Não fechar o modal nem o loading - deixar ativo durante o reload

            setTimeout(() => {
                window.location.reload();
            }, 500);

        } catch (error) {
            console.error('❌ Erro ao salvar:', error); // Debug

            // *** FECHAR LOADING EM CASO DE ERRO ***
            if (typeof Swal !== 'undefined') {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: `Erro ao salvar alterações: ${error.message}`,
                    showConfirmButton: true
                });
            } else {
                Utils.showNotification('Erro', `Erro ao salvar alterações: ${error.message}`, 'error');
            }
        }
    }

    function coletarDadosModal() {
        return {
            // Informações da cotação
            cotacao_info: DOMCache.get('.cotacao-info')?.textContent || '',
            solicitacao_info: DOMCache.get('.solicitacao-info')?.textContent || '',
            comprador_info: DOMCache.get('.comprador-info')?.textContent || '',
            fornecedor_info: DOMCache.get('.fornecedor-info')?.textContent || '',
            contato_info: DOMCache.get('.contato-info')?.textContent || '',
            email_info: DOMCache.get('.email-info')?.textContent || '',
            telefone_info: DOMCache.get('.tefone-info')?.textContent || '',

            // Campos editáveis da cotação
            data_entrega: DOMCache.get('#data_entrega')?.value || '',
            condicao_pag: DOMCache.get('input[name="condicao_pag"]')?.value || '',

            // Informações do produto
            produto_info: DOMCache.get('.produto-info')?.textContent || '',
            descricao_info: DOMCache.get('.descricao-info')?.textContent || '',
            unidade_info: DOMCache.get('.unidade-info')?.textContent || '',
            quantidade_info: DOMCache.get('.quantidade-info')?.textContent || '',

            // Campos editáveis do produto
            quantidade_fornecedor: DOMCache.get('input[name="quantidade_fornecedor"]')?.value || '',
            valor_unitario: DOMCache.get('input[name="valorunitario"]')?.value || '',
            valor_desconto: DOMCache.get('input[name="valor_desconto"]')?.value || '',

            // Valores calculados
            valor_total: DOMCache.get('.valor-info')?.textContent || '',
            valor_final: DOMCache.get('.valor-desconto-info')?.textContent || '',

            // Dados adicionais
            cotacao_atual: window.cotacaoAtual,
            itens_cotacao: window.itensCotacaoAtual || [],
            item_atual_edicao: window.itemAtualEdicao || null,
            timestamp: new Date().toISOString(),
            usuario_id: '{{ auth()->user()->id ?? 'N/A' }}'
        };
    }

    async function carregarCotacao(idCotacao) {
        if (!idCotacao) {
            Utils.showNotification('Erro', 'ID da cotação é obrigatório', 'error');
            return;
        }

        try {

            limparTodosCamposModal();

            const data = await buscarDadosCotacao(idCotacao);

            if (data.error || !data.cotacao) {
                Utils.showNotification('Erro', data.message || 'Cotação não encontrada', 'error');
                return;
            }

            preencherModalCotacao(data.cotacao);

            // Verificar se showModal está disponível
            if (typeof showModal === 'function') {
                showModal('editarForm');
            } else {
                console.warn('⚠️ Função showModal não disponível'); // Debug
            }

            TableManager.populateOrcamentoItens(data.cotacao.id_cotacoes);

            setTimeout(() => Calculator.init(), 200);

        } catch (error) {
            let mensagemErro = "Erro ao carregar cotação. ";
            if (error.message.includes('404')) {
                mensagemErro += "A cotação não foi encontrada.";
            } else {
                mensagemErro += error.message;
            }
            Utils.showNotification('Erro', mensagemErro, 'error');
        }
    }

    function preencherModalCotacao(cotacao) {
        window.cotacaoAtual = cotacao;

        // Mapeamento de campos otimizado
        const campos = {
            '.cotacao-info': cotacao.id_cotacoes,
            '.solicitacao-info': cotacao.id_solicitacoes_compras,
            '.comprador-info': cotacao.id_comprador,
            '.fornecedor-info': cotacao.nome_fornecedor,
            '.contato-info': cotacao.nome_contato,
            '.email-info': cotacao.email,
            '.tefone-info': cotacao.telefone_fornecedor,
            '.data-info': cotacao.data_entrega,
            '#data_entrega': cotacao.data_entrega,
            '#condicao_pag': cotacao.condicao_pag
        };

        // Batch de atualizações
        requestAnimationFrame(() => {
            Object.entries(campos).forEach(([selector, valor]) => {
                const elemento = DOMCache.get(selector);
                if (!elemento || !valor) return;

                if (elemento.tagName === 'INPUT') {
                    elemento.value = valor;
                } else {
                    elemento.textContent = valor;
                }
            });


            // Tratar valores formatados
            const valorInfo = DOMCache.get('.valor-info');
            if (valorInfo && cotacao.valor_total) {
                valorInfo.textContent = new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(cotacao.valor_total);
            }
        });
    }

    // ======================================================
    // FUNÇÕES DE EDIÇÃO DE ITENS OTIMIZADAS
    // ======================================================
    function editarItemCotacao(item) {
        const campos = {
            '.produto-info': item.id_produto ?? 'N/A',
            '.descricao-info': item.descricao_produto ?? 'N/A',
            '.unidade-info': item.descricao_unidade ?? 'N/A',
            '.quantidade-info': item.quantidade_solicitada ?? 'N/A',
            'input[name="quantidade_fornecedor"]': item.quantidade_fornecedor ?? '',
            'input[name="valorunitario"]': item.valorunitario ? Utils.formatBR(parseFloat(item.valorunitario)) : '',
            'input[name="valor_desconto"]': item.valor_desconto ? Utils.formatBR(parseFloat(item.valor_desconto)) :
                ''
        };

        // Preencher campos
        requestAnimationFrame(() => {
            Object.entries(campos).forEach(([selector, valor]) => {
                const elemento = DOMCache.get(selector);
                if (!elemento) return;

                if (elemento.tagName === 'INPUT') {
                    elemento.value = valor;
                } else {
                    elemento.textContent = valor;
                }
            });

            // Atualizar valores calculados
            const valorItem = parseFloat(item.valor_item) || 0;
            const valorDesconto = parseFloat(item.valor_desconto) || 0;
            const valorFinal = Math.abs(valorDesconto - valorItem);

            const valorInfo = DOMCache.get('.valor-info');
            const valorDescontoInfo = DOMCache.get('.valor-desconto-info');

            if (valorInfo) valorInfo.textContent = Utils.formatBR(valorItem);
            if (valorDescontoInfo) valorDescontoInfo.textContent = Utils.formatBR(valorFinal);
        });

        window.itemAtualEdicao = item;

        // Scroll e destaque visual
        const secaoEdicao = DOMCache.get('.mb-6.grid.grid-cols-2');
        secaoEdicao?.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // Destacar temporariamente
        const containerEdicao = DOMCache.get('.mb-6.grid.grid-cols-2.gap-4.rounded-lg.bg-gray-50.p-4');
        if (containerEdicao && containerEdicao.classList) {
            containerEdicao.classList.add('ring-2', 'ring-blue-500', 'ring-opacity-50');
            setTimeout(() => {
                if (containerEdicao.classList) {
                    containerEdicao.classList.remove('ring-2', 'ring-blue-500', 'ring-opacity-50');
                }
            }, 3000);
        }

        setTimeout(() => Calculator.init(), 100);
    }

    function limparTodosCamposModal() {
        const camposTexto = [
            '.cotacao-info', '.solicitacao-info', '.comprador-info',
            '.fornecedor-info', '.contato-info', '.email-info', '.tefone-info',
            '.produto-info', '.descricao-info', '.unidade-info', '.quantidade-info'
        ];

        const camposInput = [
            'input[name="data_entrega"]', 'input[name="condicao_pag"]',
            'input[name="quantidade_fornecedor"]',
            'input[name="valorunitario"]', 'input[name="valor_desconto"]'
        ];

        requestAnimationFrame(() => {
            camposTexto.forEach(selector => {
                const el = DOMCache.get(selector);
                if (el) el.textContent = '';
            });

            camposInput.forEach(selector => {
                const el = DOMCache.get(selector);
                if (el) el.value = '';
            });

            // Resetar valores calculados
            const valorInfo = DOMCache.get('.valor-info');
            const valorDescontoInfo = DOMCache.get('.valor-desconto-info');
            if (valorInfo) valorInfo.textContent = '0,00';
            if (valorDescontoInfo) valorDescontoInfo.textContent = '0,00';

            // Limpar tabela
            const tabelaBody = DOMCache.get('#tabelaOrcamentoItensBody');
            if (tabelaBody) tabelaBody.innerHTML = '';

        });

        // Limpar variáveis globais
        window.itemAtualEdicao = null;
        window.itensCotacaoAtual = [];
        window.cotacaoAtual = null;

        // Limpar cache DOM
        DOMCache.clear();
    }

    // ======================================================
    // SISTEMA ALPINE.JS OTIMIZADO
    // ======================================================
    document.addEventListener('alpine:init', () => {
        Alpine.store('utils', {
            loading: false,

            _mostrarAlerta(mensagem) {
                Utils.showNotification('Alerta', mensagem, 'warning');
            },

            _mostrarLoading() {
                this.loading = true;
                const loadingHtml = `
                    <div id="loading-message" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white p-6 rounded-lg shadow-lg flex items-center space-x-3">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            <span class="text-gray-700 font-medium">Processando...</span>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', loadingHtml);
            },

            _removerLoading() {
                this.loading = false;
                const loadingElement = document.getElementById('loading-message');
                if (loadingElement) loadingElement.remove();
            },

            _fazerDownload(blob, nomeArquivo) {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = nomeArquivo;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                a.remove();
            },

            _obterCSRFToken() {
                return Utils.getCSRFToken();
            },

            async _fazerRequisicao(url, data, timeout = 300000) {
                return await AjaxManager.post(url, data);
            },

            async imprimirCotacoes(solicitacao) {
                this.loading = true;
                try {
                    if (!solicitacao) {
                        throw new Error('ID da solicitação é obrigatório');
                    }

                    const solicitacaoArray = Array.isArray(solicitacao) ? solicitacao : [
                        solicitacao
                    ];
                    this._mostrarLoading();

                    const response = await fetch('/admin/compras/cotacoes/imprimir', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this._obterCSRFToken(),
                            'Accept': 'application/pdf'
                        },
                        body: JSON.stringify({
                            solicitacao: solicitacaoArray
                        })
                    });

                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({}));
                        throw new Error(errorData.message ||
                            `Erro ${response.status}: ${response.statusText}`);
                    }

                    const blob = await response.blob();
                    this._fazerDownload(blob,
                        `relatorio_cotacoes_${new Date().toISOString().split('T')[0]}.pdf`);

                } catch (error) {
                    console.error('Erro ao gerar relatório PDF:', error);

                    if (error.name === 'AbortError') {
                        this._mostrarAlerta(
                            'Timeout: A requisição demorou mais de 5 minutos para responder.');
                    } else {
                        this._mostrarAlerta('Erro ao gerar relatório: ' + error.message);
                    }
                } finally {
                    this.loading = false;
                    this._removerLoading();
                }
            }
        });
    });

    /**
     * Função para preencher automaticamente os dados do fornecedor
     * quando um fornecedor é selecionado no smart-select
     */
    async function preencherDadosFornecedor(idFornecedor, optionObject) {
        try {
            // Buscar dados completos do fornecedor pela API
            const response = await fetch(`{{ route('admin.api.fornecedores.single', '') }}/${idFornecedor}`);

            if (!response.ok) {
                throw new Error(`Erro ao buscar fornecedor: ${response.status}`);
            }

            const fornecedorData = await response.json();

            // Preencher campo nome do contato com o nome do fornecedor
            const nomeContatoField = document.querySelector('input[name="nome_contato"]');
            if (nomeContatoField && fornecedorData.nome_fornecedor) {
                nomeContatoField.value = fornecedorData.nome_fornecedor;
            }

            // Preencher campo email se existir
            const emailField = document.querySelector('input[name="email"]');
            if (emailField && fornecedorData.email) {
                emailField.value = fornecedorData.email;
            }

        } catch (error) {
            console.error('Erro ao preencher dados do fornecedor:', error);
            // Não mostrar notificação de erro para não incomodar o usuário
            // Utils.showNotification('Aviso', 'Não foi possível carregar todos os dados do fornecedor', 'warning');
        }
    }

    // ======================================================
    // FUNÇÃO UTILITÁRIA PARA GERENCIAR ABAS
    // ======================================================
    function limparAbaAtiva() {
        try {
            localStorage.removeItem('cotacoes_aba_ativa');
        } catch (error) {
            console.warn('Não foi possível limpar a aba ativa do localStorage:', error);
        }
    }

    // ======================================================
    // EXPOSIÇÃO DE FUNÇÕES GLOBAIS OTIMIZADA
    // ======================================================

    // Expor apenas as funções necessárias globalmente
    const globalFunctions = {
        testarCotacao,
        salvarEdicao,
        salvarAlteracaoItem,
        editarItemCotacao,
        buscarItemSolicitacao,
        incluirCotacao,
        // mudarStatusSolicitante,
        mudarStatus,
        enviarCotacoes,
        openTab,
        preencherDadosFornecedor,
        limparAbaAtiva,

        // Funções de compatibilidade (manter nomes antigos)
        popularTabelaOrcamentoItens: TableManager.populateOrcamentoItens.bind(TableManager),
        inicializarCalculosAutomaticos: Calculator.init.bind(Calculator),
        calcularValoresAutomatico: Calculator.calculate.bind(Calculator),
        formatarMoedaTempoReal: MoneyFormatter.formatRealTime.bind(MoneyFormatter),
        obterValorNumerico: Utils.getNumericValue,
        formatarBR: Utils.formatBR,
        limparTodosCamposModal,
        makeRequest: AjaxManager.request.bind(AjaxManager),
        buscarDadosCotacao
    };

    // Expor no objeto window
    Object.assign(window, globalFunctions);

    // Verificar se as abas existem no DOM após carregamento
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            // console.log('Verificando elementos das abas:');
            // console.log('Botões .tablink encontrados:', document.querySelectorAll('.tablink').length);
            // console.log('Divs .tabcontent encontradas:', document.querySelectorAll('.tabcontent')
            //     .length);
            // console.log('Aba1 existe:', !!document.getElementById('Aba1'));
            // console.log('Aba2 existe:', !!document.getElementById('Aba2'));

            // // Testar campos do formulário de cotação
            // console.log('Verificando campos do formulário:');
            // console.log('Campo id_solicitacoes_compras:', !!document.querySelector(
            //     '[name="id_solicitacoes_compras"]'));
            // console.log('Campo id_fornecedor:', !!document.querySelector('[name="id_fornecedor"]'));
            // console.log('Campo nome_contato:', !!document.querySelector('input[name="nome_contato"]'));
            // console.log('Campo email:', !!document.querySelector('input[name="email"]'));
        }, 500);
    });

    // ======================================================
    // FUNÇÃO DE DESMEMBRAMENTO DE COTAÇÃO UNIFICADA
    // ======================================================
    function desmembrarCotacao(idSolicitacao) {
        if (!idSolicitacao) {
            alert('ID da solicitação não informado.');
            return;
        }

        // Confirmar ação
        const confirmMessage = `Confirma o desmembramento da cotação unificada #${idSolicitacao}?\n\n` +
            `Esta ação irá:\n` +
            `• Cancelar a cotação unificada atual\n` +
            `• Restaurar as cotações originais com suas situações anteriores\n` +
            `• Esta ação não pode ser desfeita`;

        if (!confirm(confirmMessage)) {
            return;
        }

        // Desabilitar o botão para evitar cliques duplos
        const botaoDesmembrar = document.querySelector(`button[onclick*="desmembrarCotacao(${idSolicitacao})"]`);
        if (botaoDesmembrar) {
            botaoDesmembrar.disabled = true;
            botaoDesmembrar.innerHTML = `
                <svg class="h-3 w-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            `;
        }

        // Fazer a requisição
        fetch(`/admin/compras/cotacoes/${idSolicitacao}/desmembrar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => {
                // Log da resposta para debug

                if (!response.ok) {
                    // Tentar ler como texto para ver o erro
                    return response.text().then(text => {
                        throw new Error(
                            `HTTP ${response.status}: ${response.statusText} - ${text.substring(0, 200)}`
                        );
                    });
                }

                // Verificar se a resposta é JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new Error(
                            'Resposta não é JSON. Verifique se há erros de autenticação ou autorização.'
                        );
                    });
                }

                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Cotação desmembrada com sucesso! A página será recarregada.');

                    // Recarregar a página
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Erro desconhecido ao desmembrar cotação');
                }
            })
            .catch(error => {
                console.error('Erro ao desmembrar cotação:', error);

                let errorMessage = 'Erro ao desmembrar cotação: ';
                if (error.message) {
                    errorMessage += error.message;
                } else {
                    errorMessage += 'Erro desconhecido. Verifique o console para mais detalhes.';
                }

                alert(errorMessage);

                // Restaurar o botão
                if (botaoDesmembrar) {
                    botaoDesmembrar.disabled = false;
                    botaoDesmembrar.innerHTML = `
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                    </svg>
                `;
                }
            });
    }

    // Disponibilizar globalmente
    window.desmembrarCotacao = desmembrarCotacao;
</script>
