<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o Ordem de Serviço Auxiliar?')) {
            excluirOsAuxiliar(id);
        }
    }

    function excluirOsAuxiliar(id) {
        fetch(`/admin/ordemservicoauxiliares/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Ordem de Serviço Auxiliar com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Ordem de Serviço Auxiliar');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Ordem de Serviço Auxiliar');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>


@if (isset($ordemservicoauxiliares))
<script>
    function gerarOSPreventivas() {
            var ordemservicoauxiliares = @json($ordemservicoauxiliares);
            let id = ordemservicoauxiliares.id_os_auxiliar;

            let confirma = confirm('Deseja gerar as Preventivas?');

            if (id) {
                fetch('/admin/ordemservicoauxiliares/gerar-os-auxiliar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id: id,
                            confirma: confirma
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        } else {
                            alert(data.error || 'Erro ao gerar as ordens de serviço.');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar dados do veículo:', error);
                    });
            }
        }
</script>
@else
<script>
    function gerarOSPreventivas() {
            let id = 0;
            console.log(id);
            if (id == 0) {
                alert('Salve a OS Auxiliar antes de gerar as Preventivas');
                return;
            }
        }
</script>
@endif

<script>
    async function onimprimirhistorico() {
        const idVeiculo = getSmartSelectValue('id_veiculo').value;
        if (!idVeiculo) {
            alert('Selecione um veículo antes de imprimir o KM.');
            return;
        }

        // Mostrar loader
        document.getElementById('loader-overlay').style.display = 'flex';

        try {
            const response = await fetch(`/admin/ordemservicos/onimprimirkm`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    id_veiculo: idVeiculo,
                })
            });

            // Se espera um arquivo PDF
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/pdf')) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.target = '_blank';
                link.download = `relatorio_km_${idVeiculo}.pdf`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
                return;
            }

            // Caso seja JSON
            const responseData = await response.json();

            if (responseData.success === true) {
                if (responseData.data) {
                    console.log(responseData.data);
                    alert('Relatório gerado com sucesso!');
                } else {
                    console.warn('⚠️ Dados não encontrados');
                    alert('Dados não encontrados para o veículo selecionado.');
                }
            } else {
                console.warn('⚠️ Requisição não foi bem-sucedida');
                alert('Requisição não foi bem-sucedida.');
            }
        } catch (error) {
            console.error('❌ Falha na requisição imprimir Km:', error);
            alert('Erro ao gerar relatório de KM.');
        } finally {
            // Ocultar loader
            document.getElementById('loader-overlay').style.display = 'none';
        }
    }
</script>

<script>
    function openTab(evt, tabName) {
        // Esconde todos os conteúdos das abas
        const tabcontents = document.querySelectorAll(".tabcontent");
        tabcontents.forEach((tab) => {
            tab.classList.add("hidden");
        });

        // Remove a classe "active" de todos os botões
        const tablinks = document.querySelectorAll(".tablink");
        tablinks.forEach((link) => {
            link.classList.remove("bg-blue-500", "text-white");
            link.classList.add("bg-gray-200", "text-gray-700");
        });

        // Mostra o conteúdo da aba atual e adiciona a classe "active" ao botão
        document.getElementById(tabName).classList.remove("hidden");
        evt.currentTarget.classList.remove("bg-gray-200", "text-gray-700");
        evt.currentTarget.classList.add("bg-blue-500", "text-white");
    }

    // Mostra a primeira aba por padrão
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelector(".tablink").click();
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const buttons = document.querySelectorAll(".dropdown-button");

        buttons.forEach(button => {
            button.addEventListener("click", function(event) {
                event.stopPropagation();

                // Fecha todos os outros dropdowns
                document.querySelectorAll(".dropdown-menu").forEach(menu => {
                    if (menu !== this.nextElementSibling) {
                        menu.classList.add("hidden");
                    }
                });

                // Alterna apenas o menu clicado
                this.nextElementSibling.classList.toggle("hidden");
            });
        });

        // Fecha o dropdown ao clicar fora
        document.addEventListener("click", function() {
            document.querySelectorAll(".dropdown-menu").forEach(menu => {
                menu.classList.add("hidden");
            });
        });
    });
</script>
<script>
    function visualizarServicos(id) {
        showModal('vizualizar-OS-servicos');

        const servicos = @json($visualizarOrdemServico ?? []);
        const servicoFiltrado = filtrarServicosPorId(servicos, id);
        const visualizarServicosArray = formatarServicos(servicoFiltrado);

        preencherTabela(visualizarServicosArray);
    }

    function filtrarServicosPorId(servicos, idServico) {
        if (!Array.isArray(servicos)) return [];
        return servicos.filter(servico => servico.id_lancamento_os_auxiliar === idServico);
    }

    function formatarData(dataStr) {
        // Remove o 'Z' do final se existir e substitui por 'T' se necessário
        const dateStr = dataStr.endsWith('Z') ? dataStr.slice(0, -1) : dataStr;
        const data = new Date(dateStr.replace(' ', 'T'));

        return data.toLocaleDateString('pt-BR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    }

    function formatarServicos(servicos) {
        return servicos.map(campo => {
            return {
                idOrdemServico: campo.id_ordem_servico || '',
                placa: campo.veiculo.placa || '',
                dataAberturaAux: formatarData(campo.data_inclusao) || '',
                SituacaoOrdemServico: campo.status_ordem_servico.situacao_ordem_servico || '',
                recepcionista: campo.usuario || '',
                localManutencao: campo.local_manutencao || '',
                recepcionistaEncerramento: campo.usuarioEncerramento || '',
                codlancAuxiliar: campo.id_lancamento_os_auxiliar || '',
            };
        });
    }

    function formatarValorComDesconto(valor) {
        return (parseFloat(valor) || 0).toFixed(2).replace('.', ',');
    }

    function preencherTabela(visualizarServicosArray) {
        const tabelaBody = document.getElementById('tabelaBody');
        tabelaBody.innerHTML = '';

        if (visualizarServicosArray.length === 0) {
            console.log("Nenhum dado para preencher a tabela.");
            return;
        }

        visualizarServicosArray.forEach(servico => {
            const row = criarLinhaTabela(servico);
            tabelaBody.appendChild(row);
        });
    }

    function criarLinhaTabela(servico) {
        const row = document.createElement("tr");

        row.innerHTML = `
            <x-tables.cell>
                <div class="flex items-center">
                    <a href="/admin/ordemservicos/${servico.idOrdemServico}/edit_preventiva">
                    <x-icons.edit class="w-4 h-4 text-blue-600" />
                </a>
                </div>
            </x-tables.cell>
            <x-tables.cell><b>${servico.idOrdemServico}</b></x-tables.cell>
            <x-tables.cell><b>${servico.placa}</b></x-tables.cell>
            <x-tables.cell><b>${servico.dataAberturaAux}</b></x-tables.cell>
            <x-tables.cell><b>${servico.SituacaoOrdemServico}</b></x-tables.cell>
            <x-tables.cell><b>${servico.recepcionista}</b></x-tables.cell>
            <x-tables.cell><b>${servico.localManutencao}</b></x-tables.cell>
            <x-tables.cell><b>${servico.recepcionistaEncerramento}</b></x-tables.cell>
            <x-tables.cell><b>${servico.codlancAuxiliar}</b></x-tables.cell>
        `;

        return row;
    }
</script>