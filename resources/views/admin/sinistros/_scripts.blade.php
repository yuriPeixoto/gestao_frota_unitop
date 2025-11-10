<!-- Carregar dependências -->
@if (!app()->environment('production'))
    <script src="https://unpkg.com/imask@6.6.1/dist/imask.js"></script>
@else
    <script src="{{ asset('vendor/imask/imask.min.js') }}"></script>
@endif

<!-- Carregar scripts na ordem correta -->
<script src="{{ asset('js/sinistros-form.js') }}"></script>
<script src="{{ asset('js/historico-sinistro.js') }}"></script>
<script src="{{ asset('js/historico-sinistro-envolvido.js') }}"></script>
{{-- Importar os scripts de manipulação de documentos --}}
<script src="{{ asset('js/historico-sinistro-documento.js') }}"></script>
<script src="{{ asset('js/sinistro-document-uploader.js') }}"></script>

<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o registro do sinistro?')) {
            excluirPessoa(id);
        }
    }

    function excluirPessoa(id) {
        fetch(`/admin/sinistros/${id}`, {
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
                    alert('O registro foi excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir registro');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir o registro');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>

<!-- Configurações globais em fallback -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Alpine.js não inicializou, usar funções de compatibilidade
        if (typeof sinistrosForm !== 'function') {
            // Função sinistrosForm de compatibilidade
            window.sinistrosForm = function() {
                return {
                    currentTab: 'Aba1',

                    // Abrir a aba selecionada
                    openTab(tabName) {
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

                        // Mostrar a aba atual e adicionar classe active ao botão
                        document.getElementById(tabName).classList.remove("hidden");

                        // Encontrar o botão da aba atual
                        const currentTabButton = document.querySelector(
                            `.tablink[onclick="openTab(event, '${tabName}')"]`);
                        if (currentTabButton) {
                            currentTabButton.classList.remove("bg-gray-200", "text-gray-700");
                            currentTabButton.classList.add("bg-blue-500", "text-white");
                        }

                        this.currentTab = tabName;
                    },

                    init() {
                        // Mostrar a primeira aba por padrão
                        this.openTab('Aba1');
                    }
                };
            };

            // Inicializar manualmente
            const formDiv = document.querySelector('[x-data="sinistrosForm()"]');
            if (formDiv) {
                const form = sinistrosForm();
                form.init();

                // Adicionar eventos de clique às abas
                document.querySelectorAll('.tablink').forEach(link => {
                    const tabName = link.textContent.trim().replace(/\s+/g, '');
                    link.addEventListener('click', () => {
                        form.openTab('Aba' + tabName.replace(/[^0-9]/g, ''));
                    });
                });
            }
        }

        // Inicializar formatação monetária
        const currencyList = document.querySelectorAll('.monetario');
        if (currencyList.length > 0) {
            Array.from(currencyList).forEach(input => {
                input.addEventListener('input', () => {
                    // Remove o formato de moeda para manipular o valor
                    let valor = input.value.replace(/[^\d-]/g,
                        ''); // Mantém apenas números e o sinal de menos

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
                        style: 'currency',
                        currency: 'BRL',
                    }).format(valor);
                });
            });
        }

        // Inicializar máscaras IMask
        if (typeof IMask === 'function') {
            const cpfElement = document.getElementById('cpf');
            if (cpfElement) {
                IMask(cpfElement, {
                    mask: '000.000.000-00'
                });
            }

            const telefoneElement = document.getElementById('telefone');
            if (telefoneElement) {
                IMask(telefoneElement, {
                    mask: '(00) 0 0000-0000'
                });
            }
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        onSmartSelectChange('id_veiculo', function(data) {
            fetch(`/admin/sinistros/${data.value}/categoria`)
                .then(response => response.json())
                .then(dado => {
                    console.log(dado);
                    const categoriaSelect = document.getElementById('categoria_select');
                    categoriaSelect.innerHTML = `
                            <option value="${dado.categoria.value}">${dado.categoria.label}</option>
                        `;

                    const filialSelect = document.getElementById('id_filial_select');
                    filialSelect.innerHTML = `
                            <option value="${dado.filial.value}">${dado.filial.label}</option>
                        `;
                });
        });
    })
</script>

<script>
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
                style: 'currency',
                currency: 'BRL',
            }).format(valor);
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        var element = document.getElementById('cpf');

        let maskCPF = {
            mask: '000.000.000-00'
        }; // Formato DD/MM/YYYY

        var mask = IMask(element, maskCPF);

        var element = document.getElementById('telefone');

        let maskTelefone = {
            mask: '(00) 0 0000-0000'
        }; // Formato DD/MM/YYYY

        var mask = IMask(element, maskTelefone);

    });
</script>

<script>
    // Compatibilidade para botões existentes e inicialização
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar se os novos scripts estão carregados
        if (typeof SinistroDocumentUploader === 'function') {
            // Inicializar com configurações específicas
            window.uploader = new SinistroDocumentUploader({
                // Pode definir configurações específicas aqui se necessário
            });
        } else {
            // Fallback para o script legado
            console.log('Usando script legado para manipulação de documentos');

            // Verificar se botão tem onclick
            const uploadBtn = document.querySelector('button[data-action="upload-document"]');
            if (uploadBtn && !uploadBtn.hasAttribute('onclick')) {
                uploadBtn.setAttribute('onclick', 'adicionarDocumento()');
            }
        }

        // Mostrar mensagem de "sem documentos" se necessário
        const docJson = document.getElementById('documentos_json');
        const emptyMsg = document.getElementById('documentos-empty');

        if (docJson && emptyMsg) {
            try {
                const docs = JSON.parse(docJson.value);
                if (!docs || docs.length === 0) {
                    emptyMsg.classList.remove('hidden');
                }
            } catch (e) {
                console.error('Erro ao processar JSON de documentos:', e);
            }
        }
    });
</script>

<script>
    const isCreate = {{ request()->is('admin/sinistros/criar') ? 'true' : 'false' }};

    document.addEventListener('DOMContentLoaded', () => {
        if (isCreate) {
            setSmartSelectValue('status', ['Em Andamento']);
        }
    });
</script>
