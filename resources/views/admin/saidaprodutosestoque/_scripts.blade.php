{{-- -------------------- Script para as TABS -------------------- --}}
<script>
    // Variável global para armazenar a tab ativa atual
    let activeTabId = 'Aba1';

    document.addEventListener("DOMContentLoaded", function() {
        // Inicializa dropdowns
        initializeDropdowns();

        // Inicializa tabs
        initializeTabs();

        // Adiciona listeners para HTMX
        setupHtmxListeners();
    });

    function initializeDropdowns() {
        const buttons = document.querySelectorAll(".dropdown-button");

        buttons.forEach(button => {
            button.addEventListener("click", function(event) {
                event.stopPropagation();

                // Fecha todos os outros dropdowns
                document.querySelectorAll(".dropdown-menu").forEach(menu => {
                    if (menu !== this.nextElementSibling) {
                        menu.classList.add("hidden");
                    }
                });

                // Alterna apenas o menu clicado
                this.nextElementSibling.classList.toggle("hidden");
            });
        });

        // Fecha o dropdown ao clicar fora
        document.addEventListener("click", function() {
            document.querySelectorAll(".dropdown-menu").forEach(menu => {
                menu.classList.add("hidden");
            });
        });
    }

    function initializeTabs() {
        // Verifica se há uma tab salva no sessionStorage ou nos parâmetros da URL
        const urlParams = new URLSearchParams(window.location.search);
        const tabFromUrl = urlParams.get('active_tab');
        const savedTab = tabFromUrl || sessionStorage.getItem('activeTab') || 'Aba1';

        activeTabId = savedTab;

        // Encontra o botão correspondente e ativa a tab
        activateTab(savedTab);
    }

    function activateTab(tabName) {
        // Esconde todos os conteúdos das abas
        const tabcontents = document.querySelectorAll(".tabcontent");
        tabcontents.forEach((tab) => {
            tab.classList.add("hidden");
        });

        // Remove a classe "active" de todos os botões
        const tablinks = document.querySelectorAll(".tablink");
        tablinks.forEach((link) => {
            link.classList.remove("bg-gray-200", "text-black");
            link.classList.add("bg-white", "text-gray-700");
        });

        // Mostra o conteúdo da aba especificada
        const targetTab = document.getElementById(tabName);
        if (targetTab) {
            targetTab.classList.remove("hidden");
        }

        // Encontra e ativa o botão correspondente
        const targetButton = Array.from(document.querySelectorAll('.tablink')).find(btn => {
            const onclick = btn.getAttribute('onclick');
            return onclick && onclick.includes(tabName);
        });

        if (targetButton) {
            targetButton.classList.remove("bg-white", "text-gray-700");
            targetButton.classList.add("bg-gray-200", "text-black");
        }

        // Atualiza campos hidden nos formulários
        updateFormHiddenFields(tabName);
    }

    function openTab(evt, tabName) {
        // Salva a tab atual
        activeTabId = tabName;
        sessionStorage.setItem('activeTab', tabName);

        // Ativa a tab
        activateTab(tabName);

        // Dispara evento customizado para notificar mudança de tab
        const event = new CustomEvent('tabChanged', {
            detail: {
                tabName: tabName
            }
        });
        document.body.dispatchEvent(event);
    }

    function updateFormHiddenFields(tabName) {
        // Atualiza todos os campos hidden de active_tab nos formulários
        const hiddenFields = document.querySelectorAll('input[name="active_tab"]');
        hiddenFields.forEach(field => {
            field.value = tabName;
        });
    }

    function setupHtmxListeners() {
        // Listener para antes do request HTMX
        document.body.addEventListener('htmx:beforeRequest', function(evt) {
            // Garante que o campo active_tab seja enviado com a tab atual
            const form = evt.detail.elt;
            if (form && form.tagName === 'FORM') {
                let activeTabField = form.querySelector('input[name="active_tab"]');
                if (!activeTabField) {
                    // Cria o campo se não existir
                    activeTabField = document.createElement('input');
                    activeTabField.type = 'hidden';
                    activeTabField.name = 'active_tab';
                    form.appendChild(activeTabField);
                }
                activeTabField.value = activeTabId;
            }
        });

        // Listener para após o swap do HTMX
        document.body.addEventListener('htmx:afterSwap', function(evt) {
            // Verifica se o swap foi em uma das tabelas de resultados
            if (evt.detail.target && evt.detail.target.id === 'results-table') {
                // Reaplica a tab ativa após o carregamento do HTMX
                setTimeout(() => {
                    activateTab(activeTabId);
                    // Reinicializa os dropdowns para os novos elementos
                    initializeDropdowns();
                }, 50);
            }
        });

        // Listener para quando uma requisição HTMX falha
        document.body.addEventListener('htmx:responseError', function(evt) {
            console.warn('Erro na requisição HTMX:', evt.detail);
        });
    }

    // Função para debug - remove em produção
    function getCurrentTab() {
        return activeTabId;
    }
