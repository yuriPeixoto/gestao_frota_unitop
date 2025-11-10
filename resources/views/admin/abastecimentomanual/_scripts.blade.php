<script>
    // ======================================================
    // INICIALIZAÇÃO GERAL DO DOCUMENTO
    // ======================================================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM carregado - Inicializando scripts');
        
        // Inicializar manipulação da tabela
        initTableLoading();
        
        // Inicializar manipulador de CNPJ
        initCnpjHandler();
    });
    
    // ======================================================
    // GESTÃO DE LOADING DA TABELA DE RESULTADOS
    // ======================================================
    function initTableLoading() {
        const loadingElement = document.getElementById('table-loading');
        const resultsElement = document.getElementById('results-table');
        
        if (loadingElement && resultsElement) {
            console.log('Elementos de loading/resultado encontrados');
            
            // Esconder o loading e mostrar os resultados após carregamento da página
            setTimeout(function() {
                loadingElement.style.display = 'none';
                resultsElement.classList.remove('opacity-0');
                resultsElement.classList.add('opacity-100');
                console.log('Tabela de resultados exibida');
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
    // MANIPULAÇÃO DE CNPJ DO FORNECEDOR
    // ======================================================
    function initCnpjHandler() {
        console.log('Inicializando manipulador de CNPJ');

        // Pegar o token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('CSRF Token encontrado:', !!csrfToken);

        // Observe o campo de seleção de fornecedor
        const fornecedorSelect = document.querySelector('select[name="id_fornecedor"]');
        const hiddenInput = document.querySelector('input[name="id_fornecedor"]');
        const cnpjInput = document.getElementById('cnpj_fornecedor');

        console.log('Elementos encontrados:', {
            fornecedorSelect: !!fornecedorSelect,
            hiddenInput: !!hiddenInput,
            cnpjInput: !!cnpjInput
        });

        // Se não encontrou os elementos necessários, sair da função
        if ((!fornecedorSelect && !hiddenInput) || !cnpjInput) {
            console.log('Elementos necessários não encontrados para manipulador de CNPJ');
            return;
        }

        // Função para atualizar o CNPJ baseado na seleção
        function atualizarCnpj() {
            const idFornecedor = hiddenInput ? hiddenInput.value : (fornecedorSelect ? fornecedorSelect.value : null);
            console.log('ID Fornecedor selecionado:', idFornecedor);
            
            if (!idFornecedor || !cnpjInput) {
                console.log('ID Fornecedor não encontrado ou campo CNPJ não existe');
                return;
            }

            // Se não conseguimos do campo de dados, buscar via API getById
            console.log('Buscando CNPJ via API getById para ID:', idFornecedor);
            
            // Configure os headers corretamente
            const headers = {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };
            
            fetch(`/admin/api/fornecedores/single/${idFornecedor}`, {
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
            .then(data => {
                console.log('Resposta da API getById:', data);
                if (data && data.cnpj_fornecedor) {
                    cnpjInput.value = data.cnpj_fornecedor;
                    console.log('CNPJ atualizado da API getById:', data.cnpj_fornecedor);
                } else {
                    console.log('CNPJ não encontrado na resposta da API');
                }
            })
            .catch(err => {
                console.error('Erro ao buscar fornecedor:', err);
            });
        }

        // Monitorar mudanças nos selects e inputs
        if (fornecedorSelect) {
            console.log('Adicionando listener ao select de fornecedor');
            fornecedorSelect.addEventListener('change', atualizarCnpj);
        }

        if (hiddenInput) {
            console.log('Adicionando listener ao input oculto de fornecedor');
            hiddenInput.addEventListener('input', atualizarCnpj);
            hiddenInput.addEventListener('change', atualizarCnpj);
            
            // Observar as alterações de valor (algumas bibliotecas não disparam eventos)
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                        console.log('Valor do input oculto alterado:', hiddenInput.value);
                        atualizarCnpj();
                    }
                });
            });
            
            observer.observe(hiddenInput, { attributes: true });
        }
        
        // Capturar eventos da biblioteca smart-select
        document.addEventListener('select-change', function(e) {
            if (e.detail && e.detail.name === 'id_fornecedor') {
                console.log('Evento select-change para fornecedor detectado');
                setTimeout(atualizarCnpj, 100);
            }
        });
        
        // Capturar evento específico do fornecedor (se estiver sendo emitido)
        window.addEventListener('id_fornecedor:selected', function(e) {
            console.log('Evento id_fornecedor:selected detectado');
            setTimeout(atualizarCnpj, 100);
        });
        
        // Inicializar se já tiver um valor
        console.log('Verificando valor inicial');
        setTimeout(function() {
            const idFornecedor = hiddenInput ? hiddenInput.value : (fornecedorSelect ? fornecedorSelect.value : null);
            if (idFornecedor) {
                console.log('Valor inicial encontrado:', idFornecedor);
                atualizarCnpj();
            }
        }, 500);
        
        // Função para debug - permite que você teste manualmente
        window.testeBuscarCnpj = function(id) {
            console.log('Teste manual iniciado para ID:', id);
            if (hiddenInput) hiddenInput.value = id;
            if (fornecedorSelect) fornecedorSelect.value = id;
            atualizarCnpj();
        };
    }

    // ======================================================
    // FUNÇÕES DE EXCLUSÃO DE ABASTECIMENTO
    // ======================================================
    function confirmarExclusao(id) {
        if (confirm('Tem certeza que deseja excluir este abastecimento?')) {
            excluirAbastecimento(id);
        }
    }
    
    function excluirAbastecimento(id) {
        // Obter o token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            console.error('CSRF token não encontrado');
            alert('Erro de segurança: CSRF token não encontrado.');
            return;
        }
        
        // Mostrar indicador de carregamento se disponível
        const loadingElement = document.getElementById('table-loading');
        if (loadingElement) loadingElement.style.display = 'flex';
        
        // Executar a exclusão
        fetch(`/admin/abastecimentomanual/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Tentar remover a linha da tabela sem recarregar a página
                const removido = removeRowFromTable(id);
                
                if (removido) {
                    // Se conseguiu remover a linha, mostrar mensagem
                    showToast('Abastecimento excluído com sucesso', 'success');
                    
                    // Esconder o loading se disponível
                    if (loadingElement) loadingElement.style.display = 'none';
                } else {
                    // Se não conseguiu remover a linha, recarregar a página
                    showToast('Abastecimento excluído com sucesso. Recarregando...', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                }
            } else {
                throw new Error(data.message || 'Erro ao excluir abastecimento');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            
            // Esconder o loading se disponível
            if (loadingElement) loadingElement.style.display = 'none';
            
            // Mostrar mensagem de erro
            showToast('Erro ao excluir abastecimento: ' + error.message, 'error');
        });
    }
    
    function removeRowFromTable(id) {
        try {
            // Tentar encontrar a linha na tabela
            const row = document.querySelector(`tr[data-id="${id}"]`);
            
            if (row) {
                // Animar a remoção da linha
                row.style.transition = 'opacity 0.3s ease-out';
                row.style.opacity = '0';
                
                // Remover a linha após a animação
                setTimeout(() => {
                    row.remove();
                    
                    // Verificar se a tabela ficou vazia
                    const tbody = document.querySelector('table tbody');
                    if (tbody && tbody.children.length === 0) {
                        // Adicionar mensagem de tabela vazia
                        const colSpan = document.querySelectorAll('table thead th').length;
                        const emptyRow = document.createElement('tr');
                        emptyRow.innerHTML = `<td colspan="${colSpan}" class="px-6 py-4 text-center text-gray-500">Nenhum registro encontrado</td>`;
                        tbody.appendChild(emptyRow);
                    }
                }, 300);
                
                return true;
            }
            
            return false;
        } catch (error) {
            console.error('Erro ao remover linha da tabela:', error);
            return false;
        }
    }
    
    // ======================================================
    // FUNÇÕES UTILITÁRIAS
    // ======================================================
    function showToast(message, type = 'info') {
        // Verificar se existe alguma biblioteca de toast
        if (typeof window.toast === 'function') {
            window.toast(message, type);
        } else if (window.Toastify) {
            // Toastify
            Toastify({
                text: message,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: type === 'success' ? "#48BB78" : type === 'error' ? "#F56565" : "#4299E1"
            }).showToast();
        } else {
            // Fallback para alert em desenvolvimento
            if (type === 'error') {
                alert(message);
            } else {
                // Em produção, criar um toast simples
                const toast = document.createElement('div');
                toast.style.position = 'fixed';
                toast.style.right = '20px';
                toast.style.top = '20px';
                toast.style.padding = '12px 20px';
                toast.style.backgroundColor = type === 'success' ? '#48BB78' : type === 'error' ? '#F56565' : '#4299E1';
                toast.style.color = 'white';
                toast.style.borderRadius = '4px';
                toast.style.zIndex = '9999';
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease-in-out';
                toast.textContent = message;
                
                document.body.appendChild(toast);
                
                // Mostrar com animação
                setTimeout(() => { toast.style.opacity = '1'; }, 10);
                
                // Remover após 3 segundos
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 300);
                }, 3000);
            }
        }
    }
</script>