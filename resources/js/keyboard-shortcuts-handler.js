/**
 * Manipulador independente de atalhos de teclado
 * Este manipulador funciona independente do Alpine.js
 */

// Inicializar o manipulador de atalhos
export default function initializeKeyboardHandler() {
    console.log('[Shortcuts Handler] Inicializando manipulador independente de atalhos');
    
    let keySequence = '';
    let keyTimeout = null;
    
    // Garantir que os atalhos estejam disponíveis (em caso de erro de carregamento)
    if (typeof window.keyboardShortcuts !== 'object' || !window.keyboardShortcuts) {
        console.warn('[Shortcuts Handler] Atalhos não encontrados, inicializando com objeto vazio');
        window.keyboardShortcuts = window.keyboardShortcuts || {};
    }
    
    // Adicionar listener para atalhos de teclado
    window.addEventListener('keydown', (e) => {
        // Ignora se o foco estiver em um input ou textarea
        if (document.activeElement.tagName === 'INPUT' ||
            document.activeElement.tagName === 'TEXTAREA' ||
            document.activeElement.isContentEditable) return;

        // Atalho para exibir a lista de atalhos com Shift + ?
        if (e.key === '?' && e.shiftKey) {
            e.preventDefault();
            
            // Tentar acionar o modal via Alpine.js
            const shortcutsComponent = document.querySelector('[x-data]');
            if (shortcutsComponent && window.Alpine) {
                try {
                    const alpineData = window.Alpine.$data(shortcutsComponent);
                    if (alpineData && typeof alpineData.showShortcutHelper !== 'undefined') {
                        alpineData.showShortcutHelper = !alpineData.showShortcutHelper;
                        return;
                    }
                } catch (error) {
                    console.error('[Shortcuts Handler] Erro ao acessar dados Alpine:', error);
                }
            }
            
            console.log('[Shortcuts Handler] Componente Alpine não encontrado ou inacessível');
            return;
        }

        // Detectar e processar atalhos numéricos
        if (e.ctrlKey && !e.shiftKey && !e.altKey && /^\d$/.test(e.key)) {
            e.preventDefault();
            keySequence += e.key;

            if (keyTimeout) {
                clearTimeout(keyTimeout);
            }

            keyTimeout = setTimeout(() => {
                processShortcut(keySequence);
                keySequence = '';
                keyTimeout = null;
            }, 1000);
        } else if (!e.ctrlKey) {
            keySequence = '';
            if (keyTimeout) {
                clearTimeout(keyTimeout);
                keyTimeout = null;
            }
        }
    });
    
    // Processar um atalho
    function processShortcut(sequence) {
        console.log(`[Shortcuts Handler] Processando atalho: ${sequence}`);
        
        if (!window.keyboardShortcuts) {
            console.error('[Shortcuts Handler] Atalhos não disponíveis');
            return;
        }
        
        const shortcutNumber = parseInt(sequence, 10);
        let found = false;
        
        // Percorre os grupos
        for (const group in window.keyboardShortcuts) {
            const items = window.keyboardShortcuts[group]?.items || {};
            
            // E os itens em cada grupo
            for (const key in items) {
                if (key === sequence) {
                    const url = items[key]?.url;
                    if (url) {
                        console.log(`[Shortcuts Handler] Navegando para: ${url}`);
                        window.location.href = url;
                        found = true;
                        break;
                    }
                }
            }
            if (found) break;
        }
        
        // Feedback visual quando um atalho não é encontrado
        if (!found && shortcutNumber > 0) {
            showTemporaryMessage(`Atalho Ctrl+${sequence} não encontrado`);
        }
    }
    
    // Mostrar mensagem temporária
    function showTemporaryMessage(message) {
        console.log(`[Shortcuts Handler] ${message}`);
        
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded shadow-lg z-50 transition-opacity duration-300';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 2000);
    }
    
    // Adicionar badges de atalho aos links do menu
    setTimeout(addShortcutBadges, 500);
    
    // Adicionar badges aos links
    function addShortcutBadges() {
        console.log('[Shortcuts Handler] Adicionando badges de atalho aos links do menu');
        
        if (!window.keyboardShortcuts) {
            console.warn('[Shortcuts Handler] Atalhos não disponíveis para adicionar badges');
            return;
        }
        
        // Iterar pelos grupos e itens
        Object.keys(window.keyboardShortcuts).forEach(groupKey => {
            const group = window.keyboardShortcuts[groupKey];
            if (!group || !group.items) return;
            
            Object.keys(group.items).forEach(key => {
                const shortcut = group.items[key];
                if (!shortcut || !shortcut.url) return;
                
                const url = shortcut.url;
                
                // Encontrar links com esta URL
                const links = document.querySelectorAll(`a[href="${url}"]`);
                
                links.forEach(link => {
                    // Apenas adicionar se ainda não existe
                    if (!link.dataset.shortcut) {
                        link.dataset.shortcut = key;
                        
                        // Criar badge
                        const badge = document.createElement('span');
                        badge.className = 'ml-auto text-xs px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-mono';
                        badge.textContent = `Ctrl+${key}`;
                        
                        // Verificar se o link já tem a estrutura flex
                        if (link.classList.contains('flex') || link.classList.contains('inline-flex')) {
                            link.appendChild(badge);
                        } else {
                            link.classList.add('flex', 'items-center', 'justify-between');
                            
                            // Preservar conteúdo original
                            const originalContent = link.innerHTML;
                            const contentSpan = document.createElement('span');
                            contentSpan.innerHTML = originalContent;
                            
                            // Limpar e reconstruir
                            link.innerHTML = '';
                            link.appendChild(contentSpan);
                            link.appendChild(badge);
                        }
                    }
                });
            });
        });
    }
    
    return {
        processShortcut,
        showTemporaryMessage,
        addShortcutBadges
    };
}