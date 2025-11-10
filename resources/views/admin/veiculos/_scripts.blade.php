{{-- <script src="{{ asset('js/historico-transferencia.js') }}"></script> --}}

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const currencyFirstLoad = document.querySelectorAll('.monetario');
        currencyFirstLoad.forEach(input => {
            if (input.value.trim() !== '') {
                let valor = parseFloat(input.value); // Converte o valor diretamente para número

                // Formata o valor para BRL
                input.value = new Intl.NumberFormat('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(valor);
            }
        });
    });

    const currencyList = document.querySelectorAll('.monetario');
    const currencyInputs = Array.from(currencyList);

    currencyInputs.forEach(input => {
        input.addEventListener('input', () => {
            // Remove o formato de moeda para manipular o valor
            let valor = input.value.replace(/[^\d-]/g, ''); // Mantém apenas números e o sinal de menos

            // Verifica se o valor é negativo
            const isNegative = valor.startsWith('-');

            // Remove o sinal de menos para o cálculo, se presente
            valor = valor.replace('-', '');

            // Ajusta os centavos
            valor = (parseInt(valor || '0', 10) / 100).toFixed(2);

            // Adiciona o sinal de menos de volta, se for o caso
            if (isNegative) {
                valor = '-' + valor;
            }

            // Formata o valor para BRL
            input.value = new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(valor);
        });
    });

    currencyInputs.forEach(input => {
        input.addEventListener('change', () => {
            // Remove o formato de moeda para manipular o valor
            let valor = input.value.replace(/[^\d-]/g, ''); // Mantém apenas números e o sinal de menos

            // Verifica se o valor é negativo
            const isNegative = valor.startsWith('-');

            // Remove o sinal de menos para o cálculo, se presente
            valor = valor.replace('-', '');

            // Ajusta os centavos
            valor = (parseInt(valor || '0', 10) / 100).toFixed(2);

            // Adiciona o sinal de menos de volta, se for o caso
            if (isNegative) {
                valor = '-' + valor;
            }

            // Formata o valor para BRL
            input.value = new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(valor);
        });
    });

    const veiculoId = {{ $veiculo->id_veiculo ?? null }};

    function confirmDeleteVeiculo() {
        // showButtonSpinner('.botao-delete');

        fetch(`{{ route('admin.veiculos.baixar', ':id') }}`.replace(':id', veiculoId), {
            method: 'post',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }).then(response => {
            if (!response.ok) {
                return response.text().then(errorText => {
                    console.error('Error response text:', errorText);
                    throw new Error(`HTTP error! status: ${response.status}, text: ${errorText}`);
                });
            }
            return response.json();
        }).then(data => {
            // Pode exibir a notificação aqui, se quiser
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        }).catch(error => {
            console.error('Full error:', error);
        });
    }

    // Selecionar os elementos
    const openModalButton = document.getElementById('open-modal');
    const modal = document.getElementById('confirmation-modal');
    const cancelButton = document.getElementById('cancel-button');
    const confirmButton = document.getElementById('confirm-button');

    // Abrir o modal ao clicar no botão principal
    openModalButton.addEventListener('click', () => {
        modal.classList.remove('hidden');
    });

    // Fechar o modal ao clicar no botão de cancelar
    cancelButton.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    // Chamar funcao posteriormente
    confirmButton.addEventListener('click', () => {
        modal.classList.add('hidden');
        confirmDeleteVeiculo();
    });
</script>

