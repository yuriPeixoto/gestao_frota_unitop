<?php

namespace App\Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class PedidosOrdemAux extends Model
{
    use LogsActivity;


    protected $table = 'pedidos_ordem_aux';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_ordem_servico',
        'id_pedido_compras',
        'id_nf_compra_servico',
        'id_pedido_geral'
    ];

    public function nfCompraServico()
    {
        return $this->belongsTo(NfCompraServico::class, 'id_nf_compra_servico', 'id');
    }
}
