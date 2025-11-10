/**
 * Gerenciador de documentos para sinistros
 * Versão melhorada com suporte para novo sistema de arquivos
 */
document.addEventListener('DOMContentLoaded', function () {
    let registrosDocTemporarios = [];
    let uploadInProgress = false;

    // Configurações
    const config = {
        uploadUrl: '/admin/sinistros/documentos/upload',
        deleteUrl: '/admin/sinistros/documentos/excluir',
        viewUrl: '/admin/sinistros/documentos/arquivo/', // Base URL para visualização
        maxFileSize: 10 * 1024 * 1024, // 10MB
        allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx']
    };

    // Inicializar - carregar documentos existentes
    const documentosJson = document.getElementById('documentos_json');
    if (documentosJson && documentosJson.value) {
        try {
            const documentos = JSON.parse(documentosJson.value);
            if (documentos && documentos.length > 0) {
                documentos.forEach(documento => {
                    registrosDocTemporarios.push({
                        data_inclusao: formatarDocData(documento.data_inclusao),
                        data_alteracao: documento.data_alteracao ? formatarDocData(documento.data_alteracao) : '',
                        documento: documento.documento || ''
                    });
                });
                atualizarDocTabela();
            } else {
                // Se não há documentos, mostrar mensagem
                const emptyMessage = document.getElementById('documentos-empty');
                if (emptyMessage) {
                    emptyMessage.classList.remove('hidden');
                }
            }
        } catch (e) {
            console.error('Erro ao processar JSON de documentos:', e);
            mostrarFeedback('Erro ao carregar documentos existentes.', 'error');
        }
    }

    // Função para adicionar documento
    function adicionarDocumento() {
        if (uploadInProgress) {
            mostrarFeedback('Upload em andamento. Aguarde a conclusão.', 'warning');
            return;
        }

        const documentoInput = document.querySelector('[name="documento"]');

        if (!documentoInput || !documentoInput.files || !documentoInput.files[0]) {
            mostrarFeedback('Selecione um documento para adicionar!', 'error');
            return;
        }

        // Obter o arquivo selecionado
        const file = documentoInput.files[0];

        // Validar arquivo
        if (!validarArquivo(file)) {
            return;
        }

        // Iniciar upload
        uploadArquivo(file);
    }

    // Validar arquivo
    function validarArquivo(file) {
        // Verificar tamanho
        if (file.size > config.maxFileSize) {
            mostrarFeedback(`O arquivo é muito grande. Tamanho máximo: ${config.maxFileSize / (1024 * 1024)}MB`, 'error');
            return false;
        }

        // Verificar extensão
        const extension = file.name.split('.').pop().toLowerCase();
        if (!config.allowedExtensions.includes(extension)) {
            mostrarFeedback(`Formato de arquivo não permitido. Use: ${config.allowedExtensions.join(', ')}`, 'error');
            return false;
        }

        return true;
    }

    // Upload de arquivo
    function uploadArquivo(file) {
        uploadInProgress = true;
        mostrarFeedback(`Enviando ${file.name}...`, 'info', true);

        // Criar FormData para o upload
        const formData = new FormData();
        formData.append('documento', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        // ID do sinistro (se estiver editando)
        const sinistroIdInput = document.querySelector('input[name="sinistro_id"]');
        if (sinistroIdInput && sinistroIdInput.value) {
            formData.append('sinistro_id', sinistroIdInput.value);
        }

        // Enviar o arquivo ao servidor via AJAX
        fetch(config.uploadUrl, {
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
                uploadInProgress = false;

                if (data.success) {
                    // Adicionar à lista temporária
                    const registro = {
                        data_inclusao: formatarDocData(),
                        data_alteracao: '',
                        documento: data.path || data.file_name
                    };

                    registrosDocTemporarios.push(registro);
                    atualizarDocTabela();
                    limparDocFormularioTemp();

                    mostrarFeedback('Documento adicionado com sucesso!', 'success');

                    // Atualiza o campo hidden
                    document.getElementById('documentos_json').value = JSON.stringify(registrosDocTemporarios);

                    // Esconder mensagem de "sem documentos" se estiver visível
                    const emptyMessage = document.getElementById('documentos-empty');
                    if (emptyMessage) {
                        emptyMessage.classList.add('hidden');
                    }
                } else {
                    mostrarFeedback(`Erro ao fazer upload: ${data.error}`, 'error');
                }
            })
            .catch(error => {
                uploadInProgress = false;
                console.error('Erro ao enviar documento:', error);
                mostrarFeedback(`Erro no upload: ${error.message}`, 'error');
            });
    }

    // Função para atualizar a tabela de documentos
    function atualizarDocTabela() {
        const tbody = document.getElementById('tabelaDocumentoBody');
        if (!tbody) {
            console.error('Elemento #tabelaDocumentoBody não encontrado');
            return;
        }

        const bloqueado = window.appConfig.bloquear;

        // Ordenar registros por data
        registrosDocTemporarios.sort((a, b) => new Date(a.data_inclusao) - new Date(b.data_inclusao));

        tbody.innerHTML = ''; // Limpa as linhas existentes

        registrosDocTemporarios.forEach((registro, index) => {
            const tr = document.createElement('tr');

            // Verificar se o nome do documento é um caminho completo
            let docName = registro.documento;
            if (docName.includes('/')) {
                docName = docName.split('/').pop();
            }

            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${registro.data_inclusao}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.data_alteracao}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${docName}
                    <a href="#" class="text-blue-500 ml-2" title="Visualizar" onclick="visualizarDocumento('${registro.documento}'); return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        ${bloqueado ? '' : `
                        <button type="button" onclick="excluirDocRegistro(${index})" class="text-red-600 hover:text-red-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Excluir
                        </button>
                        `}
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Função para limpar o campo de upload
    function limparDocFormularioTemp() {
        const input = document.querySelector('[name="documento"]');
        if (input) {
            input.value = '';
        }
    }

    // Função para excluir documento
    function excluirDocRegistro(index) {
        if (confirm('Tem certeza que deseja excluir este documento?')) {
            const documento = registrosDocTemporarios[index];

            // Se for um documento com caminho completo, tenta excluir do servidor
            if (documento.documento && (documento.documento.includes('/'))) {
                fetch(config.deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        path: documento.documento
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Arquivo removido do servidor');
                        } else {
                            console.warn('Não foi possível remover o arquivo do servidor:', data.error);
                        }

                        // Remover da lista de qualquer forma
                        registrosDocTemporarios.splice(index, 1);
                        atualizarDocTabela();
                        document.getElementById('documentos_json').value = JSON.stringify(registrosDocTemporarios);
                        mostrarFeedback('Documento removido', 'info');

                        // Mostrar mensagem de "sem documentos" se necessário
                        if (registrosDocTemporarios.length === 0) {
                            const emptyMessage = document.getElementById('documentos-empty');
                            if (emptyMessage) {
                                emptyMessage.classList.remove('hidden');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao comunicar com o servidor:', error);
                        mostrarFeedback('Erro ao excluir documento do servidor', 'error');
                    });
            } else {
                // Remover da lista apenas
                registrosDocTemporarios.splice(index, 1);
                atualizarDocTabela();
                document.getElementById('documentos_json').value = JSON.stringify(registrosDocTemporarios);
                mostrarFeedback('Documento removido', 'info');

                // Mostrar mensagem de "sem documentos" se necessário
                if (registrosDocTemporarios.length === 0) {
                    const emptyMessage = document.getElementById('documentos-empty');
                    if (emptyMessage) {
                        emptyMessage.classList.remove('hidden');
                    }
                }
            }
        }
    }

    // Função para visualizar documento
    function visualizarDocumento(docPath) {
        // Determinar se é um caminho temporário ou permanente
        let encodedPath = btoa(docPath);
        window.open(`${config.viewUrl}${encodedPath}`, '_blank');
    }

    // Função para formatar data
    function formatarDocData(data = new Date()) {
        const dataObj = new Date(data);
        const options = { day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'UTC' };
        return dataObj.toLocaleDateString('pt-BR', options);
    }

    // Função para mostrar feedback visual
    function mostrarFeedback(mensagem, tipo = 'info', persistente = false) {
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
        switch (tipo) {
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
            <span>${mensagem}</span>
            <button class="ml-4 text-white" onclick="this.parentNode.remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;

        document.body.appendChild(notification);

        // Remover automaticamente após um tempo, se não for persistente
        if (!persistente) {
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }
    }

    // Adicionar listener ao botão
    const addDocumentoBtn = document.querySelector('button[onclick="adicionarDocumento()"]');
    if (addDocumentoBtn) {
        addDocumentoBtn.removeAttribute('onclick');
        addDocumentoBtn.addEventListener('click', function (e) {
            e.preventDefault();
            adicionarDocumento();
        });
    }

    // Adicionar listener ao input de arquivo para mostrar nome
    const fileInput = document.querySelector('input[name="documento"]');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const fileName = this.files[0].name;
                const fileSize = (this.files[0].size / 1024).toFixed(2) + ' KB';
                mostrarFeedback(`Arquivo selecionado: ${fileName} (${fileSize})`, 'info');
            }
        });
    }

    // Tornar as funções acessíveis globalmente
    window.adicionarDocumento = adicionarDocumento;
    window.atualizarDocTabela = atualizarDocTabela;
    window.limparDocFormularioTemp = limparDocFormularioTemp;
    window.excluirDocRegistro = excluirDocRegistro;
    window.visualizarDocumento = visualizarDocumento;
    window.mostrarFeedback = mostrarFeedback;
});