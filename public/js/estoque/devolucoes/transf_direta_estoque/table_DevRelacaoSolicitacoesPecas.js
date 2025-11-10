window.onload = function () {
    let registrosTemporarios = [];

    const devRequisicaoPecasJson = document.getElementById('devRequisicaoPecas_json').value;
    const devRequisicaoPecas = JSON.parse(devRequisicaoPecasJson);


    if (devRequisicaoPecas && devRequisicaoPecas.length > 0) {
        devRequisicaoPecas.forEach(devRequisicaoPeca => {
            registrosTemporarios.push({
                id_produto: devRequisicaoPeca.id_protudos,
                descricao_produto: devRequisicaoPeca.produto.descricao_produto,
                unidade_produto: devRequisicaoPeca.produto.unidade_produto.descricao_unidade || 'N/A',
                id_unidade_produto: devRequisicaoPeca.produto.id_unidade_produto || null,
                qtde_produto: devRequisicaoPeca.quantidade,
                qtde_devolucao: devRequisicaoPeca.quantidade_baixa || 0
            });
        });
        atualizarTabela();
    }

    function adicionardevRequisicaoPeca() {
        const produto = getSmartSelectValue('id_produto');
        const unidadeProduto = getSmartSelectValue('unidadeProduto');
        const qtde_produto = parseInt(document.querySelector('input[name="qtde_produto"]').value, 10);
        const qtde_devolucao = parseInt(document.querySelector('input[name="qtde_devolucao"]').value, 10);

        if (qtde_devolucao > qtde_produto) {
            alert('Quantidade devolvida não pode ser maior que a quantidade recebida!');
            return
        }

        const registro = {
            id_produto: produto.value,
            descricao_produto: produto.label,
            unidade_produto: unidadeProduto.label,
            id_unidade_produto: unidadeProduto.value,
            qtde_produto: qtde_produto,
            qtde_devolucao: qtde_devolucao || 0
        };

        registrosTemporarios.push(registro);
        atualizarTabela();
        limparFormularioTemp();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('devRequisicaoPecas_json').value = JSON.stringify(registrosTemporarios);
    }

    function atualizarTabela() {
        const tbody = document.getElementById('tabeladevRequisicaoPecaBody');
        if (!tbody) {
            console.error('Elemento #tabeladevRequisicaoPecaBody não encontrado');
            return;
        }

        // Ordenar registros por data
        registrosTemporarios.sort((a, b) => new Date(a.data_inclusao) - new Date(b.data_inclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        registrosTemporarios.forEach((registro, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <x-tooltip content="Editar">    
                            <button type="button" onclick="editarRegistro(${index})" class="text-blue-600 hover:text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </x-tooltip>
                        <x-tooltip content="Excluir">
                            <button type="button" onclick="excluirRegistro(${index})" class="text-red-600 hover:text-red-800">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244 2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </x-tooltip>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.descricao_produto}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.qtde_produto}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.qtde_devolucao}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function limparFormularioTemp() {
        clearSmartSelect('id_produto');
        clearSmartSelect('unidadeProduto');
        document.querySelector('input[name="qtde_produto"]').value = '';
        document.querySelector('input[name="qtde_devolucao"]').value = '';
    }

    function excluirRegistro(index) {
        registrosTemporarios.splice(index, 1);
        atualizarTabela();
        document.getElementById('devRequisicaoPecas_json').value = JSON.stringify(registrosTemporarios);
    }

    function editarRegistro(index) {
        const registro = registrosTemporarios[index];
        const qtde_recebida = document.querySelector('input[name="qtde_produto"]');
        const qtde_devolucao = document.querySelector('input[name="qtde_devolucao"]');

        setSmartSelectValue('id_produto', registro.id_produto, {
            createIfNotFound: true,
            tempLabel: registro.descricao_produto
        });

        setSmartSelectValue('unidadeProduto', registro.id_unidade_produto, {
            createIfNotFound: true,
            tempLabel: registro.unidade_produto
        });

        qtde_recebida.value = registro.qtde_produto;
        qtde_devolucao.value = registro.qtde_devolucao;


        excluirRegistro(index);
    }

    // Tornando as funções acessíveis no escopo global
    window.adicionardevRequisicaoPeca = adicionardevRequisicaoPeca;
    window.atualizarTabela = atualizarTabela;
    window.limparFormularioTemp = limparFormularioTemp;
    window.excluirRegistro = excluirRegistro;
    window.editarRegistro = editarRegistro;
};

