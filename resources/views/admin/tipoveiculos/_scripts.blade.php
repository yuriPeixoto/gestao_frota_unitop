<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o Tipo Veiculo?')) {
            excluirTipoVeiculo(id);
        }
    }

    function excluirTipoVeiculo(id) {
        fetch(`/admin/tipoveiculos/${id}`, {
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
                    alert('Tipo Veiculo excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Tipo Veiculo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Tipo Veiculo');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>
