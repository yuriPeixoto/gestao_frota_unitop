// Gerenciador de uploads para anexos de produtos
class UploadHandler {
    constructor() {
        this.uploadedFiles = new Map();
    }

    // Adiciona arquivo ao mapa de uploads
    addFile(productIndex, file) {
        this.uploadedFiles.set(productIndex, file);
    }

    // Remove arquivo do mapa
    removeFile(productIndex) {
        this.uploadedFiles.delete(productIndex);
    }

    // Obtém arquivo por índice
    getFile(productIndex) {
        return this.uploadedFiles.get(productIndex);
    }

    // Prepara FormData com todos os arquivos
    appendToFormData(formData) {
        this.uploadedFiles.forEach((file, index) => {
            formData.append(`anexo_produto_${index}`, file);
        });
    }

    // Limpa todos os arquivos
    clear() {
        this.uploadedFiles.clear();
    }
}

// Instância global do gerenciador
window.uploadHandler = new UploadHandler();

// Função para lidar com upload de anexo de produto
function handleProductAttachment() {
    const fileInput = document.getElementById("anexo_produto");
    const file = fileInput.files[0];

    if (file) {
        // Validação de tamanho (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert("Arquivo muito grande. Máximo permitido: 10MB");
            fileInput.value = "";
            return false;
        }

        // Validação de tipo
        const allowedTypes = [
            "image/jpeg",
            "image/jpg",
            "image/png",
            "image/gif",
            "application/pdf",
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        ];
        if (!allowedTypes.includes(file.type)) {
            alert(
                "Tipo de arquivo não permitido. Use: JPG, PNG, GIF, PDF, DOC, DOCX"
            );
            fileInput.value = "";
            return false;
        }

        return true;
    }
    return false;
}

// Função para submeter formulário com anexos
function submitFormWithAttachments() {
    const form = document.getElementById("requisicao-form");
    const formData = new FormData(form);

    // Adicionar anexos de produtos ao FormData
    window.uploadHandler.appendToFormData(formData);

    // Enviar via AJAX
    fetch(form.action, {
        method: form.method,
        body: formData,
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                alert("Erro: " + data.message);
            }
        })
        .catch((error) => {
            console.error("Erro:", error);
            alert("Erro ao enviar formulário");
        });
}
