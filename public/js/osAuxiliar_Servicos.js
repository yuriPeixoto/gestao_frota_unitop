document.addEventListener('DOMContentLoaded', function () {
    let registrosOsServicosTemporarios = [];

    const osServicosJson = document.getElementById('osServicos_json').value;
    const servicos = JSON.parse(osServicosJson);

    if (servicos && servicos.length > 0) {
        servicos.forEach(servico => {
            registrosOsServicosTemporarios.push({
                id: servico.id_os_servicos_auxiliar,
                data_inclusao: servico.data_inclusao,
                data_alteracao: servico.data_alteracao,
                IdServico: servico.id_servico || '',
                idMecanico: servico.id_mecanico,
                nomeServico: servico.servico['descricao_servico'],
            });
        });
        atualizarOsServicosTabela();
    }

    function adicionarOsServicos() {
        const IdServico = getSmartSelectValue('id_servico');
        const idMecanico = getSmartSelectValue('id_mecanico');
        const data_inclusao = formatarOsServicosData();
        const data_alteracao = formatarOsServicosData();

        if (!IdServico) {
            alert('Serviço é obrigatório!');
            return;
        }

        // if (!idMecanico) {
        //     alert('Mecanico é obrigatório!');
        //     return;
        // }

        const registroOsServicos = {
            IdServico: IdServico.value,
            idMecanico: idMecanico.value,
            data_inclusao: data_inclusao,
            data_alteracao: data_alteracao,
            nomeServico: IdServico.label
        };

        registrosOsServicosTemporarios.push(registroOsServicos);
        atualizarOsServicosTabela();
        limparOsServicosFormularioTemp();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('osServicos_json').value = JSON.stringify(registrosOsServicosTemporarios);
    }

    function atualizarOsServicosTabela() {
        const tbody = document.getElementById('tabelaOSServicosBody');
        if (!tbody) {
            console.error('Elemento #tabelaOSServicosBody não encontrado');
            return;
        }

        // Ordenar registros por data
        registrosOsServicosTemporarios.sort((a, b) => new Date(a.data_inclusao) - new Date(b.data_inclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        registrosOsServicosTemporarios.forEach((registroOsServicos, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <button type="button" onclick="editarOsServicosRegistro(${index})"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Editar
                        </button>
                        <button type="button" onclick="excluirOsServicosRegistro(${index})" 
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
                <td class="px-6 py-4 whitespace-nowrap">${formatarOsServicosData(registroOsServicos.data_inclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarOsServicosData(registroOsServicos.data_alteracao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroOsServicos.nomeServico}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function limparOsServicosFormularioTemp() {
        clearSmartSelect('id_servico');
        clearSmartSelect('id_mecanico');
    }

    function excluirOsServicosRegistro(index) {
        if (registrosOsServicosTemporarios[index].id) {
            // Se tem ID, significa que já está salvo no banco de dados
            const osId = registrosOsServicosTemporarios[index].id;

            if (confirm('Tem certeza que deseja excluir este item?')) {
                console.log('clicado');

                fetch("/admin/ordemservicoauxiliares/remover-item", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        tipo: 'servico',
                        os_id: osId
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            registrosOsServicosTemporarios.splice(index, 1);
                            atualizarOsServicosTabela();
                            document.getElementById('osVeiculos_json').value = JSON.stringify(registrosOsServicosTemporarios);
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
            registrosOsServicosTemporarios.splice(index, 1);
            atualizarOsServicosTabela();
            document.getElementById('osVeiculos_json').value = JSON.stringify(registrosOsServicosTemporarios);
        }
    }

    function editarOsServicosRegistro(index) {
        const registroOsServicos = registrosOsServicosTemporarios[index];
        setSmartSelectValue('id_servico', registroOsServicos.IdServico);
        setSmartSelectValue('id_mecanico', registroOsServicos.idMecanico);

        excluirOsServicosRegistro(index);
    }

    function formatarOsServicosData(data) {
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
    window.adicionarOsServicos = adicionarOsServicos;
    window.atualizarOsServicosTabela = atualizarOsServicosTabela;
    window.limparOsServicosFormularioTemp = limparOsServicosFormularioTemp;
    window.excluirOsServicosRegistro = excluirOsServicosRegistro;
    window.editarOsServicosRegistro = editarOsServicosRegistro;
});
