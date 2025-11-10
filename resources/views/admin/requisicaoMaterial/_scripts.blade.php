{{-- função para obter dados do produto e preencher campos --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const estoque_filial = document.getElementById('estoque_filial');
        const codReqProduto = document.getElementById('codReqProduto');

        onSmartSelectChange('id_produto', function(data) {
            if (data.value) {
                buscarDadosProduto(data.value, function(produto) {
                    if (produto) {
                        estoque_filial.value = produto.quantidade_produto || '0';
                        codReqProduto.value = produto.codigo_produto || '';
                    } else {
                        estoque_filial.value = '0';
                        codReqProduto.value = '';
                        alert('Erro ao buscar informações do produto');
                    }
                });
            } else {
                // Limpar campos quando nenhum produto está selecionado
                estoque_filial.value = '0';
                codReqProduto.value = '';
            }
        });
    });

    // Função centralizada para buscar dados do produto
    function buscarDadosProduto(produtoId, callback) {
        if (!produtoId) {
            if (callback) callback(null);
            return;
        }

        fetch(`/admin/requisicaoMaterial/onProduto/${produtoId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.produto) {
                    if (callback) callback(data.produto);
                } else {
                    console.error('Erro ao buscar dados do produto:', data.message);
                    if (callback) callback(null);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (callback) callback(null);
            });
    }

    // Funções para controlar a modal de visualização do produto
    function abrirModalVisualizarProduto() {
        const produtoId = getSmartSelectValue('id_produto').value;

        if (!produtoId) {
            alert('Por favor, selecione um produto primeiro.');
            return;
        }

        const modal = document.getElementById('modalVisualizarProduto');
        modal.classList.remove('hidden');

        // Reset do conteúdo
        document.getElementById('loadingProduto').classList.remove('hidden');
        document.getElementById('dadosProduto').classList.add('hidden');
        document.getElementById('erroProduto').classList.add('hidden');
        document.getElementById('semImagemProduto').classList.add('hidden');

        // Buscar dados do produto usando a função centralizada
        buscarDadosProduto(produtoId, function(produto) {
            document.getElementById('loadingProduto').classList.add('hidden');

            if (produto) {
                mostrarDadosProduto(produto);
            } else {
                document.getElementById('erroProduto').classList.remove('hidden');
            }
        });
    }

    function mostrarDadosProduto(produto) {
        const temImagem = produto.imagem_produto && produto.imagem_produto.trim() !== '';

        if (temImagem) {
            // Mostrar versão com imagem
            const imagemElement = document.getElementById('imagemProduto');
            const imagemUrl = `/storage/produtos/${produto.imagem_produto}`;

            imagemElement.src = imagemUrl;
            imagemElement.alt = produto.descricao_produto;

            // Adicionar tratamento de erro para imagem
            imagemElement.onerror = function() {
                // Se a imagem não carregar, mostrar versão sem imagem
                document.getElementById('dadosProduto').classList.add('hidden');
                document.getElementById('codigoProdutoSemImagem').textContent = produto.codigo_produto || produto
                    .id_produto;
                document.getElementById('descricaoProdutoSemImagem').textContent = produto.descricao_produto;
                document.getElementById('estoqueProdutoSemImagem').textContent = produto.quantidade_produto ||
                    '0';
                document.getElementById('semImagemProduto').classList.remove('hidden');
            };

            document.getElementById('codigoProduto').textContent = produto.codigo_produto || produto.id_produto;
            document.getElementById('descricaoProduto').textContent = produto.descricao_produto;
            document.getElementById('estoqueProduto').textContent = produto.quantidade_produto || '0';
            document.getElementById('dadosProduto').classList.remove('hidden');
        } else {
            // Mostrar versão sem imagem
            document.getElementById('codigoProdutoSemImagem').textContent = produto.codigo_produto || produto
                .id_produto;
            document.getElementById('descricaoProdutoSemImagem').textContent = produto.descricao_produto;
            document.getElementById('estoqueProdutoSemImagem').textContent = produto.quantidade_produto || '0';
            document.getElementById('semImagemProduto').classList.remove('hidden');
        }
    }

    function fecharModalVisualizarProduto() {
        const modal = document.getElementById('modalVisualizarProduto');
        modal.classList.add('hidden');
    }

    // Fechar modal ao clicar fora dela
    document.getElementById('modalVisualizarProduto').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalVisualizarProduto();
        }
    });

    // Funções para o modal de disponibilidade
    function abrirModalDisponibilidade() {
        const produtoId = getSmartSelectValue('id_produto').value;

        if (!produtoId) {
            alert('Por favor, selecione um produto primeiro.');
            return;
        }

        const modal = document.getElementById('modalDisponibilidade');
        modal.classList.remove('hidden');

        // Reset do conteúdo
        document.getElementById('loadingDisponibilidade').classList.remove('hidden');
        document.getElementById('dadosDisponibilidade').classList.add('hidden');
        document.getElementById('erroDisponibilidade').classList.add('hidden');
        document.getElementById('semDisponibilidade').classList.add('hidden');

        // Buscar disponibilidade do produto
        buscarDisponibilidadeProduto(produtoId);
    }

    function buscarDisponibilidadeProduto(produtoId) {
        fetch(`/admin/requisicaoMaterial/produto/disponibilidade?produto_id=${produtoId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingDisponibilidade').classList.add('hidden');

                if (data.success) {
                    if (data.disponibilidade && data.disponibilidade.length > 0) {
                        mostrarDadosDisponibilidade(data);
                    } else {
                        document.getElementById('semDisponibilidade').classList.remove('hidden');
                    }
                } else {
                    document.getElementById('mensagemErroDisponibilidade').textContent = data.message ||
                        'Erro ao buscar disponibilidade';
                    document.getElementById('erroDisponibilidade').classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('loadingDisponibilidade').classList.add('hidden');
                document.getElementById('mensagemErroDisponibilidade').textContent = 'Erro de conexão';
                document.getElementById('erroDisponibilidade').classList.remove('hidden');
            });
    }

    function mostrarDadosDisponibilidade(data) {
        // Preencher informações do produto
        document.getElementById('codigoProdutoDisp').textContent = data.produto.codigo || data.produto.id;
        document.getElementById('descricaoProdutoDisp').textContent = data.produto.descricao;

        // Preencher resumo
        document.getElementById('quantidadeTotal').textContent = data.resumo.quantidade_total || 0;
        document.getElementById('filiaisComEstoque').textContent = data.resumo.filiais_com_estoque || 0;
        document.getElementById('valorMedioGeral').textContent = formatarMoeda(data.resumo.valor_medio_geral || 0);

        // Preencher tabela
        const tbody = document.getElementById('tabelaDisponibilidade');
        tbody.innerHTML = '';

        data.disponibilidade.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';

            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${item.nome_filial}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${item.estoque}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                    ${item.quantidade_produto}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${formatarMoeda(item.valor_medio)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${item.localizacao || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <button onclick="abrirModalTransferencia('${item.id_filial}', '${item.nome_filial}', '${item.quantidade_produto}', '${data.produto.id ?? item.id_produto}', '${data.produto.descricao}')"
                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Transferência
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
        });

        document.getElementById('dadosDisponibilidade').classList.remove('hidden');
    }

    function fecharModalDisponibilidade() {
        const modal = document.getElementById('modalDisponibilidade');
        modal.classList.add('hidden');
    }

    // Fechar modal de disponibilidade ao clicar fora dela
    document.getElementById('modalDisponibilidade').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalDisponibilidade();
        }
    });

    // Funções auxiliares
    function formatarMoeda(valor) {
        if (!valor || valor === 0) return 'R$ 0,00';

        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(valor);
    }

    function formatarDataBrasileira(data) {
        if (!data) return '-';

        const dataObj = new Date(data);
        if (isNaN(dataObj.getTime())) return '-';

        return dataObj.toLocaleDateString('pt-BR') + ' ' + dataObj.toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Fechar modal com tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modalVisualizacao = document.getElementById('modalVisualizarProduto');
            const modalDisponibilidade = document.getElementById('modalDisponibilidade');
            const modalTransferencia = document.getElementById('modalTransferencia');

            if (!modalVisualizacao.classList.contains('hidden')) {
                fecharModalVisualizarProduto();
            }

            if (!modalDisponibilidade.classList.contains('hidden')) {
                fecharModalDisponibilidade();
            }

            if (!modalTransferencia.classList.contains('hidden')) {
                fecharModalTransferencia();
            }
        }
    });

    // Funções para o modal de transferência
    let dadosTransferencia = {};

    function abrirModalTransferencia(idFilialOrigem, nomeFilialOrigem, quantidadeDisponivel, idProduto,
        descricaoProduto) {
        dadosTransferencia = {
            idFilialOrigem: idFilialOrigem,
            nomeFilialOrigem: nomeFilialOrigem,
            quantidadeDisponivel: parseInt(quantidadeDisponivel),
            idProduto: idProduto,
            descricaoProduto: descricaoProduto,
        };

        // Preencher campos
        document.getElementById('descricaoProdutoTransf').textContent = descricaoProduto;
        document.getElementById('filialOrigem').value = nomeFilialOrigem;
        document.getElementById('quantidadeDisponivel').textContent = quantidadeDisponivel;
        document.getElementById('quantidadeTransf').max = quantidadeDisponivel;
        document.getElementById('quantidadeTransf').value = '';

        // Mostrar modal
        document.getElementById('modalTransferencia').classList.remove('hidden');
    }

    function fecharModalTransferencia() {
        document.getElementById('modalTransferencia').classList.add('hidden');
        document.getElementById('formTransferencia').reset();
        dadosTransferencia = {};
    }

    function processarTransferencia(event) {
        event.preventDefault();

        const quantidade = parseInt(document.getElementById('quantidadeTransf').value);

        // Validações
        if (!quantidade || quantidade <= 0) {
            alert('Por favor, informe uma quantidade válida.');
            return;
        }

        if (quantidade > dadosTransferencia.quantidadeDisponivel) {
            alert(`A quantidade não pode ser maior que ${dadosTransferencia.quantidadeDisponivel}.`);
            return;
        }

        // Adicionar produto como transferência
        adicionarProdutoTransferencia(
            dadosTransferencia.idProduto,
            dadosTransferencia.descricaoProduto,
            quantidade,
            dadosTransferencia.idFilialOrigem,
            dadosTransferencia.nomeFilialOrigem,
            dadosTransferencia.codigoProduto
        );

        // Fechar modal
        fecharModalTransferencia();
    }

    // Fechar modal de transferência ao clicar fora
    document.getElementById('modalTransferencia').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalTransferencia();
        }
    });

    // função para carregar o smart-select de acordo com o selecionado
    document.addEventListener('DOMContentLoaded', function() {
        let operacao = 2;

        // Função para atualizar produtos baseado nas seleções
        function atualizarProdutosPorSelecao(valorPneu, valorTi) {
            let novaOperacao = 2; // Default: produtos gerais

            if (valorPneu === 1) {
                novaOperacao = 1; // Produtos para pneus
            } else if (valorTi === 1) {
                novaOperacao = 3; // Produtos para T.I.
            }

            // Sempre atualizar quando há mudança nos toggles, mesmo que seja a mesma operação
            // para garantir que a lista seja recarregada corretamente
            operacao = novaOperacao;
            clearSmartSelect('id_produto');
            updateSmartSelectOptions('id_produto', [], false);
            atualizarProdutos(operacao);
        }

        // Escutar evento customizado do AlpineJS
        window.addEventListener('toggleChanged', function(event) {
            const {
                pneu,
                ti
            } = event.detail;
            atualizarProdutosPorSelecao(pneu, ti);
        });

        function atualizarProdutos(operacao) {

            fetch(`/admin/requisicaoMaterial/getProdutosPorTipo`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        operacao: operacao
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.produtos) {
                        // Atualizar completamente as opções do smart-select
                        updateSmartSelectOptions('id_produto', data.produtos, false);
                        // Limpar campos relacionados ao produto
                        document.getElementById('estoque_filial').value = '';
                        document.getElementById('id_grupo').value = '';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Carregar produtos iniciais
        atualizarProdutos(operacao);
    });

    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o registro do sinistro?')) {
            excluirPessoa(id);
        }
    };

    function excluirPessoa(id) {
        fetch(`/admin/requisicaoMaterial/${id}`, {
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
                    alert('O registro foi excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir registro');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir o registro');
            });
    };
</script>
