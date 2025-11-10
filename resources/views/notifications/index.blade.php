<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Central de Notificações
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Histórico completo de notificações e alertas do sistema
                </p>
            </div>
            <button id="mark-all-read" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-check-double mr-2"></i>
                Marcar todas como lidas
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <i class="fas fa-bell text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <i class="fas fa-envelope text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Não Lidas</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['unread'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                                <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Urgentes</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['urgent'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                                <i class="fas fa-exclamation-circle text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Alta Prioridade</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['high'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros e Tabs -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <!-- Tabs de Status -->
                        <div class="flex items-center gap-2">
                            <a href="{{ route('notifications.index', ['status' => 'all', 'priority' => request('priority')]) }}"
                               class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $status === 'all' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                                <i class="fas fa-list mr-2"></i>Todas
                            </a>
                            <a href="{{ route('notifications.index', ['status' => 'unread', 'priority' => request('priority')]) }}"
                               class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $status === 'unread' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}">
                                <i class="fas fa-envelope mr-2"></i>Não Lidas
                                @if($stats['unread'] > 0)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-600 text-white">
                                        {{ $stats['unread'] }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('notifications.index', ['status' => 'read', 'priority' => request('priority')]) }}"
                               class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $status === 'read' ? 'bg-gray-100 text-gray-700' : 'text-gray-600 hover:bg-gray-100' }}">
                                <i class="fas fa-check mr-2"></i>Lidas
                            </a>
                        </div>

                        <!-- Filtro de Prioridade -->
                        <div class="flex items-center gap-2">
                            <label for="priority-filter" class="text-sm font-medium text-gray-700">Prioridade:</label>
                            <select id="priority-filter"
                                    onchange="window.location.href = '{{ route('notifications.index') }}?status={{ $status }}&priority=' + this.value"
                                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Todas</option>
                                <option value="urgent" {{ $priority === 'urgent' ? 'selected' : '' }}>🔴 Urgente</option>
                                <option value="high" {{ $priority === 'high' ? 'selected' : '' }}>🟠 Alta</option>
                                <option value="normal" {{ $priority === 'normal' ? 'selected' : '' }}>🔵 Normal</option>
                                <option value="low" {{ $priority === 'low' ? 'selected' : '' }}>⚪ Baixa</option>
                            </select>

                            @if($priority)
                                <a href="{{ route('notifications.index', ['status' => $status]) }}"
                                   class="text-sm text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Notificações -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="divide-y divide-gray-200">
                    @php
                        // Combinar e ordenar todas as notificações
                        $allNotifications = collect();

                        foreach($targetedNotifications as $notification) {
                            $allNotifications->push([
                                'type' => 'targeted',
                                'data' => $notification,
                                'created_at' => $notification->created_at
                            ]);
                        }

                        foreach($directNotifications as $notification) {
                            $allNotifications->push([
                                'type' => 'direct',
                                'data' => $notification,
                                'created_at' => $notification->created_at
                            ]);
                        }

                        $allNotifications = $allNotifications->sortByDesc('created_at')->values();
                    @endphp

                    @if($allNotifications->isEmpty())
                        <div class="p-12 text-center text-gray-500">
                            <i class="fas fa-bell-slash text-6xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium">
                                @if($status === 'unread')
                                    Nenhuma notificação não lida
                                @elseif($status === 'read')
                                    Nenhuma notificação lida
                                @else
                                    Nenhuma notificação
                                @endif
                            </p>
                            <p class="text-sm mt-2 text-gray-400">
                                @if($status === 'unread')
                                    Você está em dia! Todas as notificações foram lidas.
                                @else
                                    Não há notificações para exibir.
                                @endif
                            </p>
                        </div>
                    @else
                        @foreach($allNotifications as $item)
                            @php
                                $notification = $item['data'];
                                $isTargeted = $item['type'] === 'targeted';
                                $isRead = $isTargeted ? $notification->read_at !== null : $notification->read_at !== null;
                                $priority = $isTargeted ? $notification->priority : ($notification->data['priority'] ?? 'normal');
                                $icon = $isTargeted ? ($notification->icon ?? 'bell') : ($notification->data['icon'] ?? 'bell');
                                $title = $isTargeted ? $notification->title : ($notification->data['title'] ?? 'Notificação');
                                $message = $isTargeted ? $notification->message : ($notification->data['message'] ?? '');
                                $notifType = $isTargeted ? $notification->notification_type : ($notification->data['type'] ?? 'system');

                                // Cores baseadas na prioridade
                                $priorityColors = [
                                    'urgent' => 'text-red-600 bg-red-50',
                                    'high' => 'text-orange-600 bg-orange-50',
                                    'normal' => 'text-blue-600 bg-blue-50',
                                    'low' => 'text-gray-600 bg-gray-50'
                                ];

                                $iconColor = match($priority) {
                                    'urgent' => 'text-red-600',
                                    'high' => 'text-orange-600',
                                    'normal' => 'text-blue-600',
                                    default => 'text-gray-600'
                                };

                                $badgeColor = match($priority) {
                                    'urgent' => 'bg-red-100 text-red-800 ring-red-600/20',
                                    'high' => 'bg-orange-100 text-orange-800 ring-orange-600/20',
                                    'normal' => 'bg-blue-100 text-blue-800 ring-blue-600/20',
                                    default => 'bg-gray-100 text-gray-800 ring-gray-600/20'
                                };
                            @endphp

                            <div class="notification-item p-5 hover:bg-gray-50 cursor-pointer transition-colors {{ $isRead ? 'opacity-75' : 'bg-indigo-50/30' }}"
                                 data-notification-id="{{ $notification->id }}"
                                 data-notification-type="{{ $isTargeted ? 'targeted' : 'direct' }}">
                                <div class="flex items-start gap-4">
                                    <!-- Ícone -->
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $priorityColors[$priority] ?? 'bg-gray-50' }}">
                                            <i class="fas fa-{{ $icon }} {{ $iconColor }} text-lg"></i>
                                        </div>
                                    </div>

                                    <!-- Conteúdo -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <p class="font-semibold text-gray-900">{{ $title }}</p>

                                            <!-- Badge de Prioridade -->
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $badgeColor }}">
                                                @if($priority === 'urgent')
                                                    🔴 URGENTE
                                                @elseif($priority === 'high')
                                                    🟠 ALTA
                                                @elseif($priority === 'normal')
                                                    🔵 NORMAL
                                                @else
                                                    ⚪ BAIXA
                                                @endif
                                            </span>

                                            <!-- Badge de Tipo -->
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700">
                                                {{ strtoupper(str_replace(['_', '.'], ' ', $notifType)) }}
                                            </span>
                                        </div>

                                        <p class="text-sm text-gray-700 mt-1">{{ $message }}</p>

                                        <div class="flex items-center gap-4 mt-2">
                                            <p class="text-xs text-gray-500">
                                                <i class="far fa-clock mr-1"></i>
                                                {{ $notification->created_at->format('d/m/Y H:i') }}
                                                <span class="text-gray-400 ml-1">({{ $notification->created_at->diffForHumans() }})</span>
                                            </p>

                                            @if($isRead)
                                                <span class="text-xs text-gray-400">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Lida
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Indicador de não lida -->
                                    @if(!$isRead)
                                        <div class="flex-shrink-0">
                                            <span class="inline-block w-3 h-3 bg-indigo-600 rounded-full ring-4 ring-indigo-100"></span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Paginação -->
                @if(!$allNotifications->isEmpty())
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        <div class="flex flex-col gap-4">
                            <!-- Informação de Resultados -->
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-700">
                                    Mostrando <span class="font-medium">{{ $allNotifications->count() }}</span> notificações nesta página
                                </p>
                                <p class="text-xs text-gray-500">
                                    Total: <span class="font-medium">{{ $stats['total'] }}</span> notificações
                                </p>
                            </div>

                            <!-- Links de Paginação -->
                            @if($targetedNotifications->hasPages() || $directNotifications->hasPages())
                                <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                                    <!-- Paginação de Notificações Targeted -->
                                    @if($targetedNotifications->hasPages())
                                        <div class="flex-1">
                                            <p class="text-xs text-gray-600 mb-2 font-medium">Notificações do Sistema:</p>
                                            <div class="flex items-center gap-2">
                                                {{ $targetedNotifications->appends(['status' => $status, 'priority' => $priority])->onEachSide(1)->links('pagination::tailwind') }}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Paginação de Notificações Diretas -->
                                    @if($directNotifications->hasPages())
                                        <div class="flex-1">
                                            <p class="text-xs text-gray-600 mb-2 font-medium">Notificações Pessoais:</p>
                                            <div class="flex items-center gap-2">
                                                {{ $directNotifications->appends(['status' => $status, 'priority' => $priority])->onEachSide(1)->links('pagination::tailwind') }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Marcar como lida ao clicar
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', async function() {
                const notificationId = this.dataset.notificationId;
                const notificationType = this.dataset.notificationType;

                try {
                    const response = await fetch(`/api/notifications/${notificationId}/read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ type: notificationType })
                    });

                    if (response.ok) {
                        // Atualizar visual suavemente
                        this.classList.remove('bg-indigo-50/30');
                        this.classList.add('opacity-75');
                        const badge = this.querySelector('.bg-indigo-600');
                        if (badge) {
                            badge.style.opacity = '0';
                            setTimeout(() => badge.remove(), 300);
                        }

                        // Atualizar contador do header
                        if (window.notificationManager) {
                            window.notificationManager.refreshUnreadCount();
                        }
                    }
                } catch (error) {
                    console.error('Erro ao marcar notificação como lida:', error);
                }
            });
        });

        // Marcar todas como lidas
        document.getElementById('mark-all-read')?.addEventListener('click', async function() {
            if (!confirm('Deseja marcar todas as notificações como lidas?')) {
                return;
            }

            try {
                const response = await fetch('/api/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    // Recarregar página
                    window.location.reload();
                }
            } catch (error) {
                console.error('Erro ao marcar todas como lidas:', error);
                alert('Erro ao marcar notificações. Tente novamente.');
            }
        });
    </script>
    @endpush
</x-app-layout>