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
{{-- -------------------- Script para as TABS -------------------- --}}


{{-- Select em cascata --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        onSmartSelectChange('id_ordem_servico', function(data) {

            // Limpa a lista de produtos
            updateSmartSelectOptions('id_produto', []);


            // Configure os headers corretamente
            const headers = {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            fetch(`/admin/devolucaosaidaestoque/getProduto/${data.value}`, {
                    method: 'GET',
                    headers: headers,
                    credentials: 'same-origin'
                })
                .then(response => {
                    console.log('Status da resposta:', response.status);

                    if (!response.ok) {
                        throw new Error('Erro na resposta da API: ' + response.status);
                    }
                    return response.json();
                })
                .then(retorno => {

                    for (const item of retorno) {
                        addSmartSelectOption('id_produto', {
                            value: item.value,
                            label: item.label,
                        });
                    }
                })
                .catch(err => {
                    console.error('Erro ao buscar dados do veículo:', err);
                });
        });
    });
</script>

{{-- ------- Função para exclusão devolução de peças -------------- --}}

<script>
    function onExcluir(id, dev = 0) {
        const confirmation = confirm(
            'Você tem certeza que deseja excluir a devolução selecionadas? Esta ação não pode ser desfeita.'
        );

        if (confirmation) {
            const url = `/admin/devolucaosaidaestoque/excluir/${id}&confirmed=${confirmation}&dev=${dev}`;

            fetch(url, {
                    method: 'DELETE',
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
                    console.log(data.status);
                    if (data.status === 'success') {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Erro ao assumir a requisição: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro ao assumir a requisição:', error);
                    alert('Ocorreu um erro ao tentar excluir a devolução.');
                });

        }
    }
</script>

{{-- Select em cascata materiais --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        onSmartSelectChange('id_solicitacao_pecas', function(data) {

            // Limpa a lista de produtos
            updateSmartSelectOptions('id_produto', []);


            // Configure os headers corretamente
            const headers = {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            fetch(`/admin/devolucaosaidaestoque/getMateriais/${data.value}`, {
                    method: 'GET',
                    headers: headers,
                    credentials: 'same-origin'
                })
                .then(response => {
                    console.log('Status da resposta:', response.status);

                    if (!response.ok) {
                        throw new Error('Erro na resposta da API: ' + response.status);
                    }
                    return response.json();
                })
                .then(retorno => {
                    console.log(retorno);
                    for (const item of retorno) {
                        console.log(item);
                        addSmartSelectOption('id_produto', {
                            value: item.value,
                            label: item.label,
                        });
                    }
                })
                .catch(err => {
                    console.error('Erro ao buscar dados do veículo:', err);
                });
        });
    });
</script>
