/**
 * Classe SinistroDocumentUploader
 * Versão orientada a objetos para gerenciamento de documentos de sinistros
 */
class SinistroDocumentUploader {
    /**
     * Inicializa o uploader de documentos
     * @param {Object} options Opções de configuração
     */
    constructor(options = {}) {
        // Opções padrão
        this.options = {
            // Seletores
            fileInputSelector: 'input[name="documento"]',
            uploadButtonSelector: 'button[data-action="upload-document"]',
            tableBodySelector: '#tabelaDocumentoBody',
            hiddenInputSelector: '#documentos_json',
            emptyMessageSelector: '#documentos-empty',
            
            // URLs
            uploadUrl: '/admin/sinistros/documentos/upload',
            deleteUrl: '/admin/sinistros/documentos/excluir',
            viewUrl: '/admin/sinistros/documentos/arquivo/',
            
            // Configurações
            maxFileSize: 10 * 1024 * 1024, // 10MB
            allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'],
            notificationDuration: 3000, // 3 segundos
            
            // ID do sinistro (opcional)
            sinistroId: null
        };
        
        // Mesclar opções fornecidas
        this.options = { ...this.options, ...options };
        
        // Estado
        this.documents = [];
        this.isUploading = false;
        
        // Inicializar
        this.init();
    }
    
    /**
     * Inicializa o sistema
     */
    init() {
        // Carregar documentos existentes
        this.loadExistingDocuments();
        
        // Configurar listeners
        this.setupEventListeners();
        
        // Verificar se há documentos para mostrar/esconder mensagem
        this.updateEmptyMessage();
    }
    
    /**
     * Carrega documentos existentes do input hidden
     */
    loadExistingDocuments() {
        const input = document.querySelector(this.options.hiddenInputSelector);
        if (!input || !input.value) return;
        
        try {
            this.documents = JSON.parse(input.value);
            this.updateTable();
        } catch (e) {
            console.error('Erro ao carregar documentos existentes:', e);
            this.showNotification('Erro ao carregar documentos existentes', 'error');
        }
    }
    
    /**
     * Configura os listeners de eventos
     */
    setupEventListeners() {
        // Botão de upload
        const uploadButton = document.querySelector(this.options.uploadButtonSelector);
        if (uploadButton) {
            uploadButton.addEventListener('click', this.handleUpload.bind(this));
        }
        
        // Botão com onclick (compatibilidade com versão antiga)
        const legacyButton = document.querySelector('button[onclick="adicionarDocumento()"]');
        if (legacyButton) {
            legacyButton.removeAttribute('onclick');
            legacyButton.addEventListener('click', this.handleUpload.bind(this));
        }
        
        // Input de arquivo
        const fileInput = document.querySelector(this.options.fileInputSelector);
        if (fileInput) {
            fileInput.addEventListener('change', this.handleFileSelection.bind(this));
        }
        
        // Expor a função globalmente para compatibilidade
        window.adicionarDocumento = this.handleUpload.bind(this);
        window.excluirDocRegistro = this.deleteDocument.bind(this);
        window.visualizarDocumento = this.viewDocument.bind(this);
    }
    
    /**
     * Manipula a seleção de arquivo
     * @param {Event} event Evento change do input
     */
    handleFileSelection(event) {
        const file = event.target.files?.[0];
        if (!file) return;
        
        const fileSize = (file.size / 1024).toFixed(2);
        this.showNotification(
            `Arquivo selecionado: ${file.name} (${fileSize} KB)`, 
            'info'
        );
        
        // Verificar o arquivo
        this.validateFile(file);
    }
    
    /**
     * Valida um arquivo
     * @param {File} file Arquivo a ser validado
     * @returns {boolean} Se o arquivo é válido
     */
    validateFile(file) {
        // Verificar tamanho
        if (file.size > this.options.maxFileSize) {
            this.showNotification(
                `O arquivo é muito grande. Tamanho máximo: ${this.options.maxFileSize / (1024 * 1024)}MB`,
                'error'
            );
            return false;
        }
        
        // Verificar extensão
        const extension = file.name.split('.').pop().toLowerCase();
        if (!this.options.allowedExtensions.includes(extension)) {
            this.showNotification(
                `Formato de arquivo não permitido. Use: ${this.options.allowedExtensions.join(', ')}`,
                'error'
            );
            return false;
        }
        
        return true;
    }
    
