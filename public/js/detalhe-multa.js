window.onload = function () {
    let registrosTemporarios = [];

    const detalheMultaJson = document.getElementById('detalheMulta_json').value;
    const detalheMultas = JSON.parse(detalheMultaJson);


    if (detalheMultas && detalheMultas.length > 0) {
        detalheMultas.forEach(detalheMulta => {
            registrosTemporarios.push({
                data_inclusao: detalheMulta.data_inclusao,
                data_alteracao: detalheMulta.data_alteracao,
                prazo_indicacao_condutor: detalheMulta.prazo_indicacao_condutor,
                data_envio_financeiro: detalheMulta.data_envio_financeiro,
                data_pagamento: detalheMulta.data_pagamento,
                data_recebimento_notificacao: detalheMulta.data_recebimento_notificacao,
                data_envio_departamento: detalheMulta.data_envio_departamento,
                data_indeferimento_recurso: detalheMulta.data_indeferimento_recurso,
                data_inicio_recurso: detalheMulta.data_inicio_recurso,
                responsavel_recurso: detalheMulta.responsavel_recurso,
                notificacao_detalhe: detalheMulta.notificacao_detalhe,
                id_motivo_multa: detalheMulta.id_motivo_multa
            });
        });
        atualizarTabela();
    }

    function adicionarDetalheMulta() {
        const dataInclusao = Date.now();
        const dataAlteracao = Date.now();
        const prazoIndicacaoCondutor = document.querySelector('[name="prazo_indicacao_condutor"]').value;
        const dataEnvioFinanceiro = document.querySelector('[name="data_envio_financeiro"]').value;
        const dataPagamento = document.querySelector('[name="data_pagamento"]').value;
        const dataRecNofiticacao = document.querySelector('[name="data_recebimento_notificacao"]').value;
        const dataEnvioDepartamento = document.querySelector('[name="data_envio_departamento"]').value;
        const dataIndeferimentoRecurso = document.querySelector('[name="data_indeferimento_recurso"]').value;
        const dataInicioRecurso = document.querySelector('[name="data_inicio_recurso"]').value;
        const responsavelRecurso = document.querySelector('[name="responsavel_recurso"]').value;
        const notificacaoDetalhe = document.querySelector('[name="notificacao_detalhe"]').value;


        const registro = {
            data_inclusao: dataInclusao,
            data_alteracao: dataAlteracao,
            prazo_indicacao_condutor: prazoIndicacaoCondutor,
            data_envio_financeiro: dataEnvioFinanceiro,
            data_pagamento: dataPagamento,
            data_recebimento_notificacao: dataRecNofiticacao,
            data_envio_departamento: dataEnvioDepartamento,
            data_indeferimento_recurso: dataIndeferimentoRecurso,
            data_inicio_recurso: dataInicioRecurso,
            responsavel_recurso: responsavelRecurso,
            notificacao_detalhe: notificacaoDetalhe
        };

        registrosTemporarios.push(registro);
        atualizarTabela();
        limparFormularioTemp();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('detalheMulta_json').value = JSON.stringify(registrosTemporarios);
    }

    function atualizarTabela() {
        const tbody = document.getElementById('tabelaDetalheMultaBody');
        if (!tbody) {
            console.error('Elemento #tabelaDetalheMultaBody não encontrado');
            return;
        }

        // Ordenar registros por data
        registrosTemporarios.sort((a, b) => new Date(a.data_inclusao) - new Date(b.data_inclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        registrosTemporarios.forEach((registro, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${formatarData(registro.data_inclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarData(registro.data_alteracao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.prazo_indicacao_condutor}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarData(registro.data_envio_financeiro)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarData(registro.data_pagamento)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarData(registro.data_recebimento_notificacao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarData(registro.data_envio_departamento)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarData(registro.data_indeferimento_recurso)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarData(registro.data_inicio_recurso)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.responsavel_recurso}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.notificacao_detalhe}</td>
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
        document.querySelector('[name="prazo_indicacao_condutor"]').value = '';
        document.querySelector('[name="data_envio_financeiro"]').value = '';
        document.querySelector('[name="data_pagamento"]').value = '';
        document.querySelector('[name="data_recebimento_notificacao"]').value = '';
        document.querySelector('[name="data_envio_departamento"]').value = '';
        document.querySelector('[name="data_indeferimento_recurso"]').value = '';
        document.querySelector('[name="data_inicio_recurso"]').value = '';
        document.querySelector('[name="responsavel_recurso"]').value = '';
        document.querySelector('[name="notificacao_detalhe"]').value = '';
    }

    function excluirRegistro(index) {
        registrosTemporarios.splice(index, 1);
        atualizarTabela();
        document.getElementById('detalheMulta_json').value = JSON.stringify(registrosTemporarios);
    }

    function editarRegistro(index) {
        const registro = registrosTemporarios[index];
        document.querySelector('[name="notificacao_detalhe"]').value = registro.notificacao_detalhe;
        document.querySelector('[name="prazo_indicacao_condutor"]').value = registro.prazo_indicacao_condutor;
        document.querySelector('[name="data_envio_financeiro"]').value = registro.data_envio_financeiro;
        document.querySelector('[name="data_pagamento"]').value = registro.data_pagamento;
        document.querySelector('[name="data_recebimento_notificacao"]').value = registro.data_recebimento_notificacao;
        document.querySelector('[name="data_envio_departamento"]').value = registro.data_envio_departamento;
        document.querySelector('[name="data_indeferimento_recurso"]').value = registro.data_indeferimento_recurso;
        document.querySelector('[name="data_inicio_recurso"]').value = registro.data_inicio_recurso;
        document.querySelector('[name="responsavel_recurso"]').value = registro.responsavel_recurso;

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
    window.adicionarDetalheMulta = adicionarDetalheMulta;
    window.atualizarTabela = atualizarTabela;
    window.limparFormularioTemp = limparFormularioTemp;
    window.excluirRegistro = excluirRegistro;
    window.editarRegistro = editarRegistro;
};

