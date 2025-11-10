<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o Planejamento Manutencão')) {
            excluirmanutencao(id);
        }
    }

    function excluirmanutencao(id) {
        fetch(`/admin/manutencaoservico/${id}`, {
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
                    alert('Planejamento Manutencão excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Planejamento Manutencão');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Planejamento Manutencão');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>
