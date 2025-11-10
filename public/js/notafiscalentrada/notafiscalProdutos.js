document.addEventListener('DOMContentLoaded', function () {
    let registrosNfProdutosTemporarios = [];

    // Detecta qual formulário está sendo usado baseado na existência de campos específicos
    const isDevForm = document.querySelector('[name="quantidade_devolucao"]') !== null;
    const isDevolucaoContext = document.getElementById('devolucao') !== null;

    const nfProdutosJson = document.getElementById('nfeProdutos_json').value;
    const nfProdutos = JSON.parse(nfProdutosJson);

    if (nfProdutos && nfProdutos.length > 0) {
        nfProdutos.forEach(produto => {
            registrosNfProdutosTemporarios.push({
                idProduto: produto.id_nota_fiscal_produtos,
                idEntrada: produto.id_nota_fiscal_entrada,
                codProduto: produto.cod_produto,
                nomeProduto: produto.nome_produto,
                ncm: produto.ncm || '-',
                unidade: produto.unidade || '-',
                quantidadeProdutos: produto.quantidade_produtos,
                valorUnitario: produto.valor_unitario_formatado,
                valorTotal: produto.valor_total_formatado,
                valorDesconto: produto.valor_unitario_desconto_formatado,
                data_inclusao: produto.data_inclusao,
                data_alteracao: produto.data_alteracao,
                qtdeDevolvida: produto.qtde_devolucao || 0,
                has_pneus: produto.has_pneus,
                is_pneu: produto.is_pneu,
                is_devolucao: produto.is_devolucao
            });
        });
        atualizarNfProdutosTabela();
    }

    function adicionarNfProdutos() {
        const codProduto = document.querySelector('[name="cod_produto"]').value;
        const nomeProduto = document.querySelector('[name="nome_produto"]').value;
        const ncm = document.querySelector('[name="ncm"]').value;
        const unidade = document.querySelector('[name="unidade"]').value;
        const quantidadeProdutos = document.querySelector('[name="quantidade_produtos"]').value;

        // Campos que podem ou não existir dependendo do formulário
        const quantidadeDevolucaoElement = document.querySelector('[name="quantidade_devolucao"]');
        const quantidadeDevolucao = quantidadeDevolucaoElement ? quantidadeDevolucaoElement.value : '';

        const valorUnitario = document.querySelector('[name="valor_unitario"]').value;
        const valorTotal = document.querySelector('[name="valor_total"]').value;
        const valorDesconto = document.getElementById('campo_valor_desconto').value;

        const devolucaoElement = document.getElementById('devolucao');
        const devolucao = devolucaoElement ? devolucaoElement.value : '';

        const idProdutoElement = document.querySelector('[name="id_nota_fiscal_produtos"]');
        const idProduto = idProdutoElement ? idProdutoElement.value : '';

        const data_inclusao = formatarNfProdutosData();
        const data_alteracao = formatarNfProdutosData();

        if (!codProduto) {
            alert('Código do Produto é obrigatório!');
            return;
        }

        // Validação específica para o formulário de devolução
        if (isDevForm && quantidadeDevolucaoElement && quantidadeDevolucao && quantidadeProdutos) {
            if (parseFloat(quantidadeDevolucao) > parseFloat(quantidadeProdutos)) {
                alert('Quantidade devolvida não pode ser maior que a quantidade de produtos!');
                return;
            }
        }

        const registroNfProdutos = {
            idProduto: idProduto,
            codProduto: codProduto,
            nomeProduto: nomeProduto,
            ncm: ncm,
            unidade: unidade,
            quantidadeProdutos: quantidadeProdutos,
            qtdeDevolvida: quantidadeDevolucao,
            valorUnitario: valorUnitario,
            valorTotal: valorTotal,
            valorDesconto: valorDesconto,
            data_inclusao: data_inclusao,
            data_alteracao: data_alteracao,
            is_devolucao: devolucao
        };

        registrosNfProdutosTemporarios.push(registroNfProdutos);
        atualizarNfProdutosTabela();
        limparNfProdutosFormularioTemp();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('nfeProdutos_json').value = JSON.stringify(registrosNfProdutosTemporarios);
    }

    function atualizarNfProdutosTabela() {
        const tbody = document.getElementById('tabelanfeProdutosBody');
        if (!tbody) {
            console.error('Elemento #tabelanfeProdutosBody não encontrado');
            return;
        }

        // Ordenar registros por data
        registrosNfProdutosTemporarios.sort((a, b) => new Date(a.data_inclusao) - new Date(b.data_inclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        registrosNfProdutosTemporarios.forEach((registroNfProdutos, index) => {
            const tr = document.createElement('tr');
            // Adicione o atributo data-id-manutencao
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <button type="button" onclick="editarNfProdutosRegistro(${index})" title="Editar"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <button type="button" onclick="excluirNfProdutosRegistro(${index})" title="Excluir"
                            class="btn-excluir inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>   
                        ${gerarBotaoFogo(index, registroNfProdutos.has_pneus, registroNfProdutos.is_pneu)}
                        ${gerarBotaoRelatorio(index, registroNfProdutos.has_pneus, registroNfProdutos.is_pneu)}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">${registroNfProdutos.idProduto}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarNfProdutosData(registroNfProdutos.data_inclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarNfProdutosData(registroNfProdutos.data_alteracao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroNfProdutos.codProduto}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroNfProdutos.nomeProduto}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroNfProdutos.ncm}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroNfProdutos.unidade}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroNfProdutos.quantidadeProdutos}</td>
                ${camposDevolucao(index, registroNfProdutos.is_devolucao, registroNfProdutos.valorUnitario, registroNfProdutos.valorTotal, registroNfProdutos.valorDesconto, registroNfProdutos.qtdeDevolvida)}
            `;

            tbody.appendChild(tr);
        });
    }

    function limparNfProdutosFormularioTemp() {
        document.querySelector('[name="cod_produto"]').value = '';
        document.querySelector('[name="nome_produto"]').value = '';
        document.querySelector('[name="ncm"]').value = '';
        document.querySelector('[name="unidade"]').value = '';
        document.querySelector('[name="quantidade_produtos"]').value = '';
        document.querySelector('[name="valor_unitario"]').value = '';
        document.querySelector('[name="valor_total"]').value = '';
        document.getElementById('campo_valor_desconto').value = '';

        // Limpa campos específicos do formulário de devolução se existirem
        const quantidadeDevolucaoElement = document.querySelector('[name="quantidade_devolucao"]');
        if (quantidadeDevolucaoElement) {
            quantidadeDevolucaoElement.value = '';
        }

        const idProdutoElement = document.querySelector('[name="id_nota_fiscal_produtos"]');
        if (idProdutoElement) {
            idProdutoElement.value = '';
        }

        const devolucaoElement = document.getElementById('devolucao');
        if (devolucaoElement) {
            devolucaoElement.value = '';
        }
    }

    function excluirNfProdutosRegistro(index) {
        registrosNfProdutosTemporarios.splice(index, 1);
        atualizarNfProdutosTabela();
        document.getElementById('nfeProdutos_json').value = JSON.stringify(registrosNfProdutosTemporarios);
    }

    function editarNfProdutosRegistro(index) {
        const registroNfProdutos = registrosNfProdutosTemporarios[index];

        // Preenche campos que existem em ambos os formulários
        document.querySelector('[name="cod_produto"]').value = registroNfProdutos.codProduto;
        document.querySelector('[name="nome_produto"]').value = registroNfProdutos.nomeProduto;
        document.querySelector('[name="ncm"]').value = registroNfProdutos.ncm;
        document.querySelector('[name="unidade"]').value = registroNfProdutos.unidade;
        document.querySelector('[name="quantidade_produtos"]').value = registroNfProdutos.quantidadeProdutos;
        document.querySelector('[name="valor_unitario"]').value = registroNfProdutos.valorUnitario;
        document.querySelector('[name="valor_total"]').value = registroNfProdutos.valorTotal;
        document.getElementById('campo_valor_desconto').value = registroNfProdutos.valorDesconto;

        // Preenche campos específicos do formulário de devolução se existirem
        const idProdutoElement = document.getElementById('id_nota_fiscal_produtos');
        if (idProdutoElement) {
            idProdutoElement.value = registroNfProdutos.idProduto;
        }

        const quantidadeDevolucaoElement = document.querySelector('[name="quantidade_devolucao"]');
        if (quantidadeDevolucaoElement) {
            quantidadeDevolucaoElement.value = registroNfProdutos.qtdeDevolvida;
        }

        const devolucaoElement = document.getElementById('devolucao');
        if (devolucaoElement) {
            devolucaoElement.value = registroNfProdutos.is_devolucao;
        }

        excluirNfProdutosRegistro(index);
    }

    function formatarNfProdutosData(data) {
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

    async function buscaPedido() {
        const id = document.getElementById('id_pedido_compra_b');
        const idFornecedor = document.getElementById('id_fornecedor');
        const cnpj = document.getElementById('cnpj');
        const nomeEmpresa = document.getElementById('nome_empresa');
        const endereco = document.getElementById('endereco');
        const numero = document.getElementById('numero');
        const bairro = document.getElementById('bairro');
        const cep = document.getElementById('cep');

        if (!id.value) {
            return alert("Atenção: Informar numero do pedido.");
        }

        try {
            const response = await fetch('/admin/notafiscalentrada/buscarPedido', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    idPedido: id.value
                })
            });

            const data = await response.json();

            if (data && data.success) {
                cnpj.value = data.dados.cnpj;
                idFornecedor.value = data.dados.id_fornecedor;
                nomeEmpresa.value = data.dados.nome_empresa;
                endereco.value = data.dados.endereco;
                numero.value = data.dados.numero;
                bairro.value = data.dados.bairro;
                cep.value = data.dados.cep;

                setSmartSelectValue('nome_municipio', data.dados.municipio, {
                    createIfNotFound: true,
                    tempLabel: data.dados.municipio
                });
                setSmartSelectValue('uf', data.dados.uf, {
                    createIfNotFound: true,
                    tempLabel: data.dados.uf
                });

                // Popula os itens na tabela dinâmica
                registrosNfProdutosTemporarios = data.dados.itens.map(item => ({
                    codProduto: item.cod_produto,
                    nomeProduto: item.nome_produto,
                    ncm: item.ncm || '-',
                    unidade: item.unidade || '-',
                    quantidadeProdutos: item.quantidade_produtos,
                    valorUnitario: item.valor_unitario,
                    valorTotal: item.valor_total,
                    valorDesconto: item.valor_desconto || '-',
                    data_inclusao: item.data_inclusao,
                    data_alteracao: item.data_alteracao
                }));

                // Atualiza a tabela dinâmica com os novos itens
                document.getElementById('nfeProdutos_json').value = JSON.stringify(registrosNfProdutosTemporarios);
                atualizarNfProdutosTabela();

            } else {
                alert(data.message);
                console.warning('ERRO');
                if (data.error) {
                    console.warn('Erro do servidor:', data.error);
                }
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
            alert('Erro ao buscar fornecedor. Tente novamente.');
        }
    }

    function gerarNumeroFogo(index) {
        const registroNfProdutos = registrosNfProdutosTemporarios[index];
        window.location.href = `/admin/notafiscalentrada/${registroNfProdutos.idEntrada}/gerarNumFogo`;
    }

    async function gerarRelatorio(index) {
        const registroNfProdutos = registrosNfProdutosTemporarios[index];
        produtoID = registroNfProdutos.idProduto;

        try {
            const response = await fetch('/admin/notafiscalentrada/relNumFogo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                        .content
                },
                body: JSON.stringify({
                    produtoID: produtoID
                })
            });

            // Se espera um arquivo PDF, use response.blob()
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/pdf')) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.target = '_blank';
                link.download = `relatorio_NumFogo_${produtoID}.pdf`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
                return;
            }

            // Caso seja JSON        
            const responseData = await response.json();

            if (responseData.success === true) {
                if (responseData.data) {
                    alert('Relatório gerado com sucesso!');
                } else {
                    console.warn('⚠️ Dados não encontrados');
                    alert('Dados não encontrados para o veículo selecionado.');
                }
            } else {
                console.warn('⚠️ Requisição não foi bem-sucedida');
                alert('Requisição não foi bem-sucedida.');
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
        }
    }

    function gerarBotaoFogo(index, hasPneu, isPneu) {
        if (!isPneu && !hasPneu) return '';

        if (hasPneu) return '';

        return `
        <button type="button" onclick="gerarNumeroFogo(${index})" title="Gerar Número de Fogo"
            class="inline-flex items-center p-1 border border-transparent shadow-sm text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            <svg class="h-4 w-4" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <g id="Dribbble-Light-Preview" transform="translate(-140.000000, -3759.000000)" fill="#000000">
                        <g id="icons" transform="translate(56.000000, 160.000000)">
                            <path d="M96.66,3608.872 L91,3612 L91,3606 L96.66,3608.872 Z M86,3617 L102,3617 L102,3601 L86,3601 L86,3617 Z M84,3619 L104,3619 L104,3599 L84,3599 L84,3619 Z" id="play-[#1008]">
                            </path>
                        </g>
                    </g>
                </g>
            </svg>
        </button>
    `;
    }

    function gerarBotaoRelatorio(index, hasPneu, isPneu) {
        if (!isPneu && !hasPneu) return '';

        return `
        <button type="button" onclick="gerarRelatorio(${index})" title="Imprimir N° de Fogo Gerados"
            class="inline-flex items-center p-1 border border-transparent shadow-sm text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500"
                viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                <path fill="currentColor"
                    d="M64 464l48 0 0 48-48 0c-35.3 0-64-28.7-64-64L0 64C0 28.7 28.7 0 64 0L229.5 0c17 0 33.3 6.7 45.3 18.7l90.5 90.5c12 12 18.7 28.3 18.7 45.3L384 304l-48 0 0-144-80 0c-17.7 0-32-14.3-32-32l0-80L64 48c-8.8 0-16 7.2-16 16l0 384c0 8.8 7.2 16 16 16zM176 352l32 0c30.9 0 56 25.1 56 56s-25.1 56-56 56l-16 0 0 32c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-48 0-80c0-8.8 7.2-16 16-16zm32 80c13.3 0 24-10.7 24-24s-10.7-24-24-24l-16 0 0 48 16 0zm96-80l32 0c26.5 0 48 21.5 48 48l0 64c0 26.5-21.5 48-48 48l-32 0c-8.8 0-16-7.2-16-16l0-128c0-8.8 7.2-16 16-16zm32 128c8.8 0 16-7.2 16-16l0-64c0-8.8-7.2-16-16-16l-16 0 0 96 16 0zm80-112c0-8.8 7.2-16 16-16l48 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-32 0 0 32 32 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-32 0 0 48c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-64 0-64z" />
            </svg>
        </button>
    `;
    }

    function camposDevolucao(index, is_devolucao, valorUnitario, valorTotal, valorDesconto, qtdeDevolvida) {
        // Se estamos no formulário de devolução (_form_dev.blade.php)
        if (isDevForm) {
            return `<td class="px-6 py-4 whitespace-nowrap">${qtdeDevolvida || '0'}</td>`;
        }

        // Se estamos no formulário principal (_form.blade.php)
        if (!is_devolucao) {
            return `<td class="px-6 py-4 whitespace-nowrap">${valorUnitario}</td>
                <td class="px-6 py-4 whitespace-nowrap">${valorTotal}</td>
                <td class="px-6 py-4 whitespace-nowrap">${valorDesconto}</td>
                `;
        }

        // Caso especial para devolução no formulário principal
        return `<td class="px-6 py-4 whitespace-nowrap">${qtdeDevolvida}</td>`;
    }

    // Tornando as funções acessíveis no escopo global
    window.adicionarNfProdutos = adicionarNfProdutos;
    window.atualizarNfProdutosTabela = atualizarNfProdutosTabela;
    window.limparNfProdutosFormularioTemp = limparNfProdutosFormularioTemp;
    window.excluirNfProdutosRegistro = excluirNfProdutosRegistro;
    window.editarNfProdutosRegistro = editarNfProdutosRegistro;
    window.gerarNumeroFogo = gerarNumeroFogo;
    window.gerarRelatorio = gerarRelatorio;
    window.buscaPedido = buscaPedido;
});
