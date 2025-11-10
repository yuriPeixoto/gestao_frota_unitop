/**
 * AbastecimentoForm.js - Parte 1: Estado global e inicialização
 * Versão: 1.0.0
 *
 * Controlador para o formulário de abastecimento manual
 */

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

// Estado global do formulário
const AbastecimentoState = {
    // Dados de cabeçalho
    cabecalho: {
        id_abastecimento: "",
        id_veiculo: "",
        id_fornecedor: "",
        id_filial: "",
        id_departamento: "",
        numero_nota_fiscal: "",
        chave_nf: "",
        id_motorista: "",
        capacidade_tanque: "",
    },

    // Dados do veículo selecionado
    veiculo: {
        kmVeiculo: 0,
        kmAnterior: 0,
        placa: "",
        capacidadeTanque: 0,
        isTerceiro: false,
    },

    // Dados do fornecedor
    fornecedor: {
        nome: "",
        isCarvalimaPost: false,
    },

    // Lista de itens adicionados
    items: [],

    // Item atual sendo editado
    itemAtual: {
        data_abastecimento: "",
        id_combustivel: "",
        id_bomba: "",
        litros: "",
        km_veiculo: "",
        valor_unitario: "",
        valor_total: "",
    },

    // Flags de controle
    isLoading: false,
    isEditingItem: false,
    editingItemIndex: -1,

    // Métodos para manipular o estado
    capturarDadosCabecalho: function () {
        this.cabecalho = {
            id_abastecimento:
                document.getElementById("id_abastecimento")?.value || "",
            id_veiculo:
                document.querySelector('input[name="id_veiculo"]')?.value || "",
            id_fornecedor:
                document.querySelector('input[name="id_fornecedor"]')?.value ||
                "",
            id_filial: document.getElementById("id_filial")?.value || "",
            id_departamento:
                document.getElementById("id_departamento")?.value || "",
            numero_nota_fiscal:
                document.getElementById("numero_nota_fiscal")?.value || "",
            chave_nf: document.getElementById("chave_nf")?.value || "",
            id_motorista:
                document.querySelector('input[name="id_motorista"]')?.value ||
                "",
            capacidade_tanque:
                document.getElementById("capacidade_tanque")?.value || "",
        };

        // Verificar texto do fornecedor para determinar se é Carvalima
        const fornecedorDisplay = document.querySelector(
            "#id_fornecedor-button span"
        );
        if (fornecedorDisplay) {
            this.fornecedor.nome = fornecedorDisplay.textContent.trim();
            this.fornecedor.isCarvalimaPost = this.fornecedor.nome
                .toUpperCase()
                .includes("CARVALIMA");
        }

        // Verificar texto do veículo
        const veiculoDisplay = document.querySelector(
            "#id_veiculo-button span"
        );
        if (veiculoDisplay) {
            this.veiculo.placa = veiculoDisplay.textContent.trim();
        }
    },
};

/**
 * Inicialização do módulo
 */
function init() {
    console.log("Inicializando AbastecimentoForm...");

    // Expor o estado para acesso global (debugging)
    window.AbastecimentoState = AbastecimentoState;

    // Carregar dados iniciais se houver
    carregarDadosIniciais();

    // Configurar eventos dos campos
    configurarEventosCampos();

    // Configurar tratamento de visibilidade da bomba para CARVALIMA
    configurarVisibilidadeBomba();

    // Verificar preenchimento automático se veículo já estiver selecionado
    setTimeout(verificarVeiculoSelecionado, 500);

    // Inicializar data padrão (hoje)
    inicializarDataPadrao();

    configurarEventosCorrigidos();

    carregarPostos();
}

/**
 * Carrega a lista de postos disponíveis e preenche o select
 */
function carregarPostos() {
    try {
        // Obter o token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
        if (!csrfToken) {
            throw new Error("Token CSRF não encontrado");
        }

        fetch(
            `/admin/abastecimentomanual/getPosto`,
            {
                method: "GET",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "Cache-Control": "no-cache"
                },
                cache: "no-store"
            }
        )
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                console.log("Postos carregados:", data);
                addSmartSelectOption('id_fornecedor', {
                    value: data.id_fornecedor,
                    label: data.nome_fornecedor
                });
                setSmartSelectValue('id_fornecedor', data.id_fornecedor);

            })
            .catch((error) => {
                console.error("Erro ao carregar postos:", error);
                mostrarAlerta("Erro ao carregar lista de postos. Por favor, tente novamente.", "error");
            });

    } catch (e) {
        console.error("Erro ao carregar postos:", e);
        mostrarAlerta("Erro ao iniciar carregamento de postos.", "error");
    }
}

/**
 * Carrega dados iniciais do formulário se estiver em modo de edição
 */
function carregarDadosIniciais() {
    try {
        // Verificar se temos dados de itens para inicializar (modo edição)
        const itemsScript = document.getElementById("items-data");
        if (itemsScript) {
            const items = JSON.parse(itemsScript.textContent);
            console.log("Itens encontrados para inicialização:", items.length);

            // Formatar os itens e armazenar no estado
            AbastecimentoState.items = items.map((item) => ({
                data_abastecimento: item.data_abastecimento,
                id_combustivel: parseInt(item.id_combustivel),
                id_bomba: item.id_bomba ? parseInt(item.id_bomba) : null,
                litros: parseFloat(item.litros),
                km_veiculo: parseFloat(item.km_veiculo),
                valor_unitario: parseFloat(item.valor_unitario),
                valor_total: parseFloat(item.valor_total),
            }));

            // Atualizar o campo hidden
            atualizarCampoItemsHidden();

            // Renderizar a tabela
            renderizarTabelaItens();
        }

        // Capturar dados do cabeçalho
        AbastecimentoState.capturarDadosCabecalho();
    } catch (e) {
        console.error("Erro ao carregar dados iniciais:", e);
    }
}

/**
 * Configura os eventos dos campos do formulário
 */
function configurarEventosCampos() {
    // SELEÇÃO DO VEÍCULO
    const veiculoInput = document.querySelector('input[name="id_veiculo"]');
    if (veiculoInput) {
        veiculoInput.addEventListener("change", function () {
            if (this.value) {
                atualizarDadosVeiculo(this.value);
            }
        });

        // Monitorar mudanças de valor via MutationObserver
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (
                    mutation.type === "attributes" &&
                    mutation.attributeName === "value"
                ) {
                    if (veiculoInput.value) {
                        atualizarDadosVeiculo(veiculoInput.value);
                    }
                }
            });
        });

        observer.observe(veiculoInput, { attributes: true });
    }

    // Eventos do smart-select para veículo
    document.addEventListener("select-change", function (event) {
        if (
            event.detail &&
            event.detail.name === "id_veiculo" &&
            event.detail.value
        ) {
            atualizarDadosVeiculo(event.detail.value);
        }

        if (event.detail && event.detail.name === "id_fornecedor") {
            AbastecimentoState.capturarDadosCabecalho();
            atualizarVisibilidadeBomba();

            console.log("Fornecedor alterado para 2:", event.detail.value);

            // Carregar bombas quando o fornecedor é alterado via select-change
            if (event.detail.value) {
                atualizarBombasPorFornecedor(event.detail.value);
            }
        }
    });

    // SELEÇÃO DE FORNECEDOR
    const fornecedorInput = document.querySelector(
        'input[name="id_fornecedor"]'
    );

    console.log("Fornecedor input encontrado:", fornecedorInput);

    if (fornecedorInput) {
        fornecedorInput.addEventListener("change", function () {
            AbastecimentoState.capturarDadosCabecalho();
            atualizarVisibilidadeBomba();
        });

        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (
                    mutation.type === "attributes" &&
                    mutation.attributeName === "value"
                ) {
                    AbastecimentoState.capturarDadosCabecalho();
                    atualizarVisibilidadeBomba();

                    console.log(
                        "Fornecedor alterado para:",
                        fornecedorInput.value
                    );

                    // Carregar bombas quando o fornecedor é alterado via MutationObserver
                    if (fornecedorInput.value) {
                        atualizarBombasPorFornecedor(fornecedorInput.value);
                    }
                }
            });
        });

        observer.observe(fornecedorInput, { attributes: true });
    }

    // CAMPOS DE ABASTECIMENTO

    // Data de abastecimento
    const dataAbastecimentoInput =
        document.getElementById("data_abastecimento");
    if (dataAbastecimentoInput) {
        dataAbastecimentoInput.addEventListener("change", function () {
            validarDataAbastecimento(this.value);
            AbastecimentoState.itemAtual.data_abastecimento = this.value;

            // Atualizar KM baseado na data
            if (AbastecimentoState.cabecalho.id_veiculo) {
                atualizarKmPorData(this.value);
            }
        });
    }

    // Litros
    const litrosInput = document.getElementById("litros");
    if (litrosInput) {
        litrosInput.addEventListener("change", function () {
            const valor = parseFloat(this.value);
            AbastecimentoState.itemAtual.litros = valor;

            // Validar capacidade do tanque
            validarCapacidadeTanque(valor);

            // Calcular valor total
            calcularValorTotal();
        });

        litrosInput.addEventListener("input", function () {
            AbastecimentoState.itemAtual.litros = this.value;
            calcularValorTotal();
        });
    }

    // KM do veículo
    const kmVeiculoInput = document.getElementById("km_veiculo");
    if (kmVeiculoInput) {
        kmVeiculoInput.addEventListener("change", function () {
            const valor = parseFloat(this.value);
            AbastecimentoState.itemAtual.km_veiculo = valor;

            // Validar KM
            validarKmVeiculo(valor);
        });

        kmVeiculoInput.addEventListener("input", function () {
            AbastecimentoState.itemAtual.km_veiculo = this.value;
        });
    }

    // Valor unitário
    const valorUnitarioInput = document.getElementById("valor_unitario");
    if (valorUnitarioInput) {
        valorUnitarioInput.addEventListener("change", function () {
            AbastecimentoState.itemAtual.valor_unitario = parseFloat(
                this.value
            );
            calcularValorTotal();
        });

        valorUnitarioInput.addEventListener("input", function () {
            AbastecimentoState.itemAtual.valor_unitario = this.value;
            calcularValorTotal();
        });
    }

    // Tipo de combustível
    const combustivelSelect = document.getElementById("id_combustivel");
    if (combustivelSelect) {
        combustivelSelect.addEventListener("change", function () {
            AbastecimentoState.itemAtual.id_combustivel = this.value;
        });
    }

    // Bomba (Bico)
    const bombaSelect = document.getElementById("id_bomba");
    if (bombaSelect) {
        bombaSelect.addEventListener("change", function () {
            AbastecimentoState.itemAtual.id_bomba = this.value;
            buscarValorUnitarioPorBomba(this.value);
        });
    }

    // BOTÕES DE AÇÃO

    // Botão adicionar item
    const btnAdicionar = document.getElementById("adicionarItem");
    if (btnAdicionar) {
        btnAdicionar.addEventListener("click", adicionarItem);
    }

    // Botão atualizar item
    const btnAtualizar = document.getElementById("atualizarItem");
    if (btnAtualizar) {
        btnAtualizar.addEventListener("click", atualizarItem);
    }

    // Botão limpar
    const btnLimpar = document.getElementById("btnLimpar");
    if (btnLimpar) {
        btnLimpar.addEventListener("click", function () {
            if (
                confirm("Deseja realmente limpar todos os dados do formulário?")
            ) {
                limparFormularioCompleto();
            }
        });
    }

    // Botão salvar (adicionar ao lote)
    const btnSalvar = document.getElementById("btnAddToLote");
    if (btnSalvar) {
        btnSalvar.addEventListener("click", salvarAbastecimento);
    }

    // Form submit
    const form = document.getElementById("abastecimentoForm");
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            salvarAbastecimento();
            return false;
        });
    }
}

