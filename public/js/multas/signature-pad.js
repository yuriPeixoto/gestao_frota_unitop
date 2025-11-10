// Script para corrigir o funcionamento do pad de assinatura
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar o SignaturePad após o Alpine ser inicializado
    document.addEventListener('alpine:initialized', function() {
        initializeSignaturePad();
    });
    
    // Inicialização independente caso o evento alpine:initialized não dispare
    setTimeout(function() {
        initializeSignaturePad();
    }, 500);
});

function initializeSignaturePad() {
    const canvas = document.getElementById('assinaturaPad');
    if (!canvas) return;

    try {
        // Verificar se já existe uma instância
        if (window.signaturePad) {
            window.signaturePad.clear();
            return;
        }

        // Ajustar dimensões do canvas
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);

        // Inicializar o SignaturePad com configurações melhoradas
        window.signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 1)',
            penColor: 'rgba(0, 0, 0, 0.8)',
            minWidth: 0.5,
            maxWidth: 2.5,
            throttle: 16, // Melhor responsividade
            velocityFilterWeight: 0.7
        });

        // Botão para limpar assinatura
        const clearButton = document.getElementById('clear-button');
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                if (window.signaturePad) {
                    window.signaturePad.clear();
                }
            });
        }

        // Capturar assinatura ao enviar o formulário
        const form = document.getElementById('multasForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const signatureField = document.getElementById('signature-data');
                const responsabilidade = document.getElementById('responsabilidade').value;
                
                // Apenas salvar assinatura se o campo estiver visível e a responsabilidade for 'Condutor'
                if (responsabilidade === 'Condutor' && window.signaturePad && !window.signaturePad.isEmpty()) {
                    signatureField.value = window.signaturePad.toDataURL();
                }
            });
        }

        // Monitorar mudanças no campo de responsabilidade para reinicializar o pad quando necessário
        const responsabilidadeField = document.getElementById('responsabilidade');
        if (responsabilidadeField) {
            responsabilidadeField.addEventListener('change', function() {
                // Dar tempo para Alpine atualizar a visibilidade
                setTimeout(function() {
                    if (responsabilidadeField.value === 'Condutor') {
                        // Reajustar o canvas se ele ficou visível
                        const canvas = document.getElementById('assinaturaPad');
                        if (canvas && canvas.offsetParent !== null) { // Verificar se o elemento está visível
                            const ratio = Math.max(window.devicePixelRatio || 1, 1);
                            canvas.width = canvas.offsetWidth * ratio;
                            canvas.height = canvas.offsetHeight * ratio;
                            canvas.getContext("2d").scale(ratio, ratio);
                            
                            if (window.signaturePad) {
                                window.signaturePad.clear();
                            }
                        }
                    }
                }, 100);
            });
        }

        // Carregar assinatura existente se disponível
        loadExistingSignature();
        
        console.log("SignaturePad inicializado com sucesso");
    } catch (error) {
        console.error('Erro ao inicializar SignaturePad:', error);
    }
}

function loadExistingSignature() {
    // Esta função será chamada apenas na edição, quando já existe uma assinatura
    const signatureElement = document.getElementById('existing-signature-url');
    if (!signatureElement || !window.signaturePad) return;
    
    const signatureUrl = signatureElement.value;
    if (!signatureUrl) return;
    
    const image = new Image();
    image.onload = function() {
        const canvas = document.getElementById('assinaturaPad');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
    };
    image.src = signatureUrl;
}