<?php

namespace App\Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;

class VSolicitacoesComprasV2 extends Model
{
    protected $table = 'v_solicitacoes_compras_v2';

    public $timestamps = false;

    protected $fillable = [
        'id_solicitacoes_compras',
        'id_ordem_servico',
        'id_veiculo',
        'situacao_compra',
        'data_inclusao',
        'descricao_grupo',
        'descricao_departamento',
        'prioridade',
        'comprador',
        'filial',
        'solicitante',
        'tipo_solicitacao'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }

    public function solicitacoesCompra()
    {
        return $this->belongsTo(SolicitacaoCompra::class, 'id_solicitacoes_compras', 'id_solicitacoes_compras');
    }

    public function cotacoes()
    {
        return $this->hasMany(Cotacoes::class, 'id_solicitacoes_compras', 'id_solicitacoes_compras');
    }

    public function cotacao()
    {
        return $this->hasOne(Cotacoes::class, 'id_solicitacoes_compras', 'id_solicitacoes_compras');
    }
}
