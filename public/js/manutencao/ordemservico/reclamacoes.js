document.addEventListener('DOMContentLoaded', function () {
    let registrosReclamacoesTemporarios = [];

    const reclamacoesJson = document.getElementById('tabelaReclamacoes_json').value;
    const reclamacoes = JSON.parse(reclamacoesJson);

    if (reclamacoes && reclamacoes.length > 0) {
        reclamacoes.forEach(reclamacao => {
            registrosReclamacoesTemporarios.push({
                preOS: reclamacao.id_pre_os,
                dataInclusao: reclamacao.data_inclusao,
                dataAlteracao: reclamacao.data_alteracao,
                placa: reclamacao.veiculo.placa,
                motorista: reclamacao.pessoal.nome,
                telefone: reclamacao.telefone_motorista,
                kmVeiculo: reclamacao.km_realizacao,
                descricaoReclamacao: reclamacao.descricao_reclamacao
            });
        });
        atualizarreclamacoesTabela();
    }

    function atualizarreclamacoesTabela() {
        const tbody = document.getElementById('tabelaReclamacoesBody');
        if (!tbody) {
            console.error('Elemento #tabelaReclamacoesBody não encontrado');
            return;
        }

        // Ordenar registros por data
        registrosReclamacoesTemporarios.sort((a, b) => new Date(a.data_inclusao) - new Date(b.data_inclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        registrosReclamacoesTemporarios.forEach((registro, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${registro.preOS}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarOsreclamacoesData(registro.dataInclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.dataAlteracao ? formatarOsreclamacoesData(registro.dataAlteracao) : '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.placa}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.motorista}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.telefone}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.kmVeiculo}</td>
                <td class="px-6 py-4 wrap">${registro.descricaoReclamacao}</td>
                `;
            tbody.appendChild(tr);
        });
    }

    function formatarOsreclamacoesData(data) {
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

    // Tornando as funções acessíveis no escopo global
    window.atualizarreclamacoesTabela = atualizarreclamacoesTabela;

});
