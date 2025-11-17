<?php

namespace App\Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;

class VcotacoesMenosValor extends Model
{
    protected $table = 'v_cotacoes_menor_valor';

    protected $fillable = [
        'id_solicitacoes_compras',
        'id_cotacoes',
        'nome_fornecedor',
        'valor_total',
        'valor_total_desconto'
    ];
}
