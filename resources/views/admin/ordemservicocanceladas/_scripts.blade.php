<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir a Ordem de Serviço?')) {
            excluirOSCancelada(id);
        }
    }

    function excluirOSCancelada(id) {
        fetch(`/admin/ordemservicocanceladas/${id}`, {
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
                    alert('O registro foi excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir registro');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir o registro');
            });
    }
</script>
