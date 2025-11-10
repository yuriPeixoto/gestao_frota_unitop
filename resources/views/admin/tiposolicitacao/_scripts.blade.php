<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o Tipo Solicitação')) {
            excluirRegistro(id);
        }
    }

    function excluirRegistro(id) {
        fetch(`/admin/tiposolicitacao/${id}`, {
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
                    alert('Tipo Silicitação excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Tipo Solicitação');
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Tipo Solicitação');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>