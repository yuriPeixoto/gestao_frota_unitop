window.onload = function () {
    let registrosTemporarios = [];

    const cadastroProdutoJson = document.getElementById('cad_produtos').value;
    const cadastroProdutos = JSON.parse(cadastroProdutoJson);


    if (cadastroProdutos && cadastroProdutos.length > 0) {
        cadastroProdutos.forEach(cadastroProduto => {
            registrosTemporarios.push({
                data_inclusao: cadastroProduto.data_inclusao,
                data_alteracao: cadastroProduto.data_alteracao,
                id_modelo_veiculo: cadastroProduto.id_modelo_veiculo,
                modelo_veiculo: cadastroProduto.modelo.descricao_modelo_veiculo
            });
        });
        atualizarTabela();
    }

    function adicionarCadastroProduto() {
        console.log('clicado');
        const dataInclusao = Date.now();
        const dataAlteracao = Date.now();

        const idModeloVeiculo = getSmartSelectValue('aplicacao').value;
        const modeloVeiculo = getSmartSelectValue('aplicacao').label;

        const registro = {
            data_inclusao: dataInclusao,
            data_alteracao: dataAlteracao,
            id_modelo_veiculo: idModeloVeiculo,
            modelo_veiculo: modeloVeiculo
        };

        registrosTemporarios.push(registro);
        atualizarTabela();
        limparFormularioTemp();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('cad_produtos').value = JSON.stringify(registrosTemporarios);
    }

    function atualizarTabela() {
        const tbody = document.getElementById('tabelaCadastroProdutoBody');
        if (!tbody) {
            console.error('Elemento #tabelaCadastroProdutoBody não encontrado');
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
                <td class="px-6 py-4 whitespace-nowrap">${formatarData(registro.data_inclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarData(registro.data_alteracao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.modelo_veiculo}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function limparFormularioTemp() {
        clearSmartSelect('aplicacao');
    }

    function excluirRegistro(index) {
        registrosTemporarios.splice(index, 1);
        atualizarTabela();
        document.getElementById('cad_produtos').value = JSON.stringify(registrosTemporarios);
    }

    function editarRegistro(index) {
        const registro = registrosTemporarios[index];

        setSmartSelectByLabel('aplicacao', registro.modelo_veiculo);

        excluirRegistro(index);
    }

    function formatarData(data) {
        if (!data)
            return '';

        const dataObj = new Date(data);
        const options = { day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'UTC' };

        if (dataObj.toLocaleDateString('pt-BR', options) === 'Invalid Date')
            return '';

        return dataObj.toLocaleDateString('pt-BR', options);
    }

    // Tornando as funções acessíveis no escopo global
    window.adicionarCadastroProduto = adicionarCadastroProduto;
    window.atualizarTabela = atualizarTabela;
    window.limparFormularioTemp = limparFormularioTemp;
    window.excluirRegistro = excluirRegistro;
    window.editarRegistro = editarRegistro;
};

