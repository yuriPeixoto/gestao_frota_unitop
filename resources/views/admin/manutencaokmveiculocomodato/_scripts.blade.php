<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o Km Veículos em Comodato?')) {
            excluirKmVeiculoComodato(id);
        }
    }

    function excluirKmVeiculoComodato(id) {
        fetch(`/admin/manutencaokmveiculocomodato/${id}`, {
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
                    alert(' Km Veículos em Comodato excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir  Km Veículos em Comodato');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir  Km Veículos em Comodato');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>
