<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaEstoqueAuxEnvio extends Model
{
    use LogsActivity;

    protected $table = 'transferencia_estoque_aux_envio';
    protected $primaryKey = 'id_transferencia_estoque_aux_envio';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_relacao_solicitacoes_novo',
        'id_relacao_solicitacoes_antigo',
        'id_tranferencia_envio',
        'id_protudos_solicitado',
        'id_produtos_solicitacoes',
        'quantidade_estoq',
        'quantidade_solici',
        'quantidade_pedido',
        'id_filial_recebe',
        'id_filial_solicita',
    ];
}
