<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class CotacoesItens extends Model
{
    use LogsActivity;


    protected $table = 'cotacoesitens';

    protected $primaryKey = 'id_cotacoes_itens';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_produto',
        'descricao_produto',
        'descricao_unidade',
        'quantidade_solicitada',
        'valor_item',
        'valor_desconto',
        'per_desconto_item',
        'id_cotacao',
        'valorunitario',
        'quantidade_fornecedor',
        'condicao_pagamento'
    ];

    /**
     * Relacionamento com cotação
     */
    public function cotacao()
    {
        return $this->belongsTo(Cotacoes::class, 'id_cotacao', 'id_cotacoes');
    }

    /**
     * Relacionamento com produto
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }

    /**
     * Accessor para valor unitário (usar valorunitario como backup)
     */
    public function getValorUnitarioAttribute($value)
    {
        return $value ?? $this->attributes['valorunitario'] ?? 0;
    }
}
