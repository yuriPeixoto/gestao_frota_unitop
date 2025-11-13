<?php

namespace App\Modules\Tickets\Models;

use App\Modules\Tickets\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketStatusHistory extends Model
{
    protected $table = 'ticket_status_history';

    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'from_status',
        'to_status',
        'comment',
    ];

    protected $casts = [
        'from_status' => TicketStatus::class,
        'to_status' => TicketStatus::class,
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($history) {
            $history->created_at = now();
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
