<?php

namespace App\Modules\Compras\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use App\Models\PedidoCompas;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ItensPedidos extends Model
{
    use LogsActivity;

    protected $table = 'itens_pedidos';
    protected $primaryKey = 'id_itens_pedidos';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'cod_produto',
        'unidade_produto',
        'quantidade_produtos',
        'valor_produto',
        'valor_total',
        'valor_total_desconto',
        'id_pedido_compras',
        'descricao_unidade',
        'quantidade_anterior',
        'id_user_edicao',
        'observacao_edicao',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(PedidoCompra::class, 'id_pedido_compra', 'id_pedido_compras');
    }

    public function produtos(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'cod_produto', 'id_produto');
    }

    public function itemSolicitacao(): BelongsTo
    {
        return $this->belongsTo(ItemSolicitacaoCompra::class, 'cod_produto', 'id_produto');
    }
}
