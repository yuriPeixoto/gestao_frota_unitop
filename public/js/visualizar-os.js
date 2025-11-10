// Função para abrir o modal com os resultados da consulta
function abrirModalVisualizacao(id) {

    // Limpar a tabela antes de adicionar novos dados
    const tbody = document.getElementById('modal-os-geral');
    tbody.innerHTML = '';

    // Fetch para buscar os dados da(s) OS(s)
    fetch(`/admin/ordemservicoauxiliares/${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao buscar dados: ' + response.status);
            }
            return response.json();
        })
        .then(data => {

            // Converter para array se não for
            const osDataArray = Array.isArray(data) ? data : [data];

            // Verificar se temos dados válidos
            if (osDataArray.length === 0) {
                throw new Error('Nenhum dado de OS encontrado');
            }

            // Processar cada item no resultado
            osDataArray.forEach(osData => {
                // Criar uma nova linha na tabela
                const row = document.createElement('tr');
                row.className = 'bg-white divide-y divide-gray-200';

                // Adicionar células com os dados
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="mt-1 text-sm text-gray-900">${osData.id_ordem_servico || 'N/A'}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="mt-1 text-sm text-gray-900">${osData.veiculo.placa || 'N/A'}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="mt-1 text-sm text-gray-900">${formatarData(osData.data_abertura, 'dd/MM/yyyy HH:mm')}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="mt-1 text-sm text-gray-900">${osData.status_ordem_servico.situacao_ordem_servico || 'N/A'}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="mt-1 text-sm text-gray-900">${osData.usuario.name || 'Não Informado'}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="mt-1 text-sm text-gray-900">${osData.departamento.descricao_departamento || 'N/A'}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="mt-1 text-sm text-gray-900">${osData.local_manutencao || 'Não Informado'}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="mt-1 text-sm text-gray-900">${osData.usuario_encerramento.name || 'Não Informado'}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="mt-1 text-sm text-gray-900">${osData.id_lancamento_os_auxiliar || 'N/A'}</p>
                    </td>
                `;

                // Adicionar a linha à tabela
                tbody.appendChild(row);
            });

            // Exibir o modal
            document.getElementById('visualizarModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Erro ao buscar dados:', error);
            alert('Erro ao carregar informações da O.S.: ' + error.message);
        });
}

// Função para fechar o modal
function fecharModal() {
    document.getElementById('visualizarModal').classList.add('hidden');
}

// Função auxiliar para formatar datas
function formatarData(dataString, formato) {
    if (!dataString) return 'Data não informada';

    const data = new Date(dataString);

    // Verifica se a data é válida
    if (isNaN(data.getTime())) return 'Data inválida';

    const dia = String(data.getDate()).padStart(2, '0');
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const ano = data.getFullYear();
    const horas = String(data.getHours()).padStart(2, '0');
    const minutos = String(data.getMinutes()).padStart(2, '0');

    if (formato === 'dd/MM/yyyy') {
        return `${dia}/${mes}/${ano}`;
    } else if (formato === 'dd/MM/yyyy HH:mm') {
        return `${dia}/${mes}/${ano} ${horas}:${minutos}`;
    }

    return dataString;
}