<script>
    function onCancel(id) {
        if (confirm('Deseja Inativar a Requisição do pneu?')) {
            fetch(`/admin/requisicaopneusvendassaida/cancelar/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Registro inativado com sucesso!!!');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Erro ao inativar o registro');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao inativar o registro');
                });
        }
    }
</script>
