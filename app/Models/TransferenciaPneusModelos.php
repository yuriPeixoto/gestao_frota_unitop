<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransferenciaPneusModelos extends Model
{
    use LogsActivity;

    protected $table = 'transferencia_pneus_modelos';

    protected $primaryKey = 'id_transferencia_pneus_modelos';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_modelos_requisitados',
        'id_transferencia_pneu',
        'quantidade_baixa',
        'quantidade'
    ];


    public function modeloPneu(): BelongsTo
    {
        return $this->belongsTo(ModeloPneu::class, 'id_modelos_requisitados', 'id_modelo_pneu');
    }

    public function transferenciaPneus(): BelongsTo
    {
        return $this->belongsTo(TransferenciaPneus::class, 'id_transferencia_pneu');
    }

    public function transferenciaPneusItens(): HasMany
    {
        return $this->hasMany(TransferenciaPneuItens::class, 'id_transferencia_modelo');
    }
}