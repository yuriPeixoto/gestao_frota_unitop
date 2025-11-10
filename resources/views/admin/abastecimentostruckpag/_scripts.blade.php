<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o Abastecimento?')) {
            excluirAbastecimento(id);
        }
    }

    function excluirAbastecimento(id) {
        fetch(`/admin/abastecimentomanual/${id}`, {
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
                    alert('Abastecimento excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir abastecimento');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir abastecimento');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>
