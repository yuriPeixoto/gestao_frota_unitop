<?php

namespace App\Modules\Tickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TicketTag extends Model
{
    protected $table = 'ticket_tags';

    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(SupportTicket::class, 'ticket_tag_pivot', 'tag_id', 'ticket_id')
            ->withTimestamps();
    }
}
