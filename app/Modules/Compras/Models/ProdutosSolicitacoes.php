<?php

namespace App\Modules\Compras\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ProdutosSolicitacoes extends Model
{
    use LogsActivity;

    protected $table = 'produtossolicitacoes';

    protected $primaryKey = 'id_produtos_solicitacoes';

    public $timestamps = true;

    const CREATED_AT = 'data_inclusao';

    const UPDATED_AT = 'data_alteracao';

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_relacao_solicitacoes',
        'id_protudos',
        'quantidade',
        'quantidade_baixa',
        'data_baixa',
        'id_filial',
        'id_unidade_produto',
        'id_user',
        'data_baixa_sistema',
        'id_ordem_servico_peca',
        'situacao_pecas',
        'observacao',
        'anexo_imagem',
        'quantidade_transferencia',
        'filial_transferencia',
        'quantidade_compra',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_protudos', 'id_produto');
    }

    public function relacaoSolicitacoesPecas()
    {
        return $this->belongsTo(RelacaoSolicitacaoPeca::class, 'id_relacao_solicitacoes', 'id_solicitacao_pecas');
    }

    public function filialTransferencia()
    {
        return $this->belongsTo(Filial::class, 'filial_transferencia', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
