<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevolucaoProdutoOrdem extends Model
{
    use LogsActivity;

    protected $table = 'devolucao_produto_ordem';

    protected $primaryKey = 'id_devolucao_produtos';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'justificativa',
        'id_produto',
        'quantidade',
        'id_ordem_servico',
        'id_filial'
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'id_ordem_servico', 'id_ordem_servico');
    }
}
