<?php

namespace App\Modules\Pneus\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContagemPneu extends Model
{
    use LogsActivity;


    protected $table = 'contagem_pneu';

    protected $primaryKey = 'id_contagem_pneu';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_modelo_pneu',
        'contagem_usuario',
        'quantidade_sistema',
        'id_filial',
        'id_responsavel_contagem',
        'id_usuario',
        'is_igual',
    ];

    public function modelopneu(): BelongsTo
    {
        return $this->belongsTo(ModeloPneu::class, 'id_modelo_pneu');
    }
}
