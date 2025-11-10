<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir a Transferência Pneus?')) {
            excluirTransferenciaPneus(id);
        }
    }

    function excluirTransferenciaPneus(id) {
        fetch(`/admin/transferenciapneus/${id}`, {
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
                    alert('Transferência excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Transferência');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Transferência');
            });
    }
</script>

<script>
    function onYesFinalizarBaixaPneu(id) {
        if (confirm('Deseja finalizar a baixa do pneu?')) {
            fetch(`/admin/transferenciapneus/finalizar/${id}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Transferência finalizada com sucesso');
                        window.location.href = @json(route('admin.transferenciapneus.index'));
                    } else {
                        alert(data.message || 'Erro ao finalizar Transferência');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao finalizar Transferência');
                });
        }
    }
</script>
