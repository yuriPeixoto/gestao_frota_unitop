<?php

namespace App\Modules\Compras\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoSolicitacao extends Model
{
    use LogsActivity;

    protected $table = 'movimentacao_solicitacao';
    protected $primaryKey = 'id_movimentacao_solicitacao';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'id_solicitacao_antigo',
        'id_solicitacao_novo',
        'situacao_antigo',
        'situacao_novo'
    ];

    public function solicitacaoAntigo()
    {
        return $this->belongsTo(SolicitacaoCompra::class, 'id_solicitacao_antigo', 'id_solicitacoes_compras');
    }

    public function solicitacaoNovo()
    {
        return $this->belongsTo(SolicitacaoCompra::class, 'id_solicitacao_novo', 'id_solicitacoes_compras');
    }
}
