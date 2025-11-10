/**
 * Gerenciador de sessão e inatividade para Laravel
 * 
 * Este script gerencia:
 * 1. Detecção de inatividade e logout automático
 * 2. Persistência de sessão entre abas/janelas
 * 3. Tratamento de fechamento de página
 */
document.addEventListener('DOMContentLoaded', function() {
    const inactivityTimeout = 24 * 60 * 60 * 1000;
    const warningTime = 22 * 60 * 60 * 1000;
    const sessionKey = 'laravel_session_active';
    const tokenKey = 'laravel_session_token';
    let inactivityTimer;
    let warningTimer;
    
    initSessionMonitor();
    
    /**
     * Inicializa o monitoramento de sessão
     */
    function initSessionMonitor() {
        checkSessionStatus();
        
        const activityEvents = [
            'mousedown', 'mousemove', 'keypress', 
            'scroll', 'touchstart', 'click', 'keydown'
        ];
        
        activityEvents.forEach(event => {
            document.addEventListener(event, resetInactivityTimer, { passive: true });
        });
        
        document.addEventListener('visibilitychange', handleVisibilityChange);
        
        window.addEventListener('beforeunload', handleBeforeUnload);
        
        resetInactivityTimer();
        
        setInterval(checkSessionStatus, 60 * 1000);
    }
    
    /**
     * Reinicia o temporizador de inatividade
     */
    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        clearTimeout(warningTimer);
        
        markSessionAsActive();
        
        warningTimer = setTimeout(function() {
            showInactivityWarning();
        }, inactivityTimeout - warningTime);
        
        inactivityTimer = setTimeout(function() {
            logoutDueToInactivity();
        }, inactivityTimeout);
    }
    
    /**
     * Verifica o status da sessão
     */
    function checkSessionStatus() {
        const tokenValue = localStorage.getItem(tokenKey);
        const lastActivity = localStorage.getItem(sessionKey);
        
        if (!lastActivity || !tokenValue) {
            markSessionAsActive();
            return;
        }
        
        const now = new Date().getTime();
        const lastActivityTime = parseInt(lastActivity);
        
        if (now - lastActivityTime > inactivityTimeout) {
            fetch('/api/check-session', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    redirectToLogin();
                } else {
                    markSessionAsActive();
                }
            })
            .catch(error => {
                console.error('Erro ao verificar sessão:', error);
            });
        }
    }
    
    /**
     * Marca a sessão como ativa no armazenamento local
     */
    function markSessionAsActive() {
        localStorage.setItem(sessionKey, new Date().getTime().toString());
        localStorage.setItem(tokenKey, getCsrfToken());
    }
    
    /**
     * Tratamento de mudança de visibilidade da página/aba
     */
    function handleVisibilityChange() {
        if (document.visibilityState === 'visible') {
            checkSessionStatus();
            resetInactivityTimer();
        }
    }
    
    /**
     * Tratamento de evento de fechamento da página
     */
    function handleBeforeUnload(event) {
        localStorage.setItem('laravel_session_closed', new Date().getTime().toString());
    }
    
    /**
     * Mostra aviso de que a sessão vai expirar
     */
    function showInactivityWarning() {
        let warningElement = document.getElementById('session-timeout-warning');
        
        if (!warningElement) {
            warningElement = document.createElement('div');
            warningElement.id = 'session-timeout-warning';
            warningElement.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background-color: #f8d7da;
                color: #721c24;
                padding: 15px;
                border-radius: 4px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                z-index: 9999;
                font-family: sans-serif;
            `;
            
            const closeBtn = document.createElement('button');
            closeBtn.textContent = '×';
            closeBtn.style.cssText = `
                position: absolute;
                top: 5px;
                right: 10px;
                background: none;
                border: none;
                font-size: 20px;
                cursor: pointer;
                color: #721c24;
            `;
            closeBtn.onclick = function() {
                document.body.removeChild(warningElement);
            };
            
            const title = document.createElement('h4');
            title.textContent = 'Aviso de Inatividade';
            title.style.margin = '0 0 10px 0';
            
            const message = document.createElement('p');
            message.textContent = 'Sua sessão expirará em aproximadamente 1 minuto por inatividade.';
            message.style.margin = '0 0 10px 0';
            
            const continueBtn = document.createElement('button');
            continueBtn.textContent = 'Continuar Conectado';
            continueBtn.style.cssText = `
                background-color: #28a745;
                color: white;
                border: none;
                padding: 5px 10px;
                border-radius: 3px;
                cursor: pointer;
            `;
            continueBtn.onclick = function() {
                document.body.removeChild(warningElement);
                resetInactivityTimer();
            };
            
            warningElement.appendChild(closeBtn);
            warningElement.appendChild(title);
            warningElement.appendChild(message);
            warningElement.appendChild(continueBtn);
            
            document.body.appendChild(warningElement);
        }
    }
    
    /**
     * Logout por inatividade
     */
    function logoutDueToInactivity() {
        const warningElement = document.getElementById('session-timeout-warning');
        if (warningElement) {
            document.body.removeChild(warningElement);
        }
        
        localStorage.removeItem(sessionKey);
        localStorage.removeItem(tokenKey);
        
        fetch('/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .finally(() => {
            redirectToLogin('Sua sessão expirou devido à inatividade.');
        });
    }
    
    /**
     * Redireciona para a página de login
     */
    function redirectToLogin(message) {
        const loginUrl = '/login' + (message ? '?message=' + encodeURIComponent(message) : '');
        window.location.href = loginUrl;
    }
    
    /**
     * Obtém o token CSRF da meta tag ou do cookie
     */
    function getCsrfToken() {
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        
        if (metaToken) {
            return metaToken.getAttribute('content');
        }
        
        const tokenCookie = document.cookie
            .split('; ')
            .find(row => row.startsWith('XSRF-TOKEN='));
        
        if (tokenCookie) {
            return decodeURIComponent(tokenCookie.split('=')[1]);
        }
        
        return null;
    }
});