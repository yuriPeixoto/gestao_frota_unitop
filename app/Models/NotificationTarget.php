<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model para notificações segmentadas por nível organizacional
 *
 * @property int $id
 * @property string $notification_type
 * @property string $title
 * @property string $message
 * @property array $data
 * @property string $icon
 * @property string $color
 * @property string $priority
 * @property string $target_type
 * @property array $target_user_ids
 * @property array $target_department_ids
 * @property array $target_role_ids
 * @property array $target_cargo_ids
 * @property array $target_filial_ids
 * @property \Carbon\Carbon $scheduled_at
 * @property \Carbon\Carbon $expires_at
 * @property bool $is_active
 * @property bool $is_broadcasted
 * @property \Carbon\Carbon $broadcasted_at
 * @property int $created_by
 */
class NotificationTarget extends Model
{
    protected $table = 'notification_targets';

    protected $fillable = [
        'notification_type',
        'title',
        'message',
        'data',
        'icon',
        'color',
        'priority',
        'target_type',
        'target_user_ids',
        'target_department_ids',
        'target_role_ids',
        'target_cargo_ids',
        'target_filial_ids',
        'scheduled_at',
        'expires_at',
        'is_active',
        'is_broadcasted',
        'broadcasted_at',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'target_user_ids' => 'array',
        'target_department_ids' => 'array',
        'target_role_ids' => 'array',
        'target_cargo_ids' => 'array',
        'target_filial_ids' => 'array',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
        'broadcasted_at' => 'datetime',
        'is_active' => 'boolean',
        'is_broadcasted' => 'boolean',
    ];

    /**
     * Usuário que criou a notificação
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Registros de leitura desta notificação
     */
    public function reads(): HasMany
    {
        return $this->hasMany(NotificationRead::class, 'notification_target_id');
    }

    /**
     * Verifica se o usuário já leu esta notificação
     */
    public function isReadByUser(int $userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    /**
     * Marca a notificação como lida para um usuário
     */
    public function markAsReadByUser(int $userId): NotificationRead
    {
        return $this->reads()->firstOrCreate([
            'user_id' => $userId,
        ]);
    }

    /**
     * Verifica se a notificação está expirada
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Verifica se a notificação deve ser enviada agora
     */
    public function shouldBeSentNow(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        if ($this->scheduled_at && $this->scheduled_at->isFuture()) {
            return false;
        }

        return true;
    }

    /**
     * Verifica se um usuário específico deve receber esta notificação
     */
    public function shouldReceiveNotification(User $user): bool
    {
        if (!$this->shouldBeSentNow()) {
            return false;
        }

        // Já leu a notificação
        if ($this->isReadByUser($user->id)) {
            return false;
        }

        // Verifica por tipo de target
        switch ($this->target_type) {
            case 'all':
                return true;

            case 'user':
                return in_array($user->id, $this->target_user_ids ?? []);

            case 'department':
                return in_array($user->departamento_id, $this->target_department_ids ?? []);

            case 'role':
                return $user->roles()
                    ->whereIn('roles.id', $this->target_role_ids ?? [])
                    ->exists();

            case 'cargo':
                return in_array($user->pessoal_id, $this->target_cargo_ids ?? []);

            case 'filial':
                foreach ($this->target_filial_ids ?? [] as $filialId) {
                    if ($user->hasAccessToFilial($filialId)) {
                        return true;
                    }
                }
                return false;

            default:
                return false;
        }
    }

    /**
     * Scope para notificações ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para notificações não expiradas
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope para notificações prontas para serem enviadas
     */
    public function scopeReadyToSend($query)
    {
        return $query->active()
            ->notExpired()
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            });
    }

    /**
     * Scope para notificações de um usuário específico
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            // Todas
            $q->where('target_type', 'all')
                // Ou usuário específico
                ->orWhere(function ($q2) use ($user) {
                    $q2->where('target_type', 'user')
                        ->whereJsonContains('target_user_ids', $user->id);
                })
                // Ou departamento
                ->orWhere(function ($q2) use ($user) {
                    if ($user->departamento_id) {
                        $q2->where('target_type', 'department')
                            ->whereJsonContains('target_department_ids', $user->departamento_id);
                    }
                })
                // Ou cargo
                ->orWhere(function ($q2) use ($user) {
                    if ($user->pessoal_id) {
                        $q2->where('target_type', 'cargo')
                            ->whereJsonContains('target_cargo_ids', $user->pessoal_id);
                    }
                })
                // Ou filial
                ->orWhere(function ($q2) use ($user) {
                    $filialIds = $user->getAccessibleFilialIds();
                    foreach ($filialIds as $filialId) {
                        $q2->orWhere(function ($q3) use ($filialId) {
                            $q3->where('target_type', 'filial')
                                ->whereJsonContains('target_filial_ids', $filialId);
                        });
                    }
                });
        });
    }

    /**
     * Scope para notificações não lidas por um usuário
     */
    public function scopeUnreadByUser($query, int $userId)
    {
        return $query->whereDoesntHave('reads', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
}
