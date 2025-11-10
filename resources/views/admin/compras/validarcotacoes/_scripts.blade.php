<script>
    // Carregar cotações automaticamente quando a página carregar
    document.addEventListener('DOMContentLoaded', function() {
        processarCotacoes();
    });

    function processarCotacoes() {
        const idSolicitacao = document.querySelector('[name="solicitacoes_compra_consulta"]').value;

        if (!idSolicitacao) {
            console.log('ID da solicitação não encontrado');
            return;
        }

        // Mostrar indicador de carregamento
        for (let i = 1; i <= 3; i++) {
            document.getElementById(`cotacao-0${i}-itens`).innerHTML =
                '<div class="text-sm text-gray-500 p-3 text-center"><div class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full mr-2"></div>Carregando...</div>';
        }

        fetch(`/admin/compras/validarcotacoes/cotacoes/${idSolicitacao}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição');
                }
                return response.json();
            })
            .then(data => {
                // Limpar campos antes de preencher
                for (let i = 1; i <= 3; i++) {
                    document.getElementById(`cotacao-0${i}-codigo`).value = '';
                    document.getElementById(`cotacao-0${i}-fornecedor`).textContent = '';
                    document.getElementById(`cotacao-0${i}-itens`).innerHTML =
                        '<div class="text-sm text-gray-500 p-3 text-center">Nenhum registro foi encontrado</div>';
                }

                if (data.length === 0) {
                    // Manter mensagem padrão se não houver cotações
                    return;
                }

                // Preencher as cotações encontradas
                data.slice(0, 3).forEach((cotacao, index) => {
                    const cotacaoNum = String(index + 1).padStart(2, '0');

                    console.log(cotacao);

                    // Preencher código da cotação
                    document.getElementById(`cotacao-${cotacaoNum}-codigo`).value = cotacao.numero;

                    // Preencher fornecedor
                    if (cotacao.fornecedor && cotacao.fornecedor !== 'N/A') {
                        document.getElementById(`cotacao-${cotacaoNum}-fornecedor`).textContent = cotacao
                            .fornecedor;
                    }

                    // Preencher itens
                    let itensHtml = '';
                    if (cotacao.itens_detalhados && cotacao.itens_detalhados.length > 0) {
                        itensHtml = cotacao.itens_detalhados.map(item => `
                        <div class="grid grid-cols-5 gap-2 text-xs p-2 border-b border-gray-100">
                            <div class="truncate text-gray-800 font-medium" title="${item.descricao}">${item.descricao}</div>
                            <div class="text-center text-gray-800">${item.quantidade}</div>
                            <div class="text-center text-gray-800">R$ ${parseFloat(item.valor_unitario).toFixed(2).replace('.', ',')}</div>
                            <div class="text-center text-gray-800">R$ ${parseFloat(item.valor_bruto).toFixed(2).replace('.', ',')}</div>
                            <div class="text-center text-gray-800">R$ ${parseFloat(item.valor_desconto).toFixed(2).replace('.', ',')}</div>
                        </div>
                    `).join('');

                        // Adicionar total
                        itensHtml += `
                        <div class="grid grid-cols-4 gap-2 text-sm font-bold p-2 bg-gray-100">
                            <div class="col-span-2 text-right">Total:</div>
                            <div class="text-center">R$ ${cotacao.valores}</div>
                            <div class="text-center">R$ ${cotacao.valoresDesconto}</div>
                        </div>
                    `;
                    } else {
                        itensHtml =
                            '<div class="text-sm text-gray-500 p-3 text-center">Nenhum registro foi encontrado</div>';
                    }

                    document.getElementById(`cotacao-${cotacaoNum}-itens`).innerHTML = itensHtml;
                });
            })
            .catch(error => {
                console.error('Erro:', error);
                // Restaurar mensagem padrão em caso de erro
                for (let i = 1; i <= 3; i++) {
                    document.getElementById(`cotacao-0${i}-itens`).innerHTML =
                        '<div class="text-sm text-red-500 p-3 text-center">Erro ao carregar cotações</div>';
                }
            });
    }

    function validarCotacao() {
        const idSolicitacao = document.querySelector('[name="solicitacoes_compra_consulta"]').value;
        const observacao = document.querySelector('[name="observacao"]').value;

        if (!idSolicitacao) {
            alert('Digite o código da solicitação');
            return;
        }

        if (confirm('Tem certeza que deseja validar esta cotação?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('admin.compras.validarcotacoes.validar') }}';

            // Token CSRF
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            form.appendChild(tokenInput);

            // ID da solicitação
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id_solicitacao_compras';
            idInput.value = idSolicitacao;
            form.appendChild(idInput);

            // Observação
            const obsInput = document.createElement('input');
            obsInput.type = 'hidden';
            obsInput.name = 'observacao';
            obsInput.value = observacao;
            form.appendChild(obsInput);

            document.body.appendChild(form);
            form.submit();
        }
    }

    function recusarCotacao() {
        const idSolicitacao = document.querySelector('[name="solicitacoes_compra_consulta"]').value;
        const observacao = document.querySelector('[name="observacao"]').value;

        if (!idSolicitacao) {
            alert('Digite o código da solicitação');
            return;
        }

        if (!observacao.trim()) {
            alert('Por favor, justifique a ação desejada no campo de observação!');
            return;
        }

        if (confirm('Tem certeza que deseja recusar esta cotação?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('admin.compras.validarcotacoes.recusar') }}';

            // Token CSRF
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            form.appendChild(tokenInput);

            // ID da solicitação
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id_solicitacao_compras';
            idInput.value = idSolicitacao;
            form.appendChild(idInput);

            // Observação
            const obsInput = document.createElement('input');
            obsInput.type = 'hidden';
            obsInput.name = 'observacao';
            obsInput.value = observacao;
            form.appendChild(obsInput);

            document.body.appendChild(form);
            form.submit();
        }
    }

    function cancelarCotacao() {
        const idSolicitacao = document.querySelector('[name="solicitacoes_compra_consulta"]').value;

        if (!idSolicitacao) {
            alert('Digite o código da solicitação');
            return;
        }

        if (confirm('Tem certeza que deseja cancelar esta cotação? Esta ação não pode ser desfeita.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('admin.compras.validarcotacoes.cancelar') }}';

            // Token CSRF
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            form.appendChild(tokenInput);

            // ID da solicitação
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id_solicitacao_compras';
            idInput.value = idSolicitacao;
            form.appendChild(idInput);

            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
