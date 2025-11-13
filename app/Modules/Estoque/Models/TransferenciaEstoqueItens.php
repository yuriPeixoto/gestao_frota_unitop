<?php

namespace App\Modules\Estoque\Models;

use Illuminate\Database\Eloquent\Model;

class TransferenciaEstoqueItens extends Model
{

    protected $table = 'transferencia_estoque_itens';
    public $timestamps = false;
    protected $primaryKey = 'id_transferencia_itens';


    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_produto',
        'quantidade',
        'id_transferencia',
        'quantidade_baixa',
        'data_baixa',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }
    public function transferencia()
    {
        return $this->belongsTo(TransferenciaEstoque::class, 'id_transferencia', 'id_tranferencia');
    }
}
