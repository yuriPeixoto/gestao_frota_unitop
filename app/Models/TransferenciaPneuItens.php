<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaPneuItens extends Model
{
    use LogsActivity;

    protected $table = 'transferencia_pneu_itens';

    protected $primaryKey = 'id_transferencia_pneu_itens';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_transferencia_modelo',
        'id_pneu',
        'recebido'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'recebido' => 'boolean'
    ];

    public function transferenciaPneuModelos(): BelongsTo
    {
        return $this->belongsTo(TransferenciaPneusModelos::class, 'id_transferencia_modelo');
    }
}