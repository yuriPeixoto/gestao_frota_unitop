/**
 * Sistema de Notificações em Tempo Real
 * Utilizando Laravel Echo + Reverb para WebSocket
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Configurar Laravel Echo com Reverb
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8081,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8081,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

class NotificationManager {
    constructor() {
        this.notificationBadge = null;
        this.notificationDropdown = null;
        this.notificationList = null;
        this.unreadCount = 0;
        this.userId = null;
        this.isInitialized = false;
    }

    /**
     * Inicializa o sistema de notificações
     */
    init(userId) {
        if (this.isInitialized) {
            console.warn('NotificationManager já foi inicializado');
            return;
        }

        this.userId = userId;
        this.setupDOM();
        this.subscribeToChannels();
        this.loadInitialNotifications();
        this.setupEventListeners();
        this.isInitialized = true;

        console.log('✅ Sistema de notificações inicializado para usuário:', userId);
    }

    /**
     * Configura elementos do DOM
     */
    setupDOM() {
        this.notificationBadge = document.getElementById('notification-badge');
        this.notificationDropdown = document.getElementById('notification-dropdown');
        this.notificationList = document.getElementById('notification-list');
    }

    /**
     * Inscreve nos canais de WebSocket
     */
    subscribeToChannels() {
        // Canal pessoal do usuário
        window.Echo.private(`notifications.user.${this.userId}`)
            .notification((notification) => {
                console.log('📬 Nova notificação recebida:', notification);
                this.handleNewNotification(notification);
            });

        // Canal global
        window.Echo.private('notifications.global')
            .notification((notification) => {
                console.log('📢 Notificação global recebida:', notification);
                this.handleNewNotification(notification);
            });

        console.log('🔌 Inscrito nos canais de notificação');
    }

    /**
     * Carrega notificações iniciais
     */
    async loadInitialNotifications() {
        try {
            const response = await fetch('/api/notifications');

            // Se a resposta não for OK (ex: 401, 419), pode ser sessão expirada
            if (!response.ok) {
                // Se for erro de autenticação, redirecionar para login
                if (response.status === 401 || response.status === 419) {
                    console.warn('Sessão expirada, redirecionando para login...');
                    window.location.href = '/';
                    return;
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            this.unreadCount = data.unread_count;
            this.updateBadge();

            if (this.notificationList && data.notifications) {
                this.renderNotifications(data.notifications);
            }
        } catch (error) {
            console.error('Erro ao carregar notificações:', error);
        }
    }

    /**
     * Processa nova notificação recebida
     */
    handleNewNotification(notification) {
        // Incrementar contador
        this.unreadCount++;
        this.updateBadge();

        // Adicionar à lista
        if (this.notificationList) {
            this.prependNotification(notification);
        }

        // Mostrar toast/alerta
        this.showNotificationToast(notification);

        // Tocar som (opcional)
        this.playNotificationSound();
    }

    /**
     * Atualiza o badge de contagem
     */
    updateBadge() {
        if (!this.notificationBadge) return;

        if (this.unreadCount > 0) {
            this.notificationBadge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
            this.notificationBadge.classList.remove('hidden');
        } else {
            this.notificationBadge.classList.add('hidden');
        }
    }

    /**
     * Renderiza lista de notificações
     */
    renderNotifications(notifications) {
        if (!this.notificationList) return;

        this.notificationList.innerHTML = '';

        if (notifications.length === 0) {
            this.notificationList.innerHTML = `
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-bell-slash text-3xl mb-2"></i>
                    <p>Nenhuma notificação</p>
                </div>
            `;
            return;
        }

        // Adicionar notificações no final da lista para manter ordem (mais recente primeiro)
        notifications.forEach(notification => {
            this.appendNotification(notification);
        });
    }

    /**
     * Adiciona notificação ao início da lista (para notificações em tempo real)
     */
    prependNotification(notification) {
        if (!this.notificationList) return;

        const notificationEl = this.createNotificationElement(notification);

        // Remove mensagem de "nenhuma notificação" se existir
        const emptyMessage = this.notificationList.querySelector('.text-gray-500');
        if (emptyMessage) {
            emptyMessage.remove();
        }

        this.notificationList.insertBefore(notificationEl, this.notificationList.firstChild);
    }

    /**
     * Adiciona notificação ao final da lista (para lista inicial já ordenada)
     */
    appendNotification(notification) {
        if (!this.notificationList) return;

        const notificationEl = this.createNotificationElement(notification);

        // Remove mensagem de "nenhuma notificação" se existir
        const emptyMessage = this.notificationList.querySelector('.text-gray-500');
        if (emptyMessage) {
            emptyMessage.remove();
        }

        this.notificationList.appendChild(notificationEl);
    }

    /**
     * Cria elemento HTML de notificação
     */
    createNotificationElement(notification) {
        const div = document.createElement('div');
        div.className = `notification-item p-4 border-b hover:bg-gray-50 cursor-pointer ${notification.is_read ? 'opacity-60' : 'bg-blue-50'}`;
        div.dataset.notificationId = notification.id;
        div.dataset.notificationType = notification.type || 'targeted';

        const priorityColors = {
            urgent: 'text-red-600',
            high: 'text-orange-600',
            normal: 'text-blue-600',
            low: 'text-gray-600'
        };

        const iconColor = priorityColors[notification.priority] || 'text-blue-600';

        div.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-${notification.icon || 'bell'} ${iconColor} text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900">${notification.title}</p>
                    <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                    <p class="text-xs text-gray-400 mt-1">${this.formatDate(notification.created_at)}</p>
                </div>
                ${!notification.is_read ? '<div class="flex-shrink-0"><span class="inline-block w-2 h-2 bg-blue-600 rounded-full"></span></div>' : ''}
            </div>
        `;

        // Event listener para marcar como lida ao clicar
        div.addEventListener('click', () => {
            this.markAsRead(notification.id, notification.type || 'targeted');
            if (notification.url) {
                window.location.href = notification.url;
            }
        });

        return div;
    }

    /**
     * Marca notificação como lida
     */
    async markAsRead(notificationId, type = 'targeted') {
        try {
            const response = await fetch(`/api/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ type })
            });

            if (response.ok) {
                this.unreadCount = Math.max(0, this.unreadCount - 1);
                this.updateBadge();

                // Atualizar visual da notificação
                const notificationEl = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationEl) {
                    notificationEl.classList.remove('bg-blue-50');
                    notificationEl.classList.add('opacity-60');
                    const badge = notificationEl.querySelector('.bg-blue-600');
                    if (badge) badge.remove();
                }
            }
        } catch (error) {
            console.error('Erro ao marcar notificação como lida:', error);
        }
    }

    /**
     * Marca todas as notificações como lidas
     */
    async markAllAsRead() {
        try {
            const response = await fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                this.unreadCount = 0;
                this.updateBadge();

                // Atualizar visual de todas as notificações
                const notificationItems = document.querySelectorAll('.notification-item');
                notificationItems.forEach(item => {
                    item.classList.remove('bg-blue-50');
                    item.classList.add('opacity-60');
                    const badge = item.querySelector('.bg-blue-600');
                    if (badge) badge.remove();
                });
            }
        } catch (error) {
            console.error('Erro ao marcar todas como lidas:', error);
        }
    }

    /**
     * Exibe toast de notificação
     */
    showNotificationToast(notification) {
        // Usando SweetAlert2 se disponível
        if (window.Swal) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            Toast.fire({
                icon: this.getPriorityIcon(notification.priority),
                title: notification.title,
                text: notification.message
            });
        } else {
            // Fallback para console
            console.log('🔔 Nova notificação:', notification.title, notification.message);
        }
    }

    /**
     * Retorna ícone baseado na prioridade
     */
    getPriorityIcon(priority) {
        const icons = {
            urgent: 'error',
            high: 'warning',
            normal: 'info',
            low: 'info'
        };
        return icons[priority] || 'info';
    }

    /**
     * Toca som de notificação
     */
    playNotificationSound() {
        // Opcional: implementar som de notificação
        // const audio = new Audio('/sounds/notification.mp3');
        // audio.play().catch(e => console.log('Não foi possível tocar o som:', e));
    }

    /**
     * Formata data para exibição
     */
    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Agora';
        if (minutes < 60) return `${minutes}m atrás`;
        if (hours < 24) return `${hours}h atrás`;
        if (days < 7) return `${days}d atrás`;

        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    /**
     * Configura event listeners adicionais
     */
    setupEventListeners() {
        // Botão de marcar todas como lidas
        const markAllButton = document.getElementById('mark-all-read');
        if (markAllButton) {
            markAllButton.addEventListener('click', () => this.markAllAsRead());
        }

        // Polling de backup a cada 60 segundos (caso WebSocket caia)
        setInterval(() => this.refreshUnreadCount(), 60000);
    }

    /**
     * Atualiza contagem de não lidas via polling
     */
    async refreshUnreadCount() {
        try {
            const response = await fetch('/api/notifications/unread-count');

            // Se a resposta não for OK (ex: 401, 419), pode ser sessão expirada
            if (!response.ok) {
                // Se for erro de autenticação, redirecionar para login
                if (response.status === 401 || response.status === 419) {
                    console.warn('Sessão expirada, redirecionando para login...');
                    window.location.href = '/';
                    return;
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            this.unreadCount = data.count;
            this.updateBadge();
        } catch (error) {
            console.error('Erro ao atualizar contagem:', error);
        }
    }
}

// Exportar instância global
window.notificationManager = new NotificationManager();

// Auto-inicializar se usuário estiver logado
document.addEventListener('DOMContentLoaded', () => {
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    if (userIdMeta) {
        const userId = userIdMeta.content;
        window.notificationManager.init(userId);
    }
});
