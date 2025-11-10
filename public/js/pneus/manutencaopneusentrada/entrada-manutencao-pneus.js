window.onload = function () {
    const pneuManutencao = []
    let editandoIndex = -1; // Controla qual item está sendo editado

    // Expor o array e funções de forma mais controlada
    window.pneuManutencaoManager = {
        // Getter para acessar os dados
        get dados() {
            return [...pneuManutencao]; // Retorna uma cópia
        },

        // Método para limpar dados
        limpar() {
            pneuManutencao.length = 0;
            editandoIndex = -1;
            atualizarTabela();
            return this;
        },

        // Método para adicionar múltiplos itens
        adicionarItens(itens) {
            if (Array.isArray(itens)) {
                pneuManutencao.push(...itens);
                atualizarTabela();
            }
            return this;
        },

        // Método para substituir todos os dados
        substituirDados(novosDados) {
            pneuManutencao.length = 0;
            editandoIndex = -1;
            if (Array.isArray(novosDados)) {
                pneuManutencao.push(...novosDados);
            }
            atualizarTabela();
            return this;
        },

        // Método para atualizar o campo hidden
        atualizarCampoHidden() {
            document.getElementById('pneus').value = JSON.stringify(pneuManutencao);
            return this;
        }
    };

    function atualizarTabela() {
        const tabela = document.getElementById('tabelaEntradaPneuBody');

        if (!tabela) {
            console.error('Elemento #tabelaEntradaPneuBody não encontrado');
            return;
        }

        tabela.innerHTML = '';

        let totalValorPneu = 0; // acumulador do total

        pneuManutencao.map((item, index) => {
            const tr = document.createElement('tr');

            if (index === editandoIndex) {
                tr.classList.add('bg-yellow-50', 'border-l-4', 'border-yellow-400');
            }

            const temArquivo = item.laudo_descarte && item.laudo_descarte.trim() !== '';
            const htmlBotaoVisualizarArquivo = temArquivo
                ? `<x-tooltip content="Visualizar arquivo">
                   <div class="cursor-pointer visualizar-arquivo" data-index="${index}" data-file="${item.laudo_descarte}" title="Visualizar arquivo">
                       <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-green-600 hover:text-green-800">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                           <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                       </svg>
                   </div>
               </x-tooltip>`
                : '';

            tr.innerHTML = `
            <td class="px-6 py-4">
                <div class="flex gap-2">
                    <x-tooltip content="Editar">
                        <div class="cursor-pointer edit-pneu" data-index="${index}" title="Editar">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-blue-600 hover:text-blue-800">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </div>
                    </x-tooltip>
                    <x-tooltip content="Excluir">
                        <div class="cursor-pointer delete-pneu" data-index="${index}" title="Excluir">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-red-600 hover:text-red-800">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </div>
                    </x-tooltip>
                    ${htmlBotaoVisualizarArquivo}
                </div>
            </td>
            <td class="px-6 py-4">${item.data_inclusao}</td>
            <td class="px-6 py-4">${item.data_alteracao ?? '-'}</td>
            <td class="px-6 py-4">${item.numero_fogo}</td>
            <td class="px-6 py-4">${item.tipo_reforma}</td>
            <td class="px-6 py-4">${item.desenho_pneu}</td>
            <td class="px-6 py-4">${item.tipo_borracha || '-'}</td>
            <td class="px-6 py-4">${item.valor_pneu}</td>
            <td class="px-6 py-4">${item.descarte == '1' ? 'Sim' : 'Não'}</td>
        `;

            // somar valor do pneu ao total
            let valor = parseFloat(String(item.valor_pneu).replace(/\./g, '').replace(',', '.'));
            if (!isNaN(valor)) totalValorPneu += valor;

            tr.querySelector(".edit-pneu").addEventListener("click", (event) => {
                const index = parseInt(event.currentTarget.getAttribute("data-index"));
                editarPneu(index);
            });

            tr.querySelector(".delete-pneu").addEventListener("click", (event) => {
                const index = parseInt(event.currentTarget.getAttribute("data-index"));
                excluir(index);
            });

            const elementoBotaoVisualizarArquivo = tr.querySelector(".visualizar-arquivo");
            if (elementoBotaoVisualizarArquivo) {
                elementoBotaoVisualizarArquivo.addEventListener("click", (event) => {
                    const index = parseInt(event.currentTarget.getAttribute("data-index"));
                    const arquivo = event.currentTarget.getAttribute("data-file");
                    visualizarArquivo(index, arquivo);
                });
            }

            tabela.appendChild(tr);
        });

        // Atualizar campo total
        const inputTotal = document.querySelector('[name="valor_pneu_total"]');
        if (inputTotal) {
            inputTotal.value = totalValorPneu.toFixed(2).replace('.', ','); // formata para padrão BR
        }

        window.pneuManutencaoManager.atualizarCampoHidden();
        atualizarEstadoBotoes();
    }



    function excluir(index) {
        if (confirm('Tem certeza que deseja excluir este item?')) {
            pneuManutencao.splice(index, 1);

            // Ajustar índice de edição se necessário
            if (editandoIndex === index) {
                cancelarEdicao();
            } else if (editandoIndex > index) {
                editandoIndex--;
            }

            atualizarTabela();
        }
    }

    function editarPneu(index) {
        if (index < 0 || index >= pneuManutencao.length) {
            console.error('Índice inválido para edição:', index);
            return;
        }

        const item = pneuManutencao[index];

        // Cancelar edição anterior se houver
        if (editandoIndex !== -1) {
            cancelarEdicao();
        }

        // Definir novo índice de edição
        editandoIndex = index;

        // Preencher campos do formulário
        try {
            document.querySelector('[name="id_pneu"]').value = item.numero_fogo || '';
            document.querySelector('[name="descarte"]').value = item.descarte || '0';

            // Smart Selects - verificar se as funções existem
            if (typeof setSmartSelectValue === 'function') {
                if (item.numero_fogo) {
                    setSmartSelectValue('id_pneu', item.numero_fogo, {
                        label: item.numero_fogo,
                        createIfNotFound: true
                    });
                }

                if (item.id_tipo_borracha && item.tipo_borracha) {
                    setSmartSelectValue('tipo_borracha', item.id_tipo_borracha, {
                        label: item.tipo_borracha
                    });
                }

                if (item.id_tipo_reforma && item.tipo_reforma) {
                    setSmartSelectValue('id_tipo_reforma', item.id_tipo_reforma, {
                        label: item.tipo_reforma
                    });
                }

                if (item.id_desenho_pneu && item.desenho_pneu) {
                    setSmartSelectValue('id_desenho_pneu', item.id_desenho_pneu, {
                        label: item.desenho_pneu
                    });
                }

                document.querySelector('[name="valor_pneu"]').value = item.valor_pneu || '';

            }

            // Atualizar visual da tabela
            atualizarTabela();

            // Scroll para o formulário
            const formulario = document.querySelector('#form-entrada-manutencao') ||
                document.querySelector('form') ||
                document.querySelector('[name="id_filial"]')?.closest('form');

            if (formulario) {
                formulario.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

        } catch (error) {
            console.error('Erro ao carregar dados para edição:', error);
            alert('Erro ao carregar dados para edição. Verifique o console.');
            cancelarEdicao();
        }
    }

    function cancelarEdicao() {
        editandoIndex = -1;
        limparFormulario();
        atualizarTabela();
    }

    function limparFormulario() {
        clearSmartSelect('id_pneu');
        clearSmartSelect('tipo_borracha');
        clearSmartSelect('id_tipo_reforma');
        clearSmartSelect('id_desenho_pneu');

        const valorInput = document.querySelector('[name="valor_pneu"]');
        if (valorInput) valorInput.value = '';
    }


    function atualizarEstadoBotoes() {
        // Atualizar texto do botão principal
        const botaoAdicionar = document.querySelector('#btn-adicionar-pneu') ||
            document.querySelector('[onclick*="adicionarpneu"]') ||
            document.querySelector('button[type="button"]');

        if (botaoAdicionar) {
            if (editandoIndex !== -1) {
                botaoAdicionar.textContent = 'Atualizar Pneu';
                botaoAdicionar.classList.remove('bg-green-600', 'hover:bg-green-700');
                botaoAdicionar.classList.add('bg-blue-600', 'hover:bg-blue-700');
            } else {
                botaoAdicionar.textContent = 'Adicionar Pneu';
                botaoAdicionar.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                botaoAdicionar.classList.add('bg-green-600', 'hover:bg-green-700');
            }
        }

        // Mostrar/ocultar botão cancelar
        let botaoCancelar = document.querySelector('#btn-cancelar-edicao');

        if (editandoIndex !== -1) {
            if (!botaoCancelar) {
                botaoCancelar = document.createElement('button');
                botaoCancelar.id = 'btn-cancelar-edicao';
                botaoCancelar.type = 'button';
                botaoCancelar.className = 'ml-2 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors';
                botaoCancelar.textContent = 'Cancelar Edição';
                botaoCancelar.addEventListener('click', cancelarEdicao);

                if (botaoAdicionar && botaoAdicionar.parentNode) {
                    botaoAdicionar.parentNode.insertBefore(botaoCancelar, botaoAdicionar.nextSibling);
                }
            }
            if (botaoCancelar) {
                botaoCancelar.style.display = 'inline-block';
            }
        } else {
            if (botaoCancelar) {
                botaoCancelar.style.display = 'none';
            }
        }
    }

    async function adicionarpneu() {
        console.log('--- Início de adicionarpneu ---');

        // Obter valores dos selects
        const id_desenho_pneu_raw = getSmartSelectValue('id_desenho_pneu');
        const tipo_borracha_raw = getSmartSelectValue('tipo_borracha');
        const id_tipo_reforma_raw = getSmartSelectValue('id_tipo_reforma');
        const pneusSelecionadosRaw = getSmartSelectValue('id_pneu');

        // Normalizar IDs
        const idDesenho = id_desenho_pneu_raw?.value ?? id_desenho_pneu_raw;
        const tipoBorracha = tipo_borracha_raw?.value ?? tipo_borracha_raw;
        const idTipoReforma = id_tipo_reforma_raw?.value ?? id_tipo_reforma_raw;
        const pneusSelecionados = pneusSelecionadosRaw
            ? Array.isArray(pneusSelecionadosRaw) ? pneusSelecionadosRaw : [pneusSelecionadosRaw]
            : [];

        // Validar campos obrigatórios
        if (!pneusSelecionados.length) return alert('Selecione pelo menos um pneu!');
        if (!idDesenho || isNaN(idDesenho)) return alert('Selecione um desenho de pneu válido!');
        if (!tipoBorracha || isNaN(tipoBorracha)) return alert('Selecione o tipo de borracha!');
        if (!idTipoReforma || isNaN(idTipoReforma)) return alert('Selecione o tipo de reforma!');

        // Valor do pneu
        let valorPneuInput = document.querySelector('[name="valor_pneu"]');
        if (!valorPneuInput) return alert('Campo valor do pneu não encontrado!');
        let valorPneu = valorPneuInput.value.replace(/\./g, '').replace(',', '.');
        valorPneu = parseFloat(valorPneu);
        if (isNaN(valorPneu) || valorPneu <= 0) return alert('Insira um valor numérico válido para o pneu!');

        // Outras flags
        const descarte = document.querySelector('[name="descarte"]:checked')?.value ?? '0';
        const is_feito = document.querySelector('[name="is_feito"]:checked')?.value ?? '0';
        const is_conferido = document.querySelector('[name="is_conferido"]:checked')?.value ?? '0';
        const laudo_descarte = document.querySelector('[name="laudo_descarte"]')?.files[0] ?? null;

        // Buscar dados adicionais do pneu
        let dadosPneu = null;
        try {
            console.log('Buscando dados do desenho do pneu ID:', idDesenho);
            dadosPneu = await buscarDadosPneu(idDesenho);
            console.log('Dados do pneu recebidos:', dadosPneu);
        } catch (error) {
            console.error('Erro ao buscar dados do pneu:', error);
            if (!confirm(`Erro ao buscar dados do pneu: ${error.message}\nDeseja continuar sem os dados adicionais?`)) return;
        }

        // Adicionar ou editar
        if (editandoIndex !== -1) {
            console.log('Editando pneu na posição', editandoIndex);
            pneuManutencao[editandoIndex] = {
                ...pneuManutencao[editandoIndex],
                data_alteracao: new Date().toLocaleDateString(),
                numero_fogo: pneusSelecionados[0].value ?? pneusSelecionados[0],
                id_desenho_pneu: idDesenho,
                desenho_pneu: construirDescricaoDesenho(dadosPneu, id_desenho_pneu_raw?.label ?? 'Desenho'),
                id_tipo_reforma: idTipoReforma,
                tipo_reforma: id_tipo_reforma_raw?.label ?? '-',
                id_tipo_borracha: tipoBorracha,
                tipo_borracha: tipo_borracha_raw?.label ?? '-',
                valor_pneu: valorPneu,
                descarte,
                is_conferido,
                is_feito,
                laudo_descarte: laudo_descarte ? laudo_descarte.name : null,
                ...(dadosPneu && {
                    marca_pneu: dadosPneu.marca || null,
                    modelo_pneu: dadosPneu.modelo || null,
                    medida_pneu: dadosPneu.medida || null,
                    status_atual: dadosPneu.status || null,
                    vida_atual: dadosPneu.vida || null
                })
            };
            editandoIndex = -1;
        } else {
            console.log('Adicionando novos pneus:', pneusSelecionados);
            pneusSelecionados.forEach(pneuSel => {
                const valor = pneuSel.value ?? pneuSel;
                const label = pneuSel.label ?? valor;

                // Evita duplicados
                if (pneuManutencao.some(p => String(p.numero_fogo) === label)) {
                    console.warn(`Pneu ${label} já adicionado, ignorando`);
                    return;
                }

                const novoPneu = {
                    id_pneu: valor,
                    numero_fogo: label,
                    data_inclusao: new Date().toLocaleDateString(),
                    id_desenho_pneu: idDesenho,
                    desenho_pneu: construirDescricaoDesenho(dadosPneu, id_desenho_pneu_raw?.label ?? 'Desenho'),
                    id_tipo_reforma: idTipoReforma,
                    tipo_reforma: id_tipo_reforma_raw?.label ?? '-',
                    id_tipo_borracha: tipoBorracha,
                    tipo_borracha: tipo_borracha_raw?.label ?? '-',
                    valor_pneu: valorPneu,
                    descarte,
                    is_conferido,
                    is_feito,
                    laudo_descarte: laudo_descarte ? laudo_descarte.name : null,
                    ...(dadosPneu && {
                        marca_pneu: dadosPneu.marca || null,
                        modelo_pneu: dadosPneu.modelo || null,
                        medida_pneu: dadosPneu.medida || null,
                        status_atual: dadosPneu.status || null,
                        vida_atual: dadosPneu.vida || null
                    })
                };
                pneuManutencao.push(novoPneu);
                console.log('✅ Pneu adicionado:', novoPneu);
            });
        }

        // Atualiza o campo hidden e a tabela
        document.getElementById('pneus').value = JSON.stringify(pneuManutencao);
        atualizarTabela();

        // Limpa formulário
        limparFormulario();
        console.log('--- Fim de adicionarpneu ---');
    }

    // Limpar formulário corrigido
    function limparFormulario() {
        clearSmartSelect('id_pneu');
        clearSmartSelect('tipo_borracha');
        clearSmartSelect('id_tipo_reforma');
        clearSmartSelect('id_desenho_pneu');

        const valorInput = document.querySelector('[name="valor_pneu"]');
        if (valorInput) valorInput.value = '';
    }



    async function buscarDadosPneu(id_desenho_pneu) {
        // Obter CSRF token de forma mais segura
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            document.querySelector('input[name="_token"]')?.value;

        if (!csrfToken) {
            throw new Error('CSRF token não encontrado. Recarregue a página.');
        }

        // Headers configurados corretamente
        const headers = {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        const response = await fetch(`/admin/manutencaopneusentrada/api/desenho/${encodeURIComponent(id_desenho_pneu)}`, {
            method: 'GET',
            headers: headers,
            credentials: 'same-origin'
        });

        // Verificar se a resposta é ok
        if (!response.ok) {
            let errorMessage = 'Erro desconhecido';

            switch (response.status) {
                case 404:
                    errorMessage = 'Pneu não encontrado';
                    break;
                case 403:
                    errorMessage = 'Sem permissão para acessar este recurso';
                    break;
                case 422:
                    errorMessage = 'Dados inválidos fornecidos';
                    break;
                case 500:
                    errorMessage = 'Erro interno do servidor';
                    break;
                default:
                    errorMessage = `Erro na API: ${response.status} - ${response.statusText}`;
            }

            throw new Error(errorMessage);
        }

        const data = await response.json();

        // Verificar se retornou dados válidos
        if (!data || Object.keys(data).length === 0) {
            throw new Error('Nenhum dado encontrado para este desenho de pneu');
        }

        return data;
    }

    function construirDescricaoDesenho(dadosPneu, labelPadrao) {
        try {
            // Se não tiver dados da API, usar label padrão
            if (!dadosPneu) {
                console.warn('⚠️ Dados do pneu não disponíveis, usando label padrão:', labelPadrao);
                return formatarLabelPadrao(labelPadrao);
            }

            const desenho = dadosPneu;
            let descricao = '';

            // 1. MARCA MODELO (obrigatório no padrão)
            if (desenho.descricao_desenho_pneu) {
                descricao = desenho.descricao_desenho_pneu.trim();
            } else {
                // Fallback: tentar extrair do label padrão ou usar genérico
                descricao = labelPadrao || 'DESENHO NÃO ESPECIFICADO';
            }

            // 2. N° Sulcos (sempre incluir, mesmo que seja 0 ou vazio)
            const numeroSulcos = desenho.numero_sulcos !== undefined && desenho.numero_sulcos !== null
                ? desenho.numero_sulcos
                : '0';
            descricao += ` / N° Sulcos: ${numeroSulcos}`;

            // 3. Qtd Lonas (sempre incluir, mesmo que seja 0 ou vazio)
            const qtdLonas = desenho.quantidade_lona_pneu !== undefined && desenho.quantidade_lona_pneu !== null
                ? desenho.quantidade_lona_pneu
                : '0';
            descricao += ` / Qtd Lonas: ${qtdLonas}`;

            return descricao;

        } catch (error) {
            console.error('❌ Erro ao construir descrição do desenho:', error);
            // Em caso de erro, retornar o padrão com dados básicos
            return formatarLabelPadrao(labelPadrao);
        }
    }

    function formatarLabelPadrao(labelPadrao) {
        if (!labelPadrao) {
            return 'DESENHO NÃO ESPECIFICADO / N° Sulcos: 0 / Qtd Lonas: 0';
        }

        // Se o label já está no formato correto, retornar como está
        if (labelPadrao.includes('N° Sulcos:') && labelPadrao.includes('Qtd Lonas:')) {
            return labelPadrao;
        }

        // Se não está no formato, adicionar as partes faltantes
        let descricaoFormatada = labelPadrao.trim();

        // Adicionar N° Sulcos se não existir
        if (!descricaoFormatada.includes('N° Sulcos:')) {
            descricaoFormatada += ' / N° Sulcos: 0';
        }

        // Adicionar Qtd Lonas se não existir
        if (!descricaoFormatada.includes('Qtd Lonas:')) {
            descricaoFormatada += ' / Qtd Lonas: 0';
        }

        return descricaoFormatada;
    }

    function carregarDadosIniciais() {
        try {
            const campoHidden = document.getElementById('pneus');

            if (!campoHidden) {
                console.warn('⚠️ Campo hidden #pneus não encontrado');
                return;
            }

            const valorCampo = campoHidden.value;

            if (!valorCampo || valorCampo.trim() === '' || valorCampo === '[]') {
                console.log('ℹ️ Nenhum dado inicial encontrado no campo hidden');
                return;
            }

            // Parse dos dados JSON
            const dadosCarregados = JSON.parse(valorCampo);

            if (!Array.isArray(dadosCarregados) || dadosCarregados.length === 0) {
                console.log('ℹ️ Array de dados vazio ou inválido');
                return;
            }

            // Mapear dados do banco para o formato esperado pelo JavaScript
            const dadosMapeados = dadosCarregados.map(item => mapearDadosDoBanco(item));

            // Usar o manager para substituir os dados
            window.pneuManutencaoManager.substituirDados(dadosMapeados);

        } catch (error) {
            console.error('❌ Erro ao carregar dados iniciais:', error);
            console.error('Valor do campo:', document.getElementById('pneus')?.value);

            // Em caso de erro, garantir que a tabela seja atualizada mesmo vazia
            atualizarTabela();
        }
    }

    function mapearDadosDoBanco(item) {
        // Mapear campos do banco para o formato esperado pelo JavaScript
        return {
            // Dados básicos
            data_inclusao: item.data_inclusao ? formatarData(item.data_inclusao) : new Date().toLocaleDateString(),
            data_ateracao: item.data_alteracao ? formatarData(item.data_alteracao) : null,

            // Dados do pneu (relacionamento)
            numero_fogo: extrairNumeroPneu(item),

            // Dados do desenho pneu (relacionamento)
            id_desenho_pneu: item.id_desenho_pneu || '',
            desenho_pneu: construirDescricaoDesenhoDoBanco(item.desenho_pneu),

            // Dados do tipo reforma (relacionamento)
            id_tipo_reforma: item.id_tipo_reforma || '',
            tipo_reforma: item.tipo_reforma?.descricao_tipo_reforma || 'Tipo não especificado',

            // Dados do tipo borracha (relacionamento)
            id_tipo_borracha: item.tipo_borracha?.id_tipo_borracha || '',
            tipo_borracha: item.tipo_borracha?.descricao_tipo_borracha || 'Tipo não especificado',

            valor_pneu: item.valor_pneu?.valor_pneu || 0,

            // Flags booleanas (converter boolean para string)
            descarte: item.descarte === true || item.descarte === '1' ? '1' : '0',
            is_conferido: item.is_conferido === true || item.is_conferido === '1' ? '1' : '0',
            is_feito: item.is_feito === true || item.is_feito === '1' ? '1' : '0',

            // Campos opcionais
            laudo_descarte: item.laudo_descarte || null,
            situacao_pneu_interno: item.situacao_pneu_interno || null,

            // Dados adicionais do pneu (se disponíveis)
            status_atual: item.pneu?.status_pneu || null,
            id_pneu_original: item.pneu?.id_pneu || item.id_pneu,

            // Dados adicionais do desenho (para futuras funcionalidades)
            numero_sulcos: item.desenho_pneu?.numero_sulcos || null,
            quantidade_lona_pneu: item.desenho_pneu?.quantidade_lona_pneu || null,
            dias_calibragem: item.desenho_pneu?.dias_calibragem || null,

            // ID para referência (útil para edição)
            id_original: item.id_manutencao_pneu_entrada_itens || null
        };
    }

    function extrairNumeroPneu(item) {
        // Priorizar dados do relacionamento pneu
        if (item.pneu?.id_pneu) {
            return item.pneu.id_pneu.toString();
        }

        // Fallback para id_pneu direto
        if (item.id_pneu) {
            return item.id_pneu.toString();
        }

        return '';
    }

    function construirDescricaoDesenhoDoBanco(desenho) {
        try {
            if (!desenho || typeof desenho !== 'object') {
                console.warn('⚠️ Dados do desenho não disponíveis:', desenho);
                return 'Desenho não especificado / N° Sulcos: 0 / Qtd Lonas: 0';
            }

            let descricao = '';

            // 1. Descrição do desenho (obrigatório)
            if (desenho.descricao_desenho_pneu) {
                descricao = desenho.descricao_desenho_pneu.trim();
            } else {
                descricao = 'DESENHO NÃO ESPECIFICADO';
            }

            // 2. N° Sulcos (sempre incluir)
            const numeroSulcos = desenho.numero_sulcos !== undefined && desenho.numero_sulcos !== null
                ? desenho.numero_sulcos
                : '0';
            descricao += ` / N° Sulcos: ${numeroSulcos}`;

            // 3. Qtd Lonas (sempre incluir)
            const qtdLonas = desenho.quantidade_lona_pneu !== undefined && desenho.quantidade_lona_pneu !== null
                ? desenho.quantidade_lona_pneu
                : '0';
            descricao += ` / Qtd Lonas: ${qtdLonas}`;

            return descricao;

        } catch (error) {
            console.error('❌ Erro ao construir descrição do desenho do banco:', error);
            return 'Desenho não especificado / N° Sulcos: 0 / Qtd Lonas: 0';
        }
    }

    function formatarData(dataString) {
        try {
            // Se já está no formato brasileiro, retornar como está
            if (dataString && dataString.includes('/')) {
                return dataString;
            }

            // Se está no formato ISO (YYYY-MM-DD), converter para brasileiro
            if (dataString && dataString.includes('-')) {
                const data = new Date(dataString);
                return data.toLocaleDateString('pt-BR');
            }

            // Fallback
            return dataString || new Date().toLocaleDateString('pt-BR');

        } catch (error) {
            console.warn('⚠️ Erro ao formatar data:', dataString, error);
            return dataString || new Date().toLocaleDateString('pt-BR');
        }
    }

    function visualizarArquivo(index, caminhoArquivo) {
        try {
            const item = pneuManutencao[index];

            if (!caminhoArquivo || caminhoArquivo.trim() === '') {
                alert('Nenhum arquivo disponível para este item.');
                return;
            }

            console.log('📁 Visualizando arquivo:', caminhoArquivo, 'do item:', item);

            // Construir URL completa do arquivo
            const urlArquivo = construirUrlArquivo(caminhoArquivo);

            // Determinar tipo do arquivo
            const extensao = obterExtensaoArquivo(caminhoArquivo);

            if (isPDF(extensao)) {
                visualizarPDF(urlArquivo, item);
            } else if (isImagem(extensao)) {
                visualizarImagem(urlArquivo, item);
            } else {
                // Para outros tipos, abrir diretamente
                window.open(urlArquivo, '_blank');
            }

        } catch (error) {
            console.error('❌ Erro ao visualizar arquivo:', error);
            alert('Erro ao abrir o arquivo. Verifique se o arquivo existe.');
        }
    }

    function construirUrlArquivo(caminhoArquivo) {
        // Remove barras iniciais se houver
        const caminhoLimpo = caminhoArquivo.replace(/^\/+/, '');

        // Construir URL baseada na estrutura do Laravel
        const baseUrl = window.location.origin;

        // Se o caminho já inclui 'storage/', usar diretamente
        if (caminhoLimpo.includes('storage/')) {
            return `${baseUrl}/${caminhoLimpo}`;
        }

        // Caso contrário, assumir que está em storage/
        return `${baseUrl}/storage/${caminhoLimpo}`;
    }

    function obterExtensaoArquivo(caminhoArquivo) {
        return caminhoArquivo.split('.').pop().toLowerCase();
    }

    function isPDF(extensao) {
        return extensao === 'pdf';
    }

    function isImagem(extensao) {
        const extensoesImagem = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
        return extensoesImagem.includes(extensao);
    }

    function visualizarPDF(urlArquivo, item) {
        // Criar modal para PDF
        const modal = criarModalVisualizacao();
        const conteudo = modal.querySelector('.modal-content');

        conteudo.innerHTML = `
        <div class="modal-header">
            <h3 class="text-lg font-semibold">Laudo de Descarte - Pneu ${item.numero_fogo}</h3>
            <button class="close-modal text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <iframe src="${urlArquivo}" width="100%" height="600px" frameborder="0">
                <p>Seu navegador não suporta visualização de PDF. 
                   <a href="${urlArquivo}" target="_blank">Clique aqui para abrir o arquivo</a>
                </p>
            </iframe>
        </div>
        <div class="modal-footer">
            <a href="${urlArquivo}" target="_blank" class="btn btn-primary">Abrir em nova aba</a>
            <button class="close-modal btn btn-secondary">Fechar</button>
        </div>
    `;

        adicionarEventosModal(modal);
    }

    function visualizarImagem(urlArquivo, item) {
        // Criar modal para imagem
        const modal = criarModalVisualizacao();
        const conteudo = modal.querySelector('.modal-content');

        conteudo.innerHTML = `
        <div class="modal-header">
            <h3 class="text-lg font-semibold">Laudo de Descarte - Pneu ${item.numero_fogo}</h3>
            <button class="close-modal text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body text-center">
            <img src="${urlArquivo}" alt="Laudo de Descarte" class="max-w-full h-auto mx-auto" 
                 onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDlWMTNNMTIgMTdIMTIuMDFNMjEgMTJDMjEgMTYuOTcwNiAxNi45NzA2IDIxIDEyIDIxQzcuMDI5NDQgMjEgMyAxNi45NzA2IDMgMTJDMyA3LjAyOTQ0IDcuMDI5NDQgMyAxMiAzQzE2Ljk3MDYgMyAyMSA3LjAyOTQ0IDIxIDEyWiIgc3Ryb2tlPSIjRkY2Mzc1IiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8L3N2Zz4K'; this.nextElementSibling.style.display='block';">
            <p style="display:none;" class="text-red-500 mt-4">Erro ao carregar a imagem</p>
        </div>
        <div class="modal-footer">
            <a href="${urlArquivo}" target="_blank" class="btn btn-primary">Abrir em nova aba</a>
            <button class="close-modal btn btn-secondary">Fechar</button>
        </div>
    `;

        adicionarEventosModal(modal);
    }


    function criarModalVisualizacao() {
        // Remover modal existente se houver
        const modalExistente = document.getElementById('modal-visualizar-arquivo');
        if (modalExistente) {
            modalExistente.remove();
        }

        const modal = document.createElement('div');
        modal.id = 'modal-visualizar-arquivo';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-screen overflow-hidden">
            <!-- Conteúdo será inserido aqui -->
        </div>
    `;

        document.body.appendChild(modal);
        return modal;
    }

    function adicionarEventosModal(modal) {
        // Fechar modal ao clicar no X ou botão fechar
        modal.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => modal.remove());
        });

        // Fechar modal ao clicar fora do conteúdo
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });

        // Fechar modal com ESC
        document.addEventListener('keydown', function escapeHandler(e) {
            if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', escapeHandler);
            }
        });
    }

    function obterExtensaoArquivo(caminhoArquivo) {
        return caminhoArquivo.split('.').pop().toLowerCase();
    }

    // Adicione também esta função melhorada no pneuManutencaoManager
    window.pneuManutencaoManager.carregarDadosIniciais = function () {
        carregarDadosIniciais();
        return this;
    };

    carregarDadosIniciais();

    // Manter compatibilidade com código existente
    window.adicionarpneu = adicionarpneu;
    window.atualizarTabela = atualizarTabela;
    window.excluir = excluir;
    window.editarPneu = editarPneu;
    window.cancelarEdicao = cancelarEdicao;
    window.pneuManutencao = pneuManutencao; // Para compatibilidade

    // Expor função para debug/teste
    window.carregarDadosIniciais = carregarDadosIniciais;
}