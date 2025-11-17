<?php

namespace App\Modules\Compras\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Cotacoes extends Model
{
    use LogsActivity;


    protected $table = 'cotacoes';

    protected $primaryKey = 'id_cotacoes';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'id_solicitacoes_compras',
        'valor_total',
        'valor_total_desconto',
        'id_fornecedor',
        'nome_fornecedor',
        'email',
        'telefone_fornecedor',
        'nome_contato',
        'id_comprador',
        'caminhoimagem',
        'data_entrega',
        'id_filial',
        'aprovado_recusado',
        'condicao_pag'
    ];

    /**
     * Relacionamento com fornecedor
     */
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Relacionamento com itens da cotação
     */
    public function itens()
    {
        return $this->hasMany(CotacoesItens::class, 'id_cotacao', 'id_cotacoes');
    }

    /**
     * Relacionamento com solicitação de compra
     */
    public function solicitacaoCompra()
    {
        return $this->belongsTo(SolicitacaoCompra::class, 'id_solicitacoes_compras', 'id_solicitacoes_compras');
    }
}
