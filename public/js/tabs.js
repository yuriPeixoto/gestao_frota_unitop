/**
 * Script para gerenciamento das abas no formulário de fornecedor
 */

/**
 * Inicializa o sistema de abas
 */
function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    if (!tabButtons.length) return;
    
    // Função para mostrar uma aba específica
    function showTab(tabId) {
        // Esconder todos os conteúdos
        tabContents.forEach(content => {
            content.classList.add('hidden');
        });
        
        // Remover a classe active de todos os botões
        tabButtons.forEach(button => {
            button.classList.remove('active', 'border-indigo-500', 'text-indigo-600');
            button.classList.add('border-transparent', 'text-gray-500');
        });
        
        // Mostrar o conteúdo da aba selecionada
        const selectedContent = document.getElementById(tabId);
        if (selectedContent) {
            selectedContent.classList.remove('hidden');
        }
        
        // Ativar o botão da aba selecionada
        const selectedButton = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
        if (selectedButton) {
            selectedButton.classList.remove('border-transparent', 'text-gray-500');
            selectedButton.classList.add('active', 'border-indigo-500', 'text-indigo-600');
        }
        
        // Salvar a aba selecionada no localStorage
        localStorage.setItem('selectedTab', tabId);
    }
    
    // Adicionar evento de clique para cada botão
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');
            showTab(tabId);
        });
    });
    
    // Verificar se há uma aba salva no localStorage
    const savedTab = localStorage.getItem('selectedTab');
    if (savedTab && document.getElementById(savedTab)) {
        showTab(savedTab);
    } else {
        // Se não houver, mostrar a primeira aba
        const firstTabId = tabButtons[0].getAttribute('data-tab');
        showTab(firstTabId);
    }
}

/**
 * Configura os radio buttons para tipo de pessoa (física/jurídica)
 */
function setupRadioButtons() {
    const inputs = document.querySelectorAll('input[name="is_juridico"]');
    const campoCpfCnpj = document.getElementById('campo_cpf_cnpj');
    const labelCpfCnpj = document.getElementById('label_cpf_cnpj');
    
    if (!inputs.length || !campoCpfCnpj || !labelCpfCnpj) return;
    
    inputs.forEach(input => {
        input.addEventListener('click', ({target}) => {
            if (target.value === '1') {
                labelCpfCnpj.innerText = 'CNPJ';
                campoCpfCnpj.name = 'cnpj_fornecedor';
                campoCpfCnpj.placeholder = 'CNPJ';
                setupCnpjMask();
            } else {
                labelCpfCnpj.innerText = 'CPF';
                campoCpfCnpj.name = 'cpf_fornecedor';
                campoCpfCnpj.placeholder = 'CPF';
                setupCpfMask();
            }
            
            // Limpar o campo
            campoCpfCnpj.value = '';
        });
    });
    
    // Configurar a máscara inicial com base no valor selecionado
    const isJuridico = document.querySelector('input[name="is_juridico"]:checked');
    if (isJuridico && isJuridico.value === '0') {
        setupCpfMask();
    } else {
        setupCnpjMask();
    }
}

/**
 * Configura máscaras para os campos de formulário
 */
function setupMasks() {
    setupTelefoneMasks();
    setupCepMask();
    
    // Configurar máscara CNPJ ou CPF dependendo da seleção atual
    const isJuridico = document.querySelector('input[name="is_juridico"]:checked');
    if (isJuridico && isJuridico.value === '0') {
        setupCpfMask();
    } else {
        setupCnpjMask();
    }
}

/**
 * Configura máscara para CPF
 */
function setupCpfMask() {
    const inputCPF = document.getElementById("campo_cpf_cnpj");
    if (!inputCPF) return;
    
    // Remover event listeners anteriores
    const oldInput = inputCPF.cloneNode(true);
    inputCPF.parentNode.replaceChild(oldInput, inputCPF);
    
    // Adicionar novo event listener
    oldInput.addEventListener("input", () => {
        let valor = oldInput.value;
        
        // Remove todos os caracteres que não sejam números
        valor = valor.replace(/\D/g, "");
        
        // Aplica a máscara no formato 000.000.000-00
        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        
        // Atualiza o valor do campo com a máscara
        oldInput.value = valor;
    });
    
    // Adicionar evento para buscar dados via API
    oldInput.addEventListener('blur', consultarViaCEP);
}

/**
 * Configura máscara para CNPJ
 */
function setupCnpjMask() {
    const inputCNPJ = document.getElementById("campo_cpf_cnpj");
    if (!inputCNPJ) return;
    
    // Remover event listeners anteriores
    const oldInput = inputCNPJ.cloneNode(true);
    inputCNPJ.parentNode.replaceChild(oldInput, inputCNPJ);
    
    // Adicionar novo event listener
    oldInput.addEventListener("input", () => {
        let valor = oldInput.value;
        
        // Remove todos os caracteres que não sejam números
        valor = valor.replace(/\D/g, "");
        
        // Aplica a máscara no formato 00.000.000/0000-00
        valor = valor.replace(/(\d{2})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d{4})/, "$1/$2");
        valor = valor.replace(/(\d{4})(\d{2})$/, "$1-$2");
        
        // Atualiza o valor do campo com a máscara
        oldInput.value = valor;
    });
    
    // Adicionar evento para buscar dados via API
    oldInput.addEventListener('blur', consultarCNPJ);
}

