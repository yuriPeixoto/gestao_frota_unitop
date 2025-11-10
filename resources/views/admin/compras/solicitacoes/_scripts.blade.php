<script>
    function abrirModalAprovacao(id) {
        const form = document.getElementById('formAprovacao');
        form.action = "{{ url('admin/compras/solicitacoes') }}/" + id + "/aprovar-gestor";

        // Limpar campos
        form.reset();

        // Abrir o modal
        document.getElementById('modalAprovacao').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    /**
     * Abrir modal de rejeição
     */
    function abrirModalRejeicao(id) {
        const form = document.getElementById('formRejeicao');
        form.action = "{{ url('admin/compras/solicitacoes') }}/" + id + "/reprovar-gestor";

        // Limpar campos
        form.reset();

        // Abrir o modal
        document.getElementById('modalRejeicao').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    /**
     * Abrir modal de cancelamento
     */
    function abrirModalCancelamento(solicitacaoId) {
        // Configurar o formulário
        const form = document.getElementById('formCancelamento');
        form.action = `{{ url('admin/compras/solicitacoes') }}/${solicitacaoId}/cancelar`;

        console.log(form.action);

        // Limpar campos
        form.reset();

        // Abrir o modal
        document.getElementById('modalCancelamento').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    /**
     * Fechar qualquer modal aberto
     */
    function fecharModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }


    /**
     * Inicializar validação básica dos formulários
     */
    document.addEventListener('DOMContentLoaded', function() {
        const formRejeicao = document.getElementById('formRejeicao');
        if (formRejeicao) {
            formRejeicao.addEventListener('submit', function(event) {
                const justificativa = this.querySelector('[name="observacao"]').value.trim();
                if (!justificativa) {
                    event.preventDefault();
                    alert('A justificativa é obrigatória para rejeitar a solicitação.');
                }
            });
        }

        const formCancelamento = document.getElementById('formCancelamento');
        if (formCancelamento) {
            formCancelamento.addEventListener('submit', function(event) {
                const justificativa = this.querySelector('[name="justificativa_edit_or_delete"]').value
                    .trim();
                if (!justificativa) {
                    event.preventDefault();
                    alert('A justificativa é obrigatória para cancelar a solicitação.');
                }
            });
        }
    });
</script>

<script>
    // Função para controlar seções de produto e serviço baseado no tipo de pedido
    function toggleSecoesPorTipoPedido() {
        const tipoPedido = document.querySelector('select[name="tipo_solicitacao"]');
        const secaoProduto = document.getElementById('secao-produto');
        const secaoServico = document.getElementById('secao-servico');
        const headerAdicionarItens = document.getElementById('header-adicionar-itens');

        if (!tipoPedido || !secaoProduto || !secaoServico || !headerAdicionarItens) return;

        const valor = tipoPedido.value.toLowerCase();
        const textoSelecionado = tipoPedido.options[tipoPedido.selectedIndex]?.text?.toLowerCase() || '';

        // Esconder todas as seções
        secaoProduto.classList.add('hidden');
        secaoServico.classList.add('hidden');

        // Controlar visibilidade do header "Adicionar Itens"
        if (!valor || valor === '') {
            headerAdicionarItens.style.display = 'block';
        } else {
            headerAdicionarItens.style.display = 'none';
        }

        // Controle para saber se já houve seleção anterior
        if (typeof window.__tipoPedidoAnterior === 'undefined') {
            window.__tipoPedidoAnterior = valor;
        }

        // Só exibe o confirm se já houve seleção anterior e está trocando de tipo
        if (window.__tipoPedidoAnterior && window.__tipoPedidoAnterior !== valor) {
            if ((valor.includes('produto') || textoSelecionado.includes('produto')) && servicos.length > 0) {
                if (confirm('Ao selecionar Produto, todos os serviços serão removidos. Deseja continuar?')) {
                    servicos.length = 0;
                    popularServicoTabela();
                    secaoProduto.classList.remove('hidden');
                } else {
                    // Restaurar valor anterior
                    for (let option of tipoPedido.options) {
                        if (option.value.toLowerCase() === window.__tipoPedidoAnterior) {
                            tipoPedido.value = option.value;
                            break;
                        }
                    }
                    return;
                }
            } else if ((valor.includes('servico') || valor.includes('serviço') || textoSelecionado.includes(
                    'servico') || textoSelecionado.includes('serviço')) && produtos.length > 0) {
                if (confirm('Ao selecionar Serviço, todos os produtos serão removidos. Deseja continuar?')) {
                    produtos.length = 0;
                    popularProdutoTabela();
                    secaoServico.classList.remove('hidden');
                } else {
                    // Restaurar valor anterior
                    for (let option of tipoPedido.options) {
                        if (option.value.toLowerCase() === window.__tipoPedidoAnterior) {
                            tipoPedido.value = option.value;
                            break;
                        }
                    }
                    return;
                }
            } else {
                // Não há conflitos, apenas mostrar a seção apropriada
                if (valor.includes('produto') || textoSelecionado.includes('produto')) {
                    secaoProduto.classList.remove('hidden');
                } else if (valor.includes('servico') || valor.includes('serviço') || textoSelecionado.includes(
                        'servico') || textoSelecionado.includes('serviço')) {
                    secaoServico.classList.remove('hidden');
                }
            }
        } else {
            // Primeira seleção ou não houve troca de tipo: não pede confirmação
            if (valor.includes('produto') || textoSelecionado.includes('produto')) {
                secaoProduto.classList.remove('hidden');
            } else if (valor.includes('servico') || valor.includes('serviço') || textoSelecionado.includes('servico') ||
                textoSelecionado.includes('serviço')) {
                secaoServico.classList.remove('hidden');
            }
        }
        window.__tipoPedidoAnterior = valor;
    }

    // Função para controlar a visibilidade da seção "Opções de Contrato"
    function toggleOpcoesContrato() {
        const tipoPedido = document.querySelector('select[name="tipo_solicitacao"]');
        const opcoesContrato = document.getElementById('opcoes-contrato');
        const headerAdicionarItens = document.getElementById('header-adicionar-itens');

        if (tipoPedido && opcoesContrato) {
            const valor = tipoPedido.value.toLowerCase();
            const textoSelecionado = tipoPedido.options[tipoPedido.selectedIndex]?.text?.toLowerCase() || '';

            // Verifica se o valor ou texto contém "produto"
            if (valor.includes('produto') || textoSelecionado.includes('produto')) {
                opcoesContrato.style.display = 'block';
            } else {
                opcoesContrato.style.display = 'none';
            }
        }

        // Controlar visibilidade do header "Adicionar Itens"
        if (tipoPedido && headerAdicionarItens) {
            const valor = tipoPedido.value;
            if (!valor || valor === '') {
                headerAdicionarItens.style.display = 'block';
            } else {
                headerAdicionarItens.style.display = 'none';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar a visibilidade das seções
        toggleSecoesPorTipoPedido();
        toggleOpcoesContrato();

        // Inicializar visibilidade do header baseado no valor atual
        const tipoPedidoSelect = document.querySelector('select[name="tipo_solicitacao"]');
        const headerAdicionarItens = document.getElementById('header-adicionar-itens');

        if (tipoPedidoSelect && headerAdicionarItens) {
            const valorAtual = tipoPedidoSelect.value;
            if (!valorAtual || valorAtual === '') {
                headerAdicionarItens.style.display = 'block';
            } else {
                headerAdicionarItens.style.display = 'none';
            }
        }

        // Adicionar evento de mudança no select de tipo de pedido
        if (tipoPedidoSelect) {
            tipoPedidoSelect.addEventListener('change', function() {
                toggleSecoesPorTipoPedido();
                toggleOpcoesContrato();
            });
        }
    });
</script>

<script>
    // script para armazenar os produtos
    const produtos = @json($produtosList ?? []);
    const produtosDescricao = @json($produtosDescricao ?? []);
    const unidadesDescricao = @json($unidadesDescricao ?? []);
    // Armazenar arquivos de imagem globalmente
    const produtosImagens = new Map();

    // Detecta se está em modo edição (edit) ou inclusão (create)
    const isEdicao = {{ isset($solicitacao) ? 'true' : 'false' }}; // Debug inicial
    document.addEventListener('DOMContentLoaded', () => {
        popularProdutoTabela();
    });

    function popularProdutoTabela() {
        const tbody = document.getElementById('tabelaProdutosBody');
        if (!tbody) {
            console.error('Elemento tabelaProdutosBody não encontrado!');
            return;
        }

        tbody.innerHTML = ''; // Limpa antes de adicionar

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Janeiro é 0
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        produtos.forEach((item, index) => {

            const tr = document.createElement('tr');

            tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">${item.id_produto ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatDate(item.data_inclusao)}</td>
            <td class="px-6 py-4 whitespace-nowrap">${produtosDescricao[item.id_produto] ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${unidadesDescricao[item.id_unidade] ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${item.quantidade_solicitada ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${item.observacao_item ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${item.justificativa_iten_solicitacao ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${item.imagem_produto ? `
                    <div class="flex items-center space-x-2">
                        <img src="${item.imagem_produto_preview}" alt="Imagem do produto" class="w-8 h-8 object-cover rounded border border-gray-300">
                        <span class="text-xs text-gray-600">Anexo</span>
                    </div>
                ` : '<span class="text-xs text-gray-400">Sem anexo</span>'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex gap-2">
                    ${!isEdicao ? `<div class=\"cursor-pointer delete-produto\" data-index=\"${index}\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\" class=\"size-6 text-red-500\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0\" />
                        </svg>
                    </div>` : ''}
                </div>
            </td>

            `;

            if (!isEdicao) {
                tr.querySelector(".delete-produto")?.addEventListener("click", (event) => {
                    const index = parseInt(event.currentTarget.getAttribute("data-index"));
                    removerProduto(index);
                });
            }

            tbody.appendChild(tr);
        });

        atualizarCampoHidden();
    }

    function removerProduto(index) {
        // Remover imagem associada se existir
        if (produtosImagens.has(index)) {
            produtosImagens.delete(index);
        }

        produtos.splice(index, 1);
        popularProdutoTabela();
    }

    function atualizarCampoHidden() {
        const campo = document.getElementById('produtos_json');
        if (campo) {
            campo.value = JSON.stringify(produtos);
        }
    }

    function adicionarProduto() {
        const id_produtos = document.querySelector('[name="id_produtos"]').value;
        const unidade = document.querySelector('[name="unidade"]').value;
        const quantidade_solicitada = document.querySelector('[name="quantidade_solicitada"]').value;
        const observacao_item = document.querySelector('[name="observacao_item"]').value;
        const justificativa_iten_solicitacao = document.querySelector('[name="justificativa_iten_solicitacao"]').value;
        const imagemProdutoInput = document.querySelector('[name="imagem_produto"]');

        if (!id_produtos || !quantidade_solicitada || !unidade) {
            alert('Preencha todos os campos para adicionar o produto.');
            return;
        }

        const novoItem = {
            id_produto: id_produtos,
            id_unidade: unidade,
            quantidade_solicitada: quantidade_solicitada,
            observacao_item: observacao_item,
            justificativa_iten_solicitacao: justificativa_iten_solicitacao,
            data_inclusao: new Date(),
        };

        // Processar imagem se foi selecionada
        if (imagemProdutoInput && imagemProdutoInput.files && imagemProdutoInput.files[0]) {
            const file = imagemProdutoInput.files[0];

            // Validar tamanho do arquivo (máx. 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('A imagem deve ter no máximo 2MB.');
                return;
            }

            // Validar tipo do arquivo
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Formato de imagem não permitido. Use JPG, PNG, GIF ou WEBP.');
                return;
            }

            // Armazenar arquivo na estrutura global
            const produtoIndex = produtos.length;
            produtosImagens.set(produtoIndex, file);

            // Criar preview da imagem para exibição na tabela
            const reader = new FileReader();
            reader.onload = function(e) {
                novoItem.imagem_produto = true; // Marcar que tem imagem
                novoItem.imagem_produto_preview = e.target.result;
                novoItem.imagem_produto_index = produtoIndex;
                produtos.push(novoItem);
                popularProdutoTabela();
                limparFormularioProduto();
            };
            reader.readAsDataURL(file);
        } else {
            produtos.push(novoItem);
            popularProdutoTabela();
            limparFormularioProduto();
        }
    }

    function limparFormularioProduto() {
        document.querySelector('[name="id_produtos"]').value = '';
        // Limpar o select de unidade
        const unidadeSelect = document.getElementById('unidade');
        unidadeSelect.innerHTML = '';
        document.querySelector('[name="quantidade_solicitada"]').value = '';
        document.querySelector('[name="observacao_item"]').value = '';
        document.querySelector('[name="justificativa_iten_solicitacao"]').value = '';

        // Limpar campo de imagem
        const imagemInput = document.querySelector('[name="imagem_produto"]');
        if (imagemInput) {
            imagemInput.value = '';
        }

        // Esconder preview
        const previewDiv = document.getElementById('preview-imagem-produto');
        if (previewDiv) {
            previewDiv.classList.add('hidden');
        }

        // Disparar evento para limpar o smart-select
        window.dispatchEvent(new CustomEvent('id_produtos:clear'));
    }
</script>

<script>
    // script para armazenar os serviços
    const servicos = @json($servicosList ?? []);
    const servicosDescricao = @json($servicosDescricao ?? []);
    // Armazenar arquivos de imagem de serviços globalmente
    const servicosImagens = new Map();
    // Detecta se está em modo edição (edit) ou inclusão (create)
    // (já definido acima, reutiliza isEdicao)

    document.addEventListener('DOMContentLoaded', () => {
        popularServicoTabela();
    });

    function popularServicoTabela() {
        const tbody = document.getElementById('tabelaServicosBody');
        tbody.innerHTML = ''; // Limpa antes de adicionar

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Janeiro é 0
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        servicos.forEach((item, index) => {

            const tr = document.createElement('tr');

            tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">${item.id_servico ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatDate(item.data_inclusao)}</td>
            <td class="px-6 py-4 whitespace-nowrap">${servicosDescricao[item.id_servico] ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${item.quantidade ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${item.observacao_item ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${item.justificativa_iten_solicitacao ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${item.imagem_servico ? `
                    <div class="flex items-center space-x-2">
                        <img src="${item.imagem_servico_preview}" alt="Imagem do serviço" class="w-8 h-8 object-cover rounded border border-gray-300">
                        <span class="text-xs text-gray-600">Anexo</span>
                    </div>
                ` : '<span class="text-xs text-gray-400">Sem anexo</span>'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex gap-2">
                    ${!isEdicao ? `<div class=\"cursor-pointer delete-servico\" data-index=\"${index}\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\" class=\"size-6 text-red-500\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0\" />
                        </svg>
                    </div>` : ''}
                </div>
            </td>
            `;

            if (!isEdicao) {
                tr.querySelector(".delete-servico")?.addEventListener("click", (event) => {
                    const index = parseInt(event.currentTarget.getAttribute("data-index"));
                    removerServico(index);
                });
            }

            tbody.appendChild(tr);
        });

        atualizarCampoHidden();
    }

    function removerServico(index) {
        // Remover imagem associada se existir
        if (servicosImagens.has(index)) {
            servicosImagens.delete(index);
        }

        servicos.splice(index, 1);
        popularServicoTabela();
    }

    function atualizarCampoHidden() {
        const campo = document.getElementById('servicos_json');
        if (campo) {
            campo.value = JSON.stringify(servicos);
        }
    }

    function adicionarServico() {
        const id_servico = document.querySelector('[name="id_servico"]').value;
        const quantidade_solicitada_servico = document.querySelector('[name="quantidade_solicitada_servico"]')
            .value; // Nome correto do campo
        const observacao_item = document.querySelector('[name="observacao_item_servico"]')
            .value; // Nome correto do campo
        const justificativa_iten_solicitacao_servico = document.querySelector(
                '[name="justificativa_iten_solicitacao_servico"]')
            .value; // Nome correto do campo
        const arquivoImagem = document.querySelector('[name="imagem_servico"]').files[0];

        if (!id_servico || !quantidade_solicitada_servico) {
            alert('Preencha todos os campos para adicionar o serviço.');
            return;
        }

        const novoItem = {
            id_servico: id_servico, // Corrigido: usar id_servico para serviços
            quantidade: quantidade_solicitada_servico, // Corrigido: usar 'quantidade' em vez de 'quantidade_solicitada'
            observacao_item: observacao_item,
            justificativa_iten_solicitacao: justificativa_iten_solicitacao_servico,
            data_inclusao: new Date(),
        };

        // Adicionar imagem se foi selecionada
        if (arquivoImagem) {
            const reader = new FileReader();
            reader.onload = function(e) {
                servicosImagens.set(servicos.length, arquivoImagem);
                novoItem.imagem_servico = `arquivo_${servicos.length}`;
                novoItem.imagem_servico_preview = e.target.result;

                servicos.push(novoItem);
                popularServicoTabela();
            };
            reader.readAsDataURL(arquivoImagem);
        } else {
            servicos.push(novoItem);
            popularServicoTabela();
        }

        // Limpar formulário
        limparFormularioServico();
    }

    function limparFormularioServico() {
        document.querySelector('[name="id_produtos"]').value = '';
        document.querySelector('[name="quantidade_solicitada"]').value = ''; // Nome correto do campo
        document.querySelector('[name="observacao_item_servico"]').value = ''; // Nome correto do campo
        document.querySelector('[name="justificativa_iten_solicitacao"]').value = ''; // Nome correto do campo

        // Limpar campo de imagem
        const arquivoInput = document.querySelector('[name="imagem_servico"]');
        const previewContainer = document.getElementById('preview-imagem-servico');
        const previewImage = document.getElementById('img-preview-servico');

        if (arquivoInput) arquivoInput.value = '';
        if (previewContainer) previewContainer.classList.add('hidden');
        if (previewImage) previewImage.src = '';

        // Disparar evento para limpar o smart-select
        window.dispatchEvent(new CustomEvent('id_produtos:clear'));
    }
</script>

<script>
    // Script para atualizar dados do produto selecionado
    document.addEventListener('DOMContentLoaded', function() {
        // Definir função callback para atualização de dados do produto
        window.atualizarDadosProdutoCallback = function(idProduto) {
            if (!idProduto) return;
            fetch('{{ route('admin.compras.solicitacoes.pega-unidade') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        produto: idProduto
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.error) {
                        const unidadeSelect = document.getElementById('unidade');
                        // Limpar opções existentes
                        unidadeSelect.innerHTML = '';

                        // Adicionar a unidade do produto automaticamente
                        if (data.value && data.label) {
                            const option = document.createElement('option');
                            option.value = data.value;
                            option.textContent = data.label;
                            option.selected = true;
                            unidadeSelect.appendChild(option);
                        } else {
                            // Se não houver dados, adicionar opção padrão
                            const defaultOption = document.createElement('option');
                            defaultOption.value = '';
                            defaultOption.textContent = 'Unidade não informada';
                            unidadeSelect.appendChild(defaultOption);
                        }
                    } else {
                        console.error(data.error);
                        // Em caso de erro, limpar o select
                        const unidadeSelect = document.getElementById('unidade');
                        unidadeSelect.innerHTML = '<option value="">Erro ao carregar unidade</option>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar dados do veículo:', error);
                });
        };

        // Inicializar com valor atual, se existir
        const idProduto = document.querySelector('[name="id_produtos"]')?.value;
        if (idProduto) {
            window.atualizarDadosProdutoCallback(idProduto);
        }

        // Escutar eventos do smart-select
        window.addEventListener('id_produtos:selected', function(e) {
            if (e.detail && e.detail.value) {
                window.atualizarDadosProdutoCallback(e.detail.value);
            }
        });

        // Garantir que os campos hidden sejam atualizados antes do envio do formulário
        const form = document.getElementById('formSolicitacao');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Verificar se há itens adicionados
                if (produtos.length === 0 && servicos.length === 0) {
                    alert('Por favor, adicione pelo menos um produto ou serviço à solicitação.');
                    return false;
                }

                // Criar FormData para enviar arquivos
                const formData = new FormData(form);

                // Adicionar produtos como JSON
                formData.set('produtos', JSON.stringify(produtos));
                formData.set('servicos', JSON.stringify(servicos));

                // Adicionar imagens dos produtos
                produtosImagens.forEach((file, index) => {
                    formData.append(`produto_imagem_${index}`, file);
                });

                // Adicionar imagens dos serviços
                servicosImagens.forEach((file, index) => {
                    formData.append(`servico_imagem_${index}`, file);
                });

                // Enviar via fetch
                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        }
                    })
                    .then(response => {
                        if (response.redirected) {
                            window.location.href = response.url;
                            return;
                        }
                        if (response.ok) {
                            return response.text();
                        } else {
                            throw new Error('Erro no servidor: ' + response.status);
                        }
                    })
                    .then(data => {
                        if (data) {
                            // Se chegou até aqui, recarregar a página com os dados
                            document.open();
                            document.write(data);
                            document.close();
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao enviar formulário. Tente novamente.');
                    });
            });
        }
    });
</script>

<script>
    // Funções para gerenciar upload de imagem
    function limparImagemProduto() {
        const imagemInput = document.querySelector('[name="imagem_produto"]');
        const previewDiv = document.getElementById('preview-imagem-produto');

        if (imagemInput) {
            imagemInput.value = '';
        }

        if (previewDiv) {
            previewDiv.classList.add('hidden');
        }
    }

    function limparImagemServico() {
        const imagemInput = document.querySelector('[name="imagem_servico"]');
        const previewDiv = document.getElementById('preview-imagem-servico');

        if (imagemInput) {
            imagemInput.value = '';
        }

        if (previewDiv) {
            previewDiv.classList.add('hidden');
        }
    }

    // Event listener para preview da imagem
    document.addEventListener('DOMContentLoaded', function() {
        const imagemInput = document.querySelector('[name="imagem_produto"]');
        const previewDiv = document.getElementById('preview-imagem-produto');
        const imgPreview = document.getElementById('img-preview-produto');

        if (imagemInput) {
            imagemInput.addEventListener('change', function(e) {
                const file = e.target.files[0];

                if (file) {
                    // Validar tamanho do arquivo (máx. 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('A imagem deve ter no máximo 2MB.');
                        e.target.value = '';
                        previewDiv.classList.add('hidden');
                        return;
                    }

                    // Validar tipo do arquivo
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Formato de imagem não permitido. Use JPG, PNG, GIF ou WEBP.');
                        e.target.value = '';
                        previewDiv.classList.add('hidden');
                        return;
                    }

                    // Mostrar preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imgPreview.src = e.target.result;
                        previewDiv.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewDiv.classList.add('hidden');
                }
            });
        }

        // Preview de imagem para serviços
        const imagemServicoInput = document.querySelector('[name="imagem_servico"]');
        const previewServicoDiv = document.getElementById('preview-imagem-servico');
        const imgPreviewServico = document.getElementById('img-preview-servico');

        if (imagemServicoInput) {
            imagemServicoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];

                if (file) {
                    // Validar tamanho do arquivo (máx. 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('A imagem deve ter no máximo 2MB.');
                        e.target.value = '';
                        previewServicoDiv.classList.add('hidden');
                        return;
                    }

                    // Validar tipo do arquivo
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Formato de imagem não permitido. Use JPG, PNG, GIF ou WEBP.');
                        e.target.value = '';
                        previewServicoDiv.classList.add('hidden');
                        return;
                    }

                    // Mostrar preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imgPreviewServico.src = e.target.result;
                        previewServicoDiv.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewServicoDiv.classList.add('hidden');
                }
            });
        }
    });

    // Funções para o Modal de Pré-Cadastro de Produto
    function abrirModalPreCadastro() {
        // Carregar dados dos selects
        carregarDadosPreCadastro();

        // Abrir o modal
        document.getElementById('modal-pre-cadastro').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function fecharModalPreCadastro() {
        // Limpar formulário
        document.getElementById('form-pre-cadastro').reset();

        // Fechar o modal
        document.getElementById('modal-pre-cadastro').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    async function carregarDadosPreCadastro() {
        try {
            // Carregar estoques
            const responseEstoque = await fetch('{{ route('admin.api.estoques.list') }}');
            if (responseEstoque.ok) {
                const estoques = await responseEstoque.json();
                const selectEstoque = document.getElementById('id_estoque_produto');
                selectEstoque.innerHTML = '<option value="">Selecione o estoque...</option>';
                estoques.forEach(estoque => {
                    selectEstoque.innerHTML += `<option value="${estoque.id}">${estoque.nome}</option>`;
                });
            }

            // Carregar unidades
            const responseUnidade = await fetch('{{ route('admin.api.unidades.list') }}');
            if (responseUnidade.ok) {
                const unidades = await responseUnidade.json();
                const selectUnidade = document.getElementById('id_unidade_produto');
                selectUnidade.innerHTML = '<option value="">Selecione a unidade...</option>';
                unidades.forEach(unidade => {
                    selectUnidade.innerHTML +=
                        `<option value="${unidade.id}">${unidade.descricao}</option>`;
                });
            }

            // Carregar grupos de serviço
            const responseGrupo = await fetch('{{ route('admin.api.grupos-servico.list') }}');
            if (responseGrupo.ok) {
                const grupos = await responseGrupo.json();
                const selectGrupo = document.getElementById('id_grupo_servico');
                selectGrupo.innerHTML = '<option value="">Selecione o grupo...</option>';
                grupos.forEach(grupo => {
                    selectGrupo.innerHTML += `<option value="${grupo.id}">${grupo.descricao}</option>`;
                });
            }
        } catch (error) {
            console.error('Erro ao carregar dados do pré-cadastro:', error);
            alert('Erro ao carregar dados. Verifique sua conexão e tente novamente.');
        }
    }

    function adicionarProdutoATabela(produto, formData) {
        // Criar um novo item para a tabela com os dados do produto recém-cadastrado
        const novoItem = {
            id_produto: produto.id,
            id_unidade: formData.get('id_unidade_produto'),
            quantidade_solicitada: formData.get('quantidade_solicitada') || 1,
            observacao_item: '', // Campo vazio conforme solicitado
            justificativa_iten_solicitacao: '', // Campo vazio conforme solicitado
            data_inclusao: new Date(),
        };

        // Adicionar o produto ao array global
        produtos.push(novoItem);

        // Atualizar as descrições globais
        produtosDescricao[produto.id] = produto.descricao;

        // Buscar descrição da unidade
        const selectUnidade = document.getElementById('id_unidade_produto');
        const opcaoSelecionada = selectUnidade.querySelector(`option[value="${formData.get('id_unidade_produto')}"]`);
        if (opcaoSelecionada) {
            unidadesDescricao[formData.get('id_unidade_produto')] = opcaoSelecionada.textContent;
        }

        // Atualizar a tabela
        popularProdutoTabela();
    }

    async function salvarPreCadastro() {
        const form = document.getElementById('form-pre-cadastro');
        const formData = new FormData(form);

        // Validar campos obrigatórios
        const camposObrigatorios = ['id_estoque_produto', 'id_filial', 'descricao_produto', 'id_unidade_produto',
            'id_grupo_servico', 'quantidade_solicitada'
        ];
        for (let campo of camposObrigatorios) {
            if (!formData.get(campo)) {
                alert('Todos os campos são obrigatórios!');
                return;
            }
        }

        try {
            const response = await fetch('{{ route('admin.produtos.pre-cadastro') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert('Produto cadastrado com sucesso!');
                fecharModalPreCadastro();

                // Adicionar automaticamente o produto à tabela
                adicionarProdutoATabela(result.produto, formData);

                // Atualizar lista de produtos no smart-select principal se existe
                const smartSelectProdutos = document.querySelector('[name="id_produtos"]');
                if (smartSelectProdutos) {
                    // Disparar evento personalizado para smart-select se necessário
                    const evento = new CustomEvent('atualizarOpcoes', {
                        detail: {
                            novoProduto: {
                                value: result.produto.id,
                                label: result.produto.descricao
                            }
                        }
                    });
                    smartSelectProdutos.dispatchEvent(evento);
                }
            } else {
                alert(result.message || 'Erro ao cadastrar produto. Tente novamente.');
            }
        } catch (error) {
            console.error('Erro ao salvar pré-cadastro:', error);
            alert('Erro ao cadastrar produto. Tente novamente.');
        }
    }
</script>
