document.addEventListener('DOMContentLoaded', function () {
    let registrosOsManutencaoTemporarios = [];

    const osManutencaoJson = document.getElementById('osManutencao_json').value;
    const manutencoes = JSON.parse(osManutencaoJson);

    if (manutencoes && manutencoes.length > 0) {
        manutencoes.forEach(manutencao => {
            registrosOsManutencaoTemporarios.push({
                id: manutencao.id_os_manutencoes_auxiliar,
                data_inclusao: manutencao.data_inclusao,
                data_alteracao: manutencao.data_alteracao,
                idManutencao: manutencao.id_manutencao,
                manutencao: manutencao.manutencao['descricao_manutencao']

            });
        });
        atualizarOsManutencaoTabela();
    }

    function adicionarOsManutencao() {
        const idManutencao = document.querySelector('[name="id_manutencao"]').value;

        // este é somente para obter o valor do texto do select
        const selectElement = document.querySelector('[name="id_manutencao"]');
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        //Fim

        const data_inclusao = formatarOsManutencaoData();
        const data_alteracao = formatarOsManutencaoData();

        if (!idManutencao) {
            alert('Manutenção é obrigatório!');
            return;
        }

        const registroOsManutencao = {
            idManutencao: idManutencao,
            data_inclusao: data_inclusao,
            data_alteracao: data_alteracao,
            manutencao: selectedOption.text
        };

        registrosOsManutencaoTemporarios.push(registroOsManutencao);
        atualizarOsManutencaoTabela();
        limparOsManutencaoFormularioTemp();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('osManutencao_json').value = JSON.stringify(registrosOsManutencaoTemporarios);
    }

    function atualizarOsManutencaoTabela() {
        const tbody = document.getElementById('tabelaOsManutencaoBody');
        if (!tbody) {
            console.error('Elemento #tabelaOsManutencaoBody não encontrado');
            return;
        }

        // Ordenar registros por data
        registrosOsManutencaoTemporarios.sort((a, b) => new Date(a.data_inclusao) - new Date(b.data_inclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        registrosOsManutencaoTemporarios.forEach((registroOsManutencao, index) => {
            const tr = document.createElement('tr');
            // Adicione o atributo data-id-manutencao
            tr.setAttribute('data-id-manutencao', registroOsManutencao.idManutencao);

            tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex gap-2">
                    <button type="button" onclick="editarOsManutencaoRegistro(${index})"
                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Editar
                    </button>
                    <button type="button" onclick="excluirOsManutencaoRegistro(${index})" 
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
            <td class="px-6 py-4 whitespace-nowrap">${formatarOsManutencaoData(registroOsManutencao.data_inclusao)}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatarOsManutencaoData(registroOsManutencao.data_alteracao)}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroOsManutencao.manutencao}</td>
        `;
            tbody.appendChild(tr);
        });
    }

    function limparOsManutencaoFormularioTemp() {
        document.querySelector('[name="id_manutencao"]').value = '';
    }

    function excluirOsManutencaoRegistro(index) {
        registrosOsManutencaoTemporarios.splice(index, 1);
        atualizarOsManutencaoTabela();
        document.getElementById('osManutencao_json').value = JSON.stringify(registrosOsManutencaoTemporarios);
    }


    function editarOsManutencaoRegistro(index) {
        const registroOsManutencao = registrosOsManutencaoTemporarios[index];
        document.querySelector('[name="id_manutencao"]').value = registroOsManutencao.idManutencao;

        excluirOsManutencaoRegistro(index);
    }

    function formatarOsManutencaoData(data) {
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
    window.adicionarOsManutencao = adicionarOsManutencao;
    window.atualizarOsManutencaoTabela = atualizarOsManutencaoTabela;
    window.limparOsManutencaoFormularioTemp = limparOsManutencaoFormularioTemp;
    window.excluirOsManutencaoRegistro = excluirOsManutencaoRegistro;
    window.editarOsManutencaoRegistro = editarOsManutencaoRegistro;
});
