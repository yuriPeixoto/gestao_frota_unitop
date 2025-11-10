// resources/js/km-updater.js

/**
 * Inicializa o componente de atualização de KM
 */
export function initKmUpdater() {
    // Adiciona o botão de atualização KM nas telas de edição ATS e TruckPag
    addKmUpdaterButtonToForms();

    // Registra handler para eventos de requisição AJAX
    setupAjaxHandlers();
}

/**
 * Adiciona botão de atualização de KM nas telas de edição
 */
function addKmUpdaterButtonToForms() {
    // Para formulário ATS
    const atsForm = document.querySelector('form[action*="inconsistencias.ats.update"]');
    if (atsForm) {
        addKmUpdaterToForm(atsForm, 'km_abastecimento', 'data_inclusao');
    }

    // Para formulário TruckPag
    const truckpagForm = document.querySelector('form[action*="inconsistencias.truckpag.update"]');
    if (truckpagForm) {
        addKmUpdaterToForm(truckpagForm, 'hodometro', 'data_inclusao');
    }
}

/**
 * Adiciona botão e funcionalidade de atualização de KM no formulário específico
 */
function addKmUpdaterToForm(form, kmFieldName, dateFieldName) {
    // Localiza o campo de KM no formulário
    const kmField = form.querySelector(`#${kmFieldName}`);
    if (!kmField) return;

    // Obtém o elemento parent para adicionar o botão
    const kmFieldParent = kmField.closest('div');
    
    // Cria o botão de atualização
    const updateButton = document.createElement('button');
    updateButton.type = 'button';
    updateButton.id = 'btn-atualizar-km';
    updateButton.className = 'mt-2 inline-flex items-center px-3 py-1.5 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150';
    updateButton.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        Atualizar KM
    `;

    // Adiciona o botão após o campo KM
    if (kmFieldParent) {
        kmFieldParent.appendChild(updateButton);
    }

    // Registra evento de clique
    updateButton.addEventListener('click', function() {
        fetchKmInfo(form, kmFieldName, dateFieldName);
    });
}

/**
 * Busca informações de KM da API
 */
function fetchKmInfo(form, kmFieldName, dateFieldName) {
    // Obtém informações necessárias
    const veiculoSelect = form.querySelector('#id_veiculo');
    const kmField = form.querySelector(`#${kmFieldName}`);
    const kmAnteriorField = form.querySelector('#km_anterior');
    
    // Verificações iniciais
    if (!veiculoSelect || !veiculoSelect.value) {
        showAlert('Selecione um veículo primeiro');
        return;
    }

    // Obtém a data do elemento hidden ou da página
    let dataAbastecimento;
    const dataInclusaoField = form.querySelector(`#${dateFieldName}`);
    if (dataInclusaoField) {
        dataAbastecimento = dataInclusaoField.value;
    } else {
        // Tenta obter da página (possível valor fixo na página)
        const dataElement = document.querySelector('.data-abastecimento');
        if (dataElement) {
            dataAbastecimento = dataElement.dataset.value;
        }
    }

    if (!dataAbastecimento) {
        showAlert('Data de abastecimento não encontrada');
        return;
    }

    // Mostra loading
    const updateButton = form.querySelector('#btn-atualizar-km');
    if (updateButton) {
        updateButton.disabled = true;
        updateButton.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Buscando...
        `;
    }

    // Faz a requisição AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Cria FormData para enviar
    const formData = new FormData();
    formData.append('id_veiculo', veiculoSelect.value);
    formData.append('data_abastecimento', dataAbastecimento);

    // URL ajustada para a estrutura de rotas do projeto
    // Considerando que o projeto já tem o prefixo 'admin' no grupo principal de rotas
    const url = '/admin/inconsistencias/get-km-info';

    // Realize a requisição AJAX
    fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualiza campos
            if (kmField && data.data.hodometro) {
                kmField.value = data.data.hodometro;
            }
            
            if (kmAnteriorField && data.data.km_anterior) {
                kmAnteriorField.value = data.data.km_anterior;
            }

            showAlert('Informações de KM atualizadas com sucesso', 'success');
        } else {
            showAlert(data.message || 'Erro ao buscar informações de KM');
        }
    })
    .catch(error => {
        console.error('Erro ao buscar informações de KM:', error);
        showAlert('Erro ao buscar informações de KM');
    })
    .finally(() => {
        // Restaura botão
        if (updateButton) {
            updateButton.disabled = false;
            updateButton.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Atualizar KM
            `;
        }
    });
}

/**
 * Configura handlers para eventos AJAX
 */
function setupAjaxHandlers() {
    // Adiciona CSRF token em todas as requisições AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const oldXHROpen = window.XMLHttpRequest.prototype.open;
        window.XMLHttpRequest.prototype.open = function() {
            this.addEventListener('loadstart', function() {
                this.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                this.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            });
            oldXHROpen.apply(this, arguments);
        };
    }
}

/**
 * Exibe alerta na interface
 */
function showAlert(message, type = 'error') {
    // Verifica se já existe um toast
    let toast = document.getElementById('toast-notification');
    if (toast) {
        toast.remove();
    }

    // Cria elemento de toast
    toast = document.createElement('div');
    toast.id = 'toast-notification';
    toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-500 flex items-center ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
    
    toast.innerHTML = `
        <span class="mr-2">
            ${type === 'success' 
                ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>'
                : '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>'
            }
        </span>
        ${message}
    `;

    // Adiciona ao documento
    document.body.appendChild(toast);

    // Remove após 3 segundos
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

// Exporta função de inicialização para ser chamada no app.js
export default initKmUpdater;