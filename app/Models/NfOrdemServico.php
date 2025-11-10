<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NfOrdemServico extends Model
{
    use LogsActivity;

    protected $table = 'nf_ordem_servico';
    protected $primaryKey = 'id_nf_ordem';
    public $timestamps = false;
    protected $fillable = [
        "data_inclusao",
        "data_alteracao",
        "numero_nf",
        "valor_bruto_nf",
        "valor_liquido_nf",
        "id_nf_compra_servico",
        "data_emissao_nf",
        "valor_previo",
        "valor_pago",
        "garantia",
        "id_ordem_servico",
        "id_fornecedor",
        "id_servico",
        "id_peca",
        "valor_descontonf",
        "serie",
        "observacao",
        "chave_nf",
    ];

    protected $casts = [
        'data_emissao_nf'     => 'datetime'
    ];
}
