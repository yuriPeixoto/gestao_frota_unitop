<script>
    // ======================================================
    // SISTEMA DE FILTROS DINÂMICOS (GRUPO/SUBGRUPO)
    // ======================================================
    document.addEventListener('DOMContentLoaded', function() {
        const grupoSelect = document.getElementById('grupo_servico');
        const subgrupoSelect = document.getElementById('subgrupo_servico');

        if (grupoSelect && subgrupoSelect) {
            grupoSelect.addEventListener('change', function() {
                const grupoId = this.value;

                // Limpar subgrupos
                subgrupoSelect.innerHTML = '<option value="">Todos os subgrupos</option>';

                if (grupoId) {
                    // Buscar subgrupos via AJAX
                    fetch(`{{ route('admin.itensparacompra.subgrupos') }}?grupo_id=${grupoId}`)
                        .then(response => response.json())
                        .then(subgrupos => {
                            subgrupos.forEach(subgrupo => {
                                const option = document.createElement('option');
                                option.value = subgrupo.id_subgrupo;
                                option.textContent = subgrupo.descricao_subgrupo;
                                subgrupoSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Erro ao carregar subgrupos:', error));
                }
            });
        }
    });

    // ======================================================
    // SISTEMA DE CHECKBOXES E MODAL PARA SOLICITAÇÃO
    // ======================================================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM carregado, inicializando sistema de checkboxes...');

        const selectAllCheckbox = document.getElementById('select-all');
        const btnCriarSolicitacao = document.getElementById('btn-criar-solicitacao');
        const itemsCount = document.getElementById('items-count');
        const modal = document.getElementById('modal-criar-solicitacao');
        const form = document.getElementById('form-criar-solicitacao');
        const btnFecharModal = document.getElementById('btn-fechar-modal');
        const btnCancelar = document.getElementById('btn-cancelar');

        console.log('Elementos encontrados:', {
            selectAllCheckbox: !!selectAllCheckbox,
            btnCriarSolicitacao: !!btnCriarSolicitacao,
            itemsCount: !!itemsCount,
            modal: !!modal,
            form: !!form
        });

        // Verificar se os elementos essenciais existem
        if (!btnCriarSolicitacao || !itemsCount) {
            console.error('Elementos essenciais não encontrados!');
            return;
        }

        // Função para atualizar contador e estado do botão
        function updateSelection() {
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            const selectedItems = document.querySelectorAll('.item-checkbox:checked');
            const count = selectedItems.length;

            console.log(`Atualizando seleção: ${count} de ${itemCheckboxes.length} itens selecionados`);

            itemsCount.textContent = count + ' itens selecionados';
            btnCriarSolicitacao.disabled = count === 0;

            // Atualizar botão visualmente
            if (count === 0) {
                btnCriarSolicitacao.classList.add('opacity-50', 'cursor-not-allowed');
                btnCriarSolicitacao.classList.remove('hover:bg-blue-700');
            } else {
                btnCriarSolicitacao.classList.remove('opacity-50', 'cursor-not-allowed');
                btnCriarSolicitacao.classList.add('hover:bg-blue-700');
            }

            // Atualizar checkbox "selecionar todos"
            if (selectAllCheckbox) {
                if (count === 0) {
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.checked = false;
                } else if (count === itemCheckboxes.length) {
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.checked = true;
                } else {
                    selectAllCheckbox.indeterminate = true;
                }
            }
        }

        // Event listener para checkboxes individuais
        function attachCheckboxListeners() {
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            console.log(`Anexando listeners a ${itemCheckboxes.length} checkboxes`);

            itemCheckboxes.forEach((checkbox, index) => {
                checkbox.addEventListener('change', function() {
                    console.log(`Checkbox ${index} alterado para: ${this.checked}`);
                    updateSelection();
                });
            });
        }

        // Event listener para "selecionar todos"
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                console.log(`Selecionar todos alterado para: ${this.checked}`);
                const itemCheckboxes = document.querySelectorAll('.item-checkbox');
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelection();
            });
        }

        // Event listener para botão de criar solicitação
        btnCriarSolicitacao.addEventListener('click', function() {
            console.log('Botão criar solicitação clicado');
            const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'))
                .map(cb => cb.value);

            console.log('Itens selecionados:', selectedItems);

            if (selectedItems.length > 0) {
                if (modal && form) {
                    document.getElementById('itens-selecionados').value = JSON.stringify(selectedItems);
                    openModal();
                } else {
                    console.error('Modal ou form não encontrado!');
                }
            } else {
                alert('Selecione pelo menos um item para criar a solicitação.');
            }
        });

        // Função para abrir modal
        function openModal() {
            console.log('Abrindo modal...');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        // Função para fechar modal
        function closeModal() {
            console.log('Fechando modal...');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                if (form) form.reset();
            }
        }

        // Event listeners para fechar modal
        if (btnFecharModal) {
            btnFecharModal.addEventListener('click', closeModal);
        }
        if (btnCancelar) {
            btnCancelar.addEventListener('click', closeModal);
        }

        // Fechar modal ao clicar fora dele
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }

        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        // Submit do formulário
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Formulário submetido');

                const submitBtn = this.querySelector('button[type="submit"]');
                const spinner = document.getElementById('spinner');

                // Mostrar loading
                submitBtn.disabled = true;
                if (spinner) spinner.classList.remove('hidden');

                const formData = new FormData(this);

                // Converter o JSON string para array
                const itensSelecionados = JSON.parse(formData.get('itens_selecionados'));
                formData.delete('itens_selecionados');

                // Adicionar cada item como array element
                itensSelecionados.forEach((item, index) => {
                    formData.append(`itens_selecionados[${index}]`, item);
                });

                fetch('{{ route('admin.itens-para-compra.criar-solicitacao') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);

                            // Redirecionar para a página da solicitação
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            alert('Erro: ' + data.message);

                            // Esconder loading
                            submitBtn.disabled = false;
                            if (spinner) spinner.classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro interno do servidor. Tente novamente.');

                        // Esconder loading
                        submitBtn.disabled = false;
                        if (spinner) spinner.classList.add('hidden');
                    });
            });
        }

        // Inicializar
        attachCheckboxListeners();
        updateSelection();

        console.log('Sistema de checkboxes inicializado com sucesso!');
    });
</script>
