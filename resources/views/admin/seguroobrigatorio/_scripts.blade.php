<script>
    // Callback global para atualização de dados do veículo
    function atualizarDadosVeiculoCallback(idVeiculo, option) {
        if (window.seguroObrigatorioFormComponent) {
            window.seguroObrigatorioFormComponent.atualizarDadosVeiculo(idVeiculo);
        }
    }

    function seguroObrigatorioForm() {
        const component = {
            chassiVeiculo: '{{ $seguroObrigatorio->veiculo->chassi ?? '' }}',
            filialVeiculo: '{{ $seguroObrigatorio->veiculo->filial->name ?? '' }}',


            async atualizarDadosVeiculo(idVeiculo) {
                if (!idVeiculo) return;

                try {
                    const response = await fetch(`/admin/api/veiculos/${idVeiculo}/dados`);
                    const data = await response.json();
                    this.chassiVeiculo = data.chassi || 'N/A';
                    this.filialVeiculo = data.filial_veiculo || 'N/A';
                } catch (error) {
                    console.error('Erro ao buscar dados do veículo:', error);
                }
            },

            init() {
                // Expor a instância para acesso global
                window.seguroObrigatorioFormComponent = this;

                // Ouvir eventos de seleção dos smart-selects
                window.addEventListener('id_veiculo:selected', (event) => {
                    const detail = event.detail;
                    if (detail && detail.value) {
                        this.atualizarDadosVeiculo(detail.value);
                    }
                });
            }
        };

        return component;
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableLoading = document.getElementById('table-loading');
        const resultsTable = document.getElementById('results-table');

        // Função para verificar se a tabela está completamente carregada
        function checkTableReady() {
            if (document.querySelectorAll('#results-table table tbody tr').length > 0) {
                // Esconde o loading e mostra a tabela com uma pequena transição
                setTimeout(function() {
                    tableLoading.style.opacity = '0';
                    resultsTable.classList.remove('opacity-0');

                    // Remove completamente o loading após a transição
                    setTimeout(function() {
                        tableLoading.style.display = 'none';
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
                tableLoading.style.display = 'flex';
                tableLoading.style.opacity = '1';
                resultsTable.classList.add('opacity-0');
            });
        }

        // Se estiver usando HTMX, intercepta os eventos
        document.body.addEventListener('htmx:beforeRequest', function(evt) {
            if (evt.detail.target.id === 'results-table') {
                tableLoading.style.display = 'flex';
                tableLoading.style.opacity = '1';
                resultsTable.classList.add('opacity-0');
            }
        });

        document.body.addEventListener('htmx:afterRequest', function(evt) {
            if (evt.detail.target.id === 'results-table') {
                setTimeout(function() {
                    tableLoading.style.opacity = '0';
                    resultsTable.classList.remove('opacity-0');

                    setTimeout(function() {
                        tableLoading.style.display = 'none';
                    }, 300);
                }, 300);
            }
        });
    });

    function confirmarExclusao(id) {
        if (confirm('Tem certeza que deseja desativar este seguro obrigatório?')) {
            excluirSeguroObrigatorio(id);
        }
    }

    function excluirSeguroObrigatorio(id) {
        fetch(`/admin/seguroobrigatorio/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Seguro obrigatório desativar com sucesso!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao desativar seguro obrigatório');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao desativar seguro obrigatório');
            });
    }
</script>
