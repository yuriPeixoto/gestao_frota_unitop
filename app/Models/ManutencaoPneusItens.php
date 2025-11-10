<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Pneu;

class ManutencaoPneusItens extends Model
{
    use LogsActivity;

    protected $table = 'manutencao_pneus_itens';

    protected $primaryKey = 'id_manutencao_pneus_itens';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_pneu',
        'id_tipo_reforma',
        'id_manutencao_pneu',
        'id_modelo_pneu',
    ];

    public function pneu(): BelongsTo
    {
        return $this->belongsTo(Pneu::class, 'id_pneu', 'id_pneu'); // Ajuste a chave primária se necessário
    }

    public function tiporeforma(): BelongsTo
    {
        return $this->belongsTo(TipoReformaPneu::class, 'id_tipo_reforma', 'id_tipo_reforma');
    }

    public function modeloPneu(): BelongsTo
    {
        return $this->belongsTo(Pneu::class, 'id_modelo_pneu', 'id_modelo_pneu');
    }
}