/**
 * Configura a visibilidade do campo bomba baseado no fornecedor
 */
function configurarVisibilidadeBomba() {
    // Verificar estado inicial
    setTimeout(atualizarVisibilidadeBomba, 100);

    // Adicionar eventos para monitorar mudanças no fornecedor
    document.addEventListener("select-change", function (event) {
        if (event.detail && event.detail.name === "id_fornecedor") {
            setTimeout(atualizarVisibilidadeBomba, 10);
        }
    });

    // Evento específico do componente select
    window.addEventListener("id_fornecedor:selected", function (event) {
        setTimeout(atualizarVisibilidadeBomba, 10);
    });
}

/**
 * Atualiza a visibilidade do campo bomba baseado no fornecedor atual
 */
function atualizarVisibilidadeBomba() {
    const bombaSelect = document.getElementById("id_bomba");
    if (!bombaSelect) return;

    const bombaDiv = bombaSelect.closest("div");
    if (!bombaDiv) return;

    // Capturar dados atuais do cabeçalho para garantir fornecedor atualizado
    AbastecimentoState.capturarDadosCabecalho();

    // Verificar se o fornecedor atual é CARVALIMA
    const mostrarBomba = AbastecimentoState.fornecedor.isCarvalimaPost;

    // Salvar altura original do div para evitar pulos no layout
    if (!bombaDiv.dataset.alturaOriginal) {
        bombaDiv.dataset.alturaOriginal = bombaDiv.offsetHeight + "px";
        bombaDiv.style.minHeight = bombaDiv.dataset.alturaOriginal;
    }

    // Mostrar ou ocultar baseado no fornecedor
    if (mostrarBomba) {
        bombaSelect.style.display = "";
        bombaSelect.disabled = false;
        const label = bombaDiv.querySelector("label");
        if (label) label.style.display = "";
        console.log("Exibindo campo bomba para fornecedor Carvalima");
    } else {
        bombaSelect.style.display = "none";
        bombaSelect.disabled = true;
        const label = bombaDiv.querySelector("label");
        if (label) label.style.display = "none";
        console.log("Ocultando campo bomba para fornecedor não-Carvalima");
    }
}

/**
 * Verifica se um veículo já está selecionado e atualiza seus dados
 */
function verificarVeiculoSelecionado() {
    const veiculoInput = document.querySelector('input[name="id_veiculo"]');
    if (veiculoInput && veiculoInput.value) {
        atualizarDadosVeiculo(veiculoInput.value);
    }
}

/**
 * Inicializa a data padrão (hoje) no campo de data de abastecimento
 */
function inicializarDataPadrao() {
    const dataAbastecimentoInput =
        document.getElementById("data_abastecimento");
    if (dataAbastecimentoInput && !dataAbastecimentoInput.value) {
        const hoje = new Date();
        const hojeFormatado = hoje.toISOString().split("T")[0];
        dataAbastecimentoInput.value = hojeFormatado;
        AbastecimentoState.itemAtual.data_abastecimento = hojeFormatado;
    }
}

/**
 * Atualiza os dados do veículo quando um novo veículo é selecionado
 */
function atualizarDadosVeiculo(idVeiculo) {
    if (!idVeiculo) return;

    // Evitar requisições redundantes em carregamento
    if (AbastecimentoState.isLoading) return;
    AbastecimentoState.isLoading = true;

    // Mostrar indicador de carregamento
    const capacidadeTanqueInput = document.getElementById("capacidade_tanque");
    const kmAnteriorInput = document.getElementById("km_anterior");

    if (capacidadeTanqueInput) capacidadeTanqueInput.value = "Carregando...";
    if (kmAnteriorInput) kmAnteriorInput.value = "Carregando...";

    // Obter o token CSRF
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    // Fazer a requisição
    fetch(
        `/admin/ajax-get-veiculo-dados?id=${idVeiculo}&t=${new Date().getTime()}`,
        {
            method: "GET",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/json",
                Accept: "application/json",
                "Cache-Control": "no-cache",
            },
            cache: "no-store",
        }
    )
        .then((response) => {
            if (!response.ok && response.status !== 200) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            console.log("Dados do veículo recebidos:", data);

            // Verificar se há mensagem de erro específica no objeto retornado
            if (data.error) {
                // Verificar se é um veículo do tipo carreta
                if (data.is_carreta) {
                    mostrarAlerta(
                        "Este veículo é uma carreta e não pode ser abastecido. Por favor, selecione outro veículo.",
                        "warning",
                        5000
                    );
                }
                // Verificar se é inconsistência no abastecimento
                else if (data.has_inconsistencia) {
                    mostrarAlerta(data.error, "warning", 5000);
                }
                // Outros erros genéricos
                else {
                    mostrarAlerta(data.error, "error");
                }

                // Limpar veículo e campos relacionados
                limparVeiculoSelecionado();

                // Limpar campos em caso de erro
                if (capacidadeTanqueInput) capacidadeTanqueInput.value = "";
                if (kmAnteriorInput) kmAnteriorInput.value = "0";

                AbastecimentoState.isLoading = false;
                return;
            }

            // Aplicar dados ao formulário (se não houver erro)
            aplicarDadosVeiculo(data);

            // Atualizar KM conforme a data, se disponível
            const dataAbastecimentoInput =
                document.getElementById("data_abastecimento");
            if (dataAbastecimentoInput && dataAbastecimentoInput.value) {
                setTimeout(() => {
                    atualizarKmPorData(dataAbastecimentoInput.value);
                }, 200);
            }
        })
        .catch((error) => {
            console.error("Erro ao buscar dados do veículo:", error);

            // Limpar campos em caso de erro
            if (capacidadeTanqueInput) capacidadeTanqueInput.value = "";
            if (kmAnteriorInput) kmAnteriorInput.value = "0";

            mostrarAlerta(
                "Erro ao buscar dados do veículo. Por favor, tente novamente.",
                "error"
            );

            // Limpar veículo selecionado
            limparVeiculoSelecionado();
        })
        .finally(() => {
            AbastecimentoState.isLoading = false;
        });
}

/**
 * Limpa o veículo selecionado e campos relacionados
 */
function limparVeiculoSelecionado() {
    // Limpar input hidden
    const veiculoInput = document.querySelector('input[name="id_veiculo"]');
    if (veiculoInput) veiculoInput.value = "";

    // Limpar texto exibido no select
    const veiculoDisplay = document.querySelector("#id_veiculo-button span");
    if (veiculoDisplay) veiculoDisplay.textContent = "Selecione o veículo...";

    // Atualizar estado global
    if (AbastecimentoState) {
        AbastecimentoState.cabecalho.id_veiculo = "";
        AbastecimentoState.veiculo = {
            kmVeiculo: 0,
            kmAnterior: 0,
            placa: "",
            capacidadeTanque: 0,
            isTerceiro: false,
        };
    }
}

/**
 * Exibe uma mensagem de alerta ao usuário com duração opcional
 */
