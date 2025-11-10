document.addEventListener('DOMContentLoaded', function () {
    let registrosSocorrosTemporarios = [];

    const socorroJson = document.getElementById('tabelaSocorro_json').value;
    const socorros = JSON.parse(socorroJson);

    if (socorros && socorros.length > 0) {
        socorros.forEach(socorro => {
            registrosSocorrosTemporarios.push({
                dataInclusao: socorro.data_inclusao,
                dataAlteracao: socorro.data_alteracao,
                idVeiculo: socorro.id_veiculo,
                placa: socorro.veiculo.placa,
                localSocorro: socorro.municipio.nome_municipio,
                idSocorrista: socorro.id_socorrista,
                socorrista: socorro.socorrista.nome,
                idLocalSocorro: socorro.local_socorro
            });
        });
        atualizarSocorrosTabela();
    }


    function adicionarSocorro() {
        const idVeiculo = getSmartSelectValue('idVeiculo_socorro');
        const socorrista = getSmartSelectValue('id_socorrista');
        const localSocorro = getSmartSelectValue('id_municipio');
        const dataInclusao = Date.now();
        const dataAlteracao = Date.now();


        if (!idVeiculo.value) {
            alert('Veiculo é obrigatório!');
            return;
        }

        if (!socorrista.value) {
            alert('Socorrista é obrigatório!');
            return;
        }

        if (!localSocorro.value) {
            alert('Local do Socorro é obrigatório!');
            return;
        }

        const registroSocorro = {
            idVeiculo: idVeiculo.value,
            placa: idVeiculo.label,
            idSocorrista: socorrista.value,
            socorrista: socorrista.label,
            localSocorro: localSocorro.label,
            idLocalSocorro: localSocorro.value,
            dataInclusao: dataInclusao,
            dataAlteracao: dataAlteracao,
        };

        registrosSocorrosTemporarios.push(registroSocorro);
        atualizarSocorrosTabela();
        limparSocorrosFormularioTemp();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('tabelaSocorro_json').value = JSON.stringify(registrosSocorrosTemporarios);
    }

    function atualizarSocorrosTabela() {
        const tbody = document.getElementById('tabelaSocorroBody');
        if (!tbody) {
            console.error('Elemento #tabelaSocorroBody não encontrado');
            return;
        }

        // Ordenar registros por data
        registrosSocorrosTemporarios.sort((a, b) => new Date(a.data_inclusao) - new Date(b.data_inclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        registrosSocorrosTemporarios.forEach((registroSocorro, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <button type="button" onclick="editarOssocorrosRegistro(${index})"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Editar
                        </button>
                        <button type="button" onclick="excluirOssocorrosRegistro(${index})" 
                            class="btn-excluir inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Excluir
                        </button>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarOssocorrosData(registroSocorro.dataInclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarOssocorrosData(registroSocorro.dataAlteracao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroSocorro.placa ?? registroSocorro.idVeiculo}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroSocorro.socorrista}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroSocorro.localSocorro}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function limparSocorrosFormularioTemp() {
        clearSmartSelect('idVeiculo_socorro');
        clearSmartSelect('id_socorrista');
        clearSmartSelect('id_municipio');
    }

    function excluirOssocorrosRegistro(index) {
        registrosSocorrosTemporarios[index].id
        registrosSocorrosTemporarios.splice(index, 1);
        atualizarSocorrosTabela();
        document.getElementById('tabelaSocorro_json').value = JSON.stringify(registrosSocorrosTemporarios);

    }

    function editarOssocorrosRegistro(index) {
        const registroSocorro = registrosSocorrosTemporarios[index];
        console.log('truco');
        setSmartSelectValue('idVeiculo_socorro', registroSocorro.idVeiculo, {
            createIfNotFound: true,
            tempLabel: registroSocorro.placa
        });

        setSmartSelectValue('id_socorrista', registroSocorro.idSocorrista, {
            createIfNotFound: true,
            tempLabel: registroSocorro.socorrista
        });

        setSmartSelectValue('id_municipio', registroSocorro.idLocalSocorro, {
            createIfNotFound: true,
            tempLabel: registroSocorro.localSocorro
        });

        excluirOssocorrosRegistro(index);
    }

    function formatarOssocorrosData(data) {
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
    window.adicionarSocorro = adicionarSocorro;
    window.atualizarSocorrosTabela = atualizarSocorrosTabela;
    window.limparSocorrosFormularioTemp = limparSocorrosFormularioTemp;
    window.excluirOssocorrosRegistro = excluirOssocorrosRegistro;
    window.editarOssocorrosRegistro = editarOssocorrosRegistro;
});
