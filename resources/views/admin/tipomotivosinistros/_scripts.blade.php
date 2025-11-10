<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o Tipo Motivo Sinistros')) {
            excluirRegistro(id);
        }
    }

    function excluirRegistro(id) {
        fetch(`/admin/tipomotivosinistros/${id}`, {
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
                    alert('Tipo Motivo Sinistros excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Tipo Motivo Sinistros');
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Tipo Motivo Sinistros');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>
