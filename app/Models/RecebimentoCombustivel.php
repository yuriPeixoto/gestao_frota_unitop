<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class RecebimentoCombustivel extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table      = 'recebimento_combustivel';
    protected $primaryKey = 'id_recebimento_combustivel';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_filial',
        'id_tanque',
        'id_fornecedor',
        'data_entrada',
        'quantidade',
        'preco_total_item',
        'valor_frete',
        'despesa_acessoria',
        'numeronotafiscal',
        'valor_unitario',
        'chave_nf',
        'temperatura_combustivel',
        'densidade_combustivel',
        'volume_convertido',
        'id_pedido',
        'numero_nf2',
        'numero_nf3',
        'numero_nf4',
        'chave_nf2',
        'chave_nf3',
        'chave_nf4',
        'situacao_nf',
        'id_user',
        'received_volume'
    ];

    protected $casts = [
        'data_inclusao'   => 'datetime',
        'data_alteracao'  => 'datetime',
        'data_entrada'    => 'datetime',
        'quantidade'      => 'float',
        'preco_total_item' => 'float',
        'valor_unitario'  => 'float',
        'volume_convertido' => 'float',
        'received_volume' => 'float'
    ];

    // Relacionamentos
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial');
    }

    public function tanque()
    {
        return $this->belongsTo(Tanque::class, 'id_tanque');
    }
}
