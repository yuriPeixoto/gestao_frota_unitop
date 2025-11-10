// resources/js/app.js
import './bootstrap';
import Alpine from 'alpinejs';
import IMask from 'imask';
import select2 from 'select2';
import SignaturePad from 'signature_pad';
import './session-activity.js';
import initKmUpdater from './km-updater.js';
import './custom.js';
import './notifications.js'; // Sistema de notificações em tempo real
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';
import '../css/tooltip.css'; // Importando os estilos de tooltip personalizados


// Definir variáveis globais
window.Alpine = Alpine;
window.IMask = IMask;
window.SignaturePad = SignaturePad;
window.select2 = select2;
window.tippy = tippy;

// Iniciar o Alpine
document.addEventListener('DOMContentLoaded', () => {
    Alpine.start();
});

document.addEventListener('DOMContentLoaded', () => {
    tippy('.tooltip-trigger', {
        allowHTML: true,
        animation: 'shift-away',
    });
});

// Adicionar badges de atalho aos links do menu após a inicialização do Alpine
document.addEventListener('DOMContentLoaded', () => {
    // Verificar se os atalhos estão definidos
    console.log("DOMContentLoaded - Atalhos definidos:",
                Object.keys(window.keyboardShortcuts || {}).length, "grupos");

    // Adicionar badges
    setTimeout(() => {
        addShortcutBadges();
    }, 500);
});

// Função para adicionar badges aos links do menu
function addShortcutBadges() {
    console.log('Adicionando badges aos links do menu...');

    // Percorrer todos os grupos e itens
    for (const groupKey in window.keyboardShortcuts) {
        const group = window.keyboardShortcuts[groupKey];
        if (!group || !group.items) continue;

        for (const key in group.items) {
            const item = group.items[key];
            if (!item || !item.url) continue;

            // Encontrar links correspondentes
            const links = document.querySelectorAll(`a[href="${item.url}"]`);

            links.forEach(function(link) {
                // Pular se já tiver um badge
                if (link.dataset.shortcut) return;

                // Marcar link com o atalho
                link.dataset.shortcut = key;

                // Criar badge
                const badge = document.createElement('span');
                badge.className = 'ml-auto text-xs px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-mono';
                badge.textContent = `Ctrl+${key}`;

                // Adicionar o badge ao link
                if (link.classList.contains('flex') || link.classList.contains('inline-flex')) {
                    link.appendChild(badge);
                } else {
                    link.classList.add('flex', 'items-center', 'justify-between');

                    const originalContent = link.innerHTML;
                    const contentSpan = document.createElement('span');
                    contentSpan.innerHTML = originalContent;

                    link.innerHTML = '';
                    link.appendChild(contentSpan);
                    link.appendChild(badge);
                }
            });
        }
    }
}

// Adicionar detecção de atalhos de teclado
document.addEventListener('keydown', function (e) {
    // Ignora se o foco estiver em um campo de texto
    if (e.target.matches('input, textarea, [contenteditable]')) {
        return;
    }

    // Detectar Ctrl+número
    if (e.ctrlKey && !e.altKey && !e.shiftKey && /^\d+$/.test(e.key)) {
        e.preventDefault();

        // Iniciar ou continuar a sequência
        window.keySequence = window.keySequence || '';
        window.keySequence += e.key;

        // Limpar timeout anterior se existir
        if (window.keyTimeout) {
            clearTimeout(window.keyTimeout);
        }

        // Processar após um curto período
        window.keyTimeout = setTimeout(function () {
            processShortcut(window.keySequence);
            window.keySequence = '';
            window.keyTimeout = null;
        }, 1000);
    }
});

// Processa um atalho
function processShortcut(sequence) {
    console.log('Processando atalho:', sequence);

    let found = false;

    // Buscar em todos os grupos
    for (const groupKey in window.keyboardShortcuts) {
        const group = window.keyboardShortcuts[groupKey];
        if (!group || !group.items) continue;

        // Buscar nos itens do grupo
        for (const key in group.items) {
            if (key === sequence) {
                const url = group.items[key].url;
                if (url) {
                    console.log('Navegando para:', url);
                    window.location.href = url;
                    found = true;
                    break;
                }
            }
        }

        if (found) break;
    }

    // Feedback para atalho não encontrado
    if (!found) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded shadow-lg z-50 transition-opacity duration-300';
        toast.textContent = `Atalho Ctrl+${sequence} não encontrado`;
        document.body.appendChild(toast);

        setTimeout(function() {
            toast.style.opacity = '0';
            setTimeout(function () {
                document.body.removeChild(toast);
            }, 300);
        }, 2000);
    }
}

// Inicializa o componente de atualização de KM quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    // Verifica se estamos em uma página de edição de inconsistências
    const isInconsistenciasEditPage = document.querySelector('form[action*="inconsistencias.ats.update"]') ||
                                     document.querySelector('form[action*="inconsistencias.truckpag.update"]');

    if (isInconsistenciasEditPage) {
        initKmUpdater();
    }
});
