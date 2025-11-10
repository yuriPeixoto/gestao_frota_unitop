window.onload = function () {
    let registrosOsVeiculosTemporarios = [];

    const osVeiculosJson = document.getElementById('osVeiculos_json').value;
    const veiculos = JSON.parse(osVeiculosJson);

    if (veiculos && veiculos.length > 0) {
        veiculos.forEach(veiculo => {
            registrosOsVeiculosTemporarios.push({
                id: veiculo.id_os_veiculos_auxiliar,
                data_inclusao: veiculo.data_inclusao,
                data_alteracao: veiculo.data_alteracao,
                idVeiculo: veiculo.id_veiculo || '',
                km: veiculo.km_atual,
                placa: veiculo.veiculo['placa']

            });
        });
        atualizarOsVeiculosTabela();
    }


    function adicionarOsVeiculos() {
        const idVeiculo = getSmartSelectValue('id_veiculo');
        const km = document.querySelector('[name="km_atual"]').value;
        const data_inclusao = formatarOsVeiculosData();
        const data_alteracao = formatarOsVeiculosData();
        console.log(idVeiculo, km, data_inclusao, data_alteracao);

        if (!idVeiculo) {
            alert('Veiculo é obrigatório!');
            return;
        }

        if (!km) {
            alert('KM é obrigatório!');
            return;
        }


        const registroOsVeiculos = {
            idVeiculo: idVeiculo.value,
            placa: idVeiculo.label,
            data_inclusao: data_inclusao,
            data_alteracao: data_alteracao,
            km: km
        };

        registrosOsVeiculosTemporarios.push(registroOsVeiculos);
        atualizarOsVeiculosTabela();
        limparOsVeiculosFormularioTemp();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('osVeiculos_json').value = JSON.stringify(registrosOsVeiculosTemporarios);
    }

    function atualizarOsVeiculosTabela() {
        const tbody = document.getElementById('tabelaOSVeiculosBody');
        if (!tbody) {
            console.error('Elemento #tabelaOSVeiculosBody não encontrado');
            return;
        }

        // Ordenar registros por data
        registrosOsVeiculosTemporarios.sort((a, b) => new Date(a.data_inclusao) - new Date(b.data_inclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        registrosOsVeiculosTemporarios.forEach((registroOsVeiculos, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <button type="button" onclick="editarOsVeiculosRegistro(${index})"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Editar
                        </button>
                        <button type="button" onclick="excluirOsVeiculosRegistro(${index})" 
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
                <td class="px-6 py-4 whitespace-nowrap">${formatarOsVeiculosData(registroOsVeiculos.data_inclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarOsVeiculosData(registroOsVeiculos.data_alteracao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroOsVeiculos.placa ?? registroOsVeiculos.idVeiculo}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroOsVeiculos.km}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function limparOsVeiculosFormularioTemp() {
        document.querySelector('[name="id_veiculo"]').value = '';
        document.querySelector('[name="km_atual"]').value = '';
    }

    function excluirOsVeiculosRegistro(index) {
        if (registrosOsVeiculosTemporarios[index].id) {
            // Se tem ID, significa que já está salvo no banco de dados
            const osId = registrosOsVeiculosTemporarios[index].id;

            if (confirm('Tem certeza que deseja excluir este item?')) {

                fetch("/admin/ordemservicoauxiliares/remover-item", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        tipo: 'veiculo',
                        os_id: osId
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            registrosOsVeiculosTemporarios.splice(index, 1);
                            atualizarOsVeiculosTabela();
                            document.getElementById('osServicos_json').value = JSON.stringify(registrosOsVeiculosTemporarios);
                            alert('Item removido com sucesso!');
                        } else {
                            alert('Erro ao remover item: ' + (data.error || 'Erro desconhecido'));
                        }
                    })
                    .catch(error => {
                        alert('Erro ao processar a solicitação: ' + error.message);
                    });
            }
        } else {
            // Se não tem ID, é apenas um registro temporário que ainda não foi salvo
            registrosOsVeiculosTemporarios.splice(index, 1);
            atualizarOsVeiculosTabela();
            document.getElementById('osServicos_json').value = JSON.stringify(registrosOsVeiculosTemporarios);
        }
    }

    function editarOsVeiculosRegistro(index) {
        const registroOsVeiculos = registrosOsVeiculosTemporarios[index];
        document.querySelector('[name="id_veiculo"]').value = registroOsVeiculos.idVeiculo;
        document.querySelector('[name="km_atual"]').value = registroOsVeiculos.km;

        excluirOsVeiculosRegistro(index);
    }

    function formatarOsVeiculosData(data) {
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
    window.adicionarOsVeiculos = adicionarOsVeiculos;
    window.atualizarOsVeiculosTabela = atualizarOsVeiculosTabela;
    window.limparOsVeiculosFormularioTemp = limparOsVeiculosFormularioTemp;
    window.excluirOsVeiculosRegistro = excluirOsVeiculosRegistro;
    window.editarOsVeiculosRegistro = editarOsVeiculosRegistro;
};
