window.onload = function () {
    let registrosTemporarios = [];

    const manutencaoVencidasJson = document.getElementById('vManutencaoVencidas_json').value;
    const manutencaoVencidas = JSON.parse(manutencaoVencidasJson);

    if (manutencaoVencidas && manutencaoVencidas.length > 0) {
        manutencaoVencidas.forEach(manutencaoVencida => {
            registrosTemporarios.push({
                placa: manutencaoVencida.placa,
                manutencao: manutencaoVencida.id_manutencao,
                descricao_manutencao: manutencaoVencida.descricao_manutencao,
                tipo_manutencao: manutencaoVencida.tipo_manutencao_descricao,
                ultimo_km: manutencaoVencida.ultkm,
                data_ultima_manutencao: manutencaoVencida.datault,
                km_frequencia: manutencaoVencida.km_frequencia,
                km_atual: manutencaoVencida.km_atual,
                km_vencer: manutencaoVencida.kmavencer,
                data_vencer: manutencaoVencida.datavencer,
                dias_vencidos: manutencaoVencida.dias_vencidos,
            });
        });
        atualizarTabela();
    }

    function atualizarTabela() {
        const tbody = document.getElementById('tabelaVManutencaoVencidasBody');
        if (!tbody) {
            console.error('Elemento #tabelaVManutencaoVencidasBody não encontrado');
            return;
        }

        // Ordenar registros por data
        registrosTemporarios.sort((a, b) => new Date(a.data_inclusao) - new Date(b.data_inclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        registrosTemporarios.forEach((registro, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${registro.placa}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.manutencao}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.descricao_manutencao}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.tipo_manutencao}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.ultimo_km}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarData(registro.data_ultima_manutencao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.km_frequencia ?? ''}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.km_atual}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.km_vencer ?? ''}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarData(registro.data_vencer)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.dias_vencidos ?? ''}</td>
            `;
            tbody.appendChild(tr);
        });
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
    window.atualizarTabela = atualizarTabela;
};


