window.confirmAction = function(options) {
    if (!options) options = {};
    
    // Disparar evento para abrir o modal de confirmação
    window.dispatchEvent(new CustomEvent('open-confirmation-modal', {
        detail: options
    }));
};

document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('click', function(event) {
        let target = event.target;
        let confirmElement = null;
        
        while (target && target !== document.body) {
            if (target.hasAttribute('data-confirm')) {
                confirmElement = target;
                break;
            }
            target = target.parentElement;
        }
        
        if (confirmElement) {
            event.preventDefault();
            
            const title = confirmElement.getAttribute('data-title');
            const message = confirmElement.getAttribute('data-message');
            const confirmText = confirmElement.getAttribute('data-confirm-text');
            const cancelText = confirmElement.getAttribute('data-cancel-text');
            const route = confirmElement.getAttribute('data-route') || confirmElement.getAttribute('href');
            const method = confirmElement.getAttribute('data-method') || 'POST';
            const paramsAttr = confirmElement.getAttribute('data-params');
            
            let params = {};
            if (paramsAttr) {
                try {
                    params = JSON.parse(paramsAttr);
                } catch (e) {
                    console.error('Erro ao parsear data-params:', e);
                }
            }
            
            window.confirmAction({
                title: title,
                message: message,
                confirmText: confirmText,
                cancelText: cancelText,
                confirmRoute: route,
                method: method,
                params: params
            });
        }
    });
});