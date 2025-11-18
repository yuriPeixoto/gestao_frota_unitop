<?php

namespace App\Modules\Configuracoes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFilial extends Model
{
    protected $table = 'user_filial';

    protected $fillable = [
        'user_id',
        'filial_id',
    ];

    /**
     * Get the user associated with this UserFilial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the VFilial instance associated with this UserFilial.
     *
     * @return \App\Models\VFilial|null
     */
    public function filial()
    {
        return VFilial::find($this->filial_id);
    }
}
