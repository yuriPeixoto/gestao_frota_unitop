<script>
    document.addEventListener('DOMContentLoaded', function() {
                const tableLoading = document.getElementById('table-loading');
                const resultsTable = document.getElementById('results-table');
                
                // Função para verificar se a tabela está completamente carregada
                function checkTableReady() {
                    if (document.querySelectorAll('#results-table table tbody tr').length > 0 || 
                        document.querySelectorAll('#results-table .empty-message').length > 0) {
                        // Esconde o loading e mostra a tabela com uma pequena transição
                        setTimeout(function() {
                            if (tableLoading) tableLoading.style.opacity = '0';
                            if (resultsTable) resultsTable.classList.remove('opacity-0');
                            
                            // Remove completamente o loading após a transição
                            setTimeout(function() {
                                if (tableLoading) tableLoading.style.display = 'none';
                            }, 300);
                        }, 300);
                    } else {
                        // Tenta novamente em 100ms se ainda não estiver pronto
                        setTimeout(checkTableReady, 100);
                    }
                }
                
                // Inicia a verificação
                setTimeout(checkTableReady, 500);
                
                // Mostra loading quando o formulário de busca for submetido
                const searchForm = document.querySelector('form');
                if (searchForm) {
                    searchForm.addEventListener('submit', function() {
                        if (tableLoading) {
                            tableLoading.style.display = 'flex';
                            tableLoading.style.opacity = '1';
                        }
                        if (resultsTable) resultsTable.classList.add('opacity-0');
                    });
                }
            });
</script>