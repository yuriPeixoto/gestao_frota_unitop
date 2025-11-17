<?php

namespace App\Modules\Manutencao\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class NfCompraServico extends Model
{
    use LogsActivity;

    protected $table = 'nf_compra_servico';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_user',
        'id_fornecedor',
        'chave_nf',
        'numero_nf',
        'serie_nf',
        'data_emissao',
        'valor_servico',
        'valor_total_nota'
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    public function usuario()
    {
        return $this->belongsTo(Fornecedor::class, 'id_user', 'id_user');
    }

    public function pedido()
    {
        return $this->hasOne(PedidosOrdemAux::class, 'id_nf_compra_servico', 'id');
    }
}