    /**
     * Manipula o upload de documento
     */
    handleUpload() {
        if (this.isUploading) {
            this.showNotification('Upload em andamento, aguarde...', 'warning');
            return;
        }
        
        const fileInput = document.querySelector(this.options.fileInputSelector);
        if (!fileInput || !fileInput.files || !fileInput.files[0]) {
            this.showNotification('Selecione um arquivo para fazer upload', 'error');
            return;
        }
        
        const file = fileInput.files[0];
        if (!this.validateFile(file)) {
            return;
        }
        
        // Fazer upload
        this.uploadFile(file);
    }
    
    /**
     * Faz upload de um arquivo
     * @param {File} file Arquivo a ser enviado
     */
    uploadFile(file) {
        this.isUploading = true;
        this.showNotification(`Enviando ${file.name}...`, 'info', true);
        
        // Preparar dados para envio
        const formData = new FormData();
        formData.append('documento', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        
        // Adicionar ID do sinistro se disponível
        if (this.options.sinistroId) {
            formData.append('sinistro_id', this.options.sinistroId);
        }
        
        // Enviar requisição
        fetch(this.options.uploadUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            this.isUploading = false;
            
            if (data.success) {
                // Adicionar documento à lista
                const doc = {
                    data_inclusao: this.formatDate(new Date()),
                    data_alteracao: '',
                    documento: data.path || data.file_name
                };
                
                this.documents.push(doc);
                this.updateTable();
                this.updateHiddenInput();
                this.clearFileInput();
                
                this.showNotification('Documento adicionado com sucesso', 'success');
            } else {
                this.showNotification(`Erro ao fazer upload: ${data.error || 'Erro desconhecido'}`, 'error');
            }
        })
        .catch(error => {
            this.isUploading = false;
            console.error('Erro no upload:', error);
            this.showNotification(`Erro ao fazer upload: ${error.message}`, 'error');
        });
    }
    
