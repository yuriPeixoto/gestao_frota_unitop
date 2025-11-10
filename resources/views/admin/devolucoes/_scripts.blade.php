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
            link.classList.remove("bg-blue-200", "text-black");
            link.classList.add("bg-white", "text-black");
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
            targetButton.classList.remove("bg-white", "text-black");
            targetButton.classList.add("bg-blue-200", "text-black");
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

<script>
    async function gerarDevolucao(id) {
        if (!confirm('Você tem certeza que deseja gerar a devolução?')) {
            return;
        }

        try {
            const response = await fetch(`/admin/devolucoes/${id}/onGerarDevolucao`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    id: id
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseData = await response.json();
            console.log('✅ Requisição Valor Servico bem-sucedida:', responseData);
            if (responseData.success === true) {
                if (responseData.data) {
                    console.log(responseData.data);

                } else {
                    alert(responseData.message);
                }
            } else {
                console.warn('⚠️ Requisição não foi bem-sucedida');
            }
        } catch (error) {
            console.log(responseData);
            console.error('❌ Falha na requisição Valor Servico:', error);
        }
    }
</script>

{{-- script modal --}}
<script>
    function openModalTransferencia(id_transferencia_estoque) {
        fetch(`/admin/devolucoes/${id_transferencia_estoque}/dados`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let itensHtml = '';
                    data.devolucao.forEach(item => {
                        itensHtml += `<tr>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <input type="checkbox" class="item-checkbox-${id_transferencia_estoque}" value="${item.id}">
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">${item.id}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${item.data_inclusao ? formatarData(item.data_inclusao) : '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${item.data_alteracao ? formatarData(item.data_alteracao) : '-'}</td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">${item.id_devolucao_matriz}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${item.produto ? item.produto.descricao_produto : '-'}</td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">${item.qtd_disponivel_envio}</td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">${item.qtd_enviada}</td>
                    </tr>`;
                    });
                    document.getElementById('modalDevolucaoItens-' + id_transferencia_estoque).innerHTML =
                        itensHtml;
                    document.getElementById('modal-transferencia-' + id_transferencia_estoque).classList.remove(
                        'hidden');
                } else {
                    alert(data.message);
                }
            });
    }

    // Função para selecionar todos os checkboxes
    function toggleCheckAll(id_transferencia_estoque) {
        const checkAll = document.getElementById('checkAll-' + id_transferencia_estoque);
        const checkboxes = document.querySelectorAll('.item-checkbox-' + id_transferencia_estoque);
        checkboxes.forEach(cb => cb.checked = checkAll.checked);
    }

    function closeModalTransferencia(id) {
        document.getElementById('modal-transferencia-' + id).classList.add('hidden');
    }

    function formatarData(data) {
        if (!data)
            return '';

        const dataObj = new Date(data);
        const options = {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            timeZone: 'UTC'
        };

        if (dataObj.toLocaleDateString('pt-BR', options) === 'Invalid Date')
            return '';

        return dataObj.toLocaleDateString('pt-BR', options);
    }

    function confirmarEnvioTransferencia(id_transferencia_estoque) {
        // Coleta os checkboxes marcados
        const checkboxes = document.querySelectorAll('.item-checkbox-' + id_transferencia_estoque + ':checked');
        const itensSelecionados = Array.from(checkboxes).map(cb => cb.value);

        if (confirm('Você tem certeza que deseja enviar os itens selecionados?')) {
            if (itensSelecionados.length === 0) {
                alert('Selecione ao menos um item para enviar.');
                return;
            }

            // Envia via fetch para o controller
            fetch(`/admin/devolucoes/onGerarTransferencia`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id_transferencia_estoque: id_transferencia_estoque,
                        itens: itensSelecionados
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Transferência gerada com sucesso!');
                        closeModalTransferencia(id_transferencia_estoque);
                    } else {
                        alert(data.message || 'Erro ao gerar transferência.');
                    }
                })
                .catch(error => {
                    alert('Erro na requisição.');
                    console.error(error);
                });
        }
    }
</script>
