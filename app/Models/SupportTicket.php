<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $ticket_number
 * @property int $user_id
 * @property int $category_id
 * @property int|null $assigned_to
 * @property int|null $filial_id
 * @property TicketType $type
 * @property TicketPriority $priority
 * @property TicketStatus $status
 * @property string $subject
 * @property string $description
 * @property int|null $quality_reviewed_by
 * @property \Carbon\Carbon|null $quality_reviewed_at
 * @property string|null $quality_comments
 * @property float|null $estimated_hours
 * @property \Carbon\Carbon|null $estimated_completion_date
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $resolved_at
 * @property \Carbon\Carbon|null $closed_at
 * @property int|null $satisfaction_rating
 * @property string|null $satisfaction_comment
 * @property bool $is_internal
 * @property bool $is_public
 */
class SupportTicket extends Model
{
    protected $table = 'support_tickets';

    protected $fillable = [
        'ticket_number',
        'user_id',
        'category_id',
        'assigned_to',
        'filial_id',
        'type',
        'priority',
        'status',
        'subject',
        'description',
        'browser',
        'device',
        'ip_address',
        'url',
        'quality_reviewed_by',
        'quality_reviewed_at',
        'quality_comments',
        'estimated_hours',
        'estimated_completion_date',
        'started_at',
        'resolved_at',
        'closed_at',
        'satisfaction_rating',
        'satisfaction_comment',
        'is_internal',
        'is_public',
    ];

    protected $casts = [
        'type' => TicketType::class,
        'priority' => TicketPriority::class,
        'status' => TicketStatus::class,
        'quality_reviewed_at' => 'datetime',
        'estimated_completion_date' => 'datetime',
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'is_internal' => 'boolean',
        'is_public' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($ticket) {
            if (!$ticket->ticket_number) {
                $ticket->ticket_number = static::generateTicketNumber();
            }

            // Definir status padrão se não estiver definido
            if (!$ticket->status) {
                // Se for melhoria, vai para qualidade primeiro
                if ($ticket->type === TicketType::MELHORIA) {
                    $ticket->status = TicketStatus::AGUARDANDO_QUALIDADE;
                } else {
                    $ticket->status = TicketStatus::NOVO;
                }
            }
        });
    }

    /**
     * Gera número único do ticket
     */
    public static function generateTicketNumber(): string
    {
        $year = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('SUP-%d-%04d', $year, $count);
    }

    /**
     * Usuário que criou o chamado
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Categoria do chamado
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    /**
     * Atendente atribuído
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Usuário da qualidade que revisou
     */
    public function qualityReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quality_reviewed_by');
    }

    /**
     * Filial do solicitante
     */
    public function filial(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'filial_id');
    }

    /**
     * Respostas do chamado
     */
    public function responses(): HasMany
    {
        return $this->hasMany(TicketResponse::class, 'ticket_id');
    }

    /**
     * Anexos do chamado
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_id');
    }

    /**
     * Histórico de status
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class, 'ticket_id');
    }

    /**
     * Histórico de atribuições
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TicketAssignment::class, 'ticket_id');
    }

    /**
     * Tags do chamado
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TicketTag::class, 'ticket_tag_pivot', 'ticket_id', 'tag_id');
    }

    /**
     * Observadores do chamado
     */
    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_watchers', 'ticket_id', 'user_id')
            ->withPivot('created_at');
    }

    /**
     * Respostas públicas (visíveis para o cliente)
     */
    public function publicResponses(): HasMany
    {
        return $this->responses()->where('is_internal', false);
    }

    /**
     * Verifica se usuário pode visualizar este chamado
     */
    public function canBeViewedBy(User $user): bool
    {
        // Superuser vê tudo
        if ($user->isSuperuser()) {
            return true;
        }

        // Criador vê sempre
        if ($this->user_id === $user->id) {
            return true;
        }

        // Atribuído vê sempre
        if ($this->assigned_to === $user->id) {
            return true;
        }

        // Observador vê sempre
        if ($this->watchers()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Equipe Unitop vê tudo
        if ($user->hasRole('Equipe Unitop')) {
            return true;
        }

        // Equipe Qualidade vê melhorias
        if ($user->hasRole('Equipe Qualidade') && $this->type === TicketType::MELHORIA) {
            return true;
        }

        return false;
    }

    /**
     * Verifica se usuário pode editar este chamado
     */
    public function canBeEditedBy(User $user): bool
    {
        if (!$this->status->canBeEdited()) {
            return false;
        }

        return $user->isSuperuser()
            || $this->user_id === $user->id
            || $this->assigned_to === $user->id
            || $user->hasRole('Equipe Unitop');
    }

    /**
     * Verifica se está atrasado (SLA vencido)
     */
    public function isOverdue(): bool
    {
        if ($this->status->isClosed()) {
            return false;
        }

        $slaDeadline = $this->created_at->addHours($this->priority->slaHours());
        return now()->greaterThan($slaDeadline);
    }

    /**
     * Retorna deadline SLA
     */
    public function slaDeadline(): \Carbon\Carbon
    {
        return $this->created_at->addHours($this->priority->slaHours());
    }

    /**
     * Calcula tempo de resolução em horas
     */
    public function resolutionTimeHours(): ?float
    {
        if (!$this->resolved_at || !$this->started_at) {
            return null;
        }

        return $this->started_at->diffInHours($this->resolved_at, true);
    }

    /**
     * Scopes
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', TicketStatus::openStatuses());
    }

    public function scopeClosed($query)
    {
        return $query->whereNotIn('status', TicketStatus::openStatuses());
    }

    public function scopeOverdue($query)
    {
        return $query->open()->whereRaw(
            "created_at + (
                CASE priority
                    WHEN 'urgente' THEN INTERVAL '4 hours'
                    WHEN 'alta' THEN INTERVAL '24 hours'
                    WHEN 'media' THEN INTERVAL '72 hours'
                    WHEN 'baixa' THEN INTERVAL '168 hours'
                END
            ) < NOW()"
        );
    }

    public function scopeByType($query, TicketType $type)
    {
        return $query->where('type', $type->value);
    }

    public function scopeByPriority($query, TicketPriority $priority)
    {
        return $query->where('priority', $priority->value);
    }

    public function scopeByStatus($query, TicketStatus $status)
    {
        return $query->where('status', $status->value);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForUser($query, User $user)
    {
        if ($user->isSuperuser() || $user->hasRole('Equipe Unitop')) {
            return $query;
        }

        if ($user->hasRole('Equipe Qualidade')) {
            return $query->where('type', TicketType::MELHORIA->value);
        }

        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('assigned_to', $user->id)
              ->orWhereHas('watchers', function ($q2) use ($user) {
                  $q2->where('user_id', $user->id);
              });
        });
    }

    public function scopeAwaitingQuality($query)
    {
        return $query->where('status', TicketStatus::AGUARDANDO_QUALIDADE->value);
    }
}
