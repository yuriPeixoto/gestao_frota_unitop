/**
 * Script para o formulário de sinistros
 * Resolve problemas de carregamento e interação no front-end
 */

// Função sinistrosForm para Alpine.js
window.sinistrosForm = function() {
    return {
        currentTab: 'Aba1',
        selectedVeiculo: null,
        selectedCategoria: null,
        
        // Inicialização
        init() {
            // Mostrar a primeira aba por padrão
            this.openTab('Aba1');
            
            // Configurar listeners de eventos
            this.setupEventListeners();
        },
        
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
            const currentTabButton = document.querySelector(`.tablink[onclick="openTab(event, '${tabName}')"]`);
            if (currentTabButton) {
                currentTabButton.classList.remove("bg-gray-200", "text-gray-700");
                currentTabButton.classList.add("bg-blue-500", "text-white");
            }
            
            this.currentTab = tabName;
        },
        
        // Configurar os event listeners
        setupEventListeners() {
            // Substituir os onclick inline por listeners de eventos
            const tabButtons = document.querySelectorAll('.tablink');
            tabButtons.forEach(button => {
                // Obter o nome da aba do atributo onclick
                const onclickValue = button.getAttribute('onclick');
                if (onclickValue) {
                    const tabName = onclickValue.match(/'([^']+)'/)[1];
                    
                    // Remover o onclick inline
                    button.removeAttribute('onclick');
                    
                    // Adicionar event listener
                    button.addEventListener('click', (event) => {
                        this.openTab(tabName);
                    });
                }
            });
            
            // Listener para atualização de categoria ao selecionar veículo
            const veiculoSelect = document.getElementById('placa_select');
            if (veiculoSelect) {
                veiculoSelect.addEventListener('change', (event) => {
                    const veiculoId = event.target.value;
                    if (veiculoId) {
                        this.updateCategoria(veiculoId);
                    }
                });
            }
            
            // Inicializar formatação de campos monetários
            this.initMonetaryFields();
            
            // Inicializar máscaras de campos
            this.initMasks();
        },
        
        // Atualizar categoria com base no veículo selecionado
        updateCategoria(veiculoId) {
            if (!veiculoId) return;
            
            fetch(`/admin/sinistros/${veiculoId}/categoria`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro ao buscar categoria');
                    }
                    return response.json();
                })
                .then(data => {
                    const categoriaSelect = document.getElementById('categoria_select');
                    if (categoriaSelect) {
                        categoriaSelect.innerHTML = `
                            <option value="${data.value}">${data.label}</option>
                        `;
                        this.selectedCategoria = data.value;
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar categoria:', error);
                    // Mostrar feedback de erro, se disponível
                    if (typeof mostrarFeedback === 'function') {
                        mostrarFeedback('Erro ao buscar categoria do veículo', 'error');
                    }
                });
        },
        
        // Inicializar campos monetários
        initMonetaryFields() {
            const currencyInputs = document.querySelectorAll('.monetario');
            
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
        },
        
        // Inicializar máscaras para campos
        initMasks() {
            // Inicializar máscaras se o IMask estiver disponível
            if (typeof IMask !== 'undefined') {
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
            } else {
                console.warn('IMask não está disponível. Máscaras de campos não serão aplicadas.');
            }
        },
        
        // Função para adicionar histórico
        adicionarHistorico() {
            // Verificar se a função global existe (definida em historico-sinistro.js)
            if (typeof window.adicionarHistorico === 'function') {
                window.adicionarHistorico();
            } else {
                console.error('Função adicionarHistorico não está definida');
                // Feedback de erro, se disponível
                if (typeof mostrarFeedback === 'function') {
                    mostrarFeedback('Erro: Função de adicionar histórico não está disponível', 'error');
                }
            }
        },
        
        // Função para adicionar documento
        adicionarDocumento() {
            // Verificar se a função global existe (definida em historico-sinistro-documento.js)
            if (typeof window.adicionarDocumento === 'function') {
                window.adicionarDocumento();
            } else {
                console.error('Função adicionarDocumento não está definida');
                // Feedback de erro, se disponível
                if (typeof mostrarFeedback === 'function') {
                    mostrarFeedback('Erro: Função de adicionar documento não está disponível', 'error');
                }
            }
        },
        
        // Função para adicionar envolvido
        adicionarEnvolvido() {
            // Verificar se a função global existe (definida em historico-sinistro-envolvido.js)
            if (typeof window.adicionarEnvolvido === 'function') {
                window.adicionarEnvolvido();
            } else {
                console.error('Função adicionarEnvolvido não está definida');
                // Feedback de erro, se disponível
                if (typeof mostrarFeedback === 'function') {
                    mostrarFeedback('Erro: Função de adicionar envolvido não está disponível', 'error');
                }
            }
        }
    };
};

// Garantir que o DOM esteja carregado antes de executar qualquer código
document.addEventListener('DOMContentLoaded', function() {
    // Definir as funções globais se estiverem faltando (como fallback)
    
    // Fallback para adicionarDocumento
    if (typeof window.adicionarDocumento !== 'function') {
        window.adicionarDocumento = function() {
            console.warn('Implementação fallback de adicionarDocumento foi chamada');
            alert('O sistema de upload de documentos não foi carregado corretamente. Por favor, recarregue a página.');
        };
    }
    
    // Fallback para adicionarHistorico
    if (typeof window.adicionarHistorico !== 'function') {
        window.adicionarHistorico = function() {
            console.warn('Implementação fallback de adicionarHistorico foi chamada');
            alert('O sistema de histórico não foi carregado corretamente. Por favor, recarregue a página.');
        };
    }
    
    // Fallback para adicionarEnvolvido
    if (typeof window.adicionarEnvolvido !== 'function') {
        window.adicionarEnvolvido = function() {
            console.warn('Implementação fallback de adicionarEnvolvido foi chamada');
            alert('O sistema de envolvidos não foi carregado corretamente. Por favor, recarregue a página.');
        };
    }
    
    // Inicializar as funções de formatação monetária globalmente
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
});

// Função de compatibilidade para openTab (para botões que ainda usam onclick="openTab")
function openTab(evt, tabName) {
    // Se Alpine.js estiver disponível, use o Alpine.js
    const form = document.querySelector('[x-data="sinistrosForm()"]');
    if (form && form.__x) {
        form.__x.$data.openTab(tabName);
    } else {
        // Fallback se Alpine.js não estiver disponível
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
        if (evt && evt.currentTarget) {
            evt.currentTarget.classList.remove("bg-gray-200", "text-gray-700");
            evt.currentTarget.classList.add("bg-blue-500", "text-white");
        }
    }
}