function mostrarAlerta(mensagem, tipo = "info", duracao = 3000) {
    // Verificar se o componente de toast existe
    if (typeof window.toast === "function") {
        window.toast(mensagem, tipo);
        return;
    }

    // Criar elemento de toast simples
    const toast = document.createElement("div");
    toast.style.position = "fixed";
    toast.style.top = "20px";
    toast.style.right = "20px";
    toast.style.padding = "12px 20px";
    toast.style.backgroundColor =
        tipo === "success"
            ? "#48BB78"
            : tipo === "error"
                ? "#F56565"
                : tipo === "warning"
                    ? "#ED8936"
                    : "#4299E1";
    toast.style.color = "white";
    toast.style.borderRadius = "4px";
    toast.style.zIndex = "9999";
    toast.style.boxShadow = "0 4px 6px rgba(0, 0, 0, 0.1)";
    toast.style.opacity = "0";
    toast.style.transition = "opacity 0.3s ease-in-out";
    toast.style.maxWidth = "400px";
    toast.style.wordWrap = "break-word";
    toast.textContent = mensagem;

    document.body.appendChild(toast);

    // Animação de entrada
    setTimeout(() => {
        toast.style.opacity = "1";
    }, 10);

    // Remover após o tempo especificado
    setTimeout(() => {
        toast.style.opacity = "0";
        setTimeout(() => {
            if (document.body.contains(toast)) {
                toast.remove();
            }
        }, 300);
    }, duracao);
}

/**
 * Aplica os dados recebidos do veículo ao formulário e ao estado
 */
function aplicarDadosVeiculo(data) {
    // Verificar erro ou restrição
    if (data.error) {
        mostrarAlerta(data.error, "error");

        // Limpar veículo
        const veiculoInput = document.querySelector('input[name="id_veiculo"]');
        if (veiculoInput) veiculoInput.value = "";

        const veiculoDisplay = document.querySelector(
            "#id_veiculo-button span"
        );
        if (veiculoDisplay)
            veiculoDisplay.textContent = "Selecione o veículo...";

        return;
    }

    // Atualizar o estado
    AbastecimentoState.veiculo.capacidadeTanque =
        data.capacidade_tanque_principal || 0;
    AbastecimentoState.veiculo.kmAnterior = data.km_atual || 0;

    // 1. Atualizar a capacidade do tanque
    const capacidadeTanqueInput = document.getElementById("capacidade_tanque");
    if (capacidadeTanqueInput) {
        capacidadeTanqueInput.value = data.capacidade_tanque_principal || "0";
    }

    // 2. Atualizar o KM anterior CORRETAMENTE
    const kmAnteriorInput = document.getElementById("km_anterior");
    if (kmAnteriorInput) {
        kmAnteriorInput.value = data.km_anterior || "0"; // USAR km_anterior
    }

    // 3. Se houver campo de KM atual, preenchê-lo também
    const kmVeiculoInput = document.getElementById("km_veiculo");
    if (kmVeiculoInput && data.km_atual) {
        if (data.km_atual && data.km_atual > 0) {
            kmVeiculoInput.value = data.km_atual;
        } else {
            // Zerar o campo quando não retornar dados ou retornar 0
            kmVeiculoInput.value = "";
        }
    }

    // 4. Atualizar departamento
    if (data.id_departamento) {
        // Atualizar o valor do campo hidden
        const idDepartamentoInput = document.getElementById("id_departamento");
        if (idDepartamentoInput) {
            idDepartamentoInput.value = data.id_departamento;
        }

        // Atualizar o texto de exibição
        const departamentoDisplay = document.getElementById(
            "departamento_display"
        );
        if (departamentoDisplay) {
            buscarNomeDepartamento(data.id_departamento)
                .then((nome) => {
                    departamentoDisplay.value = nome;
                })
                .catch(() => {
                    departamentoDisplay.value =
                        "Departamento ID: " + data.id_departamento;
                });
        }
    }

    // 5. Atualizar filial
    if (data.id_filial) {
        // Atualizar o valor do campo hidden
        const idFilialInput = document.getElementById("id_filial");
        if (idFilialInput) {
            idFilialInput.value = data.id_filial;
        }

        // Atualizar o texto de exibição
        const filialDisplay = document.getElementById("filial_display");
        if (filialDisplay) {
            buscarNomeFilial(data.id_filial)
                .then((nome) => {
                    filialDisplay.value = nome;
                })
                .catch(() => {
                    filialDisplay.value = "Filial ID: " + data.id_filial;
                });
        }
    }

    // Atualizar o estado do cabeçalho
    AbastecimentoState.capturarDadosCabecalho();
}

/**
 * Atualiza o KM baseado em uma data específica
 */
function atualizarKmPorData(dataAbastecimento) {
    const idVeiculo = AbastecimentoState.cabecalho.id_veiculo;
    if (!idVeiculo || !dataAbastecimento) return;

    // Evitar requisições redundantes
    if (AbastecimentoState.isLoading) return;
    AbastecimentoState.isLoading = true;

    // Mostrar indicador de carregamento
    const kmAnteriorInput = document.getElementById("km_anterior");
    if (kmAnteriorInput) kmAnteriorInput.value = "Carregando...";

    // Obter o token CSRF
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    // URL com data específica
    const url = `/admin/ajax-get-veiculo-dados?id=${idVeiculo}&data_abastecimento=${encodeURIComponent(
        dataAbastecimento
    )}&t=${new Date().getTime()}`;

    // Fazer a requisição
    fetch(url, {
        method: "GET",
        headers: {
            "X-CSRF-TOKEN": csrfToken,
            "Content-Type": "application/json",
            Accept: "application/json",
            "Cache-Control": "no-cache",
        },
        cache: "no-store",
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            console.log("Dados do KM para data específica recebidos:", data);

            // Atualizar o campo de KM anterior
            const kmAnteriorInput = document.getElementById("km_anterior");
            if (kmAnteriorInput && data.km_anterior) {
                kmAnteriorInput.value = data.km_anterior;
                AbastecimentoState.veiculo.kmAnterior = data.km_anterior;
            } else {
                // Se não foi retornado km_anterior, mostrar 0
                if (kmAnteriorInput) {
                    kmAnteriorInput.value = "0";
                    AbastecimentoState.veiculo.kmAnterior = 0;
                }
            }
        })
        .catch((error) => {
            console.error(
                "Erro ao buscar dados do veículo para data específica:",
                error
            );

            // Em caso de erro, mostrar 0 em vez de deixar "carregando..."
            if (kmAnteriorInput) {
                kmAnteriorInput.value = "0";
                AbastecimentoState.veiculo.kmAnterior = 0;
            }
        })
        .finally(() => {
            AbastecimentoState.isLoading = false;
        });
}

/**
 * Busca o nome do departamento pelo ID
 */
function buscarNomeDepartamento(idDepartamento) {
    return new Promise((resolve, reject) => {
        // Verificar se temos um select com os departamentos na página
        const departamentoOptions = Array.from(
            document.querySelectorAll("#id_departamento_original option")
        );

        if (departamentoOptions.length > 0) {
            // Buscar a opção que corresponde ao ID
            const departamentoOption = departamentoOptions.find(
                (opt) => opt.value == idDepartamento
            );
            if (departamentoOption) {
                return resolve(departamentoOption.textContent.trim());
            }
        }

        // Se não encontrou localmente, buscar via AJAX
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        fetch(`/admin/api/departamentos/single/${idDepartamento}`, {
            method: "GET",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            credentials: "same-origin",
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                if (data && data.label) {
                    resolve(data.label);
                } else {
                    reject("Nome do departamento não encontrado");
                }
            })
            .catch((error) => {
                console.error("Erro ao buscar nome do departamento:", error);
                reject(error);
            });
    });
}

/**
 * Busca o nome da filial pelo ID
 */
function buscarNomeFilial(idFilial) {
    return new Promise((resolve, reject) => {
        // Verificar se temos um select com as filiais na página
        const filialOptions = Array.from(
            document.querySelectorAll("#id_filial_original option")
        );

        if (filialOptions.length > 0) {
            // Buscar a opção que corresponde ao ID
            const filialOption = filialOptions.find(
                (opt) => opt.value == idFilial
            );
            if (filialOption) {
                return resolve(filialOption.textContent.trim());
            }
        }

        // Se não encontrou localmente, buscar via AJAX
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        fetch(`/admin/api/filiais/single/${idFilial}`, {
            method: "GET",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            credentials: "same-origin",
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                if (data && data.label) {
                    resolve(data.label);
                } else {
                    reject("Nome da filial não encontrado");
                }
            })
            .catch((error) => {
                console.error("Erro ao buscar nome da filial:", error);
                reject(error);
            });
    });
}

/**
 * Validação da data de abastecimento
 */
function validarDataAbastecimento(data) {
    if (!data) return true;

    // Verificar se a data é futura
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);

    const dataSelecionada = new Date(data);
    dataSelecionada.setHours(0, 0, 0, 0);

    if (dataSelecionada > hoje) {
        mostrarAlerta("A data de abastecimento não pode ser futura.", "error");

        // Limpar o campo
        const dataInput = document.getElementById("data_abastecimento");
        if (dataInput) dataInput.value = "";

        AbastecimentoState.itemAtual.data_abastecimento = "";
        return false;
    }

    return true;
}

/**
 * Validação de capacidade do tanque
 */
function validarCapacidadeTanque(litros) {
    const capacidadeTanque = AbastecimentoState.veiculo.capacidadeTanque;

    if (
        capacidadeTanque &&
        litros &&
        parseFloat(litros) > parseFloat(capacidadeTanque)
    ) {
        mostrarAlerta(
            "Atenção: Volume informado maior que a capacidade do tanque.",
            "warning"
        );
        return false;
    }

    return true;
}

