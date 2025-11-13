<?php

namespace App\Modules\Estoque\Models;

use Illuminate\Database\Eloquent\Model;

class DevolucaoTransferenciaEstoqueRequisicao extends Model
{
    protected $table = 'devolucao_transferencia_estoque_requisicao';

    protected $primaryKey = 'id_devolucao_transferencia_estoque_requisicao';
    public $timestamps = false; // desabilita created_at e updated_at automáticos

    protected $fillable =  [
        'id_produtos_solicitacoes',
        'id_relacao_solicitacoes',
        'id_protudos',
        'situacao_pecas',
        'data_inclusao',
        'data_alteracao',
        'qtde_devolucao',
        'quantidade_baixa'
    ];
}
