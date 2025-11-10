document.addEventListener('DOMContentLoaded', function () {
    let notaFiscalPneuTemp = [];
    let fornecedorSelecionado = null;

    // Logar todos os inputs para diagnóstico inicial
    console.log('Campos do formulário encontrados:');
    document.querySelectorAll('input,select').forEach(el => {
        console.log(`${el.name || el.id}: ${el.tagName} ${el.type}`);
    });

    const notaPneuJson = document.getElementById('pneus_nf_json')?.value || '[]';
    console.log('JSON carregado:', notaPneuJson);
    const nfpneus = JSON.parse(notaPneuJson);

    // Processar dados existentes (código mantido igual)
    if (nfpneus && nfpneus.length > 0) {
        // Código existente...
    }

    // Log completo do evento de seleção
    window.addEventListener('id_fornecedor_nf:selected', function (event) {
        console.log('=== EVENTO DE FORNECEDOR SELECIONADO ===');
        console.log('Event detail completo:', event.detail);

        for (const key in event.detail) {
            console.log(`${key}:`, event.detail[key]);
        }

        if (event.detail.object) {
            console.log('Propriedades do objeto:');
            for (const key in event.detail.object) {
                console.log(`  ${key}:`, event.detail.object[key]);
            }
        }

        fornecedorSelecionado = event.detail;

        // Verificar se há dados no campo hidden JSON
        const fornecedorDataField = document.getElementById('id_fornecedor_nf_selected_data');
        if (fornecedorDataField) {
            console.log('Dados JSON armazenados:', fornecedorDataField.value);
            try {
                const parsedData = JSON.parse(fornecedorDataField.value);
                console.log('Dados JSON parseados:', parsedData);
            } catch (e) {
                console.error('Erro ao parsear dados:', e);
            }
        } else {
            console.log('Campo id_fornecedor_nf_selected_data não encontrado');
        }
    });

    function adicionarNFPneu() {
        console.log('=== INÍCIO ADICIONAR NF PNEU ===');

        const idNFEntradaPneu = document.querySelector('[name="id_nota_fiscal_entrada"]')?.value;
        const nfSerie = document.querySelector('[name="serie"]')?.value;
        const dataEmissao = document.querySelector('[name="data_emissao"]')?.value;

        console.log('Valores do formulário:', {
            idNFEntradaPneu,
            nfSerie,
            dataEmissao
        });

        // Obter o ID e nome do fornecedor
        let fornecedorID, fornecedorNome;

        console.log('Fornecedor selecionado armazenado:', fornecedorSelecionado);

        // Verificar se temos um fornecedor selecionado através do evento
        if (fornecedorSelecionado && fornecedorSelecionado.value) {
            fornecedorID = fornecedorSelecionado.value;

            console.log('Tentando obter nome do fornecedor de várias fontes:');
            // Examinar cada fonte possível para o nome
            if (fornecedorSelecionado.label) {
                console.log('- label disponível:', fornecedorSelecionado.label);
                fornecedorNome = fornecedorSelecionado.label;
            }

            if (fornecedorSelecionado.object) {
                console.log('- object disponível, procurando propriedades:');
                console.log('  nome_fornecedor:', fornecedorSelecionado.object.nome_fornecedor);
                console.log('  nome:', fornecedorSelecionado.object.nome);
                console.log('  text:', fornecedorSelecionado.object.text);

                // Tentar cada propriedade possível
                if (fornecedorSelecionado.object.nome_fornecedor) {
                    fornecedorNome = fornecedorSelecionado.object.nome_fornecedor;
                } else if (fornecedorSelecionado.object.nome) {
                    fornecedorNome = fornecedorSelecionado.object.nome;
                } else if (fornecedorSelecionado.object.text) {
                    fornecedorNome = fornecedorSelecionado.object.text;
                }
            }

            // Se ainda não temos um nome, usar a label ou valor padrão
            if (!fornecedorNome) {
                fornecedorNome = fornecedorSelecionado.label || `Fornecedor ID: ${fornecedorID}`;
            }

            console.log('Nome final escolhido:', fornecedorNome);
        } else {
            // Tentar pegar diretamente do campo hidden
            const fornecedorSelect = document.querySelector('[name="id_fornecedor_nf"]');
            console.log('Campo select encontrado:', !!fornecedorSelect);

            if (fornecedorSelect) {
                fornecedorID = fornecedorSelect.value;
                console.log('ID do fornecedor do campo:', fornecedorID);

                // Tentar obter o campo JSON com dados completos
                const fornecedorDataField = document.getElementById('id_fornecedor_nf_selected_data');
                console.log('Campo de dados JSON encontrado:', !!fornecedorDataField);

                if (fornecedorDataField && fornecedorDataField.value) {
                    try {
                        const selectedData = JSON.parse(fornecedorDataField.value);
                        console.log('Dados JSON parseados:', selectedData);

                        if (selectedData && selectedData.length > 0) {
                            console.log('Objeto do fornecedor:', selectedData[0]);
                            // Testar cada propriedade possível
                            fornecedorNome = selectedData[0].nome_fornecedor ||
                                selectedData[0].nome ||
                                selectedData[0].text ||
                                selectedData[0].label ||
                                `Fornecedor ID: ${fornecedorID}`;
                            console.log('Nome encontrado nos dados JSON:', fornecedorNome);
                        }
                    } catch (e) {
                        console.error('Erro ao analisar dados do fornecedor:', e);
                    }
                }

                // Se não conseguir obter o nome, use um placeholder
                if (!fornecedorNome) {
                    fornecedorNome = `Fornecedor ID: ${fornecedorID}`;
                    console.log('Usando nome padrão:', fornecedorNome);
                }
            } else {
                console.log('Fornecedor não encontrado, exibindo alerta');
                alert('Por favor, selecione um fornecedor!');
                return;
            }
        }

        const valorUnitario = document.querySelector('[name="valor_unitario"]')?.value || '';
        const valorTotal = document.querySelector('[name="valor_total"]')?.value || '';

        if (!idNFEntradaPneu) {
            alert('Número da Nota Fiscal é obrigatório!');
            return;
        }

        const registroNFPneus = {
            fornecedorNome: fornecedorNome,
            fornecedorID: fornecedorID,
            numeroNF: idNFEntradaPneu,
            serie: nfSerie,
            valorUnitario: valorUnitario,
            valorTotal: valorTotal,
            dataEmissao: dataEmissao,
            dataInclusao: new Date().toISOString(),
            dataAlteracao: new Date().toISOString()
        };

        console.log('Registro a ser adicionado:', registroNFPneus);

        notaFiscalPneuTemp.push(registroNFPneus);
        atualizarTabelaNFPneu();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('pneus_nf_json').value = JSON.stringify(notaFiscalPneuTemp);
    }

    // Restante do código permanece igual...

    window.atualizarTabelaNFPneu = atualizarTabelaNFPneu;
    window.adicionarNFPneu = adicionarNFPneu;
});