/**
 * Validação do KM do veículo
 */
function validarKmVeiculo(kmAtual) {
    const kmAnterior = AbastecimentoState.veiculo.kmAnterior;
    const departamento = AbastecimentoState.cabecalho.id_departamento;

    if (!kmAtual || kmAtual <= 0) {
        mostrarAlerta(
            "O KM do veículo é obrigatório e deve ser maior que zero.",
            "error"
        );
        return false;
    }

    let isValido = true;

    // Validar se KM atual é menor que KM anterior
    if (kmAnterior && parseFloat(kmAtual) < parseFloat(kmAnterior)) {
        if (
            !confirm(
                "Atenção: O KM informado é menor que o KM anterior do veículo. Deseja continuar mesmo assim?"
            )
        ) {
            // Limpar o campo
            const kmInput = document.getElementById("km_veiculo");
            if (kmInput) kmInput.value = "";

            AbastecimentoState.itemAtual.km_veiculo = "";
            isValido = false;
        }
    }

    // Validar diferença máxima de KM (mais de 2800 km - exceto departamento 90)
    if (kmAnterior && departamento && departamento != 90) {
        const diferenca = parseFloat(kmAtual) - parseFloat(kmAnterior);
        if (diferenca > 2800) {
            if (
                !confirm(
                    "Atenção: KM do Abastecimento menos o KM anterior é maior que a autonomia do veículo (2800 km). Deseja continuar mesmo assim?"
                )
            ) {
                // Limpar o campo
                const kmInput = document.getElementById("km_veiculo");
                if (kmInput) kmInput.value = "";

                AbastecimentoState.itemAtual.km_veiculo = "";
                isValido = false;
            }
        }
    }

    return isValido;
}

/**
 * Calcula o valor total baseado em litros e valor unitário
 */
function calcularValorTotal() {
    console.log("Calculando valor total...");

    // Garantir que os valores sejam numéricos
    const litrosInput = document.getElementById("litros");
    const valorUnitarioInput = document.getElementById("valor_unitario");
    const valorTotalInput = document.getElementById("valor_total");

    if (!litrosInput || !valorUnitarioInput || !valorTotalInput) {
        console.error("Campos necessários para cálculo não encontrados");
        return;
    }

    // Obter valores diretamente dos campos e limpar formatação
    let litrosValue = litrosInput.value
        .replace(/[^\d.,]/g, "")
        .replace(",", ".");
    let valorUnitarioValue = valorUnitarioInput.value
        .replace(/[^\d.,]/g, "")
        .replace(",", ".");

    // Converter para números
    const litros = parseFloat(litrosValue) || 0;
    const valorUnitario = parseFloat(valorUnitarioValue) || 0;

    console.log("Litros:", litros, "(do input:", litrosInput.value, ")");
    console.log(
        "Valor unitário:",
        valorUnitario,
        "(do input:",
        valorUnitarioInput.value,
        ")"
    );

    // Calcular total e formatar para duas casas decimais
    const total = (litros * valorUnitario).toFixed(2);
    console.log("Valor total calculado:", total);

    // Atualizar o campo no formulário
    valorTotalInput.value = total;

    // Atualizar também o estado do item atual
    if (window.AbastecimentoState && window.AbastecimentoState.itemAtual) {
        window.AbastecimentoState.itemAtual.litros = litros;
        window.AbastecimentoState.itemAtual.valor_unitario = valorUnitario;
        window.AbastecimentoState.itemAtual.valor_total = parseFloat(total);
    }

    return total;
}

/**
 * Busca o valor unitário para a bomba selecionada
 */
function buscarValorUnitarioPorBomba(idBomba) {
    if (!idBomba) return;

    // Verificar se o veículo é terceiro
    const isTerceiro = AbastecimentoState.veiculo.isTerceiro ? 1 : 0;

    // Obter o token CSRF
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    // Fazer a requisição
    fetch(
        `/admin/api/bombas/valor-unitario?id_bomba=${idBomba}&is_terceiro=${isTerceiro}`,
        {
            method: "GET",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/json",
                Accept: "application/json",
            },
        }
    )
        .then((response) => {
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            if (data && data.valor) {
                // Atualizar o campo valor unitário
                const valorUnitarioInput =
                    document.getElementById("valor_unitario");
                if (valorUnitarioInput) {
                    valorUnitarioInput.value = formatarNumero(data.valor, 2);
                    AbastecimentoState.itemAtual.valor_unitario = data.valor;

                    // Recalcular valor total
                    calcularValorTotal();
                }
            }
        })
        .catch((error) => {
            console.error("Erro ao buscar valor unitário da bomba:", error);
        });
}

/**
 * Atualiza as bombas disponíveis baseado no tipo de combustível
 */

/**
 * Atualiza as bombas disponíveis baseado no fornecedor selecionado
 */
function atualizarBombasPorFornecedor(idFornecedor) {
    console.log(
        "Iniciando atualização de bombas para fornecedor ID:",
        idFornecedor
    );

    if (!idFornecedor) {
        console.log("ID do fornecedor não fornecido");
        return;
    }

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
    if (!csrfToken) {
        console.error("Token CSRF não encontrado");
        return;
    }

    const bombaSelect = document.getElementById("id_bomba");
    if (!bombaSelect) {
        console.error("Elemento select de bombas não encontrado");
        return;
    }

    bombaSelect.innerHTML = '<option value="">Carregando...</option>';
    bombaSelect.disabled = true;

    fetch(`/admin/api/bombas/get-bomba-data?idFornecedor=${idFornecedor}`, {
        method: "GET",
        headers: {
            Accept: "application/json",
        },
    })
        .then((response) => {
            console.log("Resposta recebida, status:", response.status);
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            console.log("Dados recebidos:", data);

            bombaSelect.innerHTML = '<option value="">Selecione...</option>';

            if (data.combustivel && data.combustivel.length > 0) {
                data.combustivel.forEach((bomba) => {
                    const option = document.createElement("option");
                    option.value = bomba.value;
                    option.textContent = bomba.label;
                    bombaSelect.appendChild(option);
                });
            } else {
                console.warn("Nenhuma bomba encontrada para este fornecedor");
                bombaSelect.innerHTML =
                    '<option value="">Nenhuma bomba disponível</option>';
            }

            bombaSelect.disabled = false;
        })
        .catch((error) => {
            console.error("Erro na requisição:", error);
            bombaSelect.innerHTML =
                '<option value="">Erro ao carregar</option>';
            bombaSelect.disabled = false;
            mostrarAlerta(
                "Erro ao carregar bombas. Por favor, tente novamente.",
                "error"
            );
        });
}
/**
 * Atualiza o campo hidden com JSON dos itens
 */
function atualizarCampoItemsHidden() {
    const itemsInput = document.querySelector('input[name="items"]');
    if (itemsInput) {
        itemsInput.value = JSON.stringify(AbastecimentoState.items);
    } else {
        console.error("Campo hidden items não encontrado!");
    }
}

/**
 * Valida se o combustível selecionado é compatível com a bomba
 */
async function validarCompatibilidadeCombustivelBomba(idCombustivel, idBomba) {
    if (!idCombustivel || !idBomba) {
        return { valid: true }; // Se não temos bomba, não validamos
    }

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        const response = await fetch(
            "/admin/abastecimentomanual/get-combustivel-bomba",
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    bomba: idBomba,
                    combustivel: idCombustivel,
                }),
            }
        );

        const data = await response.json();

        if (!response.ok) {
            return {
                valid: false,
                error: data.error || "Erro ao validar compatibilidade",
                details: data,
            };
        }

        return {
            valid: data.compatible,
            message: data.message,
        };
    } catch (error) {
        console.error(
            "Erro ao validar compatibilidade combustível/bomba:",
            error
        );
        return {
            valid: false,
            error: "Erro de comunicação com o servidor",
        };
    }
}

/**
 * Validação completa do item antes de adicionar
 */
async function validarItem() {
    // Verificar campos obrigatórios
    const camposObrigatorios = [
        { id: "data_abastecimento", nome: "Data de Abastecimento" },
        { id: "id_combustivel", nome: "Tipo de Combustível" },
        { id: "litros", nome: "Litros" },
        { id: "km_veiculo", nome: "KM do Veículo" },
        { id: "valor_unitario", nome: "Valor Unitário" },
    ];

    for (const campo of camposObrigatorios) {
        const input = document.getElementById(campo.id);
        if (!input || !input.value) {
            mostrarAlerta(`O campo "${campo.nome}" é obrigatório.`, "error");
            return false;
        }
    }

    // Verificar valores numéricos
    const litrosInput = document.getElementById("litros");
    if (parseFloat(litrosInput.value) <= 0) {
        mostrarAlerta(
            "A quantidade de litros deve ser maior que zero.",
            "error"
        );
        return false;
    }

    const valorUnitarioInput = document.getElementById("valor_unitario");
    if (parseFloat(valorUnitarioInput.value) <= 0) {
        mostrarAlerta("O valor unitário deve ser maior que zero.", "error");
        return false;
    }

    // Verificar veículo selecionado
    if (!AbastecimentoState.cabecalho.id_veiculo) {
        mostrarAlerta("É necessário selecionar um veículo.", "error");
        return false;
    }

    // Verificar fornecedor selecionado
    if (!AbastecimentoState.cabecalho.id_fornecedor) {
        mostrarAlerta("É necessário selecionar um fornecedor.", "error");
        return false;
    }

    // Verificar NF preenchida
    if (
        !AbastecimentoState.cabecalho.numero_nota_fiscals &&
        !AbastecimentoState.fornecedor.isCarvalimaPost
    ) {
        mostrarAlerta(
            "É necessário informar o número da Nota Fiscal.",
            "error"
        );
        return false;
    }

    // Validações adicionais
    const kmValido = validarKmVeiculo(
        document.getElementById("km_veiculo").value
    );
    const dataValida = validarDataAbastecimento(
        document.getElementById("data_abastecimento").value
    );
    const capacidadeValida = validarCapacidadeTanque(
        document.getElementById("litros").value
    );

    return kmValido && dataValida;
}

