// Arquivo de funcionalidades auxiliares para o sistema de anexos

// Função global para preview de anexos
function openAttachmentPreview(url, filename) {
    const modal = document.createElement("div");
    modal.className =
        "fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50";
    modal.onclick = function (e) {
        if (e.target === modal) {
            modal.remove();
        }
    };

    const extension = filename.split(".").pop().toLowerCase();
    const isImage = ["jpg", "jpeg", "png", "gif", "bmp", "webp"].includes(
        extension
    );

    modal.innerHTML = `
        <div class="relative max-w-4xl max-h-screen mx-4 bg-white rounded-lg overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">${filename}</h3>
                <div class="flex space-x-2">
                    <a href="${url}" target="_blank" download
                       class="inline-flex items-center px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                        </svg>
                        Download
                    </a>
                    <button onclick="this.closest('.fixed').remove()"
                            class="inline-flex items-center px-3 py-1 text-sm bg-gray-600 text-white rounded hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Fechar
                    </button>
                </div>
            </div>
            <div class="p-4" style="max-height: calc(100vh - 120px); overflow: auto;">
                ${
                    isImage
                        ? `<img src="${url}" alt="${filename}" class="max-w-full h-auto mx-auto">`
                        : `<div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500">Preview não disponível para este tipo de arquivo.</p>
                        <p class="text-sm text-gray-400 mt-2">Clique em "Download" para baixar o arquivo.</p>
                    </div>`
                }
            </div>
        </div>
    `;

    document.body.appendChild(modal);
}

// Tornar a função globalmente disponível
window.openAttachmentPreview = openAttachmentPreview;

// Validação de arquivo antes do upload
function validateFileUpload(fileInput, maxSizeMB = 10) {
    const file = fileInput.files[0];

    if (!file) return true;

    // Verificar tamanho
    const maxSize = maxSizeMB * 1024 * 1024; // Converter para bytes
    if (file.size > maxSize) {
        alert(`Arquivo muito grande! Tamanho máximo permitido: ${maxSizeMB}MB`);
        fileInput.value = "";
        return false;
    }

    // Tipos permitidos
    const allowedTypes = [
        "image/jpeg",
        "image/jpg",
        "image/png",
        "image/gif",
        "image/bmp",
        "image/webp",
        "application/pdf",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "text/plain",
    ];

    if (!allowedTypes.includes(file.type)) {
        alert(
            "Tipo de arquivo não permitido! Use: JPG, PNG, GIF, PDF, DOC, DOCX ou TXT"
        );
        fileInput.value = "";
        return false;
    }

    return true;
}

// Tornar função de validação globalmente disponível
window.validateFileUpload = validateFileUpload;

// Adicionar evento de validação aos inputs de arquivo quando o DOM carregar
document.addEventListener("DOMContentLoaded", function () {
    // Adicionar validação aos inputs de arquivo existentes
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach((input) => {
        input.addEventListener("change", function () {
            validateFileUpload(this);
        });
    });
});
