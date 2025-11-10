<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ItemCompra extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'item_compra';
    protected $primaryKey = 'id_item_compra';

    public $timestamps = true;
    const CREATED_AT = 'data_inclusao';
    const UPDATED_AT = 'data_alteracao';

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_produto',
        'id_user',
        'situacao',
        'id_relacaosolicitacoespecas',
        'quantidade_compra'
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }

    public function relacaoSolicitacoesPecas(): BelongsTo
    {
        return $this->belongsTo(RelacaoSolicitacaoPeca::class, 'id_relacaosolicitacoespecas', 'id_solicitacao_pecas');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
