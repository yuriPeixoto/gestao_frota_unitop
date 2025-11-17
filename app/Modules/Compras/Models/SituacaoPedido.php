<?php

namespace App\Modules\Compras\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SituacaoPedido extends Model
{
    use LogsActivity;

    protected $table = 'situacao_pedido';
    protected $primaryKey = 'id_situacao_pedido';
    public $timestamps = false;

    protected $fillable = [
        'descricao_situacao_pedido',
        'data_inclusao',
        'data_alteracao',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    protected $dates = [
        'data_inclusao',
        'data_alteracao',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->data_inclusao = now();
            $model->data_alteracao = now();
        });

        static::updating(function ($model) {
            $model->data_alteracao = now();
        });
    }

    /**
     * Get the pedidos with this status
     */
    public function pedidosCompra()
    {
        return $this->hasMany(PedidoCompra::class, 'situacao_pedido', 'id_situacao_pedido');
    }

    /**
     * Alias para descricao_situacao_pedido
     */
    public function getDescricaoAttribute()
    {
        return $this->descricao_situacao_pedido;
    }
}
