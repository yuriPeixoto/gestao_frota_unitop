<script>
    let licenciamentoId = null;

    function editLicenciamento(id) {
        window.location.href = `{{ route('admin.licenciamentoveiculos.edit', ':id') }}`.replace(':id', id)
    }

    function destroylicenciamento(id, nome) {
        showModal('delete-licenciamento');
        licenciamentoId = id;
        domEl('.bw-delete-licenciamento .title').innerText = nome;
    }

    function confirmDeleteLicenciamento() {
        fetch(`{{ route('admin.licenciamentoveiculos.destroy', ':id') }}`.replace(':id', licenciamentoId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }).then(response => {
            if (!response.ok) {
                return response.text().then(errorText => {
                    console.error('Error response text:', errorText);
                    throw new Error(`HTTP error! status: ${response.status}, text: ${errorText}`);
                });
            }
            return response.json();
        }).then(data => {
            if (data.notification) {
                showNotification(
                    data.notification.title,
                    data.notification.message,
                    data.notification.type
                );

                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        }).catch(error => {
            console.error('Full error:', error);

            showNotification(
                'Erro',
                error.message,
                'error'
            );
        });
    }

    @if (session('notification') && is_array(session('notification')))
        showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}',
            '{{ session('notification')['type'] }}');
    @endif

    function executeSearch() {
        const searchTerm = document.getElementById('search-input').value;
        const currentUrl = new URL(window.location.href);

        if (searchTerm) {
            currentUrl.searchParams.set('search', searchTerm);
        } else {
            currentUrl.searchParams.delete('search');
        }

        window.location.href = currentUrl.toString();
    }

    function clearSearch() {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.delete('search');
        window.location.href = currentUrl.toString();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('search-input');
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    executeSearch();
                }
            });
        }
    });
    
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

        function licenciamentoVeiculosSearchForm() {
        return {
            filial: "{{ isset($licenciamentoveiculos) && $licenciamentoveiculos->veiculo && $licenciamentoveiculos->veiculo->filial ? $licenciamentoveiculos->veiculo->filial->name : 'Selecionar uma placa...' }}",

            init() {
                // Listener para atualizar a filial quando um veículo for selecionado
                window.addEventListener('id_veiculo:selected', (event) => {
                    if (event.detail && event.detail.value) {
                        this.atualizarDadosVeiculo(event.detail.value);
                    }
                });
            },

            atualizarDadosVeiculo(id) {
                this.filial = 'Carregando...'; // Mensagem temporária

                fetch('/admin/licenciamentoveiculos/get-vehicle-data', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            placa: id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.error) {
                            this.filial = String(data.filial || 'Selecionar uma placa...');
                        } else {
                            console.error('Erro ao buscar dados do veículo:', data.error);
                            this.filial = 'Erro ao carregar';
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar dados do veículo:', error);
                        this.filial = 'Erro ao carregar';
                    });
            }
        };
    }

</script>


{{-- clonar registro --}}
<script>
    function cloneUser(id) {
        if (confirm('Tem certeza que deseja clonar esse usuário?')) {
            const url = `licenciamentoveiculos/${id}/replica`;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na resposta do servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    alert(data.message || 'Registro replicado com sucesso!');
                    if (!data.error) {
                        window.location.href = "{{ route('admin.licenciamentoveiculos.index') }}";
                    }
                })
                .catch(error => {
                    console.error('Erro ao replicar o registro:', error);
                    alert('Ocorreu um erro ao replicar o registro. Por favor, tente novamente.');
                });
        }
    }
</script>