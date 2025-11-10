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

    function destroyOrdemServico(id) {
        showModal('delete-autorizacao');
        autorizacaooId = id;
        domEl('.bw-delete-autorizacao .title').innerText = id;
    }

    function confirmarExclusao(id) {
        excluirOrdemServico(id);
    }


    function excluirOrdemServico(id) {
        fetch(`/admin/testefumacas/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(errorText => {
                        throw new Error(`HTTP error! status: ${response.status}, text: ${errorText}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.notification) {
                    console.log(data.notification);
                    showNotification(
                        data.notification.title,
                        data.notification.message,
                        data.notification.type
                    );

                    // setTimeout(() => {
                    window.location.reload();
                    // }, 500);
                }
            })
            .catch(error => {
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
</script>

<script>
    window.addEventListener('id_veiculo:selected', function(event) {
        const idVeiculo = event.detail.value;
        const filial = document.getElementById('prefixo');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const headers = {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        if (idVeiculo) {
            fetch(`/admin/testefumacas/${idVeiculo}/getFilial`, {
                    method: 'GET',
                    headers: headers,
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na resposta da API: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Resposta da API:', data);
                    if (data) {
                        filial.value = data;
                    } else {
                        console.log('Filial näo encontrada');
                    }
                })
                .catch(err => {
                    console.error('Erro ao buscar dados do veículo:', err);
                    renavamInput.value = 'Erro ao buscar';
                });
        }
    });
</script>

{{-- clonar registro --}}
<script>
    function cloneUser(id) {
        if (confirm('Tem certeza que deseja clonar esse usuário?')) {
            const url = `testefumacas/${id}/replica`;

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
                        window.location.href = "{{ route('admin.testefumacas.index') }}";
                    }
                })
                .catch(error => {
                    console.error('Erro ao replicar o registro:', error);
                    alert('Ocorreu um erro ao replicar o registro. Por favor, tente novamente.');
                });
        }
    }
</script>
