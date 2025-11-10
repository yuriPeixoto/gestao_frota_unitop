<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o NF Rateio')) {
            excluirmanutencao(id);
        }
    }

    function excluirmanutencao(id) {
        fetch(`/admin/listagemoslacamentoservicorateio/${id}`, {
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
                    alert('NF Rateio excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir NF Rateio');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir NF Rateio');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>

<script>
    function manutencaoServico() {
        return {
            items: @json($cadastros->servicos ?? []),
            novoItem: {
                data_inclusao: new Date().toLocaleString(),
                descricao_servico: '',
                id_servico: '',
                valor_produto: '',
                quantidade: '',
                total_produto: ''
            },

            adicionarItem() {
                const servicoSelect = id_servico;
                const smartSelect = document.querySelector(
                    '[x-data*="asyncSearchableSelect"][x-data*="id_servico"]');
                const span = smartSelect.querySelector('span');


                if (!servicoSelect || servicoSelect.selectedIndex === -1 || servicoSelect.value === '') {
                    alert('Por favor, selecione um serviço.');
                    return;
                }

                // Captura os valores dos outros campos
                const valorProdutoElement = document.querySelector("[name='valor_produto']");
                const quantidadeElement = document.querySelector("[name='quantidade']");
                const totalProdutoElement = document.querySelector("[name='total_produto']");

                if (!valorProdutoElement || !quantidadeElement || !totalProdutoElement) {
                    alert('Erro: campos não encontrados.');
                    return;
                }

                if (servicoSelect.value && !valorProdutoElement.value || !valorProdutoElement.value || !
                    valorProdutoElement.value) {
                    alert(
                        'Por favor, preencha os campos Quantiddade, Valor Produto e Total produto para continuar.'
                    );
                    return;
                }

                const valorProduto = valorProdutoElement.value;
                const quantidade = quantidadeElement.value;
                const totalProduto = totalProdutoElement.value;

                // Atualiza o objeto novoItem com os valores capturados
                this.novoItem.descricao_servico = span.textContent;
                this.novoItem.id_servico = id_servico.value;
                this.novoItem.valor_produto = valorProduto;
                this.novoItem.quantidade = quantidade;
                this.novoItem.total_produto = totalProduto;

                // Adiciona o novo item à lista
                this.items.push({
                    ...this.novoItem
                });

                // Limpa o formulário para o próximo item
                this.limparNovoItem();
            },

            editarItem(index) {
                const item = this.items[index];
                this.novoItem = {
                    ...item
                };

                // Preenche os campos do formulário com os valores do item selecionado
                const servicoSelect = document.getElementById('id_servico');
                const smartSelect = document.querySelector(
                    '[x-data*="asyncSearchableSelect"][x-data*="id_servico"]');
                const span = smartSelect.querySelector('span');
                const valorProdutoElement = document.getElementById('valor_produto');
                const quantidadeElement = document.getElementById('quantidade');
                const totalProdutoElement = document.getElementById('total_produto');

                if (smartSelect) {
                    servicoSelect.value = item.id_servico;
                    span.textContent = item.servico.descricao_servico;
                }
                if (valorProdutoElement) valorProdutoElement.value = item.valor_produto;
                if (quantidadeElement) quantidadeElement.value = item.quantidade;
                if (totalProdutoElement) totalProdutoElement.value = item.total_produto;

                // Remove o item da lista para edição
                this.items.splice(index, 1);
            },

            removerItem(index) {
                this.items.splice(index, 1);
            },

            limparNovoItem() {
                const smartSelect = document.querySelector(
                    '[x-data*="asyncSearchableSelect"][x-data*="id_servico"]');
                const span = smartSelect.querySelector('span');

                this.novoItem = {
                    data_inclusao: new Date().toLocaleString(),
                    descricao_servico: '',
                    id_servico: '',
                    valor_produto: '',
                    quantidade: '',
                    total_produto: '',
                };

                // Limpa os campos do formulário
                const servicoSelect = document.getElementById('id_servico');
                const valorProdutoElement = document.getElementById('valor_produto');
                const quantidadeElement = document.getElementById('quantidade');
                const totalProdutoElement = document.getElementById('total_produto');

                if (servicoSelect) {
                    servicoSelect.selectedIndex = 0;
                    span.textContent = 'Selecione...';
                }
                if (valorProdutoElement) valorProdutoElement.value = '';
                if (quantidadeElement) quantidadeElement.value = '';
                if (totalProdutoElement) totalProdutoElement.value = '';
            },

            limparFormulario() {
                if (!confirm('Deseja realmente limpar todos os dados do formulário?')) return;

                this.items = [];
                this.limparNovoItem();

                const form = document.getElementById('manutencaoServico');
                if (form) {
                    form.reset();
                }
            },

            formatarValorProduto(event) {
                let valor = event.target.value;

                // Remove tudo que não é dígito
                valor = valor.replace(/\D/g, '');

                // Se não tem valor, limpa o campo
                if (!valor) {
                    event.target.value = '';
                    return;
                }

                // Converte para centavos
                valor = parseInt(valor);

                // Formata como moeda brasileira
                const valorFormatado = (valor / 100).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Atualiza o campo
                event.target.value = valorFormatado;
            },

            calcularTotal() {
                const quantidadeElement = document.getElementById('quantidade');
                const valorProdutoElement = document.getElementById('valor_produto');
                const totalProdutoElement = document.getElementById('total_produto');

                if (!quantidadeElement || !valorProdutoElement || !totalProdutoElement) {
                    console.log('Erro: campos não encontrados.');
                    return;
                }

                // Obtendo os valores
                const quantidade = parseFloat(quantidadeElement.value) || 0;
                const valorProdutoStr = valorProdutoElement.value || "0";

                // Remove R$ e converte valor formatado para número
                const valorProduto = parseFloat(
                    valorProdutoStr
                    .replace('R$', '') // Remove R$
                    .replace(/\s/g, '') // Remove espaços
                    .replace(/\./g, '') // Remove pontos (separadores de milhares)
                    .replace(',', '.') // Troca vírgula por ponto decimal
                ) || 0;

                // Fazendo o cálculo
                const total = quantidade * valorProduto;

                // Formatando o resultado
                const totalFormatado = total.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Atualizando o campo total_produto
                totalProdutoElement.value = totalFormatado;
            },

            formatarData(data) {
                // Se não houver data, ou se for inválida, use a data atual
                if (!data || new Date(data).toString() === 'Invalid Date') {
                    return new Date().toLocaleString('pt-BR', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        timeZone: 'America/Cuiaba'
                    });
                }

                const dataObj = new Date(data);
                return dataObj.toLocaleString('pt-BR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    timeZone: 'America/Cuiaba'
                });
            }
        }
    }
</script>
