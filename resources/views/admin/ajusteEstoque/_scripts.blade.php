<script>
    function confirmarExclusao(id) {
        if (confirm('Tem certeza que deseja excluir?')) {
            excluirAjusteEstoque(id);
        }
    }


    function excluirAjusteEstoque(id) {
        fetch(`/admin/ajuste-estoque/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw data;
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log(data);
                if (data.notification) {
                    console.log(data.notification);

                    Swal.fire({
                        title: data.notification.title,
                        text: data.notification.message,
                        icon: data.notification.type, // 'error', 'success', 'warning', 'info'
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            })
            .catch(error => {
                console.error('Full error:', error);

                if (error.notification) {
                    Swal.fire({
                        title: error.notification.title,
                        text: error.notification.message,
                        icon: error.notification.type,
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        title: 'Erro',
                        text: 'Erro inesperado. Verifique o console.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
    }
</script>

<script>
    async function selectCascade() {
        const idFilial = document.querySelector('select[name="id_filial"]').value;

        try {
            // Configure os headers corretamente
            const headers = {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            const response = await fetch(`/admin/ajuste-estoque/getEstoqueByFilial/${idFilial}`, {
                method: 'GET',
                headers: headers,
                credentials: 'same-origin'
            });


            if (!response.ok) {
                throw new Error(`Erro na resposta da API: ${response.status}`);
            }

            const retorno = await response.json();

            // Limpa as opções existentes antes de adicionar novas
            updateSmartSelectOptions('id_estoque', []);

            // Popula o select com as opções retornadas
            for (const item of retorno) {
                addSmartSelectOption('id_estoque', {
                    value: item.value,
                    label: item.label,
                });
            }

            // Retorna os dados para uso em outros scripts
            return retorno;

        } catch (error) {
            console.error('Erro ao buscar dados de manutenção:', error);

            // Limpa o select em caso de erro
            updateSmartSelectOptions('id_estoque', []);

            // Re-propaga o erro para tratamento externo
            throw error;
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        selectCascade();

        onSmartSelectChange('id_estoque', async function(data) {
            const idFilial = document.querySelector('select[name="id_filial"]').value;
            const idEstoque = data.value;

            try {
                // Configure os headers corretamente
                const headers = {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };

                const response = await fetch(
                    `/admin/ajuste-estoque/getProdutoByEstoque/${idFilial}/${idEstoque}`, {
                        method: 'GET',
                        headers: headers,
                        credentials: 'same-origin'
                    });

                if (!response.ok) {
                    throw new Error(`Erro na resposta da API: ${response.status}`);
                }

                const retorno = await response.json();

                // Limpa as opções existentes antes de adicionar novas
                updateSmartSelectOptions('id_produto', []);

                // Popula o select com as opções retornadas
                for (const item of retorno) {
                    addSmartSelectOption('id_produto', {
                        value: item.value,
                        label: item.label,
                    });
                }

                // Retorna os dados para uso em outros scripts
                return retorno;

            } catch (error) {
                console.error('Erro ao buscar dados de produto:', error);

                // Limpa o select em caso de erro
                updateSmartSelectOptions('id_produto', []);

                // Re-propaga o erro para tratamento externo
                throw error;
            }

        });

        onSmartSelectChange('id_produto', async function(data) {
            const idFilial = document.querySelector('select[name="id_filial"]').value;
            const quantidadeAtual = document.querySelector('input[name="quantidade_atual"]');
            const precoMedio = document.querySelector('input[name="preco_medio"]');
            const idProduto = data.value;

            try {
                // Configure os headers corretamente
                const headers = {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };

                const response = await fetch(
                    `/admin/ajuste-estoque/getEstoqueByProduto/${idFilial}/${idProduto}`, {
                        method: 'GET',
                        headers: headers,
                        credentials: 'same-origin'
                    });


                if (!response.ok) {
                    throw new Error(`Erro na resposta da API: ${response.status}`);
                }

                const retorno = await response.json();

                quantidadeAtual.value = retorno[0].quantidade_produto;
                precoMedio.value = retorno[0].valor_medio;


                // Retorna os dados para uso em outros scripts
                return retorno;

            } catch (error) {
                console.error('Erro ao buscar dados de manutenção:', error);

                // Re-propaga o erro para tratamento externo
                throw error;
            }

        });
    });
</script>
