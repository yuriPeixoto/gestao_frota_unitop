<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o Tipo Categoria Veiculo')) {
            excluirRegistro(id);
        }
    }

    function excluirRegistro(id) {
        fetch(`/admin/tipocategorias/${id}`, {
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
                    alert('Tipo Categoria Veiculo excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Tipo Categoria Veiculo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Tipo Categoria Veiculo');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>
