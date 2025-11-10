<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoMovimentacaoEstoque extends Model
{
    use LogsActivity;

    protected $table = 'historico_movimentacao_estoque';
    protected $primaryKey = 'id_historico_movimentacao';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_produto',
        'id_filial',
        'qtde_estoque',
        'qtde_baixa',
        'qtde_entrada',
        'numero_nf',
        'saldo_total',
        'id_ordem_servico',
        'id_devolucao',
        'id_nf_entrada',
        'id_relacaosolicitacoespecas',
        'id_acerto',
        'id_inventario',
        'id_transferencia',
        'tipo',
    ];
}