/**
 * Configura máscaras para telefones
 */
function setupTelefoneMasks() {
    const phoneInputs = [
        document.getElementById("telefone_fixo"),
        document.getElementById("telefone_celular"),
        document.getElementById("telefone_contato")
    ];
    
    phoneInputs.forEach(input => {
        if (!input) return;
        
        input.addEventListener("input", () => {
            let valor = input.value;
            
            // Remove todos os caracteres que não sejam números
            valor = valor.replace(/\D/g, "");
            
            // Aplica a máscara no formato adequado
            if (valor.length > 10) {
                // Formato para celular: (00) 00000-0000
                valor = valor.replace(/(\d{2})(\d)/, "($1) $2");
                valor = valor.replace(/(\d{5})(\d)/, "$1-$2");
            } else {
                // Formato para fixo: (00) 0000-0000
                valor = valor.replace(/(\d{2})(\d)/, "($1) $2");
                valor = valor.replace(/(\d{4})(\d)/, "$1-$2");
            }
            
            // Atualiza o valor do campo com a máscara
            input.value = valor;
        });
    });
}

/**
 * Configura máscara para CEP
 */
function setupCepMask() {
    const inputCEP = document.getElementById("cep");
    if (!inputCEP) return;
    
    inputCEP.addEventListener("input", () => {
        let valor = inputCEP.value;
        
        // Remove todos os caracteres que não sejam números
        valor = valor.replace(/\D/g, "");
        
        // Aplica a máscara no formato 00000-000
        valor = valor.replace(/^(\d{5})(\d)/, "$1-$2");
        
        // Atualiza o valor do campo com a máscara
        inputCEP.value = valor;
    });
    
    // Adicionar evento para consultar CEP
    inputCEP.addEventListener('blur', consultarViaCEP);
}

/**
 * Consulta dados do CNPJ
 */
function consultarCNPJ() {
    const cnpjInput = document.getElementById('campo_cpf_cnpj');
    if (!cnpjInput) return;
    
    const cnpj = cnpjInput.value.replace(/\D/g, '');
    if (cnpj.length !== 14) return;
    
    // Exemplo usando BrasilAPI - você pode substituir por outra API de sua preferência
    fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao consultar CNPJ');
            }
            return response.json();
        })
        .then(data => {
            // Preencher os campos com os dados retornados
            if (document.getElementById('nome_fornecedor')) 
                document.getElementById('nome_fornecedor').value = data.razao_social || '';
            
            if (document.getElementById('apelido_fornecedor')) 
                document.getElementById('apelido_fornecedor').value = data.nome_fantasia || '';
            
            if (document.getElementById('email'))
                document.getElementById('email').value = '';  // API normalmente não retorna email
            
            if (document.getElementById('rua'))
                document.getElementById('rua').value = data.logradouro || '';
            
            if (document.getElementById('numero'))
                document.getElementById('numero').value = data.numero || '';
            
            if (document.getElementById('complemento'))
                document.getElementById('complemento').value = data.complemento || '';
            
            if (document.getElementById('bairro'))
                document.getElementById('bairro').value = data.bairro || '';
            
            if (document.getElementById('nome_municipio'))
                document.getElementById('nome_municipio').value = data.municipio || '';
            
            if (document.getElementById('cep'))
                document.getElementById('cep').value = data.cep || '';
            
            if (document.getElementById('id_uf')) {
                const selectUF = document.getElementById('id_uf');
                const options = selectUF.options;
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value === data.uf) {
                        selectUF.selectedIndex = i;
                        break;
                    }
                }
            }
        })
        .catch(error => {
            console.error('Erro ao consultar CNPJ:', error);
        });
}

/**
 * Consulta dados do CEP via ViaCEP
 */
function consultarViaCEP() {
    const cepInput = document.getElementById('cep');
    if (!cepInput) return;
    
    const cep = cepInput.value.replace(/\D/g, '');
    if (cep.length !== 8) return;
    
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(response => {
            if (!response.ok) {
                throw new Error('CEP não encontrado');
            }
            return response.json();
        })
        .then(data => {
            if (data.erro) {
                throw new Error('CEP não encontrado');
            }
            
            // Preencher os campos com os dados retornados
            if (document.getElementById('rua'))
                document.getElementById('rua').value = data.logradouro || '';
            
            if (document.getElementById('bairro'))
                document.getElementById('bairro').value = data.bairro || '';
            
            if (document.getElementById('nome_municipio'))
                document.getElementById('nome_municipio').value = data.localidade || '';
            
            if (document.getElementById('id_uf')) {
                const selectUF = document.getElementById('id_uf');
                const options = selectUF.options;
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value === data.uf) {
                        selectUF.selectedIndex = i;
                        break;
                    }
                }
            }
        })
        .catch(error => {
            console.error('Erro ao consultar CEP:', error);
        });
}