<?php

namespace App\Services;

use App\Models\NotificationTarget;
use App\Modules\Configuracoes\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Serviço central para envio e gerenciamento de notificações
 */
class NotificationService
{
    /**
     * Envia notificação para usuários específicos
     */
    public function sendToUsers(
        array $userIds,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal',
        ?string $icon = 'bell',
        ?string $color = 'blue'
    ): NotificationTarget {
        return $this->createNotification(
            type: $type,
            title: $title,
            message: $message,
            targetType: 'user',
            targetIds: $userIds,
            data: $data,
            priority: $priority,
            icon: $icon,
            color: $color
        );
    }

    /**
     * Envia notificação para departamentos específicos
     */
    public function sendToDepartments(
        array $departmentIds,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal',
        ?string $icon = 'bell',
        ?string $color = 'blue'
    ): NotificationTarget {
        return $this->createNotification(
            type: $type,
            title: $title,
            message: $message,
            targetType: 'department',
            targetIds: $departmentIds,
            data: $data,
            priority: $priority,
            icon: $icon,
            color: $color
        );
    }

    /**
     * Envia notificação para roles específicas
     */
    public function sendToRoles(
        array $roleIds,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal',
        ?string $icon = 'bell',
        ?string $color = 'blue'
    ): NotificationTarget {
        return $this->createNotification(
            type: $type,
            title: $title,
            message: $message,
            targetType: 'role',
            targetIds: $roleIds,
            data: $data,
            priority: $priority,
            icon: $icon,
            color: $color
        );
    }

    /**
     * Envia notificação para cargos específicos
     */
    public function sendToCargos(
        array $cargoIds,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal',
        ?string $icon = 'bell',
        ?string $color = 'blue'
    ): NotificationTarget {
        return $this->createNotification(
            type: $type,
            title: $title,
            message: $message,
            targetType: 'cargo',
            targetIds: $cargoIds,
            data: $data,
            priority: $priority,
            icon: $icon,
            color: $color
        );
    }

    /**
     * Envia notificação para filiais específicas
     */
    public function sendToFiliais(
        array $filialIds,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal',
        ?string $icon = 'bell',
        ?string $color = 'blue'
    ): NotificationTarget {
        return $this->createNotification(
            type: $type,
            title: $title,
            message: $message,
            targetType: 'filial',
            targetIds: $filialIds,
            data: $data,
            priority: $priority,
            icon: $icon,
            color: $color
        );
    }

    /**
     * Envia notificação para todos os usuários
     */
    public function sendToAll(
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal',
        ?string $icon = 'bell',
        ?string $color = 'blue'
    ): NotificationTarget {
        return $this->createNotification(
            type: $type,
            title: $title,
            message: $message,
            targetType: 'all',
            targetIds: [],
            data: $data,
            priority: $priority,
            icon: $icon,
            color: $color
        );
    }

    /**
     * Cria uma notificação no banco e faz broadcast se necessário
     */
    protected function createNotification(
        string $type,
        string $title,
        string $message,
        string $targetType,
        array $targetIds,
        array $data = [],
        string $priority = 'normal',
        ?string $icon = 'bell',
        ?string $color = 'blue',
        ?\DateTime $scheduledAt = null,
        ?\DateTime $expiresAt = null
    ): NotificationTarget {
        // Mapear targetIds para o campo correto
        $targetField = match($targetType) {
            'user' => 'target_user_ids',
            'department' => 'target_department_ids',
            'role' => 'target_role_ids',
            'cargo' => 'target_cargo_ids',
            'filial' => 'target_filial_ids',
            default => null,
        };

        $notificationData = [
            'notification_type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'icon' => $icon,
            'color' => $color,
            'priority' => $priority,
            'target_type' => $targetType,
            'scheduled_at' => $scheduledAt,
            'expires_at' => $expiresAt,
            'is_active' => true,
            'created_by' => auth()->id(),
        ];

        if ($targetField) {
            $notificationData[$targetField] = $targetIds;
        }

        $notification = NotificationTarget::create($notificationData);

        // Fazer broadcast imediatamente se não for agendada
        if (!$scheduledAt || $scheduledAt <= now()) {
            $this->broadcastNotification($notification);
        }

        return $notification;
    }

    /**
     * Faz broadcast de uma notificação para os usuários relevantes
     */
    public function broadcastNotification(NotificationTarget $notification): void
    {
        try {
            $users = $this->getUsersForNotification($notification);

            if ($users->isEmpty()) {
                Log::info("Nenhum usuário encontrado para notificação #{$notification->id}");
                return;
            }

            // Enviar via sistema de notificações do Laravel
            Notification::send($users, new SystemNotification($notification));

            // Marcar como broadcast enviado
            $notification->update([
                'is_broadcasted' => true,
                'broadcasted_at' => now(),
            ]);

            Log::info("Notificação #{$notification->id} enviada para {$users->count()} usuários");
        } catch (\Exception $e) {
            Log::error("Erro ao fazer broadcast da notificação #{$notification->id}: {$e->getMessage()}");
        }
    }

    /**
     * Obtém os usuários que devem receber uma notificação
     */
    protected function getUsersForNotification(NotificationTarget $notification)
    {
        $query = User::query()->where('is_ativo', true);

        switch ($notification->target_type) {
            case 'all':
                // Todos os usuários ativos
                break;

            case 'user':
                $query->whereIn('id', $notification->target_user_ids ?? []);
                break;

            case 'department':
                $query->whereIn('departamento_id', $notification->target_department_ids ?? []);
                break;

            case 'cargo':
                $query->whereIn('pessoal_id', $notification->target_cargo_ids ?? []);
                break;

            case 'role':
                $query->whereHas('roles', function ($q) use ($notification) {
                    $q->whereIn('roles.id', $notification->target_role_ids ?? []);
                });
                break;

            case 'filial':
                $query->where(function ($q) use ($notification) {
                    foreach ($notification->target_filial_ids ?? [] as $filialId) {
                        $q->orWhereHas('filiais', function ($q2) use ($filialId) {
                            $q2->where('filiais.id', $filialId);
                        })->orWhere('filial_id', $filialId);
                    }
                });
                break;
        }

        return $query->get();
    }

    /**
     * Marca uma notificação como lida para um usuário
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = NotificationTarget::find($notificationId);

        if (!$notification) {
            return false;
        }

        $notification->markAsReadByUser($userId);
        return true;
    }

    /**
     * Marca todas as notificações como lidas para um usuário
     */
    public function markAllAsRead(int $userId): int
    {
        $user = User::find($userId);

        if (!$user) {
            return 0;
        }

        // Marcar notificações diretas como lidas
        $user->unreadNotifications->markAsRead();

        // Marcar notificações segmentadas como lidas
        $notifications = NotificationTarget::readyToSend()
            ->forUser($user)
            ->unreadByUser($userId)
            ->get();

        $count = 0;
        foreach ($notifications as $notification) {
            $notification->markAsReadByUser($userId);
            $count++;
        }

        return $count;
    }

    /**
     * Obtém notificações não lidas de um usuário
     */
    public function getUnreadNotifications(int $userId, int $limit = 50)
    {
        $user = User::find($userId);

        if (!$user) {
            return collect();
        }

        return NotificationTarget::readyToSend()
            ->forUser($user)
            ->unreadByUser($userId)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Limpa notificações antigas
     */
    public function cleanupOldNotifications(int $daysToKeep = 90): int
    {
        return \DB::select('SELECT cleanup_old_notifications(?)', [$daysToKeep])[0]->cleanup_old_notifications;
    }
}
