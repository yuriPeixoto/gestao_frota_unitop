<?php

namespace App\Modules\Tickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TicketAttachment extends Model
{
    protected $table = 'ticket_attachments';

    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'response_id',
        'user_id',
        'original_name',
        'stored_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($attachment) {
            $attachment->created_at = now();
        });

        static::deleting(function ($attachment) {
            // Deletar arquivo físico
            if (Storage::exists($attachment->file_path)) {
                Storage::delete($attachment->file_path);
            }
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(TicketResponse::class, 'response_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Retorna URL do arquivo
     */
    public function url(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Retorna tamanho formatado
     */
    public function formattedSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Verifica se é imagem
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }
}
