<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model para configurações de notificação do usuário
 *
 * @property int $id
 * @property int $user_id
 * @property bool $enable_database
 * @property bool $enable_email
 * @property bool $enable_broadcast
 * @property bool $enable_push
 * @property array $notification_preferences
 * @property string $quiet_hours_start
 * @property string $quiet_hours_end
 * @property bool $quiet_hours_enabled
 */
class UserNotificationSetting extends Model
{
    protected $table = 'user_notification_settings';

    protected $fillable = [
        'user_id',
        'enable_database',
        'enable_email',
        'enable_broadcast',
        'enable_push',
        'notification_preferences',
        'quiet_hours_start',
        'quiet_hours_end',
        'quiet_hours_enabled',
    ];

    protected $casts = [
        'enable_database' => 'boolean',
        'enable_email' => 'boolean',
        'enable_broadcast' => 'boolean',
        'enable_push' => 'boolean',
        'notification_preferences' => 'array',
        'quiet_hours_enabled' => 'boolean',
    ];

    /**
     * Usuário relacionado
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se um tipo de notificação está habilitado
     */
    public function isNotificationTypeEnabled(string $type): bool
    {
        $preferences = $this->notification_preferences ?? [];
        return $preferences[$type]['enabled'] ?? true;
    }

    /**
     * Obtém os canais habilitados para um tipo de notificação
     */
    public function getChannelsForNotificationType(string $type): array
    {
        if (!$this->isNotificationTypeEnabled($type)) {
            return [];
        }

        $preferences = $this->notification_preferences ?? [];
        $typeChannels = $preferences[$type]['channels'] ?? ['database', 'broadcast'];

        $enabledChannels = [];

        if ($this->enable_database && in_array('database', $typeChannels)) {
            $enabledChannels[] = 'database';
        }

        if ($this->enable_email && in_array('email', $typeChannels)) {
            $enabledChannels[] = 'email';
        }

        if ($this->enable_broadcast && in_array('broadcast', $typeChannels)) {
            $enabledChannels[] = 'broadcast';
        }

        if ($this->enable_push && in_array('push', $typeChannels)) {
            $enabledChannels[] = 'push';
        }

        return $enabledChannels;
    }

    /**
     * Verifica se está em horário de silêncio
     */
    public function isInQuietHours(): bool
    {
        if (!$this->quiet_hours_enabled) {
            return false;
        }

        $now = now()->format('H:i:s');
        $start = $this->quiet_hours_start ?? '22:00:00';
        $end = $this->quiet_hours_end ?? '08:00:00';

        // Se o período passa pela meia-noite
        if ($start > $end) {
            return $now >= $start || $now <= $end;
        }

        return $now >= $start && $now <= $end;
    }

    /**
     * Obtém ou cria as configurações padrão para um usuário
     */
    public static function getOrCreateForUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'enable_database' => true,
                'enable_email' => true,
                'enable_broadcast' => true,
                'enable_push' => false,
                'quiet_hours_enabled' => false,
            ]
        );
    }
}