</script>
{{-- -------------------- Script para as TABS -------------------- --}}

{{-- -------------------- Script para botão ações ---------------- --}}
<script>
    function closeAllDropdowns() {
        document.querySelectorAll(".dropdown-menu").forEach(menu => {
            menu.classList.add("hidden");
        });

        // Remove o foco do elemento ativo
        if (document.activeElement) {
            document.activeElement.blur();
        }
    }

    function toggleDropdown() {
        const buttons = document.querySelectorAll(".dropdown-button");

        buttons.forEach(button => {
            button.addEventListener("click", function(event) {
                event.stopPropagation();

                // Fecha todos os outros dropdowns
                document.querySelectorAll(".dropdown-menu").forEach(menu => {
                    if (menu !== this.nextElementSibling) {
                        menu.classList.add("hidden");
                    }
                });

                // Alterna apenas o menu clicado
                this.nextElementSibling.classList.toggle("hidden");
            });
        });

        // Fecha dropdowns ao clicar fora
        document.addEventListener("click", closeAllDropdowns);

        // Fecha dropdowns ao pressionar ESC
        document.addEventListener("keydown", function(event) {
            if (event.key === "Escape") {
                closeAllDropdowns();
            }
        });
    }
</script>
{{-- -------------------- Script para botão ações ---------------- --}}

