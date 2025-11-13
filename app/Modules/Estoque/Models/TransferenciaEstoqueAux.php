<?php

namespace App\Modules\Estoque\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaEstoqueAux extends Model
{
    use LogsActivity;

    protected $table = 'transferencia_estoque_aux';
    protected $primaryKey = 'id_transferencia_estoque_aux';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_relacao_solicitacoes_novo',
        'id_relacao_solicitacoes_antigo',
        'id_protudos_solicitado',
        'id_produtos_solicitacoes',
        'quantidade_estoq',
        'quantidade_solici',
        'quantidade_superavit',
        'id_filial_solicita',
        'id_filial_recebe', // Adicionar se o campo existir na tabela
        'solicitacao',
    ];

    public function filialSolicitante(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial_solicita', 'id');
    }

    public function relacaoSolicitacoesNovo(): BelongsTo
    {
        return $this->belongsTo(RelacaoSolicitacaoPeca::class, 'id_relacao_solicitacoes_novo', 'id_solicitacao_pecas');
    }
}
