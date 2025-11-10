/**
 * Função chamada quando a página termina de carregar.
 * Ela é responsável por popular as tabelas com os registros
 * de transferência e histórico de km de comodato.
 * Além disso, ela também define as funções para adicionar,
 * excluir e editar esses registros.
 */
window.onload = function () {
    // Funções para o primeiro script (historico-trasferencia)
    let registrosTemporarios = [];
    const historicosJson = document.getElementById("historicos_json").value;
    const historicos = JSON.parse(historicosJson);
    if (historicos && historicos.length > 0) {
        historicos.forEach((historico) => {
            registrosTemporarios.push({
                data_inclusao_hist_transferencia: historico.data_inclusao,
                data_alteracao_hist_transferencia: historico.data_alteracao,
                id_filial_origem: historico.filial_origem.name,
                id_filial_destino: historico.filial_destino.name,
                km_transferencia: historico.km_transferencia,
                data_transferencia: historico.data_transferencia,
            });
        });
        atualizarTabela();
    }

    function adicionarHistoricoTransferencia() {
        const filialOrigem = document.querySelector(
            '[name="id_filial_origem"]'
        ).value;
        const filialDestino = document.querySelector(
            '[name="id_filial_destino"]'
        ).value;
        const kmTransferencia = document.querySelector(
            '[name="km_transferencia"]'
        ).value;
        const dataTransferencia = document.querySelector(
            '[name="data_transferencia"]'
        ).value;
        const data_inclusao_hist_transferencia = formatarData();
        const data_alteracao_hist_transferencia = formatarData();

        if (
            filialOrigem == "" ||
            filialDestino == "" ||
            kmTransferencia == "" ||
            dataTransferencia == ""
        ) {
            alert("Preencha todos os campos!");
            return;
        }

        const registro = {
            id_filial_origem: filialOrigem,
            id_filial_destino: filialDestino,
            km_transferencia: kmTransferencia,
            data_transferencia: dataTransferencia,
            data_inclusao_hist_transferencia: data_inclusao_hist_transferencia,
            data_alteracao_hist_transferencia:
                data_alteracao_hist_transferencia,
        };

        registrosTemporarios.push(registro);
        atualizarTabela();
        limparFormularioTemp();

        alert("Registro adicionado com sucesso!");
        document.getElementById("historicos_json").value =
            JSON.stringify(registrosTemporarios);
    }

    // Funções do segundo script (historico-km-comodato)-----------------------------------------------------------------
    let registrosTemporariosKm = [];
    const historicosJsonKm =
        document.getElementById("historicos_json_km").value;
    const historicosKm = JSON.parse(historicosJsonKm);

    if (historicosKm && historicosKm.length > 0) {
        historicosKm.forEach((historico) => {
            registrosTemporariosKm.push({
                horimetro: historico.horimetro,
                km_realizacao: historico.km_realizacao,
                data_realizacao: historico.data_realizacao,
                data_inclusaokm: historico.data_inclusao,
                data_alteracaokm: historico.data_alteracao,
            });
        });
        atualizarTabelaKm();
    }

    function adicionarHistoricoComodatoKm() {
        const horimetroKm = document.querySelector('[name="horimetro"]').value;
        const kmTransferencia = document.querySelector(
            '[name="km_realizacao"]'
        ).value;
        const dataRealizacao = document.querySelector(
            '[name="data_realizacao"]'
        ).value;
        const data_inclusaokm = formatarData();
        const data_alteracaokm = formatarData();

        if (
            horimetroKm == "" ||
            kmTransferencia == "" ||
            dataRealizacao == ""
        ) {
            alert("Preencha todos os campos!");
            return;
        }

        const registroKm = {
            horimetro: horimetroKm,
            km_realizacao: kmTransferencia,
            data_realizacao: dataRealizacao,
            data_inclusaokm: data_inclusaokm,
            data_alteracaokm: data_alteracaokm,
        };

        registrosTemporariosKm.push(registroKm);
        atualizarTabelaKm();
        limparFormularioTempKm();

        alert("Registro adicionado com sucesso!");
        document.getElementById("historicos_json_km").value = JSON.stringify(
            registrosTemporariosKm
        );
    }

    function atualizarTabela() {
        const tbody = document.getElementById("tabelaHistoricoBody");
        if (!tbody) {
            console.error("Elemento #tabelaHistoricoBody não encontrado");
            return;
        }
        registrosTemporarios.sort(
            (a, b) =>
                new Date(a.data_transferencia) - new Date(b.data_transferencia)
        );
        tbody.innerHTML = "";
        registrosTemporarios.forEach((registro, index) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${
                    formatarData(registro.data_inclusao_hist_transferencia) ||
                    "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">${
                    formatarData(registro.data_alteracao_hist_transferencia) ||
                    "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">${
                    formatarData(registro.data_transferencia, true) || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">${
                    registro.id_filial_origem || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">${
                    registro.id_filial_destino || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">${
                    registro.km_transferencia || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <button type="button" onclick="editarRegistro(${index})" class="text-blue-600 hover:text-blue-800">Editar</button>
                        <button type="button" onclick="excluirRegistro(${index})" class="text-red-600 hover:text-red-800">Excluir</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function atualizarTabelaKm() {
        const tbody = document.getElementById("tabelaHistoricoBodyKm");
        if (!tbody) {
            console.error("Elemento #tabelaHistoricoBodyKm não encontrado");
            return;
        }
        registrosTemporariosKm.sort(
            (a, b) => new Date(a.data_realizacao) - new Date(b.data_realizacao)
        );
        tbody.innerHTML = "";
        registrosTemporariosKm.forEach((registro_Km, index) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${
                    formatarData(registro_Km.data_inclusaokm) || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">${
                    formatarData(registro_Km.data_alteracaokm) || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">${
                    formatarData(registro_Km.data_realizacao, true) || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">${
                    registro_Km.km_realizacao || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">${
                    registro_Km.horimetro || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <button type="button" onclick="editarRegistro(${index})" class="text-blue-600 hover:text-blue-800">Editar</button>
                        <button type="button" onclick="excluirRegistro(${index})" class="text-red-600 hover:text-red-800">Excluir</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Funções auxiliares
    function limparFormularioTemp() {
        document.querySelector('[name="id_filial_origem"]').value = "";
        document.querySelector('[name="id_filial_destino"]').value = "";
        document.querySelector('[name="km_transferencia"]').value = "";
        document.querySelector('[name="data_transferencia"]').value = "";
    }

    function limparFormularioTempKm() {
        document.querySelector('[name="horimetro"]').value = "";
        document.querySelector('[name="km_realizacao"]').value = "";
        document.querySelector('[name="data_realizacao"]').value = "";
    }

    function excluirRegistro(index) {
        registrosTemporarios.splice(index, 1);
        atualizarTabela();
        document.getElementById("historicos_json").value =
            JSON.stringify(registrosTemporarios);
    }

    function excluirRegistroKm(index) {
        registrosTemporariosKm.splice(index, 1);
        atualizarTabelaKm();
        document.getElementById("historicos_json_km").value = JSON.stringify(
            registrosTemporariosKm
        );
    }

    function editarRegistro(index) {
        const registro = registrosTemporarios[index];
        document.querySelector('[name="id_filial_origem"]').value =
            registro.id_filial_origem;
        document.querySelector('[name="id_filial_destino"]').value =
            registro.id_filial_destino;
        document.querySelector('[name="km_transferencia"]').value =
            registro.km_transferencia;
        document.querySelector('[name="data_transferencia"]').value =
            registro.data_transferencia;
        excluirRegistro(index);
    }

    function editarRegistroKm(index) {
        const registroKm = registrosTemporariosKm[index];
        document.querySelector('[name="horimetro"]').value =
            registroKm.horimetro;
        document.querySelector('[name="km_realizacao"]').value =
            registroKm.km_realizacao;
        document.querySelector('[name="data_realizacao"]').value =
            registroKm.data_realizacao;
        excluirRegistroKm(index);
    }

    function formatarData(data, formato) {
        const dataObj = new Date(data);
        if (formato == true) {
            const options = {
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
            };
            return dataObj.toLocaleDateString("pt-BR", options);
        }

        const options = {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
        };

        return dataObj.toLocaleDateString("pt-BR", options);
    }

    // Tornando as funções acessíveis no escopo global
    window.adicionarHistoricoTransferencia = adicionarHistoricoTransferencia;
    window.atualizarTabela = atualizarTabela;
    window.limparFormularioTemp = limparFormularioTemp;
    window.excluirRegistro = excluirRegistro;
    window.editarRegistro = editarRegistro;

    window.adicionarHistoricoComodatoKm = adicionarHistoricoComodatoKm;
    window.atualizarTabelaKm = atualizarTabelaKm;
    window.limparFormularioTempKm = limparFormularioTempKm;
    window.excluirRegistroKm = excluirRegistroKm;
    window.editarRegistroKm = editarRegistroKm;
};
