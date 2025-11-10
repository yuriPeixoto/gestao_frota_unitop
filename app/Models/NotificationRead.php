<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model para controle de leitura de notificações
 *
 * @property int $id
 * @property int $notification_target_id
 * @property int $user_id
 * @property \Carbon\Carbon $read_at
 */
class NotificationRead extends Model
{
    protected $table = 'notification_reads';

    public $timestamps = false;

    protected $fillable = [
        'notification_target_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($read) {
            if (!$read->read_at) {
                $read->read_at = now();
            }
        });
    }

    /**
     * Notificação relacionada
     */
    public function notificationTarget(): BelongsTo
    {
        return $this->belongsTo(NotificationTarget::class, 'notification_target_id');
    }

    /**
     * Usuário que leu a notificação
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
