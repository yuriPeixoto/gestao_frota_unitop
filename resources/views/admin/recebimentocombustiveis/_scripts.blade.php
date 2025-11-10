<script>
    // ======================================================
    // INICIALIZAÇÃO GERAL DO DOCUMENTO
    // ======================================================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM carregado - Inicializando scripts para RecebimentoCombustivel');
        
        // Inicializar manipulação da tabela
        initTableLoading();
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
    // FUNÇÕES DE EXCLUSÃO DE RECEBIMENTO DE COMBUSTÍVEL
    // ======================================================
    function confirmarExclusao(id) {
        if (confirm('Tem certeza que deseja excluir este recebimento de combustível?')) {
            excluirRecebimento(id);
        }
    }
    
    function excluirRecebimento(id) {
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
        fetch(`/admin/recebimentocombustiveis/${id}`, {
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
                    showToast('Recebimento excluído com sucesso', 'success');
                    
                    // Esconder o loading se disponível
                    if (loadingElement) loadingElement.style.display = 'none';
                } else {
                    // Se não conseguiu remover a linha, recarregar a página
                    showToast('Recebimento excluído com sucesso. Recarregando...', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                }
            } else {
                throw new Error(data.message || 'Erro ao excluir recebimento');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            
            // Esconder o loading se disponível
            if (loadingElement) loadingElement.style.display = 'none';
            
            // Mostrar mensagem de erro
            showToast('Erro ao excluir recebimento: ' + error.message, 'error');
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

