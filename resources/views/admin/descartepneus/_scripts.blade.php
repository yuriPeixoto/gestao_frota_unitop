<script>
    let idSelecionado = null;

    function showPneu(id) {
        window.location.href = `{{ route('admin.descartepneus.show', ':id') }}`.replace(':id', id);
    }

    function editPneu(id) {
        window.location.href = `{{ route('admin.descartepneus.edit', ':id') }}`.replace(':id', id)
    }

    function destroyPneu(id, nome) {
        showModal('delete-pneu');
        idSelecionado = id;
        domEl('.bw-delete-pneu .title').innerText = nome;
    }

    function confirmDelete() {
        showButtonSpinner('.botao-delete');
        console.log(idSelecionado)
        fetch(`{{ route('admin.descartepneus.destroy', ':id') }}`.replace(':id', idSelecionado), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
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
                'Não foi possível excluir o descarte',
                'error'
            );
        });
    }

    @if (session('notification') && is_array(session('notification')))
        showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}',
            '{{ session('notification')['type'] }}');
        setTimeout(() => {
            window.location.reload();
        }, 500);
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

    document.getElementById('search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            executeSearch();
        }
    });
</script>

<script>
    function destroyOrdemServico(id) {
        showModal('delete-autorizacao');
        autorizacaooId = id;
        domEl('.bw-delete-autorizacao .title').innerText = id;
    }

    function confirmarExclusao(id) {
        excluirOrdemServico(id);
    }


    function excluirOrdemServico(id) {
        fetch(`/admin/descartepneus/${id}`, {
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
</script>


<script>
    function formatarMoedaBrasileira(input) {
        let valor = input.value.replace(/\D/g, '');
        if (valor === '') {
            input.value = '';
            return;
        }
        const valorNumerico = parseInt(valor, 10) / 100;
        input.value = valorNumerico.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2
        });
        const length = input.value.length;
        input.setSelectionRange(length, length);
    }
</script>
