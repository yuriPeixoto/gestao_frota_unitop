document.addEventListener('DOMContentLoaded', function () {
    let notaFiscalPneuTemp = [];
    let fornecedorSelecionado = null; // Para armazenar os dados do fornecedor

    const notaPneuJson = document.getElementById('pneus_nf_json').value;
    const nfpneus = JSON.parse(notaPneuJson || '[]');

    if (nfpneus && nfpneus.length > 0) {
        nfpneus.forEach(nfpneu => {
            console.log(nfpneu);
            notaFiscalPneuTemp.push({
                fornecedorID: nfpneu.fornecedor ? nfpneu.fornecedor.id : null,
                dataInclusao: nfpneu.data_inclusao,
                dataAlteracao: nfpneu.data_alteracao,
                numeroNF: nfpneu.numero_nf,
                serie: nfpneu.serie,
                valorUnitario: nfpneu.valor_formatado,
                valorTotal: nfpneu.valor_total_formatado,
                dataEmissao: nfpneu.data_nf
            });
        });
        atualizarTabelaNFPneu();
    }

    // Adicionar listener para o evento de seleção do fornecedor
    window.addEventListener('id_fornecedor_nf:selected', function (event) {
        console.log('Fornecedor selecionado:', event.detail);
        fornecedorSelecionado = event.detail;
    });

    function adicionarNFPneu() {
        const idNFEntradaPneu = document.querySelector('[name="nf_entrada_pneu"]').value;
        const nfSerie = document.querySelector('[name="serie"]').value;
        const dataEmissao = document.querySelector('[name="data_emissao"]').value;
        const valorUnitario = document.querySelector('[name="valor_unitario"]').value;
        const valorTotal = document.querySelector('[name="valor_total"]').value;
        const fornecedorID = document.querySelector('[name="id_fornecedor_nf"]').value;

        if (!idNFEntradaPneu) {
            alert('Número da Nota Fiscal é obrigatório!');
            return;
        }

        const registroNFPneus = {
            fornecedorID: fornecedorID,
            numeroNF: idNFEntradaPneu,
            serie: nfSerie,
            valorUnitario: valorUnitario,
            valorTotal: valorTotal,
            dataEmissao: dataEmissao,
            dataInclusao: new Date().toISOString(),
            dataAlteracao: new Date().toISOString()
        };

        notaFiscalPneuTemp.push(registroNFPneus);
        atualizarTabelaNFPneu();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('pneus_nf_json').value = JSON.stringify(notaFiscalPneuTemp);
    }


    function atualizarTabelaNFPneu() {
        const tbody = document.getElementById('tabela-nf-body');
        if (!tbody) {
            console.error('Elemento #tabela-nf-body não encontrado');
            return;
        }

        // Ordenar registros por data
        notaFiscalPneuTemp.sort((a, b) => new Date(a.dataInclusao) - new Date(b.dataInclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        notaFiscalPneuTemp.forEach((regNFPneu, index) => {
            console.log(regNFPneu);
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${regNFPneu.fornecedorID}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarNFPneuData(regNFPneu.dataInclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarNFPneuData(regNFPneu.dataAlteracao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${regNFPneu.numeroNF}</td>
                <td class="px-6 py-4 whitespace-nowrap">${regNFPneu.serie ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${regNFPneu.valorUnitario}</td>
                <td class="px-6 py-4 whitespace-nowrap">${regNFPneu.valorTotal}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarNFPneuData(regNFPneu.dataEmissao, 'data')}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function formatarNFPneuData(data, formato = 'completo', opcoesCustomizadas = null) {
        if (!data) return '-';

        // Cria a data preservando o fuso horário específico
        let dataObj;

        if (typeof data === 'string') {
            // Se for uma string ISO, precisamos garantir que a data seja interpretada corretamente
            if (data.includes('T') || data.includes('Z')) {
                // String no formato ISO
                dataObj = new Date(data);
            } else {
                // String sem informação de fuso, assume que é na hora local
                // Primeiro, padronize o formato para YYYY-MM-DD HH:MM:SS
                const [dataParte, horaParte = '00:00:00'] = data.split(' ');
                const [ano, mes, dia] = dataParte.includes('-')
                    ? dataParte.split('-')
                    : dataParte.split('/').reverse();

                // Cria a data especificando todos os componentes para evitar problemas de fuso
                const [hora, minuto, segundo] = horaParte.split(':');
                dataObj = new Date(ano, mes - 1, dia, hora, minuto, segundo);
            }
        } else if (data instanceof Date) {
            dataObj = new Date(data);
        } else {
            return '';
        }

        // Configura as opções de formatação com base no formato solicitado
        let opcoes;

        switch (formato) {
            case 'data':
                opcoes = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    timeZone: 'America/Cuiaba'
                };
                break;
            case 'hora':
                opcoes = {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    timeZone: 'America/Cuiaba'
                };
                break;
            case 'completo':
                opcoes = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    timeZone: 'America/Cuiaba'
                };
                break;
            case 'customizado':
                opcoes = opcoesCustomizadas || {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    timeZone: 'America/Cuiaba'
                };
                break;
            default:
                opcoes = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    timeZone: 'America/Cuiaba'
                };
        }

        return dataObj.toLocaleString('pt-BR', opcoes);
    }

    window.atualizarTabelaNFPneu = atualizarTabelaNFPneu;
    window.adicionarNFPneu = adicionarNFPneu;
});