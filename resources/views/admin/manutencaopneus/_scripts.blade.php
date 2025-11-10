<script>
    // exclusao da Manutenção
    function confirmarExclusao(id) {
        if (confirm('Tem certeza que deseja excluir esta manutenção?')) {
            excluirOrdemServico(id);
        }
    }

    function excluirOrdemServico(id) {
        fetch(`{{ route('admin.manutencaopneus.destroy', ':id') }}`.replace(':id', id), {
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
                alert(
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

            alert(
                'Erro',
                error.message,
                'error'
            );
        });
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modeloPneu = document.querySelector('input[name="id_tipo_modelo_pneu"]');

        // Escuta id_pneu
        onSmartSelectChange('id_pneu', function(data) {
            if (data.value != null) {
                const idPneu = data.value;

                if (!idPneu) {
                    console.log('Número de fogo não encontrado.');
                    return;
                }

                // Configure os headers corretamente
                const headers = {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };

                fetch(`/admin/pneus/api/${idPneu}`, {
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
                        if (data) {
                            if (data.data.modelo.descricao_modelo != null) {
                                modeloPneu.value = data.data.modelo.descricao_modelo;

                            } else {
                                modeloPneu.value = 'Modelo não informado';
                            }
                        } else {
                            modeloPneu.value = 'Dados não encontrados';
                        }
                    })
                    .catch(err => {
                        console.error('Erro ao buscar dados do veículo:', err);
                        modeloPneu.value = 'Erro ao buscar';
                    });
            }
        });
    });
</script>
