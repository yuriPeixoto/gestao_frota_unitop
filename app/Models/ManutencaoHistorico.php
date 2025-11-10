<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManutencaoHistorico extends Model
{
    use LogsActivity;

    protected $table = 'manutencao_pneus_historico';

    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao', // corrigido
        'id_manutencao_pneus',
        'usuario_id',
        'status'
    ];

    protected $casts = [
        'data_inclusao'  => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }

    public function manutencaoPneu(): BelongsTo
    {
        return $this->belongsTo(ManutencaoPneus::class, 'id_manutencao_pneus', 'id_manutencao_pneu');
    }
}