<script>
    const controlesVeiculo = @json($controlesVeiculo ?? []);

    document.addEventListener('DOMContentLoaded', () => {
        popularHistoricoTabela();

        // Garantir que o campo hidden tenha um valor inicial
        atualizarCampoHidden();
    });

    function popularHistoricoTabela() {
        const tbody = document.getElementById('tabelaHistoricoBody');
        tbody.innerHTML = ''; // Limpa antes de adicionar

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Janeiro é 0
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        controlesVeiculo.forEach((item, index) => {
            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${formatDate(item.data_inclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.is_controle_manutencao === true ? 'Sim' : 'Não'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.is_controla_licenciamento === true ? 'Sim' : 'Não'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.is_controla_seguro_obrigatorio === true ? 'Sim' : 'Não'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.is_controla_ipva === true ? 'Sim' : 'Não'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.is_controla_pneu === true ? 'Sim' : 'Não'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.is_considera_para_rateio === true ? 'Sim' : 'Não'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <div class="cursor-pointer delete-produto" data-index="${index}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </div>
                    </div>
                </td>
            `;

            tr.querySelector(".delete-produto").addEventListener("click", (event) => {
                const index = parseInt(event.currentTarget.getAttribute("data-index"));
                removerHistorico(index);
            });

            tbody.appendChild(tr);
        });

        // atualizarCampoHidden();
        document.getElementById('historicos_json').value = JSON.stringify(controlesVeiculo);
        // console.log('atualizarCampoHidden', document.getElementById('historicos_json').value = JSON.stringify(controlesVeiculo););

    }

    function removerHistorico(index) {
        controlesVeiculo.splice(index, 1);
        popularHistoricoTabela();
    }

    // function atualizarCampoHidden() {
    //     document.getElementById('historicos_json').value = JSON.stringify(controlesVeiculo);
    // }

    function adicionarHistorico() {
        // Obter todos os elementos radio selecionados
        const getRadioValue = (name) => {
            const radio = document.querySelector(`input[name="${name}"]:checked`);
            return radio ? radio.value === 'true' : false;
        };

        const is_considera_para_rateio = getRadioValue('is_considera_para_rateio');
        const is_controle_manutencao = getRadioValue('is_controle_manutencao');
        const is_controla_licenciamento = getRadioValue('is_controla_licenciamento');
        const is_controla_seguro_obrigatorio = getRadioValue('is_controla_seguro_obrigatorio');
        const is_controla_ipva = getRadioValue('is_controla_ipva');
        const is_controla_pneu = getRadioValue('is_controla_pneu');

        const novoItem = {
            is_considera_para_rateio: is_considera_para_rateio,
            is_controle_manutencao: is_controle_manutencao,
            is_controla_licenciamento: is_controla_licenciamento,
            is_controla_seguro_obrigatorio: is_controla_seguro_obrigatorio,
            is_controla_ipva: is_controla_ipva,
            is_controla_pneu: is_controla_pneu,
            data_inclusao: new Date().toISOString()
        };

        controlesVeiculo.push(novoItem);
        popularHistoricoTabela();

        document.getElementById('historicos_json').value = JSON.stringify(controlesVeiculo);

        // atualizarCampoHidden();
    }
</script>

<script>
    const config = {
        eixoHeight: 180,
        pneuWidth: 40,
        pneuHeight: 80,
        spacing: 30,
        startY: 80,
        estepeStartY: 40, // Posição Y inicial para estepes
        estepeSpacing: 60, // Espaçamento entre estepes
        estepesPerRow: 3 // Limita para 3 estepes por linha
    };

    let selectedPneu = null;
    let dadosArray = null;
    let trocaEmAndamento = false;

    const formattedData = @json($formattedData ?? '');
    const kmAtual = @json($kmAtual ?? '');

    console.log('🚛 Dados formatados:', formattedData);
    console.log('📊 Pneus aplicados:', formattedData?.pneusAplicadosFormatados);

    renderizarCaminhao(formattedData, kmAtual);

    function renderizarCaminhao(formattedData, km_atual = null) {
        const svg = document.getElementById('caminhao');
        const mensagemSemPneus = document.getElementById('mensagemSemPneus');
        const mostarDiv = document.getElementById('mostarDiv');
        const headerPneus = document.getElementById('headerPneus');

        // Verificar se há dados válidos
        if (!formattedData || !formattedData.eixos) {
            if (svg) {
                svg.innerHTML =
                    '<text x="250" y="300" text-anchor="middle" fill="#999" font-size="14">Configuração de tipo de equipamento não encontrada</text>';
            }
            if (mensagemSemPneus) mensagemSemPneus.classList.add('hidden');
            if (mostarDiv) mostarDiv.classList.remove('hidden');
            if (headerPneus) headerPneus.classList.add('hidden');
            console.warn('⚠️ Tipo de equipamento não configurado');
            return;
        }

        // Verificar se há pneus aplicados
        if (!formattedData.pneusAplicadosFormatados || formattedData.pneusAplicadosFormatados.length === 0) {
            if (mensagemSemPneus) {
                mensagemSemPneus.classList.remove('hidden');
            }
            if (mostarDiv) {
                mostarDiv.classList.add('hidden');
            }
            if (headerPneus) {
                headerPneus.classList.add('hidden');
            }
            console.log('⚠️ Nenhum pneu aplicado encontrado para este veículo');
            return;
        }

        // Há pneus, mostrar o diagrama e esconder a mensagem
        if (mensagemSemPneus) {
            mensagemSemPneus.classList.add('hidden');
        }
        if (mostarDiv) {
            mostarDiv.classList.remove('hidden');
        }
        if (headerPneus) {
            headerPneus.classList.remove('hidden');
        }

        svg.innerHTML = '';
        let yPositions = [];
        let centerX = 0;

        // Para rastrear quaisquer erros de localização
        let localizacoesInvalidas = [];

        // Cria um elemento de tooltip
        const tooltip = document.createElement('div');
        tooltip.style.position = 'absolute';
        tooltip.style.backgroundColor = 'white';
        tooltip.style.border = '1px solid black';
        tooltip.style.padding = '5px';
        tooltip.style.display = 'none'; // Inicialmente oculto
        document.body.appendChild(tooltip);

        // Dicionário melhorado de localização para posição
        const localizacaoParaPosicao = {
            // Primeiro eixo
            '1D': {
                eixo: 0,
                lado: 'direita',
                posicao: 0
            },
            '1E': {
                eixo: 0,
                lado: 'esquerda',
                posicao: 0
            },
            '1EE': {
                eixo: 0,
                lado: 'direita',
                posicao: 0
            },
            '1EI': {
                eixo: 0,
                lado: 'direita',
                posicao: 1
            },
            '1DE': {
                eixo: 0,
                lado: 'esquerda',
                posicao: 0
            },
            '1DI': {
                eixo: 0,
                lado: 'esquerda',
                posicao: 1
            },

            // Segundo eixo
            '2DE': {
                eixo: 1,
                lado: 'esquerda',
                posicao: 0
            },
            '2DI': {
                eixo: 1,
                lado: 'esquerda',
                posicao: 1
            },
            '2EE': {
                eixo: 1,
                lado: 'direita',
                posicao: 0
            },
            '2EI': {
                eixo: 1,
                lado: 'direita',
                posicao: 1
            },

            // Terceiro eixo
            '3DE': {
                eixo: 2,
                lado: 'esquerda',
                posicao: 0
            },
            '3DI': {
                eixo: 2,
                lado: 'esquerda',
                posicao: 1
            },
            '3EE': {
                eixo: 2,
                lado: 'direita',
                posicao: 0
            },
            '3EI': {
                eixo: 2,
                lado: 'direita',
                posicao: 1
            },

            // Quarto eixo
            '4DE': {
                eixo: 3,
                lado: 'esquerda',
                posicao: 0
            },
            '4DI': {
                eixo: 3,
                lado: 'esquerda',
                posicao: 1
            },
            '4EE': {
                eixo: 3,
                lado: 'direita',
                posicao: 0
            },
            '4EI': {
                eixo: 3,
                lado: 'direita',
                posicao: 1
            },
        };

        // Função auxiliar para identificar estepes por padrão
        function isEstepe(localizacao) {
            // Verifica se a localização começa com 'E' e o restante é um número
            return /^E\d+$/.test(localizacao);
        }

        // Função para obter o número do estepe (retorna o número após o 'E')
        function getEstepeNumero(localizacao) {
            return parseInt(localizacao.substring(1));
        }

        // Desenhar eixos e posições dos pneus
        for (let i = 0; i < formattedData.eixos; i++) {
            const y = config.startY + i * (config.eixoHeight + config.spacing);
            yPositions.push(y);
            const numPneus = formattedData.pneus_por_eixo[i] || 0;
            const totalWidth = numPneus * config.pneuWidth + (numPneus - 1) * config.spacing;
            const startX = (600 - totalWidth) / 2;

            for (let j = 0; j < numPneus; j++) {
                const x = startX + j * (config.pneuWidth + config.spacing);
                const yRect = y - config.pneuHeight / 2;
                let pneu = criarPneu(x, yRect, '#ccc', i, j);
                svg.appendChild(pneu);
            }

            if (i === 0) {
                centerX = (startX + totalWidth / 2);
            }

            let eixoLinha = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            eixoLinha.setAttribute('x1', startX + 5);
            eixoLinha.setAttribute('y1', y);
            eixoLinha.setAttribute('x2', (startX + totalWidth) - 5);
            eixoLinha.setAttribute('y2', y);
            eixoLinha.setAttribute('stroke', '#000');
            eixoLinha.setAttribute('stroke-width', 2);
            svg.appendChild(eixoLinha);
        }

        let eixoVertical = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        eixoVertical.setAttribute('x1', centerX);
        eixoVertical.setAttribute('y1', yPositions[0]);
        eixoVertical.setAttribute('x2', centerX);
        eixoVertical.setAttribute('y2', yPositions[yPositions.length - 1]);
        eixoVertical.setAttribute('stroke', '#000');
        eixoVertical.setAttribute('stroke-width', 2);
        svg.appendChild(eixoVertical);

        // Coletamos todos os estepes primeiro para posicioná-los corretamente
        const estepes = [];
        const pneusPorPosicao = {};

        formattedData.pneusAplicadosFormatados.forEach(pneu => {
            const localizacao = pneu.localizacao;

            // Verifica se é um estepe
            if (isEstepe(localizacao)) {
                estepes.push(pneu);
            } else {
                // Armazena informação do pneu por localização
                pneusPorPosicao[localizacao] = pneu;
            }
        });

        // Agora renderizamos os pneus regulares
        formattedData.pneusAplicadosFormatados.forEach(pneu => {
            const localizacao = pneu.localizacao;

            // Pula os estepes, serão tratados separadamente
            if (isEstepe(localizacao)) {
                return;
            }

            const posicao = localizacaoParaPosicao[localizacao];

            if (posicao) {
                const eixoIndex = posicao.eixo;
                const lado = posicao.lado;
                const pneuIndex = posicao.posicao;

                const yEixo = yPositions[eixoIndex];
                const numPneus = formattedData.pneus_por_eixo[eixoIndex];
                const totalWidth = numPneus * config.pneuWidth + (numPneus - 1) * config.spacing;
                const startX = (600 - totalWidth) / 2;

                let x;
                if (lado === 'direita') {
                    x = startX + pneuIndex * (config.pneuWidth + config.spacing);
                } else {
                    x = startX + (numPneus - 1 - pneuIndex) * (config.pneuWidth + config.spacing);
                }

                const y = yEixo - config.pneuHeight / 2;

                const sulco = parseFloat(pneu.suco_pneu);
                let corPneu = '#ccc';

                if (sulco > 24) {
                    corPneu = 'black';
                } else if (sulco >= 21 && sulco <= 24) {
                    corPneu = 'green';
                } else if (sulco >= 16 && sulco <= 20) {
                    corPneu = 'blue';
                } else if (sulco >= 10 && sulco <= 15) {
                    corPneu = 'yellow';
                } else if (sulco < 10) {
                    corPneu = 'red';
                }

                let pneuRect = criarPneu(x, y, corPneu, eixoIndex, pneuIndex, pneu.id_pneu, localizacao);
                adicionarEventosPneu(pneuRect, pneu, km_atual, tooltip);

                const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', x + config.pneuWidth / 2);
                text.setAttribute('y', y + config.pneuHeight + 15);
                text.setAttribute('font-size', '10');
                text.setAttribute('text-anchor', 'middle');
                text.textContent = `N: ${pneu.id_pneu} (${localizacao})`;
                text.setAttribute('data-id', pneu.id_pneu);

                svg.appendChild(text);
                svg.appendChild(pneuRect);
            } else {
                // Registra localização desconhecida
                localizacoesInvalidas.push(localizacao);
            }
        });

        // Renderiza estepes
        if (estepes.length > 0) {
            // Criar título para área de estepes - alinhado à esquerda
            const estepeTitle = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            estepeTitle.setAttribute('x', 120);
            estepeTitle.setAttribute('y', 20);
            estepeTitle.setAttribute('font-size', '14');
            estepeTitle.setAttribute('font-weight', 'bold');
            estepeTitle.setAttribute('text-anchor', 'middle');
            estepeTitle.textContent = 'Estepes';
            svg.appendChild(estepeTitle);

            // Calcula posições para múltiplos estepes - posicionados mais à esquerda
            const estepesPerRow = config.estepesPerRow; // Limita para 3 estepes por linha
            const estepeWidth = config.pneuWidth + 20; // Largura do estepe + espaço extra
            const estepeStartX = 50; // Posição fixa à esquerda

            estepes.forEach((pneu, index) => {
                const row = Math.floor(index / estepesPerRow);
                const col = index % estepesPerRow;

                const x = estepeStartX + col * estepeWidth;
                const y = config.estepeStartY + row * config.estepeSpacing;

                const sulco = parseFloat(pneu.suco_pneu);
                let corPneu = '#ccc';

                if (sulco > 24) {
                    corPneu = 'black';
                } else if (sulco >= 21 && sulco <= 24) {
                    corPneu = 'green';
                } else if (sulco >= 16 && sulco <= 20) {
                    corPneu = 'blue';
                } else if (sulco >= 10 && sulco <= 15) {
                    corPneu = 'yellow';
                } else if (sulco < 10) {
                    corPneu = 'red';
                }

                let pneuRect = criarPneu(x, y, corPneu, -1, getEstepeNumero(pneu.localizacao), pneu.id_pneu,
                    pneu.localizacao);
                adicionarEventosPneu(pneuRect, pneu, km_atual, tooltip);

                const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', x + config.pneuWidth / 2);
                text.setAttribute('y', y + config.pneuHeight + 15);
                text.setAttribute('font-size', '10');
                text.setAttribute('text-anchor', 'middle');
                text.textContent = `N: ${pneu.id_pneu} (${pneu.localizacao})`;
                text.setAttribute('data-id', pneu.id_pneu);

                svg.appendChild(text);
                svg.appendChild(pneuRect);
            });
        }

        // Registra localizações inválidas no console para debugging
        if (localizacoesInvalidas.length > 0) {
            console.warn('⚠️ Localizações de pneus não reconhecidas:', localizacoesInvalidas);
        }

        // Log de sucesso e atualizar contador
        const totalPneus = formattedData.pneusAplicadosFormatados.length;
        console.log(`✅ Renderização concluída: ${totalPneus} pneu(s) aplicado(s)`);

        // Atualizar o contador
        const contadorPneus = document.getElementById('contadorPneus');
        if (contadorPneus) {
            contadorPneus.textContent = totalPneus;
        }
    }

    function criarPneu(x, y, color, eixo, posicao, id = null, localizacao = null) {
        // Mapeamento das cores para os caminhos dos SVGs
        const svgPaths = {
            'black': '/vendor/bladewind/images/pneu_preto.svg',
            'green': '/vendor/bladewind/images/pneu_verde.svg',
            'blue': '/vendor/bladewind/images/pneu_azul.svg',
            'yellow': '/vendor/bladewind/images/pneu_amarelo.svg',
            'red': '/vendor/bladewind/images/pneu_vermelho.svg',
            '#ccc': '/vendor/bladewind/images/pneu_cinza.svg'
        };

        // Verifica se a cor existe no mapeamento
        const svgPath = svgPaths[color] || svgPaths['#ccc']; // Usa cinza como padrão se a cor não for encontrada

        // Cria o elemento <image>
        let pneu = document.createElementNS('http://www.w3.org/2000/svg', 'image');
        pneu.setAttribute('x', x);
        pneu.setAttribute('y', y);
        pneu.setAttribute('width', config.pneuWidth);
        pneu.setAttribute('height', config.pneuHeight);
        pneu.setAttribute('href', svgPath); // Define o caminho do SVG
        pneu.setAttribute('class', 'pneu');
        pneu.setAttribute('data-eixo', eixo);
        pneu.setAttribute('data-posicao', posicao);
        pneu.setAttribute('data-original-svg', svgPath);
        if (id) pneu.setAttribute('data-id', id);
        if (localizacao) pneu.setAttribute('data-localizacao', localizacao);

        return pneu;
    }

    function adicionarEventosPneu(pneuElement, pneuData, km_atual, tooltip) {
        pneuElement.addEventListener('mouseover', (event) => {
            if (!trocaEmAndamento) {
                let kmPneu = km_atual - pneuData.km_adicionado;
                tooltip.style.display = 'block';
                tooltip.style.left = `${event.clientX}px`;
                tooltip.style.top = `${event.clientY}px`;
                tooltip.innerHTML = `
                    <strong>Pneu ID:</strong> ${pneuData.id_pneu}<br>
                    <strong>Localização:</strong> ${pneuData.localizacao}<br>
                    <strong>Sulco do pneu:</strong> ${pneuData.suco_pneu}<br>
                    <strong>Km percorrido:</strong> ${kmPneu}<br>
                    <strong>Data aplicação:</strong> ${pneuData.data_inclusao}
                `;
            }
        });

        pneuElement.addEventListener('mouseout', () => {
            tooltip.style.display = 'none';
        });

        // Pode adicionar evento de clique se necessário
        pneuElement.addEventListener('click', () => {
            if (!trocaEmAndamento) {
                // Código para seleção de pneu se necessário
                console.log(`Pneu selecionado: ${pneuData.id_pneu} (${pneuData.localizacao})`);

                // Aqui você pode adicionar código para selecionar o pneu se desejado
                // Por exemplo, alterando a classe CSS ou adicionando um efeito visual
            }
        });
    }
</script>

<script>
    const historicosJsonInput = document.getElementById("historicos_transferencia_json");
    const historicosTransferencia = JSON.parse(historicosJsonInput.value || '[]');
    const filiaisDesc = @json($filial ?? []);

    // Marcar todos os registros existentes como não novos
    historicosTransferencia.forEach(item => {
        item.isNovoRegistro = false;
    });

    document.addEventListener('DOMContentLoaded', () => {
        popularTransferenciaTabela();
    });

    function popularTransferenciaTabela() {
        const tbody = document.getElementById('tabelaTransferenciaBody');
        tbody.innerHTML = ''; // Limpa antes de adicionar

        function formatDate(dateStr) {
            if (!dateStr) return '-';

            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Janeiro é 0
            const year = date.getFullYear();

            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');

            return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
        }

        historicosTransferencia.forEach((item, index) => {
            const tr = document.createElement('tr');

            // Conteúdo da linha
            let trHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${formatDate(item.data_inclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatDate(item.data_alteracao) ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatDate(item.data_transferencia) ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${filiaisDesc[item.id_filial_origem] ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${filiaisDesc[item.id_filial_destino] ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.km_transferencia ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <a href="https://lcarvalima.unitopconsultoria.com.br:8443/dashboards/checklist/checklist/${item.checklist}" class="text-blue-500" target="_blank">
                        ${item.checklist ?? '-'}
                    </a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">`;

            // Só adiciona o botão de excluir se for um novo registro
            if (item.isNovoRegistro === true) {
                trHTML += `
                        <div class="cursor-pointer delete-produto" data-index="${index}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-red-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </div>`;
            }

            trHTML += `
                    </div>
                </td>
            `;

            tr.innerHTML = trHTML;

            // Adicionar evento de exclusão apenas se houver o botão
            const deleteButton = tr.querySelector(".delete-produto");
            if (deleteButton) {
                deleteButton.addEventListener("click", (event) => {
                    const index = parseInt(event.currentTarget.getAttribute("data-index"));
                    removerImobilizado(index);
                });
            }

            tbody.appendChild(tr);
        });

        atualizarCampoHidden();
    }

    function removerImobilizado(index) {
        historicosTransferencia.splice(index, 1);
        popularTransferenciaTabela();
    }

    function atualizarCampoHidden() {
        document.getElementById('historicos_transferencia_json').value = JSON.stringify(historicosTransferencia);
    }

    function adicionarHistoricoTransferencia() {
        const id_filial_origem = document.querySelector('[name="id_filial_origem"]').value;
        const id_filial_destino = document.querySelector('[name="id_filial_destino"]').value;
        const km_transferencia = document.querySelector('[name="km_transferencia"]').value;
        const data_transferencia = document.querySelector('[name="data_transferencia"]').value;

        if (id_filial_destino === id_filial_origem) {
            alert('A filial de origem e a filial de destino não podem ser iguais.');
            return;
        }

        if (!id_filial_origem || !id_filial_destino || !km_transferencia || !data_transferencia) {
            alert('Preencha todos os campos para adicionar a transferência.');
            return;
        }

        const novoItem = {
            id_filial_origem: id_filial_origem,
            id_filial_destino: id_filial_destino,
            km_transferencia: km_transferencia,
            data_transferencia: data_transferencia,
            data_inclusao: new Date(),
            data_alteracao: '',
            isNovoRegistro: true // Marca como novo registro
        };

        historicosTransferencia.push(novoItem);
        popularTransferenciaTabela();
    }
</script>

<script>
    // Definindo funções no escopo global
    let registrosTemporariosKm = [];

    // Função para carregar os dados iniciais
    function carregarDadosIniciais() {
        const historicosJsonKm = document.getElementById("historicos_json_km").value;
        const historicosKm = JSON.parse(historicosJsonKm || '[]');

        if (historicosKm && historicosKm.length > 0) {
            historicosKm.forEach((historico) => {
                registrosTemporariosKm.push({
                    horimetro: historico.horimetro,
                    km_realizacao: historico.km_realizacao,
                    data_realizacao: historico.data_realizacao,
                    data_inclusaokm: historico.data_inclusao,
                    data_alteracaokm: historico.data_alteracao,
                });
            });
            atualizarTabelaKm();
        }
    }

    // Função para adicionar histórico
    function adicionarHistoricoComodatoKm() {
        const horimetroKm = document.querySelector('[name="horimetro"]').value;
        const kmTransferencia = document.querySelector(
            '[name="km_realizacao"]'
        ).value;
        const dataRealizacao = document.querySelector(
            '[name="data_realizacao"]'
        ).value;
        const data_inclusaokm = new Date().toISOString();
        const data_alteracaokm = '';

        if (
            horimetroKm == "" ||
            kmTransferencia == "" ||
            dataRealizacao == ""
        ) {
            alert("Preencha todos os campos!");
            return;
        }

        const registroKm = {
            horimetro: horimetroKm,
            km_realizacao: kmTransferencia,
            data_realizacao: dataRealizacao,
            data_inclusaokm: data_inclusaokm,
            data_alteracaokm: '',
        };

        registrosTemporariosKm.push(registroKm);
        atualizarTabelaKm();
        limparFormularioTempKm();

        alert("Registro adicionado com sucesso!");
        document.getElementById("historicos_json_km").value = JSON.stringify(
            registrosTemporariosKm
        );
    }

    // Função para atualizar tabela
    function atualizarTabelaKm() {
        const tbody = document.getElementById("tabelaHistoricoBodyKm");
        if (!tbody) {
            console.error("Elemento #tabelaHistoricoBodyKm não encontrado");
            return;
        }
        registrosTemporariosKm.sort(
            (a, b) => new Date(a.data_realizacao) - new Date(b.data_realizacao)
        );
        tbody.innerHTML = "";
        registrosTemporariosKm.forEach((registro_Km, index) => {
            const tr = document.createElement("tr");

            // Verifica se o registro já existe no sistema (data_inclusaokm está presente)
            const registroExistente = registro_Km.data_inclusaokm ? true : false;

            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${
                    formatarData(registro_Km.data_inclusaokm) || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">${
                    formatarData(registro_Km.data_alteracaokm) || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">${
                    formatarData(registro_Km.data_realizacao, true) || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">${
                    registro_Km.km_realizacao || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">${
                    registro_Km.horimetro || "-"
                }</td>
                <td class="px-6 py-4 whitespace-nowrap">
                </td>
            `;
            tbody.appendChild(tr);
        });
    }
    // <div class="flex gap-2">
    //     <button type="button" onclick="window.editarRegistroKm(${index})" class="text-blue-600 hover:text-blue-800" ${registroExistente ? 'disabled title="Não é possível editar registros já existentes"' : ''}>
    //         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
    //             <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
    //         </svg>
    //     </button>
    //     <button type="button" onclick="window.excluirRegistroKm(${index})" class="text-red-600 hover:text-red-800" ${registroExistente ? 'disabled title="Não é possível excluir registros já existentes"' : ''}>
    //         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
    //             <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
    //         </svg>
    //     </button>
    // </div>

    // Função para limpar formulário
    function limparFormularioTempKm() {
        document.querySelector('[name="horimetro"]').value = "";
        document.querySelector('[name="km_realizacao"]').value = "";
        document.querySelector('[name="data_realizacao"]').value = "";
    }

    // Função para excluir registro
    function excluirRegistroKm(index) {
        const registro = registrosTemporariosKm[index];

        // Verifica se o registro já existe no sistema
        if (registro.data_inclusaokm) {
            alert("Não é possível excluir registros já existentes no sistema.");
            return;
        }

        registrosTemporariosKm.splice(index, 1);
        atualizarTabelaKm();
        document.getElementById("historicos_json_km").value = JSON.stringify(
            registrosTemporariosKm
        );
    }

    // Função para editar registro
    function editarRegistroKm(index) {
        const registro = registrosTemporariosKm[index];

        // Verifica se o registro já existe no sistema
        if (registro.data_inclusaokm) {
            alert("Não é possível editar registros já existentes no sistema.");
            return;
        }

        document.querySelector('[name="horimetro"]').value = registro.horimetro;
        document.querySelector('[name="km_realizacao"]').value = registro.km_realizacao;
        document.querySelector('[name="data_realizacao"]').value = registro.data_realizacao;
        excluirRegistroKm(index);
    }

    // Função para formatar data
    function formatarData(data, apenasData = false) {
        if (!data) return "-";

        try {
            const dataObj = new Date(data);

            // Verificar se a data é válida
            if (isNaN(dataObj.getTime())) {
                return "-";
            }

            if (apenasData) {
                return dataObj.toLocaleDateString('pt-BR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            } else {
                return dataObj.toLocaleDateString('pt-BR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        } catch (error) {
            console.error("Erro ao formatar data:", error);
            return "-";
        }
    }

    // Exporta as funções para o escopo global de forma correta
    window.adicionarHistoricoComodatoKm = adicionarHistoricoComodatoKm;
    window.atualizarTabelaKm = atualizarTabelaKm;
    window.limparFormularioTempKm = limparFormularioTempKm;
    window.excluirRegistroKm = excluirRegistroKm;
    window.editarRegistroKm = editarRegistroKm;
    window.formatarData = formatarData;

    // Chamada inicial para carregar os dados
    document.addEventListener('DOMContentLoaded', carregarDadosIniciais);
</script>


<script>
    const historicosNaoTracionadoInput = document.getElementById("historicos_nao_tracionado_json");
    let historicosNaoTracionado = [];

    // Tenta carregar dados existentes
    try {
        const dadosIniciais = historicosNaoTracionadoInput.value;
        historicosNaoTracionado = dadosIniciais && dadosIniciais !== '[]' ?
            JSON.parse(dadosIniciais) : [];
        // Se historicosNaoTracionado não for um array, inicializa como array vazio
        if (!Array.isArray(historicosNaoTracionado)) {
            historicosNaoTracionado = [historicosNaoTracionado]; // Converte objeto único em array
        }
    } catch (error) {
        console.error("Erro ao carregar dados iniciais:", error);
        historicosNaoTracionado = [];
    }

    // Função para adicionar um novo histórico
    function adicionarHistoricoNaoTracionado() {
        // Captura os dados do formulário
        const novoHistorico = {
            id: Date.now(), // ID único baseado no timestamp
            data_inclusao: new Date().toISOString(),
            data_alteracao: null,
            modelo_carroceria: document.querySelector('[name="modelo_carroceria"]').value,
            marca_carroceria: document.querySelector('[name="marca_carroceria"]').value,
            tara_nao_tracionado: document.querySelector('[name="tara_nao_tracionado"]').value,
            lotacao_nao_tracionado: document.querySelector('[name="lotacao_nao_tracionado"]').value,
            ano_carroceria: document.querySelector('[name="ano_carroceria"]').value,
            refrigeracao_carroceria: document.querySelector('[name="refrigeracao_carroceria"]').value,
            comprimento_carroceria: document.querySelector('[name="comprimento_carroceria"]').value,
            largura_carroceria: document.querySelector('[name="largura_carroceria"]').value,
            altura_carroceria: document.querySelector('[name="altura_carroceria"]').value,
            capacidade_volumetrica_1: document.querySelector('[name="capacidade_volumetrica_1"]').value,
            capacidade_volumetrica_2: document.querySelector('[name="capacidade_volumetrica_2"]').value,
            capacidade_volumetrica_3: document.querySelector('[name="capacidade_volumetrica_3"]').value,
            capacidade_volumetrica_4: document.querySelector('[name="capacidade_volumetrica_4"]').value,
            capacidade_volumetrica_5: document.querySelector('[name="capacidade_volumetrica_5"]').value,
            capacidade_volumetrica_6: document.querySelector('[name="capacidade_volumetrica_6"]').value,
            capacidade_volumetrica_7: document.querySelector('[name="capacidade_volumetrica_7"]').value
        };

        // Calcula a capacidade volumétrica total
        novoHistorico.capacidade_volumetrica = calcularCapacidadeTotal(novoHistorico);

        // Adiciona ao array
        historicosNaoTracionado.push(novoHistorico);

        // Atualiza o input hidden com os dados atualizados
        historicosNaoTracionadoInput.value = JSON.stringify(historicosNaoTracionado);

        // Atualiza a tabela
        popularTabelaNaoTracionado();

        // Limpa o formulário
        limparFormulario();

        // Feedback visual
        alert('Item adicionado com sucesso!');
    }

    // Função para calcular a capacidade volumétrica total
    function calcularCapacidadeTotal(item) {
        let total = 0;
        for (let i = 1; i <= 7; i++) {
            const valor = parseFloat(item[`capacidade_volumetrica_${i}`]) || 0;
            total += valor;
        }
        return total;
    }

    // Função para popular a tabela
    function popularTabelaNaoTracionado() {
        const tbody = document.getElementById('tabelaNaoTracionadoBody');
        if (!tbody) {
            console.error('Elemento tabelaNaoTracionadoBody não encontrado');
            return;
        }

        tbody.innerHTML = ''; // Limpa antes de adicionar

        historicosNaoTracionado.forEach((item, index) => {
            const tr = document.createElement('tr');

            // Formatação de datas
            const dataInclusao = formatarData(item.data_inclusao);
            const dataAlteracao = item.data_alteracao ? formatarData(item.data_alteracao) : '-';

            tr.innerHTML = `
                <td class="px-4 py-2">
                    <div class="text-sm">
                        <div><strong>Inclusão:</strong> ${dataInclusao}</div>
                        <div><strong>Alteração:</strong> ${dataAlteracao}</div>
                    </div>
                </td>
                <td class="px-4 py-2">
                    <div class="text-sm">
                        <div><strong>Modelo:</strong> ${item.modelo_carroceria || '-'}</div>
                        <div><strong>Marca:</strong> ${item.marca_carroceria || '-'}</div>
                        <div><strong>Ano:</strong> ${item.ano_carroceria || '-'}</div>
                        <div><strong>Refrigeração:</strong> ${item.refrigeracao_carroceria || '-'}</div>
                    </div>
                </td>
                <td class="px-4 py-2">
                    <div class="text-sm">
                        <div><strong>Tara:</strong> ${item.tara_nao_tracionado || '-'} Kg</div>
                        <div><strong>Lotação:</strong> ${item.lotacao_nao_tracionado || '-'} Kg</div>
                    </div>
                </td>
                <td class="px-4 py-2">
                    <div class="text-sm">
                        <div><strong>C × L × A:</strong> ${item.comprimento_carroceria || '-'} × ${item.largura_carroceria || '-'} × ${item.altura_carroceria || '-'}</div>
                    </div>
                </td>
                <td class="px-4 py-2">
                    <div class="text-sm">
                        <a class="text-blue-500 hover:text-blue-700 text-xs underline" 
                                onclick="toggleDetalhesVolume(${index})">
                            Ver detalhes
                        </a>
                        <div id="detalhes-volume-${index}" class="hidden mt-1 text-xs">
                            ${gerarDetalhesVolume(item)}
                        </div>
                    </div>
                </td>
                <td class="px-4 py-2">
                    <div class="flex space-x-2">
                        <a onclick="editarItem(${index})" class="text-blue-500 hover:text-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </a>
                        <a onclick="excluirItem(${index})" class="text-red-500 hover:text-red-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </a>
                    </div>
                </td>
            `;

            tbody.appendChild(tr);
        });
    }

    // Função para formatar data
    function formatarData(dataStr) {
        if (!dataStr) return '-';

        try {
            const data = new Date(dataStr);
            return data.toLocaleDateString('pt-BR');
        } catch (error) {
            console.error("Erro ao formatar data:", error);
            return '-';
        }
    }

    // Função para gerar detalhes de volume
    function gerarDetalhesVolume(item) {
        let detalhes = '';
        for (let i = 1; i <= 7; i++) {
            const valor = item[`capacidade_volumetrica_${i}`];
            if (valor) {
                detalhes += `<div>Vol. ${i}: ${valor}</div>`;
            }
        }
        return detalhes || '<div>Sem detalhes adicionais</div>';
    }

    // Função para alternar visibilidade dos detalhes de volume
    function toggleDetalhesVolume(index) {
        const detalhes = document.getElementById(`detalhes-volume-${index}`);
        if (detalhes) {
            detalhes.classList.toggle('hidden');
        }
    }

    // Função para editar item
    function editarItem(index) {
        const item = historicosNaoTracionado[index];

        // Preenche o formulário com os dados do item
        document.querySelector('[name="modelo_carroceria"]').value = item.modelo_carroceria || '';
        document.querySelector('[name="marca_carroceria"]').value = item.marca_carroceria || '';
        document.querySelector('[name="tara_nao_tracionado"]').value = item.tara_nao_tracionado || '';
        document.querySelector('[name="lotacao_nao_tracionado"]').value = item.lotacao_nao_tracionado || '';
        document.querySelector('[name="ano_carroceria"]').value = item.ano_carroceria || '';
        document.querySelector('[name="refrigeracao_carroceria"]').value = item.refrigeracao_carroceria || '';
        document.querySelector('[name="comprimento_carroceria"]').value = item.comprimento_carroceria || '';
        document.querySelector('[name="largura_carroceria"]').value = item.largura_carroceria || '';
        document.querySelector('[name="altura_carroceria"]').value = item.altura_carroceria || '';

        for (let i = 1; i <= 7; i++) {
            document.querySelector(`[name="capacidade_volumetrica_${i}"]`).value = item[
                `capacidade_volumetrica_${i}`] || '';
        }

        // Remove o item do array para que possa ser adicionado novamente após edição
        historicosNaoTracionado.splice(index, 1);

        // Atualiza o input hidden
        historicosNaoTracionadoInput.value = JSON.stringify(historicosNaoTracionado);

        // Atualiza a tabela
        popularTabelaNaoTracionado();

        // Feedback visual
        alert('Item carregado para edição. Faça as alterações necessárias e clique em Adicionar para salvar.');
    }

    // Função para excluir item
    function excluirItem(index) {
        if (confirm('Tem certeza que deseja excluir este item?')) {
            // Remove o item do array
            historicosNaoTracionado.splice(index, 1);

            // Atualiza o input hidden
            historicosNaoTracionadoInput.value = JSON.stringify(historicosNaoTracionado);

            // Atualiza a tabela
            popularTabelaNaoTracionado();

            // Feedback visual
            alert('Item excluído com sucesso!');
        }
    }

    // Função para limpar o formulário
    function limparFormulario() {
        // Limpa todos os campos do formulário
        document.querySelector('[name="modelo_carroceria"]').value = '';
        document.querySelector('[name="marca_carroceria"]').value = '';
        document.querySelector('[name="tara_nao_tracionado"]').value = '';
        document.querySelector('[name="lotacao_nao_tracionado"]').value = '';
        document.querySelector('[name="ano_carroceria"]').value = '';
        document.querySelector('[name="refrigeracao_carroceria"]').value = '';
        document.querySelector('[name="comprimento_carroceria"]').value = '';
        document.querySelector('[name="largura_carroceria"]').value = '';
        document.querySelector('[name="altura_carroceria"]').value = '';

        for (let i = 1; i <= 7; i++) {
            document.querySelector(`[name="capacidade_volumetrica_${i}"]`).value = '';
        }
    }

    // Inicialize a tabela quando o documento estiver pronto
    document.addEventListener('DOMContentLoaded', () => {
        popularTabelaNaoTracionado();
    });
</script>

<script>
    let idSelecionado = null;

    function showVeiculo(id) {
        window.location.href = `{{ route('admin.veiculos.show', ':id') }}`.replace(':id', id);
    }

    function editVeiculo(id) {
        window.location.href = `{{ route('admin.veiculos.edit', ':id') }}`.replace(':id', id);
    }

    function destroyVeiculo(id) {
        showModal('delete-autorizacao');
        idSelecionado = id;
        domEl('.bw-delete-autorizacao .title').innerText = id;
    }

    function executeSearch() {
        const searchTerm = document.getElementById('search-input').value;
        const currentUrl = new URL(window.location.href);

        if (searchTerm) {
            currentUrl.searchParams.set('search', searchTerm);
        } else {
            currentUrl.searchParams.delete('search');
        }

        window.location.href = currentUrl.toString();
    }

    function clearSearch() {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.delete('search');
        window.location.href = currentUrl.toString();
    }

    document.getElementById('search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            executeSearch();
        }
    });
</script>

<script>
    // exclusao da Manutenção
    function destroyVeiculo(id) {
        showModal('delete-autorizacao');
        autorizacaooId = id;
        domEl('.bw-delete-autorizacao .title').innerText = id;
    }

    function confirmarExclusao(id) {
        excluirVeiculo(id);
    }

    function excluirVeiculo(id) {
        fetch(`{{ route('admin.veiculos.destroy', ':id') }}`.replace(':id', autorizacaooId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }).then(response => {
            if (!response.ok) {
                return response.text().then(errorText => {
                    console.error('Error response text:', errorText);
                    throw new Error(`HTTP error! status: ${response.status}, text: ${errorText}`);
                });
            }
            return response.json();
        }).then(data => {
            if (data.notification) {
                showNotification(
                    data.notification.title,
                    data.notification.message,
                    data.notification.type
                );

                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        }).catch(error => {
            console.error('Full error:', error);

            showNotification(
                'Erro',
                error.message,
                'error'
            );
        });
    }

    @if (session('notification') && is_array(session('notification')))
        showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}',
            '{{ session('notification')['type'] }}');
    @endif
</script>

<script>
    function formatarMoedaBrasileira(input) {
        // Remove tudo que não é dígito
        let valor = input.value.replace(/\D/g, '');

        // Se estiver vazio, retorna vazio
        if (valor === '') {
            input.value = '';
            return;
        }

        // Converte para número e divide por 100 para obter os centavos
        const valorNumerico = parseInt(valor, 10) / 100;

        // Formata para o padrão brasileiro
        input.value = valorNumerico.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2
        });

        // Mantém o cursor na posição correta
        const length = input.value.length;
        input.setSelectionRange(length, length);
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateSituacaoVeiculoLabels() {
            const ativo = document.getElementById('situacao_veiculo_1');
            const inativo = document.getElementById('situacao_veiculo_0');
            const labelAtivo = document.getElementById('label_situacao_veiculo_1');
            const labelInativo = document.getElementById('label_situacao_veiculo_0');
            if (ativo.checked) {
                labelAtivo.classList.add('bg-gray-500', 'text-white');
                labelAtivo.classList.remove('bg-white', 'text-gray-700');
                labelInativo.classList.remove('bg-gray-500', 'text-white');
                labelInativo.classList.add('bg-white', 'text-gray-700');
            } else if (inativo.checked) {
                labelInativo.classList.add('bg-gray-500', 'text-white');
                labelInativo.classList.remove('bg-white', 'text-gray-700');
                labelAtivo.classList.remove('bg-gray-500', 'text-white');
                labelAtivo.classList.add('bg-white', 'text-gray-700');
            }
        }
        document.getElementById('situacao_veiculo_1').addEventListener('change', updateSituacaoVeiculoLabels);
        document.getElementById('situacao_veiculo_0').addEventListener('change', updateSituacaoVeiculoLabels);
        // Inicializa ao carregar
        updateSituacaoVeiculoLabels();
    });
</script>
