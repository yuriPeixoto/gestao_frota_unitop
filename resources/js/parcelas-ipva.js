window.onload = function () {
    let registrosTemporarios = [];

    const ipvaVeiculosJson = document.getElementById('ipvaveiculos_json').value;
    const ipvaVeiculos = JSON.parse(ipvaVeiculosJson);


    if (ipvaVeiculos && ipvaVeiculos.length > 0) {
        ipvaVeiculos.forEach(ipvaVeiculos => {
            registrosTemporarios.push({
                id_parcelas_ipva: ipvaVeiculos.id_parcelas_ipva,
                data_inclusao: ipvaVeiculos.data_inclusao,
                data_alteracao: ipvaVeiculos.data_alteracao,
                numero_parcela: ipvaVeiculos.numero_parcela,
                data_vencimento: ipvaVeiculos.data_vencimento,
                valor_parcela: ipvaVeiculos.valor_parcela,
                data_pagamento: ipvaVeiculos.data_pagamento,
                valor_juros: ipvaVeiculos.valor_juros,
                valor_desconto: ipvaVeiculos.valor_desconto,
                valor_pagamento: ipvaVeiculos.valor_pagamento
            });
        });
        atualizarTabela();
    }

    function adicionaripvaVeiculo() {
        const idParcelasIPVA = document.querySelector('[name="id_parcelas_ipva"]').value;
        const dataInclusao = Date.now();
        const dataAlteracao = Date.now();
        const numeroParcela = document.querySelector('[name="numero_parcela"]').value;
        const dataVencimento = document.querySelector('[name="data_vencimento"]').value;
        const dataPagamento = document.querySelector('[name="data_pagamento"]').value;
        const valorParcela = SanitizeMoeda(document.querySelector('[name="valor_parcela"]').value);
        const valorDesconto = SanitizeMoeda(document.querySelector('[name="valor_desconto"]').value);
        const valorJuros = SanitizeMoeda(document.querySelector('[name="valor_juros"]').value);
        const valorPagamento = SanitizeMoeda(document.querySelector('[name="valor_pagamento"]').value);

        const registro = {
            id_parcelas_ipva: idParcelasIPVA,
            data_inclusao: dataInclusao,
            data_alteracao: dataAlteracao,
            numero_parcela: numeroParcela,
            data_vencimento: dataVencimento,
            valor_parcela: valorParcela,
            data_pagamento: dataPagamento,
            valor_juros: valorJuros,
            valor_desconto: valorDesconto,
            valor_pagamento: valorPagamento
        };

        registrosTemporarios.push(registro);
        atualizarTabela();
        limparFormularioTemp();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('ipvaveiculos_json').value = JSON.stringify(registrosTemporarios);
    }

    function atualizarTabela() {
        const tbody = document.getElementById('tabelaParcelasIpvaBody');
        if (!tbody) {
            console.error('Elemento #tabelaParcelasIpvaBody não encontrado');
            return;
        }

        // Ordenar registros por data
        registrosTemporarios.sort((a, b) => new Date(a.data_inclusao) - new Date(b.data_inclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        registrosTemporarios.forEach((registro, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-center">${registro.id_parcelas_ipva}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${formatarData(registro.data_inclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${formatarData(registro.data_alteracao)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${registro.numero_parcela}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${formatarData(registro.data_vencimento)}</td>
                <td class="valor_parcela px-6 py-4 whitespace-nowrap text-center">${ConverterMoeda(registro.valor_parcela)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${formatarData(registro.data_pagamento)}</td>
                <td class="valor_desconto px-6 py-4 whitespace-nowrap text-center">${ConverterMoeda(registro.valor_desconto)}</td>
                <td class="valor_juros px-6 py-4 whitespace-nowrap text-center">${ConverterMoeda(registro.valor_juros)}</td>
                <td class="valor_pagamento px-6 py-4 whitespace-nowrap text-center">${ConverterMoeda(registro.valor_pagamento)}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <button type="button" onclick="editarRegistro(${index})" class="text-blue-600 hover:text-blue-800">
                            Editar
                        </button>
                        <button type="button" onclick="excluirRegistro(${index})" class="text-red-600 hover:text-red-800">
                            Excluir
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function limparFormularioTemp() {
        document.querySelector('[name="id_parcelas_ipva"]').value = '';
        document.querySelector('[name="numero_parcela"]').value = '';
        document.querySelector('[name="data_vencimento"]').value = '';
        document.querySelector('[name="data_pagamento"]').value = '';
        document.querySelector('[name="valor_parcela"]').value = '';
        document.querySelector('[name="valor_desconto"]').value = '';
        document.querySelector('[name="valor_juros"]').value = '';
        document.querySelector('[name="valor_pagamento"]').value = '';
    }

    function excluirRegistro(index) {
        registrosTemporarios.splice(index, 1);
        atualizarTabela();
        document.getElementById('ipvaVeiculos_json').value = JSON.stringify(registrosTemporarios);
    }

    function editarRegistro(index) {
        const registro = registrosTemporarios[index];

        document.querySelector('[name="id_parcelas_ipva"]').value = registro.id_parcelas_ipva;
        document.querySelector('[name="numero_parcela"]').value = registro.numero_parcela;
        document.querySelector('[name="data_vencimento"]').value = registro.data_vencimento;
        document.querySelector('[name="data_pagamento"]').value = registro.data_pagamento;
        document.querySelector('[name="valor_parcela"]').value = registro.valor_parcela ?? 0;
        document.querySelector('[name="valor_desconto"]').value = registro.valor_desconto ?? 0;
        document.querySelector('[name="valor_juros"]').value = registro.valor_juros ?? 0;
        document.querySelector('[name="valor_pagamento"]').value = registro.valor_pagamento ?? 0;
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

    function ConverterMoeda(valor) {
        return Number(valor).toLocaleString("pt-BR", {
            style: "currency",
            currency: "BRL"
        });
    }

    function SanitizeMoeda(valor) {
        if (!valor) return 0; // Caso valor seja undefined ou vazio
        let retorno = valor.replace(/R\$\s*/g, '').replace(/\./g, '').replace(/,/g, '.');
        return parseFloat(retorno) || 0; // Converte para número, retornando 0 se falhar
    }

    // Tornando as funções acessíveis no escopo global
    window.adicionaripvaVeiculo = adicionaripvaVeiculo;
    window.atualizarTabela = atualizarTabela;
    window.limparFormularioTemp = limparFormularioTemp;
    window.excluirRegistro = excluirRegistro;
    window.editarRegistro = editarRegistro;
};

