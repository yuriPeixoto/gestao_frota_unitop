<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir situação ordem de serviço')) {
            excluirmanutencao(id);
        }
    }

    function excluirmanutencao(id) {
        fetch(`/admin/statusordemservico/${id}`, {
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
                    alert('situação ordem de serviço excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir situação ordem de serviço');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir situação ordem de serviço');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>
