/**
 * Função para formatar data de "yyyy-MM-dd HH:mm:ss" para "yyyy-MM-dd"
 */
function formatarDataParaInput(dataCompleta) {
    if (!dataCompleta) return '';
    return dataCompleta.split(' ')[0];
}

/**
 * Função para alternar corretamente a visibilidade dos botões
 */
function alternarBotoes(modo) {
    const btnAdicionar = document.getElementById("adicionarItem");
    const btnAtualizar = document.getElementById("atualizarItem");

    if (modo === 'editar') {
        if (btnAdicionar) {
            btnAdicionar.style.display = "none";
            btnAdicionar.disabled = true;
        }
        if (btnAtualizar) {
            btnAtualizar.style.display = "inline-flex";
            btnAtualizar.disabled = false;
        }
    } else {
        if (btnAdicionar) {
            btnAdicionar.style.display = "inline-flex";
            btnAdicionar.disabled = false;
        }
        if (btnAtualizar) {
            btnAtualizar.style.display = "none";
            btnAtualizar.disabled = true;
        }
    }
}

/**
 * Modificação da função editarItem para corrigir problemas
 */
function editarItem(index) {
    const item = AbastecimentoState.items[index];
    if (!item) return;

    // Ativar modo de edição
    AbastecimentoState.isEditingItem = true;
    AbastecimentoState.editingItemIndex = index;

    // Alternar botões usando a nova função
    alternarBotoes('editar');
    const btnAtualizar = document.getElementById("atualizarItem");
    if (btnAtualizar) {
        btnAtualizar.setAttribute("data-index", index);
    }

    // Preencher os campos com formatação correta da data
    const campos = [
        { id: "data_abastecimento", valor: formatarDataParaInput(item.data_abastecimento) },
        { id: "id_combustivel", valor: item.id_combustivel },
        { id: "id_bomba", valor: item.id_bomba },
        { id: "litros", valor: item.litros },
        { id: "km_veiculo", valor: item.km_veiculo },
        { id: "valor_unitario", valor: item.valor_unitario },
        { id: "valor_total", valor: item.valor_total },
    ];

    campos.forEach((campo) => {
        const input = document.getElementById(campo.id);
        if (input) {
            input.value = campo.valor !== null ? campo.valor : "";
        }
    });

    // Atualizar o estado
    AbastecimentoState.itemAtual = { ...item };
}

/**
 * Modificação da função limparCamposItemAtual para usar a nova função de alternar botões
 */
function limparCamposItemAtual() {
    // Limpar o estado
    AbastecimentoState.itemAtual = {
        data_abastecimento: "",
        id_combustivel: "",
        id_bomba: "",
        litros: "",
        km_veiculo: "",
        valor_unitario: "",
        valor_total: "",
    };

    // Configurar data padrão (hoje)
    const hoje = new Date();
    const hojeFormatado = hoje.toISOString().split("T")[0];
    AbastecimentoState.itemAtual.data_abastecimento = hojeFormatado;

    // Limpar campos do formulário
    const campos = [
        "id_combustivel",
        "id_bomba",
        "litros",
        "km_veiculo",
        "valor_unitario",
        "valor_total",
    ];

    campos.forEach((campo) => {
        const input = document.getElementById(campo);
        if (input) input.value = "";
    });

    // Definir a data padrão
    const dataInput = document.getElementById("data_abastecimento");
    if (dataInput) {
        dataInput.value = hojeFormatado;
    }

    // Desativar modo de edição
    AbastecimentoState.isEditingItem = false;
    AbastecimentoState.editingItemIndex = -1;

    // Alternar botões usando a nova função
    alternarBotoes('adicionar');
}