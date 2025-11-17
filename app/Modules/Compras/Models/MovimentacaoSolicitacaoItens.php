<?php

namespace App\Modules\Compras\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoSolicitacaoItens extends Model
{
    use LogsActivity;

    protected $table = 'movimentacao_solicitacao_itens';
    protected $primaryKey = 'id_movimentacao_solicitacao_itens';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'id_itens_solicitacoes_antigo',
        'id_itens_solicitacoes_novo',
        'quantidade_solicitada',
    ];

    public function solicitacaoItensAntigo()
    {
        return $this->belongsTo(ItemSolicitacaoCompra::class, 'id_itens_solicitacoes_antigo', 'id_itens_solicitacoes');
    }

    public function solicitacaoNovo()
    {
        return $this->belongsTo(ItemSolicitacaoCompra::class, 'id_itens_solicitacoes_novo', 'id_itens_solicitacoes');
    }
}
