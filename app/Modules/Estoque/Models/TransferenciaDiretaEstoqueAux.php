<?php

namespace App\Modules\Estoque\Models;

use Illuminate\Database\Eloquent\Model;

class TransferenciaDiretaEstoqueAux extends Model
{
    protected $table = 'transferencia_direta_estoque_aux';
    protected $primaryKey = 'id_transferencia_direta_estoque_aux';
    //protected $connection = 'pgsql';
    public $timestamps = false;

    protected $fillable =  [
        'data_inclusao',
        'id_transferencia_direta_estoque_novo',
        'id_transferencia_direta_estoque_antigo',
        'id_transferencia_direta_estoque_itens',
        'id_produto',
        'quantidade_estoq',
        'qtde_produto_superavit',
        'qtde_produto',
        'filial',
        'filial_solicita',
        'solicitacao',
        'id_transferencia_recebimento'
    ];
    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial', 'id');
    }

    public function filial_solicita()
    {
        return $this->belongsTo(VFilial::class, 'filial_solicita', 'id');
    }
}
