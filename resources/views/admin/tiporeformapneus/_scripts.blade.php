<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o Tipo Reforma Pneus')) {
            excluirRegistro(id);
        }
    }

    function excluirRegistro(id) {
        fetch(`/admin/tiporeformapneus/${id}`, {
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
                    alert('Tipo Reforma Pneus excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Tipo Reforma Pneus');
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Tipo Reforma Pneus');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>
