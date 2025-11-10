<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o Serviço X Prestador')) {
            excluirServico(id);
        }
    }

    function excluirServico(id) {
        fetch(`/admin/servicofornecedor/${id}`, {
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
                    alert('Serviço X Prestador excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Serviço X Prestador');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Serviço X Prestador');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>

<script>
    function formatarMoedaBrasileira(input) {
        // Remove tudo que não é dígito
        let valor = input.value.replace(/\D/g, '');

        // Se estiver vazio, retorna vazio
        if (valor === '') {
            input.value = '';
            return;
        }

        // Converte para número e divide por 100 para obter os centavos
        const valorNumerico = parseInt(valor, 10) / 100;

        // Formata para o padrão brasileiro
        input.value = valorNumerico.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2
        });

        // Mantém o cursor na posição correta
        const length = input.value.length;
        input.setSelectionRange(length, length);
    }
</script>
