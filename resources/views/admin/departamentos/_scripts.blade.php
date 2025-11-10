<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o Tipo Departamento')) {
            excluirRegistro(id);
        }
    }

    function excluirRegistro(id) {
        fetch(`/admin/departamentos/${id}`, {
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
                    alert('Tipo Departamento excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Tipo Departamento');
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Tipo Departamento');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>
