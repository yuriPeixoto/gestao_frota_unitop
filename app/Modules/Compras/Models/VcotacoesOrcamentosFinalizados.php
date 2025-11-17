<?php

namespace App\Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;

class VcotacoesOrcamentosFinalizados extends Model
{
    protected $table = 'v_cotacoes_orcamentos_finalizados';

    public $timestamps = false;

    protected $fillable = [
        'id_solicitacoes_compras',
        'id_fornecedor',
        'nome_fornecedor',
        'nome_contato',
        'data_entrega',
        'telefone_fornecedor',
        'id_produto',
        'descricao_produto',
        'descricao_unidade',
        'quantidade_solicitada',
        'valorunitario',
        'valor_item',
        'valor_desconto',
        'id_cotacoes'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
    ];
}
