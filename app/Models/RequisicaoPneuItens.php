<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisicaoPneuItens extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'requisicao_pneu_itens';

    protected $primaryKey = 'id_requisicao_pneu_itens';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_requisicao_pneu_modelos',
        'id_pneu',
        'valor_venda',
        'id_user_edit',
    ];

    public function requisicao_pneu_modelos(): BelongsTo
    {
        return $this->belongsTo(RequisicaoPneuModelos::class, 'id_requisicao_pneu_modelos');
    }

    public function pneu(): BelongsTo
    {
        return $this->belongsTo(Pneu::class, 'id_pneu', 'id_pneu');
    }
}
