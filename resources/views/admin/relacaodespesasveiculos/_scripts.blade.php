<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o despesas veiculos')) {
            excluirServico(id);
        }
    }

    function excluirServico(id) {
        fetch(`/admin/relacaodespesasveiculos/${id}`, {
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
                    alert('despesas veiculos excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir despesas veiculos');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir despesas veiculos');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>