{{-- ---------------- Script para visualizar produtos ------------ --}}
<script>
    async function visualizarProdutos(idRequisicao) {
        // Usar a função showModal que já existe no seu projeto
        if (typeof showModal === 'function') {
            showModal('vizualizar-produtos');
        } else {
            // Se não existir, mostrar modal manualmente
            const modal = document.getElementById('vizualizar-produtos');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        // Mostrar loading
        const tabelaBody = document.getElementById('tabelaBodyProdutos');
        if (tabelaBody) {
            tabelaBody.innerHTML = `
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <div style="border: 2px solid #f3f3f3; border-top: 2px solid #3498db; border-radius: 50%; width: 20px; height: 20px; animation: spin 1s linear infinite;"></div>
                            <span>Carregando produtos...</span>
                        </div>
                    </td>
                </tr>
            `;
        }

        try {

            const response = await fetch(`/admin/saidaprodutosestoque/${idRequisicao}/visualizar`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || ''
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.status === 'success') {
                preencherTabelaProdutos(data.items, data.filial_usuario);
            } else if (data.status === 'redirect') {
                alert('Este item possui imobilizado. Redirecionamento necessário.');
                fecharModal();
            } else if (data.status === 'error') {
                throw new Error(data.message || 'Erro desconhecido');
            }

        } catch (error) {
            mostrarErroNaTabela(error.message);
        }
    }

    function mostrarErroNaTabela(mensagem) {
        const tabelaBody = document.getElementById('tabelaBodyProdutos');
        if (tabelaBody) {
            tabelaBody.innerHTML = `
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px; color: #e74c3c;">
                        <span>Erro: ${mensagem}</span>
                    </td>
                </tr>
            `;
        }
    }

    function preencherTabelaProdutos(produtos, filialUsuario) {
        const tabelaBody = document.getElementById('tabelaBodyProdutos');

        if (!tabelaBody) {
            console.error('Elemento tabelaBodyProdutos não encontrado!');
            return;
        }

        if (!produtos || produtos.length === 0) {
            tabelaBody.innerHTML = `
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px; color: #666;">
                        <span>Nenhum produto encontrado para esta requisição.</span>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        produtos.forEach(produto => {
            // Formatação segura dos dados
            const idSolicitacao = produto.id_relacao_solicitacoes || 'N/A';
            const descricao = produto.produto?.descricao_produto || 'N/A';
            const unidade = produto.produto?.unidade_produto.descricao_unidade || 'N/A';
            const quantidade = produto.quantidade || 0;
            const quantidadeBaixa = produto.quantidade_baixa || 'N/A';
            const estoqueFilial = produto.quantidade_estoque_filial || 0;
            const localizacao = produto.localizacao_filial || 'N/A';
            const valorMedio = produto.valor_medio_filial || 'N/A';
            const dataBaixa = produto.data_baixa ? new Date(produto.data_baixa).toLocaleDateString('pt-BR') :
                'N/A';

            html += `
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 text-sm text-gray-900">${idSolicitacao}</td>
                    <td class="px-3 py-2 text-sm text-gray-900">${descricao}</td>
                    <td class="px-3 py-2 text-sm text-center text-gray-900">${unidade}</td>
                    <td class="px-3 py-2 text-sm text-center text-gray-900">${quantidade}</td>
                    <td class="px-3 py-2 text-sm text-center text-gray-900">${quantidadeBaixa}</td>
                    <td class="px-3 py-2 text-sm text-center text-gray-900">${estoqueFilial}</td>
                    <td class="px-3 py-2 text-sm text-center text-gray-900">${localizacao}</td>
                    <td class="px-3 py-2 text-sm text-gray-900">${dataBaixa}</td>
                </tr>
            `;
        });

        tabelaBody.innerHTML = html;
    }

    function fecharModal() {
        if (typeof closeModal === 'function') {
            closeModal('vizualizar-produtos');
        } else {
            const modal = document.getElementById('vizualizar-produtos');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }
    }

    // Adicionar estilos CSS se não existir
    if (!document.getElementById('loading-styles')) {
        const style = document.createElement('style');
        style.id = 'loading-styles';
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    }
</script>
{{-- ---------------- Script para visualizar produtos ------------ --}}

<script>
    function imprimirProdutos(id) {
        fetch(`/admin/saidaprodutosestoque/imprimir/${id}`)
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                a.download = 'relatorio_requisicao.pdf';
                a.click();
                window.URL.revokeObjectURL(url);
                modalImprimir.classList.add('hidden');
            })
            .catch(error => {
                console.error('Erro ao buscar os dados:', error);
            });
    }

    function cancelarTransferencia(id) {
        const url = `/admin/saidaprodutosestoque/cancelarTransferencia/${id}`;

        fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    alert('Item cancelado com sucesso!');
                    fecharModalEstorno();
                    location.reload();
                } else {
                    alert('Erro ao cancelar o item: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro ao cancelar o item:', error);
                alert('Ocorreu um erro ao tentar cancelar o item.');
            });
    }
</script>

<script>
    function imprimirReqPecas(id) {
        fetch(`/admin/saidaprodutosestoque/imprimir-pecas/${id}`)
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                a.download = 'relatorio_requisicao.pdf';
                a.click();
                window.URL.revokeObjectURL(url);
                modalImprimir.classList.add('hidden');
            })
            .catch(error => {
                console.error('Erro ao buscar os dados:', error);
            });
    }
</script>

{{-- ------- Função para unificação de peças -------------- --}}
<script>
    function onAssumir(id) {
        if (confirm("Atenção: Deseja assumir a requisição?")) {
            const url = `/admin/saidaprodutosestoque/assumir/${id}`;

            fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        alert('Requisição assumida com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro ao assumir a requisição: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro ao assumir a requisição:', error);
                    alert('Ocorreu um erro ao tentar assumir a requisição.');
                });

        }
    }
</script>
