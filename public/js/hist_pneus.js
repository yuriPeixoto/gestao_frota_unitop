window.onload = function () {
    let historicoPneuTemp = [];

    const histPneuJson = document.getElementById('histPneus_json').value;
    const pneus = JSON.parse(histPneuJson);

    if (pneus && pneus.length > 0) {
        pneus.forEach(pneu => {
            historicoPneuTemp.push({
                dataInclusao: pneu.data_inclusao,
                dataAlteracao: pneu.data_alteracao,
                codVidaPneu: pneu.vida_pneu ? pneu.vida_pneu.descricao_vida_pneu : '-',
                idVeiculo: pneu.veiculo ? pneu.veiculo.placa : '-',
                kmInicial: pneu.km_inicial,
                kmFinal: pneu.km_final,
                hrInicial: pneu.hr_inicial,
                hrFinal: pneu.hr_final,
                eixoAplicado: pneu.eixo_aplicado,
                dataRetirada: pneu.data_retirada
            });
        });
        atualizarTabelaHistPneu();
    }


    function atualizarTabelaHistPneu() {
        const tbody = document.getElementById('tabela-historico-body');
        if (!tbody) {
            console.error('Elemento #tabela-historico-body não encontrado');
            return;
        }

        // Ordenar registros por data
        historicoPneuTemp.sort((a, b) => new Date(a.data_inclusao) - new Date(b.data_inclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        historicoPneuTemp.forEach((regHistPneu, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${formatarHistPneuData(regHistPneu.dataInclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarHistPneuData(regHistPneu.dataAlteracao) ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${regHistPneu.codVidaPneu}</td>
                <td class="px-6 py-4 whitespace-nowrap">${regHistPneu.idVeiculo}</td>
                <td class="px-6 py-4 whitespace-nowrap">${regHistPneu.kmInicial ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${regHistPneu.kmFinal ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${regHistPneu.hrInicial ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${regHistPneu.hrFinal ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${regHistPneu.eixoAplicado ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarHistPneuData(regHistPneu.dataRetirada) ?? '-'}</td>

            `;
            tbody.appendChild(tr);
        });
    }

    function formatarHistPneuData(data) {
        if (data) {
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

    window.atualizarTabelaHistPneu = atualizarTabelaHistPneu;

};
