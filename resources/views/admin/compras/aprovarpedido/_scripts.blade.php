<script>
    // Carregar cotações automaticamente quando a página carregar
    document.addEventListener('DOMContentLoaded', function() {
        processarCotacoes();
    });

    function cancelarCotacao(id) {
        const idSolicitacao = id;

        console.log(idSolicitacao);

        if (!idSolicitacao) {
            alert('Digite o código da solicitação');
            return;
        }

        if (confirm('Tem certeza que deseja cancelar esta cotação? Esta ação não pode ser desfeita.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('admin.compras.aprovarpedido.cancelar') }}';

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

    function processarCotacoes() {
        const idSolicitacao = document.querySelector('[name="solicitacoes_compra_consulta"]').value;

        if (!idSolicitacao) {
            console.log('ID da solicitação não encontrado');
            return;
        }

        // Mostrar indicador de carregamento
        for (let i = 1; i <= 3; i++) {
            const itensElement = document.getElementById(`cotacao-0${i}-itens`);
            if (itensElement) {
                itensElement.innerHTML =
                    '<div class="text-sm text-gray-500 p-3 text-center"><div class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full mr-2"></div>Carregando...</div>';
            }
        }

        fetch(`/admin/compras/aprovarpedido/cotacoes/${idSolicitacao}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição');
                }
                return response.json();
            })
            .then(data => {
                // Limpar todos os campos antes de preencher
                for (let i = 1; i <= 3; i++) {
                    const codigoElement = document.getElementById(`cotacao-0${i}-codigo`);
                    const fornecedorElement = document.getElementById(`cotacao-0${i}-fornecedor`);
                    const itensElement = document.getElementById(`cotacao-0${i}-itens`);
                    const containerElement = document.querySelector(`#cotacoes-container > div:nth-child(${i})`);

                    if (codigoElement) codigoElement.value = '';
                    if (fornecedorElement) fornecedorElement.textContent = '';
                    if (itensElement) {
                        itensElement.innerHTML =
                            '<div class="text-sm text-gray-500 p-3 text-center">Nenhum registro foi encontrado</div>';
                    }

                    // Resetar aparência padrão
                    if (containerElement) {
                        containerElement.className = 'rounded-lg border border-gray-200 bg-white p-6 shadow-sm';
                        const h3Element = containerElement.querySelector('h3');
                        if (h3Element) {
                            h3Element.innerHTML = `Cotação - 0${i}`;
                            h3Element.className = 'text-lg font-semibold text-gray-900';
                        }
                    }
                }

                if (!data || data.length === 0) {
                    console.log('Nenhuma cotação encontrada');
                    return;
                }

                // Preencher as cotações encontradas
                data.slice(0, 3).forEach((cotacao, index) => {
                    const cotacaoNum = String(index + 1).padStart(2, '0');
                    const containerElement = document.querySelector(
                        `#cotacoes-container > div:nth-child(${index + 1})`);

                    // Verificar se é cotação vencedora e aplicar estilo
                    if (cotacao.is_vencedora && containerElement) {
                        containerElement.className =
                            'rounded-lg border-2 border-green-300 bg-green-50 p-6 shadow-lg';
                        const h3Element = containerElement.querySelector('h3');
                        if (h3Element) {
                            h3Element.innerHTML = `
                                <svg class="mr-2 h-5 w-5 text-green-600 inline" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                Cotação Vencedora
                            `;
                            h3Element.className = 'text-lg font-semibold text-green-800 flex items-center';
                        }
                    }

                    // Preencher código da cotação
                    const codigoElement = document.getElementById(`cotacao-${cotacaoNum}-codigo`);
                    if (codigoElement) {
                        codigoElement.value = cotacao.numero || '';
                    }

                    // Preencher fornecedor
                    const fornecedorElement = document.getElementById(`cotacao-${cotacaoNum}-fornecedor`);
                    if (fornecedorElement && cotacao.fornecedor && cotacao.fornecedor !== 'N/A') {
                        fornecedorElement.textContent = cotacao.fornecedor;
                        if (cotacao.is_vencedora) {
                            fornecedorElement.className = 'text-sm text-green-700';
                        }
                    }

                    // Preencher itens
                    const itensElement = document.getElementById(`cotacao-${cotacaoNum}-itens`);
                    if (itensElement) {
                        let itensHtml = '';
                        if (cotacao.itens_detalhados && cotacao.itens_detalhados.length > 0) {
                            const headerClass = cotacao.is_vencedora ? 'bg-green-200 text-green-800' :
                                'bg-blue-100 text-gray-600';
                            const bodyClass = cotacao.is_vencedora ? 'bg-green-100' : 'bg-blue-50';

                            itensHtml = `
                                <div class="rounded-b ${bodyClass}">
                            `;

                            itensHtml += cotacao.itens_detalhados.map(item => `
                                <div class="grid grid-cols-5 gap-2 text-xs p-2 border-b border-gray-100 last:border-b-0">
                                    <div class="truncate text-gray-800 font-medium" title="${item.descricao || ''}">${item.descricao || ''}</div>
                                    <div class="text-center text-gray-800">${item.quantidade || ''}</div>
                                    <div class="text-center text-gray-800">R$ ${parseFloat(item.valor_unitario || 0).toFixed(2).replace('.', ',')}</div>
                                    <div class="text-center text-gray-800">R$ ${parseFloat(item.valor_bruto || 0).toFixed(2).replace('.', ',')}</div>
                                    <div class="text-center text-gray-800">R$ ${parseFloat(item.valor_desconto || 0).toFixed(2).replace('.', ',')}</div>
                                </div>
                            `).join('');

                            // Adicionar total
                            const totalClass = cotacao.is_vencedora ? 'bg-green-200' : 'bg-gray-100';
                            itensHtml += `
                                    <div class="grid grid-cols-4 gap-2 text-sm font-bold p-2 ${totalClass}">
                                        <div class="col-span-2 text-right">Total:</div>
                                        <div class="text-center">R$ ${cotacao.valores}</div>
                                        <div class="text-center">R$ ${cotacao.valoresDesconto}</div>
                                    </div>
                                </div>
                            `;
                        } else {
                            const emptyClass = cotacao.is_vencedora ? 'text-green-600' : 'text-gray-500';
                            itensHtml =
                                `<div class="text-sm ${emptyClass} p-3 text-center">Nenhum registro foi encontrado</div>`;
                        }

                        itensElement.innerHTML = itensHtml;
                    }
                });

                // Carregar opções do select de fornecedores
                const selectFornecedores = document.getElementById('cotacao_selecionada');
                if (selectFornecedores) {
                    // Limpar opções existentes (exceto a primeira)
                    selectFornecedores.innerHTML = '<option value="">Selecione um fornecedor</option>';

                    if (data && data.length > 0) {
                        // Adicionar opções dos fornecedores das cotações
                        data.forEach((cotacao, index) => {
                            if (cotacao && cotacao.fornecedor && cotacao.fornecedor !== 'N/A') {
                                const option = document.createElement('option');
                                option.value = cotacao.numero || ''; // Usar o número da cotação como value
                                option.textContent = `${cotacao.numero || 'S/N'} - ${cotacao.fornecedor}`;
                                option.dataset.fornecedor = cotacao.fornecedor;
                                selectFornecedores.appendChild(option);

                            } else {
                                console.log(`Cotação ${index + 1} ignorada - fornecedor inválido:`, cotacao
                                    ?.fornecedor);
                            }
                        });
                    } else {
                        console.log('Nenhuma cotação válida para o select');
                    }
                } else {
                    console.error('Elemento select não encontrado: cotacao_selecionada');
                }
            })
            .catch(error => {
                console.error('Erro:', error);

                // Restaurar mensagem padrão em caso de erro
                for (let i = 1; i <= 3; i++) {
                    const itensElement = document.getElementById(`cotacao-0${i}-itens`);
                    if (itensElement) {
                        itensElement.innerHTML =
                            '<div class="text-sm text-red-500 p-3 text-center">Erro ao carregar cotações</div>';
                    }
                }

                const vencedoraItensElement = document.getElementById('cotacao-vencedora-valor');
                if (vencedoraItensElement) {
                    vencedoraItensElement.innerHTML =
                        '<div class="text-sm text-red-600 p-3 text-center">Erro ao carregar cotação vencedora</div>';
                }
            });
    }

    async function aprovarCotacao(id) {
        // id opcional: pode ser passado diretamente ou buscado do input
        const idSolicitacao = id || document.querySelector('[name="solicitacoes_compra_consulta"]').value;
        const filialEntrega = document.querySelector('[name="filial_entrega"]').value;
        const filialFaturamento = document.querySelector('[name="filial_faturamento"]').value;
        const tipoAprovacao = document.querySelector('[name="tipo_aprovacao"]').value;
        const observacoes = document.querySelector('[name="observacoes"]').value;

        if (!tipoAprovacao) {
            alert('Selecione um tipo de aprovação!');
            return;
        }

        if (!idSolicitacao) {
            alert('Digite o código da solicitação');
            return;
        }

        const baseUrl = '/admin/compras/aprovarpedido/aprovarCotacoes';

        const payload = {
            id_solicitacao_compras: idSolicitacao,
            tipoAprovacao: tipoAprovacao,
            filialEntrega: filialEntrega,
            filialFaturamento: filialFaturamento,
            observacoes: observacoes
        };

        console.log('Enviando payload aprovarCotacao:', payload);

        try {
            const resp = await fetch(baseUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            console.log('Status da resposta:', resp.status);

            if (!resp.ok) {
                let errorMessage = 'Erro ao aprovar cotação';

                try {
                    const errorData = await resp.json();
                    console.error('Erro detalhado:', errorData);
                    errorMessage = errorData.message || errorData.error || errorMessage;
                } catch (e) {
                    const text = await resp.text();
                    console.error('Erro ao aprovar cotação:', resp.status, text);
                    errorMessage = `Erro HTTP ${resp.status}: ${text.substring(0, 200)}`;
                }

                alert(errorMessage);
                return;
            }

            const data = await resp.json();
            console.log('Resposta completa:', data);

            if (data && data.message) {
                alert(data.message);
            } else {
                alert('Cotação aprovada com sucesso.');
            }

            // Se o servidor enviar uma URL de redirect, navegar para ela; caso contrário recarregar
            if (data && data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.reload();
            }
        } catch (error) {
            console.error('Erro ao comunicar com aprovarCotacao:', error);
            alert('Erro ao comunicar com o servidor ao aprovar a cotação.');
        }
    }

    // Variável global para armazenar os dados das cotações
    let dadosCotacoesCompletas = [];
    let paginaAtual = 1;
    const itensPorPagina = 10;

    // Função para abrir o modal de seleção de cotações
    function abrirModalCotacoes() {
        const idSolicitacao = document.querySelector('[name="solicitacoes_compra_consulta"]').value;

        if (!idSolicitacao) {
            alert('Digite o código da solicitação primeiro');
            return;
        }

        // Mostrar o modal e o loading
        document.getElementById('modal-selecionar-cotacao').classList.remove('hidden');
        document.getElementById('loading-cotacoes').classList.remove('hidden');
        document.getElementById('tabela-cotacoes-modal').style.display = 'none';

        // Buscar todas as cotações completas
        fetch(`/admin/compras/aprovarpedido/cotacoes-completas/${idSolicitacao}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição');
                }
                return response.json();
            })
            .then(data => {
                dadosCotacoesCompletas = data;
                document.getElementById('loading-cotacoes').classList.add('hidden');
                document.getElementById('tabela-cotacoes-modal').style.display = 'table';
                preencherTabelaCotacoes();
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('loading-cotacoes').classList.add('hidden');
                alert('Erro ao buscar cotações detalhadas.');
                fecharModalCotacoes();
            });
    }

    // Função para fechar o modal
    function fecharModalCotacoes() {
        document.getElementById('modal-selecionar-cotacao').classList.add('hidden');
        // Limpar seleções
        document.getElementById('select-all-cotacoes').checked = false;
        paginaAtual = 1;
        dadosCotacoesCompletas = [];
    }

    // Função para preencher a tabela de cotações
    function preencherTabelaCotacoes() {
        const corpoTabela = document.getElementById('corpo-tabela-cotacoes');
        corpoTabela.innerHTML = '';

        if (!dadosCotacoesCompletas || dadosCotacoesCompletas.length === 0) {
            corpoTabela.innerHTML =
                '<tr><td colspan="11" class="text-center py-4 text-gray-500">Nenhuma cotação encontrada</td></tr>';
            atualizarInfoPaginacao(0, 0, 0);
            return;
        }

        // Calcular itens para a página atual
        const inicio = (paginaAtual - 1) * itensPorPagina;
        const fim = Math.min(inicio + itensPorPagina, dadosCotacoesCompletas.length);
        const itensParaPagina = dadosCotacoesCompletas.slice(inicio, fim);

        itensParaPagina.forEach((item, index) => {
            const linha = document.createElement('tr');
            linha.className = index % 2 === 0 ? 'bg-white hover:bg-gray-50' : 'bg-gray-50 hover:bg-gray-100';

            linha.innerHTML = `
                <td class="px-2 py-3 border border-gray-300 text-center">
                    <input type="checkbox" name="cotacao_item_selecionado"
                           value="${item.id_cotacao_item || item.id}"
                           data-cotacao="${item.id_cotacao}"
                           data-fornecedor="${item.fornecedor || 'N/A'}"
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                </td>
                <td class="px-2 py-3 border border-gray-300 text-center text-sm font-medium">${item.codigo_item || 'N/A'}</td>
                <td class="px-2 py-3 border border-gray-300 text-sm" title="${item.fornecedor || ''}">${(item.fornecedor || 'N/A').substring(0, 25)}${(item.fornecedor || '').length > 25 ? '...' : ''}</td>
                <td class="px-2 py-3 border border-gray-300 text-sm" title="${item.descricao_produto || ''}">${(item.descricao_produto || 'N/A').substring(0, 35)}${(item.descricao_produto || '').length > 35 ? '...' : ''}</td>
                <td class="px-2 py-3 border border-gray-300 text-sm text-center">${item.unidade || 'UN'}</td>
                <td class="px-2 py-3 border border-gray-300 text-sm text-center font-medium">${item.quantidade_solicitada || '1'}</td>
                <td class="px-2 py-3 border border-gray-300 text-sm text-center font-medium">${item.quantidade_fornecedor || '1'}</td>
                <td class="px-2 py-3 border border-gray-300 text-sm text-right font-medium">R$ ${parseFloat(item.valor_unitario || 0).toFixed(2).replace('.', ',')}</td>
                <td class="px-2 py-3 border border-gray-300 text-sm text-right font-bold text-green-700">R$ ${parseFloat(item.valor_item || 0).toFixed(2).replace('.', ',')}</td>
                <td class="px-2 py-3 border border-gray-300 text-sm text-right font-medium text-red-600">R$ ${parseFloat(item.valor_desconto || 0).toFixed(2).replace('.', ',')}</td>
                <td class="px-2 py-3 border border-gray-300 text-sm text-center font-medium">${parseFloat(item.percentual_desconto || 0).toFixed(0)}%</td>
            `;

            corpoTabela.appendChild(linha);
        });

        atualizarInfoPaginacao(inicio + 1, fim, dadosCotacoesCompletas.length);
        atualizarBotoesPaginacao();
    }

    // Função para atualizar informações de paginação
    function atualizarInfoPaginacao(inicio, fim, total) {
        document.getElementById('itens-inicio').textContent = inicio;
        document.getElementById('itens-fim').textContent = fim;
        document.getElementById('total-itens').textContent = total;

        const totalPaginas = Math.ceil(total / itensPorPagina);
        document.getElementById('pagina-atual-info').textContent = `Página ${paginaAtual} de ${totalPaginas}`;
    }

    // Função para atualizar os botões de paginação
    function atualizarBotoesPaginacao() {
        const totalPaginas = Math.ceil(dadosCotacoesCompletas.length / itensPorPagina);

        document.getElementById('btn-anterior').disabled = paginaAtual <= 1;
        document.getElementById('btn-proximo').disabled = paginaAtual >= totalPaginas;
    }

    // Função para página anterior
    function paginaAnterior() {
        if (paginaAtual > 1) {
            paginaAtual--;
            preencherTabelaCotacoes();
        }
    }

    // Função para próxima página
    function proximaPagina() {
        const totalPaginas = Math.ceil(dadosCotacoesCompletas.length / itensPorPagina);
        if (paginaAtual < totalPaginas) {
            paginaAtual++;
            preencherTabelaCotacoes();
        }
    }

    // Função para selecionar/desselecionar todas as cotações
    function selecionarTodasCotacoes() {
        const selectAll = document.getElementById('select-all-cotacoes');
        const checkboxes = document.querySelectorAll('input[name="cotacao_item_selecionado"]');

        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }

    // Função para gerar pedidos com itens selecionados
    function gerarPedidosComItensSelecionados() {
        const checkboxesSelecionados = document.querySelectorAll('input[name="cotacao_item_selecionado"]:checked');

        if (checkboxesSelecionados.length === 0) {
            alert('Selecione pelo menos um item para gerar o pedido');
            return;
        }

        const idSolicitacao = document.querySelector('[name="solicitacoes_compra_consulta"]').value;

        if (!idSolicitacao) {
            alert('ID da solicitação não encontrado');
            return;
        }

        const itensSelecionados = Array.from(checkboxesSelecionados).map(checkbox => ({
            id_cotacao_item: checkbox.value,
            id_cotacao: checkbox.dataset.cotacao,
            fornecedor: checkbox.dataset.fornecedor
        }));

        console.log('Enviando dados para gerar cotação:', {
            id_solicitacao: idSolicitacao,
            itens_selecionados: itensSelecionados
        });

        // Mostrar loading no botão
        const btnGerar = document.querySelector('button[onclick="gerarPedidosComItensSelecionados()"]');
        const textoOriginal = btnGerar.innerHTML;
        btnGerar.innerHTML = '⏳ Gerando pedidos...';
        btnGerar.disabled = true;

        // Chamar a função gerarCotacao no backend
        fetch('/admin/compras/aprovarpedido/gerar-cotacao', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id_solicitacao: idSolicitacao,
                    itens_selecionados: itensSelecionados
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    fecharModalCotacoes();
                    // Opcional: recarregar a página para atualizar os dados
                    window.location.reload();
                } else {
                    alert('❌ Erro: ' + (data.error || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro ao gerar cotação:', error);
                alert('❌ Erro ao gerar cotação: ' + (error.error || error.message ||
                    'Erro de comunicação com o servidor'));
            })
            .finally(() => {
                // Restaurar botão
                btnGerar.innerHTML = textoOriginal;
                btnGerar.disabled = false;
            });
    }

    // Adicionar event listener para o select de tipo de aprovação
    document.addEventListener('DOMContentLoaded', function() {
        const tipoAprovacao = document.getElementById('tipo_aprovacao');
        if (tipoAprovacao) {
            tipoAprovacao.addEventListener('change', function() {
                if (this.value === 'selecionarCotacao') {
                    abrirModalCotacoes();
                }
            });
        }
    });
</script>
