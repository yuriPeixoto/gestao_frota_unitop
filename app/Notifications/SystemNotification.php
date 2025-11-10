<?php

namespace App\Notifications;

use App\Models\NotificationTarget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    public NotificationTarget $notificationTarget;

    /**
     * Create a new notification instance.
     */
    public function __construct(NotificationTarget $notificationTarget)
    {
        $this->notificationTarget = $notificationTarget;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Obter configurações do usuário
        $settings = $notifiable->getNotificationSettings();

        // Verificar se está em horário de silêncio
        if ($settings->isInQuietHours()) {
            return ['database']; // Apenas salva no banco durante horário de silêncio
        }

        // Obter canais habilitados para este tipo de notificação
        $type = $this->getNotificationType();
        $channels = $settings->getChannelsForNotificationType($type);

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/notifications');

        return (new MailMessage)
            ->subject($this->notificationTarget->title)
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line($this->notificationTarget->message)
            ->action('Ver Notificação', $url)
            ->line('Obrigado por usar nosso sistema!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'notification_target_id' => $this->notificationTarget->id,
            'type' => $this->notificationTarget->notification_type,
            'title' => $this->notificationTarget->title,
            'message' => $this->notificationTarget->message,
            'icon' => $this->notificationTarget->icon,
            'color' => $this->notificationTarget->color,
            'priority' => $this->notificationTarget->priority,
            'data' => $this->notificationTarget->data,
            'url' => $this->notificationTarget->data['url'] ?? null,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'notification_target_id' => $this->notificationTarget->id,
            'type' => $this->notificationTarget->notification_type,
            'title' => $this->notificationTarget->title,
            'message' => $this->notificationTarget->message,
            'icon' => $this->notificationTarget->icon,
            'color' => $this->notificationTarget->color,
            'priority' => $this->notificationTarget->priority,
            'data' => $this->notificationTarget->data,
            'url' => $this->notificationTarget->data['url'] ?? null,
            'created_at' => $this->notificationTarget->created_at->toIso8601String(),
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // Canal pessoal do usuário
        $channels[] = 'notifications.user.' . $this->notifiable->id;

        // Canais baseados no tipo de target
        switch ($this->notificationTarget->target_type) {
            case 'all':
                $channels[] = 'notifications.global';
                break;

            case 'department':
                foreach ($this->notificationTarget->target_department_ids ?? [] as $deptId) {
                    $channels[] = 'notifications.department.' . $deptId;
                }
                break;

            case 'role':
                foreach ($this->notificationTarget->target_role_ids ?? [] as $roleId) {
                    $channels[] = 'notifications.role.' . $roleId;
                }
                break;

            case 'cargo':
                foreach ($this->notificationTarget->target_cargo_ids ?? [] as $cargoId) {
                    $channels[] = 'notifications.cargo.' . $cargoId;
                }
                break;

            case 'filial':
                foreach ($this->notificationTarget->target_filial_ids ?? [] as $filialId) {
                    $channels[] = 'notifications.filial.' . $filialId;
                }
                break;
        }

        return $channels;
    }

    /**
     * Obtém o tipo de notificação
     */
    protected function getNotificationType(): string
    {
        $type = $this->notificationTarget->notification_type;

        // Extrair categoria principal (ex: "sistema.boas_vindas" -> "sistema")
        if (str_contains($type, '.')) {
            return explode('.', $type)[0];
        }

        return $type;
    }
}
