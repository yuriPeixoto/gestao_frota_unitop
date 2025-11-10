document.addEventListener('DOMContentLoaded', function () {
    let registrosEnvTemporarios = [];

    const envolvidosJson = document.getElementById('envolvidos_json').value;
    const envolvidos = JSON.parse(envolvidosJson);

    if (envolvidos && envolvidos.length > 0) {
        envolvidos.forEach(envolvido => {
            registrosEnvTemporarios.push({
                data_inclusao: formatarEnvData(envolvido.data_inclusao),
                nome: envolvido.nome_pessoal,
                telefone: envolvido.telefone,
                cpf: envolvido.cpf
            });
        });
        atualizarEnvTabela();
    }

    function adicionarEnvolvido() {
        const data_inclusao = formatarEnvData();
        const nome = document.querySelector('[name="nome"]').value;
        const telefone = document.querySelector('[name="telefone"]').value;
        const cpf = document.querySelector('[name="cpf"]').value;

        if (!nome || !telefone || !cpf) {
            alert('Documentos são obrigatórios!');
            return;
        }

        const registroEnvolvido = {
            data_inclusao: data_inclusao,
            nome: nome,
            telefone: telefone,
            cpf: cpf
        };

        registrosEnvTemporarios.push(registroEnvolvido);
        atualizarEnvTabela();
        limparEnvFormularioTemp();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('envolvidos_json').value = JSON.stringify(registrosEnvTemporarios);
    }

    function atualizarEnvTabela() {
        const tbody = document.getElementById('tabelaEnvolvidoBody');
        if (!tbody) {
            console.error('Elemento #tabelaEnvolvidoBody não encontrado');
            return;
        }

        const bloqueado = window.appConfig.bloquear;

        // Ordenar registros por data
        registrosEnvTemporarios.sort((a, b) => new Date(a.data_inclusao) - new Date(b.data_inclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        registrosEnvTemporarios.forEach((registroEnvolvido, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${registroEnvolvido.data_inclusao}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroEnvolvido.nome}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroEnvolvido.telefone}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registroEnvolvido.cpf}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        ${bloqueado ? '' : `
                            <button type="button" onclick="editarEnvRegistro(${index})" class="text-blue-600 hover:text-blue-800">
                                Editar
                            </button>
                            <button type="button" onclick="excluirEnvRegistro(${index})" class="text-red-600 hover:text-red-800">
                                Excluir
                            </button>
                        `}
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function limparEnvFormularioTemp() {
        document.querySelector('[name="nome"]').value = '';
        document.querySelector('[name="telefone"]').value = '';
        document.querySelector('[name="cpf"]').value = '';
    }

    function excluirEnvRegistro(index) {
        registrosEnvTemporarios.splice(index, 1);
        atualizarEnvTabela();
        document.getElementById('envolvidos_json').value = JSON.stringify(registrosEnvTemporarios);
    }

    function editarEnvRegistro(index) {
        const registroEnvolvido = registrosEnvTemporarios[index];
        document.querySelector('[name="nome"]').value = registroEnvolvido.nome;
        document.querySelector('[name="telefone"]').value = registroEnvolvido.telefone;
        document.querySelector('[name="cpf"]').value = registroEnvolvido.cpf;

        excluirEnvRegistro(index);
    }

    function formatarEnvData(data = new Date()) {
        const dataObj = new Date(data);
        const options = { day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'UTC' };
        return dataObj.toLocaleDateString('pt-BR', options);
    }

    // Tornando as funções acessíveis no escopo global
    window.adicionarEnvolvido = adicionarEnvolvido;
    window.atualizarEnvTabela = atualizarEnvTabela;
    window.limparEnvFormularioTemp = limparEnvFormularioTemp;
    window.excluirEnvRegistro = excluirEnvRegistro;
    window.editarEnvRegistro = editarEnvRegistro;
});