/**
 * Adiciona um item à lista
 */
async function adicionarItem() {
    try {
        // Capturar dados do cabeçalho antes da validação
        AbastecimentoState.capturarDadosCabecalho();

        // Validar item
        const isValido = await validarItem();
        if (!isValido) return;

        // Obter valores dos campos para validação de compatibilidade
        const idCombustivelInput = document.getElementById("id_combustivel");
        const idBombaInput = document.getElementById("id_bomba");

        // Validar compatibilidade combustível/bomba
        if (idBombaInput.value && idCombustivelInput.value) {
            console.log("Validando compatibilidade combustível/bomba...");
            const compatibilidade =
                await validarCompatibilidadeCombustivelBomba(
                    idCombustivelInput.value,
                    idBombaInput.value
                );

            if (!compatibilidade.valid) {
                mostrarAlerta(
                    compatibilidade.error ||
                    "O combustível selecionado não é compatível com esta bomba",
                    "error"
                );
                return;
            }

            console.log("Compatibilidade validada com sucesso");
        }

        // Obter valores dos campos
        const dataAbastecimentoInput =
            document.getElementById("data_abastecimento");
        const litrosInput = document.getElementById("litros");
        const kmVeiculoInput = document.getElementById("km_veiculo");
        const valorUnitarioInput = document.getElementById("valor_unitario");
        const valorTotalInput = document.getElementById("valor_total");

        // Criar o novo item com dados do cabeçalho
        const novoItem = {
            // Dados do item
            data_abastecimento: dataAbastecimentoInput.value,
            id_combustivel: parseInt(idCombustivelInput.value),
            id_bomba: idBombaInput.value ? parseInt(idBombaInput.value) : null,
            litros: parseFloat(litrosInput.value),
            km_veiculo: parseFloat(kmVeiculoInput.value),
            valor_unitario: parseFloat(valorUnitarioInput.value),
            valor_total: parseFloat(valorTotalInput.value),

            // Dados do cabeçalho (necessários para processamento)
            id_fornecedor: AbastecimentoState.cabecalho.id_fornecedor,
            id_veiculo: AbastecimentoState.cabecalho.id_veiculo,
            id_filial: AbastecimentoState.cabecalho.id_filial,
            id_departamento: AbastecimentoState.cabecalho.id_departamento,
            id_motorista: AbastecimentoState.cabecalho.id_motorista,
            numero_nota_fiscal: AbastecimentoState.cabecalho.numero_nota_fiscal,
            chave_nf: AbastecimentoState.cabecalho.chave_nf,

            // Dados extras para exibição
            placa_display:
                document
                    .querySelector("#id_veiculo-button span")
                    ?.textContent.trim() || "",
            fornecedor_display:
                document
                    .querySelector("#id_fornecedor-button span")
                    ?.textContent.trim() || "",
            motorista_display:
                document
                    .querySelector("#id_motorista-button span")
                    ?.textContent.trim() || "",
            filial_display:
                document.getElementById("filial_display")?.value || "",
            departamento_display:
                document.getElementById("departamento_display")?.value || "",
        };

        // Adicionar à lista de itens
        AbastecimentoState.items.push(novoItem);

        // Atualizar o campo hidden
        atualizarCampoItemsHidden();

        // Renderizar a tabela
        renderizarTabelaItens();

        // Limpar campos para novo item
        limparCamposItemAtual();

        mostrarAlerta("Item adicionado com sucesso!", "success");
    } catch (e) {
        console.error("Erro ao adicionar item:", e);
        mostrarAlerta(`Erro ao adicionar item: ${e.message}`, "error");
    }
}

/**
 * Limpa apenas os campos do item atual, mantendo dados do cabeçalho
 */
