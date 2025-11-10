<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class DevolucaoTransferenciaEstoque extends Model
{
    use LogsActivity;

    protected $table = 'devolucao_transferencia_estoque';
    protected $primaryKey = 'id_devolucao_transferencia_estoque';
    public $timestamps = false;

    protected $fillable = [
        'id_transferencia_direta_estoque',
        'id_transferencia_direta_estoque_itens',
        'id_produto',
        'stauts',
        'data_inclusao',
        'data_alteracao',
        'qtde_devolucao',
        'qtd_baixa'
    ];

    public function transferencia_direta_estoque()
    {
        return $this->belongsTo(TransferenciaDiretaEstoque::class, 'id_transferencia_direta_estoque', 'id_transferencia_direta_estoque');
    }
}
