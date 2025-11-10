document.addEventListener("DOMContentLoaded", function () {
    let registrosReqMatTemporarios = [];

    const reqMatJson = document.getElementById("tabelaReqMats_json").value;
    const requisicoes = JSON.parse(reqMatJson || "[]");

    if (requisicoes && requisicoes.length > 0) {
        requisicoes.forEach((item) => {
            // Criar objeto de anexo se existir
            let anexoInfo = null;
            if (item.anexo_imagem) {
                anexoInfo = {
                    name: item.anexo_imagem.split("/").pop(), // Extrair nome do arquivo do path
                    url: "/storage/" + item.anexo_imagem, // URL para exibição
                    type: getFileTypeFromExtension(item.anexo_imagem),
                    existing: true, // Flag para indicar que é um anexo existente
                };
            }

            registrosReqMatTemporarios.push({
                codProduto: item.produto
                    ? item.produto.codigo_produto
                    : item.id_produtos_solicitacoes || "", // Usar código do produto
                produto: item.produto.descricao_produto,
                idProduto: item.id_protudos,
                quantidade: item.quantidade,
                dataInclusao: item.data_inclusao,
                dataAlteracao: item.data_alteracao,
                observacao: item.observacao,
                anexo: anexoInfo,
                situacao_pecas: item.situacao_pecas || null,
                quantidade_transferencia: item.quantidade_transferencia || null,
                filial_transferencia: item.filial_transferencia || null,
            });
        });
        atualizarTabelarequisicoes();
    }

    // Função helper para determinar tipo de arquivo pela extensão
    function getFileTypeFromExtension(filename) {
        const extension = filename.split(".").pop().toLowerCase();
        const imageExtensions = ["jpg", "jpeg", "png", "gif", "bmp", "webp"];

        if (imageExtensions.includes(extension)) {
            return "image/" + extension;
        } else if (extension === "pdf") {
            return "application/pdf";
        } else if (extension === "doc") {
            return "application/msword";
        } else if (extension === "docx") {
            return "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
        }

        return "application/octet-stream";
    }

    function adicionarRequisicoes() {
        const codProduto = document.getElementById("codReqProduto").value ?? 0;
        const produto = getSmartSelectValue("id_produto").label;
        const idProduto = getSmartSelectValue("id_produto").value;
        const quantidade = document.getElementById("quantidade").value;
        const qtd_estoque = document.getElementById("estoque_filial").value;
        const observacao = document.getElementById("observacao_produto").value;
        const anexoInput = document.getElementById("anexo_produto");
        const dataInclusao = formatarData();
        const dataAlteracao = formatarData();

        if (!produto) {
            alert("Produto é obrigatório!");
            return;
        }

        if (!quantidade) {
            alert("Quantidade é obrigatória!");
            return;
        }

        // Converter para números para comparação
        const qtdEstoqueNum = parseFloat(qtd_estoque) || 0;
        const quantidadeNum = parseFloat(quantidade) || 0;

        // Processar anexo
        let anexoInfo = null;
        let anexoFile = null;
        if (anexoInput.files && anexoInput.files[0]) {
            anexoFile = anexoInput.files[0];

            anexoInfo = {
                name: anexoFile.name,
                size: anexoFile.size,
                type: anexoFile.type,
                index: registrosReqMatTemporarios.length,
                file: anexoFile, // Armazenar o objeto file
            };
        }

        const registrorequisicoes = {
            codProduto: codProduto,
            produto: produto,
            idProduto: idProduto,
            quantidade: quantidade,
            dataInclusao: dataInclusao,
            dataAlteracao: dataAlteracao,
            observacao: observacao,
            anexo: anexoInfo,
            situacao_pecas: null,
            quantidade_transferencia: null,
            filial_transferencia: null,
        };

        registrosReqMatTemporarios.push(registrorequisicoes);

        // Criar input file hidden para o anexo se existir
        if (anexoFile) {
            criarInputAnexoProduto(
                registrosReqMatTemporarios.length - 1,
                anexoFile
            );
        }

        atualizarTabelarequisicoes();
        limparReqMateriaisFormularioTemp();

        alert("Registro adicionado com sucesso!");

        // Atualiza o campo hidden (sem o objeto file)
        document.getElementById("tabelaReqMats_json").value = JSON.stringify(
            registrosReqMatTemporarios.map((item) => ({
                ...item,
                anexo: item.anexo
                    ? {
                          name: item.anexo.name,
                          size: item.anexo.size,
                          type: item.anexo.type,
                          index: item.anexo.index,
                      }
                    : null,
            }))
        );
    }

    // Função para criar input file hidden para anexos de produtos
    function criarInputAnexoProduto(index, file) {
        const container = document.getElementById("anexos-produtos-container");
        if (!container || !file) return;

        // Remover input existente com o mesmo índice
        const existingInput = container.querySelector(
            `input[name="anexo_produto_${index}"]`
        );
        if (existingInput) {
            existingInput.remove();
        }

        // Criar um novo DataTransfer para poder definir o arquivo no input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);

        // Criar input file
        const input = document.createElement("input");
        input.type = "file";
        input.name = `anexo_produto_${index}`;
        input.style.display = "none";
        input.files = dataTransfer.files;

        container.appendChild(input);

        console.log(`Input anexo criado: anexo_produto_${index}`, file.name);
    }

    // Função para remover input de anexo
    function removerInputAnexoProduto(index) {
        const container = document.getElementById("anexos-produtos-container");
        if (!container) return;

        const input = container.querySelector(
            `input[name="anexo_produto_${index}"]`
        );
        if (input) {
            input.remove();
        }
    }

    function atualizarTabelarequisicoes() {
        const tbody = document.getElementById("tabelaReqMateriasBody");
        if (!tbody) return;

        tbody.innerHTML = "";

        registrosReqMatTemporarios.forEach((registrorequisicoes, index) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <button type="button" onclick="editarReqMateriaisRegistro(${index})" title="Editar"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <button type="button" onclick="excluirReqMateriaisRegistro(${index})" title="Excluir"
                            class="btn-excluir inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </td>
            <td class="px-6 py-4">${
                registrorequisicoes.codProduto || registrorequisicoes.idProduto
            }</td>
            <td class="px-6 py-4">${registrorequisicoes.produto}</td>
            <td class="px-6 py-4">${registrorequisicoes.quantidade}</td>
            <td class="px-6 py-4">
                ${
                    registrorequisicoes.situacao_pecas === "TRANSFERENCIA"
                        ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">TRANSFERÊNCIA</span>'
                        : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">NORMAL</span>'
                }
            </td>
            <td class="px-6 py-4">${formatarData(
                registrorequisicoes.dataInclusao
            )}</td>
            <td class="px-6 py-4">${formatarData(
                registrorequisicoes.dataAlteracao
            )}</td>
            <td class="px-6 py-4">${registrorequisicoes.observacao || "-"}</td>
            <td class="px-6 py-4">
                ${
                    registrorequisicoes.anexo
                        ? `<div class="flex items-center justify-center">
                        ${
                            registrorequisicoes.anexo.type.startsWith("image/")
                                ? `<img src="${getFilePreview(
                                      registrorequisicoes.anexo
                                  )}"
                                 alt="Anexo"
                                 class="h-8 w-8 rounded object-cover border border-gray-300"
                                 onclick="previewAnexo('${
                                     registrorequisicoes.anexo.name
                                 }', '${getFilePreviewUrl(
                                      registrorequisicoes.anexo
                                  )}')"
                                 style="cursor: pointer;"
                                 title="${registrorequisicoes.anexo.name}">`
                                : `<div class="flex h-8 w-8 items-center justify-center rounded border bg-gray-100 cursor-pointer"
                                 onclick="previewAnexo('${
                                     registrorequisicoes.anexo.name
                                 }', '${getFilePreviewUrl(
                                      registrorequisicoes.anexo
                                  )}')"
                                 title="${registrorequisicoes.anexo.name}">
                                 <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                 </svg>
                             </div>`
                        }
                    </div>`
                        : '<span class="text-gray-400">-</span>'
                }
            </td>
        `;
            tbody.appendChild(tr);
        });
    }

    function limparReqMateriaisFormularioTemp() {
        clearSmartSelect("id_produto");

        document.getElementById("quantidade").value = "";
        document.getElementById("estoque_filial").value = "";
        document.getElementById("observacao_produto").value = "";
        document.getElementById("anexo_produto").value = "";
    }

    function excluirReqMateriaisRegistro(index) {
        // Remover input de anexo se existir
        removerInputAnexoProduto(index);

        registrosReqMatTemporarios.splice(index, 1);

        // Reorganizar os inputs de anexo após exclusão
        reorganizarInputsAnexos();

        atualizarTabelarequisicoes();
        document.getElementById("tabelaReqMats_json").value = JSON.stringify(
            registrosReqMatTemporarios
        );
    }

    // Função para reorganizar os inputs de anexo após exclusão
    function reorganizarInputsAnexos() {
        const container = document.getElementById("anexos-produtos-container");
        if (!container) return;

        // Limpar todos os inputs
        container.innerHTML = "";

        // Recriar inputs com índices corretos
        registrosReqMatTemporarios.forEach((item, newIndex) => {
            if (item.anexo && item.anexo.file) {
                criarInputAnexoProduto(newIndex, item.anexo.file);
                // Atualizar o índice no objeto anexo
                item.anexo.index = newIndex;
            }
        });
    }

    function editarReqMateriaisRegistro(index) {
        const registrorequisicoes = registrosReqMatTemporarios[index];

        document.getElementById("codReqProduto").value =
            registrorequisicoes.codProduto;

        setSmartSelectValue("id_produto", registrorequisicoes.idProduto, {
            createIfNotFound: true,
            tempLabel: registrorequisicoes.produto, // Corrigido: era nomeFornecedor, agora é produto
        });

        document.getElementById("quantidade").value =
            registrorequisicoes.quantidade;

        document.getElementById("observacao_produto").value =
            registrorequisicoes.observacao;

        excluirReqMateriaisRegistro(index);
    }

    function formatarData(data) {
        // Se não houver data, ou se for inválida, use a data atual
        if (!data || new Date(data).toString() === "Invalid Date") {
            return new Date().toLocaleString("pt-BR", {
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
                timeZone: "America/Cuiaba",
            });
        }

        const dataObj = new Date(data);
        return dataObj.toLocaleString("pt-BR", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
            timeZone: "America/Cuiaba",
        });
    }

    // Função para obter preview do arquivo
    function getFilePreview(anexoInfo) {
        if (!anexoInfo) return "";

        // Se é um anexo existente (já salvo no servidor)
        if (anexoInfo.existing && anexoInfo.url) {
            if (anexoInfo.type.startsWith("image/")) {
                return anexoInfo.url;
            }
        }

        // Se tem file object (novo upload), criar URL temporária
        if (anexoInfo.file && anexoInfo.type.startsWith("image/")) {
            return URL.createObjectURL(anexoInfo.file);
        }

        // Para outros tipos de arquivo, retorna um ícone genérico
        return "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIGZpbGw9IiM2MzY2ZjEiIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTE0LDJIMTZMMjAsMTZWMjJBMiwyIDAgMCwxIDE4LDI0SDZBMiwyIDAgMCwxIDQsMjJWMkE2LDYgMCAwLDEgMTAsNEgxNFYyTTE2LDRIMThBMiwyIDAgMCwxIDIwLDZWOEgxNlY0WiIvPjwvc3ZnPg==";
    }

    // Função para obter URL correta para preview (incluindo não-imagens)
    function getFilePreviewUrl(anexoInfo) {
        if (!anexoInfo) return "";

        // Se é um anexo existente, retorna a URL do servidor
        if (anexoInfo.existing && anexoInfo.url) {
            return anexoInfo.url;
        }

        // Se tem file object (novo upload), criar URL temporária
        if (anexoInfo.file) {
            return URL.createObjectURL(anexoInfo.file);
        }

        return "";
    }

    // Função para preview do anexo
    function previewAnexo(filename, src) {
        // Criar modal para preview
        const modal = document.createElement("div");
        modal.className =
            "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50";
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-2xl max-h-96 overflow-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">${filename}</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <img src="${src}" alt="${filename}" class="max-w-full max-h-64 object-contain mx-auto">
            </div>
        `;
        document.body.appendChild(modal);
    }

    // Função para adicionar produto como transferência
    function adicionarProdutoTransferencia(
        idProduto,
        descricaoProduto,
        quantidade,
        filialOrigem,
        nomeFilialOrigem,
        codigoProduto
    ) {
        const dataInclusao = formatarData();
        const dataAlteracao = formatarData();

        const registrorequisicoes = {
            codProduto: codigoProduto || idProduto, // Usar código do produto ou ID como fallback
            produto: descricaoProduto,
            idProduto: idProduto,
            quantidade: quantidade,
            dataInclusao: dataInclusao,
            dataAlteracao: dataAlteracao,
            observacao: `Transferência de ${nomeFilialOrigem}`,
            anexo: null,
            situacao_pecas: "TRANSFERENCIA",
            quantidade_transferencia: quantidade,
            filial_transferencia: filialOrigem,
        };

        registrosReqMatTemporarios.push(registrorequisicoes);
        atualizarTabelarequisicoes();

        // Atualiza o campo hidden
        document.getElementById("tabelaReqMats_json").value = JSON.stringify(
            registrosReqMatTemporarios.map((item) => ({
                ...item,
                anexo: item.anexo
                    ? {
                          name: item.anexo.name,
                          size: item.anexo.size,
                          type: item.anexo.type,
                          index: item.anexo.index,
                      }
                    : null,
            }))
        );

        alert("Produto adicionado para transferência com sucesso!");
    }

    window.atualizarTabelarequisicoes = atualizarTabelarequisicoes;
    window.adicionarRequisicoes = adicionarRequisicoes;
    window.adicionarProdutoTransferencia = adicionarProdutoTransferencia;
    window.limparReqMateriaisFormularioTemp = limparReqMateriaisFormularioTemp;
    window.excluirReqMateriaisRegistro = excluirReqMateriaisRegistro;
    window.editarReqMateriaisRegistro = editarReqMateriaisRegistro;
    window.getFilePreview = getFilePreview;
    window.getFilePreviewUrl = getFilePreviewUrl;
    window.previewAnexo = previewAnexo;
    window.criarInputAnexoProduto = criarInputAnexoProduto;

    // Função de debug
    window.debugFormData = function () {
        const form = document.getElementById("requisicao-form");
        const formData = new FormData(form);

        console.log("=== DEBUG FORM DATA ===");
        console.log("Produtos registrados:", registrosReqMatTemporarios.length);

        for (let [key, value] of formData.entries()) {
            if (key.startsWith("anexo_produto_")) {
                console.log(`${key}:`, value.name, value.size, "bytes");
            }
        }

        const container = document.getElementById("anexos-produtos-container");
        if (container) {
            console.log(
                "Inputs de anexo no container:",
                container.children.length
            );
        }
        console.log("========================");
    };
});
