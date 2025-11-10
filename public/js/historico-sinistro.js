document.addEventListener('DOMContentLoaded', function () {
    let registrosTemporarios = [];

    const historicosJson = document.getElementById('historicos_json').value;
    const historicos = JSON.parse(historicosJson);

    if (historicos && historicos.length > 0) {
        historicos.forEach(historico => {
            registrosTemporarios.push({
                data_evento: historico.data_evento,
                descricao_situacao: historico.descricao_situacao,
                observacao: historico.observacao || ''
            });
        });
        atualizarTabela();
    }

    function adicionarHistorico() {
        const dataEvento = document.querySelector('[name="temp_data_evento"]').value;
        const descricao = document.querySelector('[name="temp_descricao_situacao"]').value;
        const observacao = document.querySelector('[name="temp_observacao"]').value;

        if (!dataEvento || !descricao) {
            alert('Data do evento e situação são obrigatórios!');
            return;
        }

        const registro = {
            data_evento: dataEvento,
            descricao_situacao: descricao,
            observacao: observacao
        };

        registrosTemporarios.push(registro);
        atualizarTabela();
        limparFormularioTemp();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('historicos_json').value = JSON.stringify(registrosTemporarios);
    }

    function atualizarTabela() {
        const tbody = document.getElementById('tabelaHistoricoBody');
        if (!tbody) {
            console.error('Elemento #tabelaHistoricoBody não encontrado');
            return;
        }

        // Ordenar registros por data
        registrosTemporarios.sort((a, b) => new Date(a.data_evento) - new Date(b.data_evento));

        const bloqueado = window.appConfig.bloquear;

        tbody.innerHTML = ''; // Limpa as linhas existentes

        registrosTemporarios.forEach((registro, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${formatarData(registro.data_evento)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.descricao_situacao}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.observacao || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        ${bloqueado ? '' : `
                            <button type="button" onclick="editarRegistro(${index})" class="text-blue-600 hover:text-blue-800">
                                Editar
                            </button>
                            <button type="button" onclick="excluirRegistro(${index})" class="text-red-600 hover:text-red-800">
                                Excluir
                            </button>
                        `}
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function limparFormularioTemp() {
        document.querySelector('[name="temp_data_evento"]').value = '';
        document.querySelector('[name="temp_descricao_situacao"]').value = '';
        document.querySelector('[name="temp_observacao"]').value = '';
    }

    function excluirRegistro(index) {
        registrosTemporarios.splice(index, 1);
        atualizarTabela();
        document.getElementById('historicos_json').value = JSON.stringify(registrosTemporarios);
    }

    function editarRegistro(index) {
        const registro = registrosTemporarios[index];
        document.querySelector('[name="temp_data_evento"]').value = registro.data_evento;
        document.querySelector('[name="temp_descricao_situacao"]').value = registro.descricao_situacao;
        document.querySelector('[name="temp_observacao"]').value = registro.observacao;

        excluirRegistro(index);
    }

    function formatarData(data) {
        const dataObj = new Date(data);
        const options = { day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'UTC' };
        return dataObj.toLocaleDateString('pt-BR', options);
    }

    // Tornando as funções acessíveis no escopo global
    window.adicionarHistorico = adicionarHistorico;
    window.atualizarTabela = atualizarTabela;
    window.limparFormularioTemp = limparFormularioTemp;
    window.excluirRegistro = excluirRegistro;
    window.editarRegistro = editarRegistro;
});
