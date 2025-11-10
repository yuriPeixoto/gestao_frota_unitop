<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o Tipo de Equipamentos?')) {
            excluirTipoEquipamento(id);
        }
    }

    function excluirTipoEquipamento(id) {
        fetch(`/admin/tipoequipamentos/${id}`, {
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
                   alert('Tipo Equipamento excluído com sucesso!');
                    window.location.reload();
                } else {
                    alert(data.message || ' Erro ao excluir tipo equipamento');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir tipo equipamento');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>