    /**
     * Exclui um documento
     * @param {number} index Índice do documento na lista
     */
    deleteDocument(index) {
        if (!confirm('Tem certeza que deseja excluir este documento?')) {
            return;
        }
        
        const doc = this.documents[index];
        
        // Se for um documento com caminho, tentar excluir do servidor
        if (doc.documento && doc.documento.includes('/')) {
            this.showNotification('Excluindo documento...', 'info', true);
            
            fetch(this.options.deleteUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ path: doc.documento })
            })
            .then(response => response.json())
            .then(data => {
                // Remover da lista independente do resultado da exclusão do arquivo
                this.documents.splice(index, 1);
                this.updateTable();
                this.updateHiddenInput();
                this.updateEmptyMessage();
                
                if (data.success) {
                    this.showNotification('Documento excluído com sucesso', 'success');
                } else {
                    this.showNotification(`Registro excluído, mas não foi possível remover o arquivo físico: ${data.error || 'Erro desconhecido'}`, 'warning');
                }
            })
            .catch(error => {
                console.error('Erro ao excluir documento:', error);
                this.showNotification(`Erro ao excluir documento: ${error.message}`, 'error');
            });
        } else {
            // Apenas remover da lista
            this.documents.splice(index, 1);
            this.updateTable();
            this.updateHiddenInput();
            this.updateEmptyMessage();
            this.showNotification('Documento removido', 'success');
        }
    }
    
    /**
     * Visualiza um documento
     * @param {string} path Caminho do documento
     */
    viewDocument(path) {
        const encodedPath = btoa(path);
        window.open(`${this.options.viewUrl}${encodedPath}`, '_blank');
    }
    
    /**
     * Atualiza a tabela de documentos
     */
    updateTable() {
        const tbody = document.querySelector(this.options.tableBodySelector);
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        this.documents.forEach((doc, index) => {
            const tr = document.createElement('tr');
            
            // Extrair nome do arquivo do caminho
            let fileName = doc.documento;
            if (fileName.includes('/')) {
                fileName = fileName.split('/').pop();
            }
            
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${doc.data_inclusao || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${doc.data_alteracao || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${fileName}
                    <a href="#" class="text-blue-500 ml-2" title="Visualizar" data-action="view-document" data-index="${index}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <button type="button" class="text-red-600 hover:text-red-800 flex items-center" data-action="delete-document" data-index="${index}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        Excluir
                    </button>
                </td>
            `;
            
            tbody.appendChild(tr);
        });
        
        // Adicionar eventos aos botões da tabela
        this.setupTableEvents();
    }
    
    /**
     * Configura eventos para botões na tabela
     */
    setupTableEvents() {
        // Botões de exclusão
        document.querySelectorAll('[data-action="delete-document"]').forEach(button => {
            button.addEventListener('click', (e) => {
                const index = parseInt(e.currentTarget.dataset.index);
                this.deleteDocument(index);
            });
        });
        
        // Botões de visualização
        document.querySelectorAll('[data-action="view-document"]').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const index = parseInt(e.currentTarget.dataset.index);
                const doc = this.documents[index];
                this.viewDocument(doc.documento);
            });
        });
    }
    
    /**
     * Atualiza o input hidden com a lista de documentos
     */
    updateHiddenInput() {
        const input = document.querySelector(this.options.hiddenInputSelector);
        if (input) {
            input.value = JSON.stringify(this.documents);
        }
    }
    
    /**
     * Atualiza a mensagem de "sem documentos"
     */
    updateEmptyMessage() {
        const emptyMessage = document.querySelector(this.options.emptyMessageSelector);
        if (!emptyMessage) return;
        
        if (this.documents.length === 0) {
            emptyMessage.classList.remove('hidden');
        } else {
            emptyMessage.classList.add('hidden');
        }
    }
    
    /**
     * Limpa o input de arquivo
     */
    clearFileInput() {
        const input = document.querySelector(this.options.fileInputSelector);
        if (input) {
            input.value = '';
        }
    }
    
    /**
     * Formata data como DD/MM/YYYY
     * @param {Date} date Data a ser formatada
     * @returns {string} Data formatada
     */
    formatDate(date) {
        const dia = String(date.getDate()).padStart(2, '0');
        const mes = String(date.getMonth() + 1).padStart(2, '0');
        const ano = date.getFullYear();
        return `${dia}/${mes}/${ano}`;
    }
    
    /**
     * Mostra uma notificação visual
     * @param {string} message Mensagem a ser exibida
     * @param {string} type Tipo de notificação (success, error, warning, info)
     * @param {boolean} persistent Se a notificação não deve desaparecer automaticamente
     */
    showNotification(message, type = 'info', persistent = false) {
        // Remover notificação existente
        const existingNotification = document.getElementById('doc-notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Criar elemento de notificação
        const notification = document.createElement('div');
        notification.id = 'doc-notification';
        notification.classList.add('fixed', 'top-4', 'right-4', 'p-4', 'rounded', 'shadow-lg', 'z-50', 'flex', 'items-center');
        
        // Definir classes com base no tipo
        let bgColor, textColor, icon;
        switch (type) {
            case 'success':
                bgColor = 'bg-green-500';
                textColor = 'text-white';
                icon = `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
                break;
            case 'error':
                bgColor = 'bg-red-500';
                textColor = 'text-white';
                icon = `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
                break;
            case 'warning':
                bgColor = 'bg-yellow-500';
                textColor = 'text-white';
                icon = `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`;
                break;
            case 'info':
            default:
                bgColor = 'bg-blue-500';
                textColor = 'text-white';
                icon = `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
                break;
        }
        
        notification.classList.add(bgColor, textColor);
        notification.innerHTML = `
            ${icon}
            <span>${message}</span>
            <button class="ml-4 text-white" onclick="this.parentNode.remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        document.body.appendChild(notification);
        
        // Remover automaticamente após um tempo, se não for persistente
        if (!persistent) {
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, this.options.notificationDuration);
        }
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se estamos em uma página de sinistro
    if (document.getElementById('documentos_json')) {
        // Obter ID do sinistro, se disponível
        let sinistroId = null;
        const sinistroIdInput = document.querySelector('input[name="sinistro_id"]');
        if (sinistroIdInput) {
            sinistroId = sinistroIdInput.value;
        } else {
            // Tentar extrair da URL (para páginas de edição)
            const urlMatch = window.location.pathname.match(/\/sinistros\/(\d+)\/editar/);
            if (urlMatch && urlMatch[1]) {
                sinistroId = urlMatch[1];
            }
        }
        
        // Inicializar o uploader
        window.documentUploader = new SinistroDocumentUploader({
            sinistroId: sinistroId
        });
        
        // Expor as funções necessárias globalmente para compatibilidade
        window.adicionarDocumento = function() {
            window.documentUploader.handleUpload();
        };
        
        // Compatibilidade com botões existentes
        const uploadButton = document.querySelector('button[onclick="adicionarDocumento()"]');
        if (uploadButton) {
            uploadButton.removeAttribute('onclick');
            uploadButton.setAttribute('data-action', 'upload-document');
        }
    }
});