function limparCamposItemAtual() {
    console.log("Limpando campos do item atual..."); // Debug

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

/**
 * Renderiza a tabela de itens
 */
function renderizarTabelaItens() {
    const tableBody = document.getElementById("itemsTableBody");
    if (!tableBody) return;

    // Limpar a tabela
    tableBody.innerHTML = "";

    // Verificar se há itens
    if (AbastecimentoState.items.length === 0) {
        // Adicionar uma linha vazia com mensagem
        const emptyRow = document.createElement("tr");
        emptyRow.innerHTML = `
            <td colspan="8" class="py-3 px-6 text-center text-gray-500">
                Nenhum item de abastecimento adicionado
            </td>
        `;
        tableBody.appendChild(emptyRow);
        return;
    }

    // Adicionar itens à tabela
    AbastecimentoState.items.forEach((item, index) => {
        const row = document.createElement("tr");

        // Aplicar estilo zebra
        if (index % 2 === 0) {
            row.classList.add("bg-gray-50");
        }

        // Formatar data para display
        const dataFormatada = formatarData(item.data_abastecimento);

        // Obter nome do tipo de combustível e bomba
        const nomeCombustivel = buscarTextoSelect(
            "id_combustivel",
            item.id_combustivel
        );
        const nomeBomba = buscarTextoSelect("id_bomba", item.id_bomba);

        // Adicionar colunas
        row.innerHTML = `
            <td class="py-3 px-6">${dataFormatada}</td>
            <td class="py-3 px-6">${nomeCombustivel}</td>
            <td class="py-3 px-6">${nomeBomba || "-"}</td>
            <td class="py-3 px-6">${formatarNumero(item.litros, 2)}</td>
            <td class="py-3 px-6">${formatarNumero(item.km_veiculo, 0)}</td>
            <td class="py-3 px-6">${formatarMoeda(item.valor_unitario)}</td>
            <td class="py-3 px-6">${formatarMoeda(item.valor_total)}</td>
            <td class="py-3 px-6">
                <div class="flex items-center space-x-2">
                    <button type="button" class="btn-edit-item text-blue-500 hover:text-blue-700" data-index="${index}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button type="button" class="btn-remove-item text-red-500 hover:text-red-700" data-index="${index}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </td>
        `;

        // Adicionar à tabela
        tableBody.appendChild(row);
    });

    // Adicionar event listeners para os botões
    document.querySelectorAll(".btn-edit-item").forEach((button) => {
        button.addEventListener("click", (e) => {
            const index = parseInt(e.currentTarget.getAttribute("data-index"));
            editarItem(index);
        });
    });

    document.querySelectorAll(".btn-remove-item").forEach((button) => {
        button.addEventListener("click", (e) => {
            const index = parseInt(e.currentTarget.getAttribute("data-index"));
            removerItem(index);
        });
    });
}

/**
 * Edita um item existente
 */
function editarItem(index) {
    const item = AbastecimentoState.items[index];
    if (!item) return;

    console.log("Editando item:", item); // Debug

    // Ativar modo de edição
    AbastecimentoState.isEditingItem = true;
    AbastecimentoState.editingItemIndex = index;

    // Alternar visibilidade dos botões
    const btnAdicionar = document.getElementById("adicionarItem");
    const btnAtualizar = document.getElementById("atualizarItem");

    if (btnAdicionar) {
        btnAdicionar.style.display = "none";
        btnAdicionar.style.visibility = "hidden";
    }
    if (btnAtualizar) {
        btnAtualizar.style.display = "inline-flex";
        btnAtualizar.style.visibility = "visible";
        btnAtualizar.setAttribute("data-index", index);
    }

    // Tratar a data corretamente
    let dataFormatada = "";
    if (item.data_abastecimento) {
        dataFormatada = item.data_abastecimento.split(' ')[0];
        console.log("Data formatada:", dataFormatada); // Debug
    }

    // Preencher os campos
    const campos = [
        { id: "data_abastecimento", valor: dataFormatada },
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
 * Atualiza um item após edição
 */
async function atualizarItem() {
    try {
        const atualizarItemBtn = document.getElementById("atualizarItem");
        if (!atualizarItemBtn) return;

        const index = atualizarItemBtn.getAttribute("data-index");
        if (index === null || index === undefined) return;

        // Obter valores dos campos
        const dataAbastecimentoInput =
            document.getElementById("data_abastecimento");
        const idCombustivelInput = document.getElementById("id_combustivel");
        const idBombaInput = document.getElementById("id_bomba");
        const litrosInput = document.getElementById("litros");
        const kmVeiculoInput = document.getElementById("km_veiculo");
        const valorUnitarioInput = document.getElementById("valor_unitario");
        const valorTotalInput = document.getElementById("valor_total");

        // Validar compatibilidade combustível/bomba antes de atualizar
        if (idBombaInput.value && idCombustivelInput.value) {
            console.log(
                "Validando compatibilidade combustível/bomba na atualização..."
            );
            const compatibilidade =
                await validarCompatibilidadeCombustivelBomba(
                    idCombustivelInput.value,
                    idBombaInput.value
                );

            if (!compatibilidade.valid) {
                mostrarAlerta(
                    compatibilidade.error ||
                    "O combustível selecionado não é compatível com esta bomba",
                    "error"
                );
                return;
            }

            console.log("Compatibilidade validada com sucesso na atualização");
        }

        // Atualizar o item
        AbastecimentoState.items[index] = {
            // Manter os dados do cabeçalho do item original
            ...AbastecimentoState.items[index],

            // Atualizar os dados específicos do item
            data_abastecimento: dataAbastecimentoInput.value,
            id_combustivel: parseInt(idCombustivelInput.value),
            id_bomba: idBombaInput.value ? parseInt(idBombaInput.value) : null,
            litros: parseFloat(litrosInput.value),
            km_veiculo: parseFloat(kmVeiculoInput.value),
            valor_unitario: parseFloat(valorUnitarioInput.value),
            valor_total: parseFloat(valorTotalInput.value),
        };

        // Atualizar o campo hidden
        atualizarCampoItemsHidden();

        // Renderizar a tabela
        renderizarTabelaItens();

        // Limpar campos para novo item
        limparCamposItemAtual();

        // Alternar botões
        const adicionarItemBtn = document.getElementById("adicionarItem");
        if (adicionarItemBtn) adicionarItemBtn.style.display = "";
        if (atualizarItemBtn) atualizarItemBtn.style.display = "none";

        mostrarAlerta("Item atualizado com sucesso!", "success");
    } catch (e) {
        console.error("Erro ao atualizar item:", e);
        mostrarAlerta(`Erro ao atualizar item: ${e.message}`, "error");
    }
}

/**
 * Remove um item da lista
 */
function removerItem(index) {
    if (confirm("Deseja realmente remover este item?")) {
        AbastecimentoState.items.splice(index, 1);

        // Atualizar o campo hidden
        atualizarCampoItemsHidden();

        // Renderizar a tabela
        renderizarTabelaItens();

        mostrarAlerta("Item removido com sucesso!", "info");
    }
}

/**
 * Limpa completamente o formulário
 */
function limparFormularioCompleto() {
    // Limpar itens
    AbastecimentoState.items = [];
    atualizarCampoItemsHidden();

    // Limpar item atual
    limparCamposItemAtual();

    // Limpar campos de cabeçalho
    document.getElementById("abastecimentoForm")?.reset();

    // Limpar campos que não são limpos pelo reset
    limparSmartSelect("id_veiculo", "Selecione o veículo...");
    limparSmartSelect("id_fornecedor", "Selecione o fornecedor...");
    limparSmartSelect("id_motorista", "Selecione o motorista...");

    // Limpar campos de exibição
    const camposExibicao = [
        "filial_display",
        "departamento_display",
        "capacidade_tanque",
        "km_anterior",
    ];

    camposExibicao.forEach((id) => {
        const campo = document.getElementById(id);
        if (campo) campo.value = "";
    });

    // Limpar campos hidden
    const camposHidden = ["id_filial", "id_departamento"];

    camposHidden.forEach((id) => {
        const campo = document.getElementById(id);
        if (campo) campo.value = "";
    });

    // Reiniciar o estado
    AbastecimentoState.veiculo = {
        kmAnterior: 0,
        placa: "",
        capacidadeTanque: 0,
        isTerceiro: false,
    };

    AbastecimentoState.fornecedor = {
        nome: "",
        isCarvalimaPost: false,
    };

    AbastecimentoState.cabecalho = {
        id_abastecimento: "",
        id_veiculo: "",
        id_fornecedor: "",
        id_filial: "",
        id_departamento: "",
        numero_nota_fiscal: "",
        chave_nf: "",
        id_motorista: "",
        capacidade_tanque: "",
    };

    // Limpar a tabela de itens
    renderizarTabelaItens();

    mostrarAlerta("Formulário completamente limpo.", "info");
}

/**
 * Função auxiliar para limpar campos smart-select
 */
function limparSmartSelect(name, placeholder) {
    // Limpar o input hidden
    const input = document.querySelector(`input[name="${name}"]`);
    if (input) input.value = "";

    // Limpar o texto exibido
    const display = document.querySelector(`#${name}-button span`);
    if (display) display.textContent = placeholder;
}

/**
 * Busca o texto de um select pelo valor
 */
function buscarTextoSelect(idSelect, valor) {
    if (!valor) return "";

    const select = document.getElementById(idSelect);
    if (!select) return valor;

    const option = Array.from(select.options).find((opt) => opt.value == valor);
    return option ? option.textContent : valor;
}

/**
 * Salva o abastecimento completo
 */
function salvarAbastecimento() {
    // Capturar dados do cabeçalho atuais
    AbastecimentoState.capturarDadosCabecalho();

    // Verificar se há itens para salvar
    if (AbastecimentoState.items.length === 0) {
        mostrarAlerta(
            "É necessário adicionar pelo menos um item de abastecimento.",
            "error"
        );

        // Mostrar alerta no formulário
        const alertNoItems = document.getElementById("alertNoItems");
        if (alertNoItems) {
            alertNoItems.style.display = "block";
            setTimeout(() => {
                alertNoItems.style.display = "none";
            }, 5000);
        }
        return;
    }

    // Verificar campos obrigatórios do cabeçalho
    const camposObrigatorios = [
        { campo: "id_fornecedor", nome: "Fornecedor" },
        { campo: "id_veiculo", nome: "Veículo (Placa)" },
        { campo: "id_filial", nome: "Filial" },
        { campo: "id_departamento", nome: "Departamento" },
        { campo: "numero_nota_fiscal", nome: "Número da NF" },
    ];

    const camposFaltantes = [];
    camposObrigatorios.forEach((campo) => {
        if (!AbastecimentoState.cabecalho[campo.campo]) {
            camposFaltantes.push(campo.nome);
        }
    });

    if (camposFaltantes.length > 0) {
        mostrarAlerta(
            `Os seguintes campos são obrigatórios: ${camposFaltantes.join(
                ", "
            )}`,
            "error"
        );
        return;
    }

    // Validar se todos os itens têm os dados do cabeçalho (garantir que não se perderam)
    // Isso corrige um bug onde os itens podem perder a referência ao cabeçalho
    const cabecalhoDados = AbastecimentoState.cabecalho;
    AbastecimentoState.items = AbastecimentoState.items.map((item) => ({
        ...item,
        id_fornecedor: cabecalhoDados.id_fornecedor,
        id_veiculo: cabecalhoDados.id_veiculo,
        id_filial: cabecalhoDados.id_filial,
        id_departamento: cabecalhoDados.id_departamento,
        numero_nota_fiscal: cabecalhoDados.numero_nota_fiscal,
        chave_nf: cabecalhoDados.chave_nf,
        id_motorista: cabecalhoDados.id_motorista,
    }));

    // Atualizar o campo hidden
    atualizarCampoItemsHidden();

    // Submeter o formulário
    const form = document.getElementById("abastecimentoForm");
    if (form) {
        // Se estamos em modo de processamento em lote, adicionar flag para backend
        const processandoLote = AbastecimentoState.items.length > 1;

        if (processandoLote) {
            // Criar hidden input para modo lote se ainda não existir
            let loteInput = document.querySelector(
                'input[name="processando_lote"]'
            );
            if (!loteInput) {
                loteInput = document.createElement("input");
                loteInput.type = "hidden";
                loteInput.name = "processando_lote";
                form.appendChild(loteInput);
            }
            loteInput.value = "1";

            // Criar input para abastecimentos em lote
            let abastecimentosInput = document.querySelector(
                'input[name="abastecimentos"]'
            );
            if (!abastecimentosInput) {
                abastecimentosInput = document.createElement("input");
                abastecimentosInput.type = "hidden";
                abastecimentosInput.name = "abastecimentos";
                form.appendChild(abastecimentosInput);
            }

            // Criar objeto de abastecimento para envio
            const abastecimento = {
                id_fornecedor: AbastecimentoState.cabecalho.id_fornecedor,
                id_filial: AbastecimentoState.cabecalho.id_filial,
                id_departamento: AbastecimentoState.cabecalho.id_departamento,
                numero_nota_fiscal:
                    AbastecimentoState.cabecalho.numero_nota_fiscal,
                chave_nf: AbastecimentoState.cabecalho.chave_nf,
                id_veiculo: AbastecimentoState.cabecalho.id_veiculo,
                id_motorista: AbastecimentoState.cabecalho.id_motorista,
                items: AbastecimentoState.items,
            };

            // Adicionar ao array de abastecimentos
            const abastecimentos = [abastecimento];

            // Converter para JSON e armazenar no input
            abastecimentosInput.value = JSON.stringify(abastecimentos);
        }

        // Desativar botão para evitar clique duplo
        const submitButtons = form.querySelectorAll(
            'button[type="submit"], button[type="button"]'
        );
        submitButtons.forEach((button) => {
            button.disabled = true;
        });

        // Mostrar indicador de carregamento
        const loadingIndicator = document.createElement("div");
        loadingIndicator.className =
            "fixed inset-0 z-50 bg-white bg-opacity-75 flex items-center justify-center";
        loadingIndicator.id = "loading-indicator";
        loadingIndicator.innerHTML = `
            <div class="animate-spin rounded-full h-32 w-32 border-t-2 border-b-2 border-indigo-500"></div>
            <span class="ml-3 text-indigo-500 font-medium">Processando...</span>
        `;
        document.body.appendChild(loadingIndicator);

        // Submeter o formulário (aguardar um pouco para o indicador de carregamento ser exibido)
        setTimeout(() => {
            form.submit();
        }, 100);
    }
}

/**
 * Funções utilitárias de formatação
 */
function formatarData(data) {
    if (!data) return "";
    try {
        const dataObj = new Date(data);
        return dataObj.toLocaleDateString("pt-BR");
    } catch (e) {
        console.error("Erro ao formatar data:", e);
        return data.toString();
    }
}

function formatarNumero(numero, casasDecimais = 2) {
    if (numero === null || numero === undefined || isNaN(numero)) return "0,00";

    try {
        return parseFloat(numero).toLocaleString("pt-BR", {
            minimumFractionDigits: casasDecimais,
            maximumFractionDigits: casasDecimais,
        });
    } catch (e) {
        console.error("Erro ao formatar número:", e);
        return numero.toString();
    }
}

function formatarMoeda(valor) {
    if (valor === null || valor === undefined || isNaN(valor)) return "R$ 0,00";

    try {
        return parseFloat(valor).toLocaleString("pt-BR", {
            style: "currency",
            currency: "BRL",
        });
    } catch (e) {
        console.error("Erro ao formatar moeda:", e);
        return `R$ ${valor.toString()}`;
    }
}

/**
 * Exibe uma mensagem de alerta ao usuário
 */
function mostrarAlerta(mensagem, tipo = "info", duracao = 3000) {
    // Verificar se o componente de toast existe
    if (typeof window.toast === "function") {
        window.toast(mensagem, tipo);
        return;
    }

    // Criar elemento de toast simples
    const toast = document.createElement("div");
    toast.style.position = "fixed";
    toast.style.top = "20px";
    toast.style.right = "20px";
    toast.style.padding = "12px 20px";
    toast.style.backgroundColor =
        tipo === "success"
            ? "#48BB78"
            : tipo === "error"
                ? "#F56565"
                : "#4299E1";
    toast.style.color = "white";
    toast.style.borderRadius = "4px";
    toast.style.zIndex = "9999";
    toast.style.boxShadow = "0 4px 6px rgba(0, 0, 0, 0.1)";
    toast.style.opacity = "0";
    toast.style.transition = "opacity 0.3s ease-in-out";
    toast.textContent = mensagem;

    document.body.appendChild(toast);

    // Animação de entrada
    setTimeout(() => {
        toast.style.opacity = "1";
    }, 10);

    // Remover após o tempo especificado
    setTimeout(() => {
        toast.style.opacity = "0";
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, duracao);
}

/**
 * Inspeciona o estado atual do formulário
 */
function inspecionarEstado() {
    console.log("[DEBUG] Estado atual:");

    if (window.AbastecimentoState) {
        console.log("- Items:", window.AbastecimentoState.items);
        console.log(
            "- Abastecimentos:",
            window.AbastecimentoState.abastecimentos
        );
    } else {
        console.log("- AbastecimentoState não encontrado");
    }

    const campoItems = document.getElementById("itemsHidden");
    if (campoItems) {
        console.log("- Campo itemsHidden:", campoItems.value);
    }
}

/**
 * Obtem o valor de um campo pelo ID ou nome
 */
function obterValorCampo(id) {
    const campo =
        document.querySelector(`input[name="${id}"]`) ||
        document.getElementById(id);
    return campo ? campo.value : "";
}

/**
 * Obtem o texto exibido em um select
 */
function obterTextoSelect(id) {
    const display = document.querySelector(`#${id}-button span`);
    return display ? display.textContent.trim() : "";
}

/**
 * Captura todos os dados do cabeçalho para associar aos itens
 */
function capturarDadosCabecalho() {
    // Capturar todos os dados relevantes do cabeçalho
    const dadosCabecalho = {
        id_fornecedor: obterValorCampo("id_fornecedor"),
        id_veiculo: obterValorCampo("id_veiculo"),
        id_filial: obterValorCampo("id_filial"),
        id_departamento: obterValorCampo("id_departamento"),
        numero_nota_fiscal: obterValorCampo("numero_nota_fiscal"),
        chave_nf: obterValorCampo("chave_nf"),
        id_motorista: obterValorCampo("id_motorista"),

        // Inclua também os dados de display para facilitar a visualização na tabela
        placa_display: obterTextoSelect("id_veiculo"),
        fornecedor_display: obterTextoSelect("id_fornecedor"),
        motorista_display: obterTextoSelect("id_motorista"),
        filial_display: obterValorCampo("filial_display"),
        departamento_display: obterValorCampo("departamento_display"),
    };

    return dadosCabecalho;
}

/**
 * Limpa todos os campos visuais do formulário
 */
function limparTodosOsCampos() {
    console.log("[DEBUG] Limpando todos os campos...");

    // Limpar campos de texto
    const camposTexto = [
        "id_abastecimento",
        "capacidade_tanque",
        "filial_display",
        "departamento_display",
        "numero_nota_fiscal",
        "chave_nf",
        "km_anterior",
        "id_filial",
        "id_departamento",
        "id_combustivel",
        "id_bomba",
        "litros",
        "km_veiculo",
        "valor_unitario",
        "valor_total",
    ];

    camposTexto.forEach(function (id) {
        const campo = document.getElementById(id);
        if (campo) campo.value = "";
    });

    // Limpar smart selects
    const smartSelects = [
        { id: "id_veiculo", text: "Selecione o veículo..." },
        { id: "id_fornecedor", text: "Selecione o fornecedor..." },
        { id: "id_motorista", text: "Selecione o motorista..." },
    ];

    smartSelects.forEach(function (select) {
        const input = document.querySelector(`input[name="${select.id}"]`);
        const display = document.querySelector(`#${select.id}-button span`);

        if (input) input.value = "";
        if (display) display.textContent = select.text;
    });

    // Limpar o item novo no estado
    if (window.AbastecimentoState) {
        const dataAtual = document.getElementById("data_abastecimento")
            ? document.getElementById("data_abastecimento").value
            : "";

        window.AbastecimentoState.novoItem = {
            data_abastecimento: dataAtual,
            id_combustivel: "",
            id_bomba: "",
            litros: "",
            km_veiculo: "",
            valor_unitario: "",
            valor_total: "",
        };

        // Limpar valores de capacidade e KM
        window.AbastecimentoState.capacidadeTanque = "";
        window.AbastecimentoState.kmAnterior = "";
    }
}

// No evento de adição de item, substituir ou estender a função existente
// Esta é a correção crucial que preserva os dados do cabeçalho quando itens são adicionados
function adicionarItemCorrigido() {
    console.log("[DEBUG] Botão Adicionar clicado");

    // 1. Capturar os dados do cabeçalho ANTES de qualquer operação
    const dadosCabecalho = capturarDadosCabecalho();
    console.log("[DEBUG] Dados do cabeçalho capturados:", dadosCabecalho);

    // 2. Chamar a função original de adição de item (se existir)
    if (typeof adicionarItem === "function") {
        adicionarItem();
    }

    // 3. Aguardar o processamento do item
    setTimeout(function () {
        // 4. Preservar os itens já adicionados
        let items = [];
        if (window.AbastecimentoState && window.AbastecimentoState.items) {
            items = [...window.AbastecimentoState.items];
            console.log("[DEBUG] Itens preservados:", items.length);

            // 5. IMPORTANTE: Adicionar os dados do cabeçalho a cada item!
            items.forEach((item) => {
                // Adicionar dados do cabeçalho a cada item
                Object.assign(item, dadosCabecalho);
            });

            console.log("[DEBUG] Itens com dados do cabeçalho:", items);
        }

        // 6. Atualizar o estado e a visualização
        if (window.AbastecimentoState) {
            window.AbastecimentoState.items = items;

            // Atualizar campo hidden
            const itemsHidden = document.getElementById("itemsHidden");
            if (itemsHidden) {
                itemsHidden.value = JSON.stringify(items);
            }

            // Renderizar a tabela novamente
            if (typeof renderizarTabelaItens === "function") {
                renderizarTabelaItens();
            }

            // Se tiver método para atualizar o campo hidden, chamar
            if (typeof atualizarCampoItemsHidden === "function") {
                atualizarCampoItemsHidden();
            }
        }

        console.log("[DEBUG] Limpeza e restauração concluídas");
        inspecionarEstado();
    }, 500);
}

// Função para verificar se há itens antes de enviar o formulário
function verificarEnvioFormulario(e) {
    console.log("[DEBUG] Botão Salvar clicado, verificando itens...");

    inspecionarEstado();

    // Verificar se há itens para enviar
    if (
        !window.AbastecimentoState ||
        !window.AbastecimentoState.items ||
        window.AbastecimentoState.items.length === 0
    ) {
        console.error("[ERRO] Nenhum item para enviar!");
        alert("É necessário adicionar pelo menos um item antes de salvar.");
        if (e) {
            e.preventDefault();
        }
        return false;
    }

    // Verificar dados do cabeçalho em pelo menos um item
    const primeiroItem = window.AbastecimentoState.items[0];
    if (
        !primeiroItem.id_fornecedor ||
        !primeiroItem.id_veiculo ||
        !primeiroItem.id_filial ||
        !primeiroItem.id_departamento ||
        !primeiroItem.numero_nota_fiscal
    ) {
        console.error("[ERRO] Dados do cabeçalho faltando nos itens!");

        // Capturar dados do cabeçalho atuais (se houver)
        const dadosCabecalho = capturarDadosCabecalho();

        // Se houver dados do cabeçalho, adicionar a todos os itens
        if (dadosCabecalho.id_fornecedor && dadosCabecalho.id_veiculo) {
            console.log(
                "[DEBUG] Adicionando dados do cabeçalho aos itens antes de salvar"
            );

            window.AbastecimentoState.items.forEach((item) => {
                Object.assign(item, dadosCabecalho);
            });

            // Atualizar o campo hidden
            const itemsHidden = document.getElementById("itemsHidden");
            if (itemsHidden) {
                itemsHidden.value = JSON.stringify(
                    window.AbastecimentoState.items
                );
            }
        }
    }

    console.log("[DEBUG] Formulário pronto para envio");
    return true;
}

// Substituir ou estender as configurações de eventos
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

function configurarEventosCorrigidos() {
    // Botão adicionar item
    const btnAdicionar = document.getElementById("adicionarItem");
    if (btnAdicionar) {
        // Remover eventos existentes
        const novoBtn = btnAdicionar.cloneNode(true);
        btnAdicionar.parentNode.replaceChild(novoBtn, btnAdicionar);

        // Adicionar novo evento com nossa versão corrigida
        novoBtn.addEventListener("click", adicionarItemCorrigido);
    }

    // Botão salvar (adicionar ao lote)
    const btnSalvar =
        document.getElementById("btnAddToLote") ||
        document.querySelector('button[type="submit"]');
    if (btnSalvar) {
        // Remover eventos existentes
        const novoBtn = btnSalvar.cloneNode(true);
        btnSalvar.parentNode.replaceChild(novoBtn, btnSalvar);

        // Adicionar novo evento com nossa verificação
        novoBtn.addEventListener("click", function (e) {
            if (!verificarEnvioFormulario(e)) {
                return;
            }

            // Se chegou aqui, podemos prosseguir com o salvamento normal
            if (typeof salvarAbastecimento === "function") {
                salvarAbastecimento();
            } else {
                // Submeter o formulário diretamente
                const form = document.getElementById("abastecimentoForm");
                if (form) {
                    form.submit();
                }
            }
        });
    }

    // Verificar estado inicial
    setTimeout(inspecionarEstado, 2000);
}

/**
 * Configura os eventos para o cálculo automático do valor total
 */
function configurarEventosCalculoValorTotal() {
    console.log("Configurando eventos para cálculo de valor total...");

    // Obter referências aos campos
    const litrosInput = document.getElementById("litros");
    const valorUnitarioInput = document.getElementById("valor_unitario");

    if (!litrosInput || !valorUnitarioInput) {
        console.error("Campos litros ou valor_unitario não encontrados!");
        return;
    }

    // Remover eventos existentes para evitar duplicação
    function removeAllEventListeners(element) {
        const clone = element.cloneNode(true);
        element.parentNode.replaceChild(clone, element);
        return clone;
    }

    // Refazer as referências após limpar eventos
    const litrosInputNew = removeAllEventListeners(litrosInput);
    const valorUnitarioInputNew = removeAllEventListeners(valorUnitarioInput);

    // Adicionar evento de input para litros
    litrosInputNew.addEventListener("input", function (e) {
        console.log("Evento input em litros:", this.value);

        // Calcular valor total imediatamente
        setTimeout(calcularValorTotal, 10);
    });

    // Adicionar evento de change para litros (quando o campo perde foco)
    litrosInputNew.addEventListener("change", function (e) {
        console.log("Evento change em litros:", this.value);

        // Validar o valor
        const valor = parseFloat(this.value.replace(",", ".")) || 0;

        // Validar capacidade do tanque se necessário
        if (typeof validarCapacidadeTanque === "function") {
            validarCapacidadeTanque(valor);
        }

        // Atualizar o estado
        if (window.AbastecimentoState && window.AbastecimentoState.itemAtual) {
            window.AbastecimentoState.itemAtual.litros = valor;
        }

        // Recalcular valor total
        calcularValorTotal();
    });

    // Adicionar evento de input para valor unitário
    valorUnitarioInputNew.addEventListener("input", function (e) {
        console.log("Evento input em valor unitário:", this.value);

        // Calcular valor total imediatamente
        setTimeout(calcularValorTotal, 10);
    });

    // Adicionar evento de change para valor unitário (quando o campo perde foco)
    valorUnitarioInputNew.addEventListener("change", function (e) {
        console.log("Evento change em valor unitário:", this.value);

        // Atualizar o estado
        const valor = parseFloat(this.value.replace(",", ".")) || 0;
        if (window.AbastecimentoState && window.AbastecimentoState.itemAtual) {
            window.AbastecimentoState.itemAtual.valor_unitario = valor;
        }

        // Recalcular valor total
        calcularValorTotal();
    });

    console.log(
        "Eventos para cálculo de valor total configurados com sucesso!"
    );
}

/**
 * Inicializa os eventos e cálculos quando a página carrega
 */
function inicializarCalculoValorTotal() {
    // Esperar o DOM estar completamente carregado
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function () {
            configurarEventosCalculoValorTotal();

            // Executar cálculo inicial se os campos já tiverem valores
            setTimeout(calcularValorTotal, 500);
        });
    } else {
        // DOM já carregado
        configurarEventosCalculoValorTotal();

        // Executar cálculo inicial se os campos já tiverem valores
        setTimeout(calcularValorTotal, 500);
    }
}

/**
 * Função para buscar valor unitário por bomba com recálculo automático
 */
function buscarValorUnitarioPorBomba(idBomba) {
    if (!idBomba) return;

    // Verificar se o veículo é terceiro
    const isTerceiro =
        window.AbastecimentoState &&
            window.AbastecimentoState.veiculo.isTerceiro
            ? 1
            : 0;

    // Obter o token CSRF
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    console.log(
        "Buscando valor unitário para bomba ID:",
        idBomba,
        "Terceiro:",
        isTerceiro
    );

    // Mostrar feedback visual
    const valorUnitarioInput = document.getElementById("valor_unitario");
    if (valorUnitarioInput) {
        valorUnitarioInput.value = "Carregando...";
        valorUnitarioInput.readOnly = true;
    }

    // Fazer a requisição
    fetch(
        `/admin/api/bombas/valor-unitario?id_bomba=${idBomba}&is_terceiro=${isTerceiro}`,
        {
            method: "GET",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/json",
                Accept: "application/json",
            },
        }
    )
        .then((response) => {
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            if (data && data.valor) {
                console.log("Valor unitário recebido para bomba:", data.valor);

                // Atualizar o campo valor unitário
                if (valorUnitarioInput) {
                    valorUnitarioInput.value = data.valor;

                    // Forçar atualização dos botões após carregar o valor
                    const btnAdicionar = document.getElementById("adicionarItem");
                    const btnAtualizar = document.getElementById("atualizarItem");

                    if (AbastecimentoState.isEditingItem) {
                        if (btnAdicionar) {
                            btnAdicionar.style.display = "none";
                            btnAdicionar.style.visibility = "hidden";
                        }
                        if (btnAtualizar) {
                            btnAtualizar.style.display = "inline-flex";
                            btnAtualizar.style.visibility = "visible";
                        }
                    } else {
                        if (btnAdicionar) {
                            btnAdicionar.style.display = "inline-flex";
                            btnAdicionar.style.visibility = "visible";
                        }
                        if (btnAtualizar) {
                            btnAtualizar.style.display = "none";
                            btnAtualizar.style.visibility = "hidden";
                        }
                    }

                    // Atualizar o estado
                    if (
                        window.AbastecimentoState &&
                        window.AbastecimentoState.itemAtual
                    ) {
                        window.AbastecimentoState.itemAtual.valor_unitario =
                            parseFloat(data.valor);
                    }

                    // Recalcular valor total
                    calcularValorTotal();
                }
            } else {
                console.warn(
                    "Resposta da API sem valor para bomba ID:",
                    idBomba
                );
            }
        })
        .catch((error) => {
            console.error("Erro ao buscar valor unitário da bomba:", error);
            mostrarAlerta(
                "Erro ao buscar valor unitário. Por favor, insira manualmente.",
                "warning"
            );
        })
        .finally(() => {
            if (valorUnitarioInput) {
                valorUnitarioInput.readOnly = false;
            }
        });
}

// Iniciar configuração
inicializarCalculoValorTotal();

// Expor funções ao escopo global
if (window.AbastecimentoForm) {
    window.AbastecimentoForm.calcularValorTotal = calcularValorTotal;
    window.AbastecimentoForm.configurarEventosCalculoValorTotal =
        configurarEventosCalculoValorTotal;

    // Substituir a função existente para garantir que o cálculo seja executado
    const originalBuscarValorUnitarioPorBomba =
        window.AbastecimentoForm.buscarValorUnitarioPorBomba;
    window.AbastecimentoForm.buscarValorUnitarioPorBomba = function (idBomba) {
        // Executar a função original se existir
        if (typeof originalBuscarValorUnitarioPorBomba === "function") {
            originalBuscarValorUnitarioPorBomba(idBomba);
        }

        // Executar nossa nova versão
        buscarValorUnitarioPorBomba(idBomba);
    };
}

// Inicializar quando o documento estiver carregado
document.addEventListener("DOMContentLoaded", init);

// Expor funções para acesso global (útil para debugging e extensibilidade)
window.AbastecimentoForm = {
    init,
    atualizarDadosVeiculo,
    adicionarItem,
    editarItem,
    removerItem,
    atualizarItem,
    limparFormularioCompleto,
    salvarAbastecimento,
    atualizarCampoItemsHidden,
    renderizarTabelaItens,
    mostrarAlerta,
    validarCompatibilidadeCombustivelBomba,
    carregarPostos
};
