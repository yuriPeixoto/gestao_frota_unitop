<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalibragemPneusItens extends Model
{
    public $timestamps = false;
    protected $table = "calibragem_pneus_itens";
    protected $primaryKey = "id_calibragem_pneus_itens";
    protected $fillable = [
        'id_calibragem_pneus_itens',
        'data_inclusao',
        'data_alteracao',
        'id_numero_fogo',
        'id_calibragem',
        'libras',
        'calibrado',
        'sulco_pneu',
        'localizacao',
    ];

    public function calibragem(): BelongsTo
    {
        return $this->belongsTo(CalibragemPneus::class, 'id_calibragem', 'id_calibragem_pneu');
    }
}
