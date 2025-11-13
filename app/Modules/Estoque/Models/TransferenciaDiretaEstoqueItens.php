<?php

namespace App\Modules\Estoque\Models;

use Illuminate\Database\Eloquent\Model;

class TransferenciaDiretaEstoqueItens extends Model
{
    protected $table = 'transferencia_direta_estoque_itens';
    protected $primaryKey = 'id_transferencia_direta_estoque_itens';
    public $timestamps = false;

    protected $fillable =  [
        'id_transferencia_direta_estoque',
        'id_produto',
        'qtde_produto',
        'data_inclusao',
        'data_alteracao',
        'qtd_baixa',
        'qtde_devolucao'
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }

    public function transferencia()
    {
        return $this->belongsTo(TransferenciaDiretaEstoque::class, 'id_transferencia_direta_estoque');
    }
}
