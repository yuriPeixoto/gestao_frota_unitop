<?php

namespace App\Modules\Manutencao\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servico extends Model
{
    use LogsActivity;

    protected $table = 'servico';
    protected $primaryKey = 'id_servico';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_filial',
        'descricao_servico',
        'id_grupo',
        'ativo_servico',
        'id_categoria',
        'id_manutencao',
        'hora_servico',
        'auxiliar'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'ativo_servico' => 'boolean',
        'auxiliar' => 'boolean',
        'hora_servico' => 'datetime:H:i'
    ];

    /**
     * Relacionamento com OrdemServicoServicos
     */
    public function ordemServicoServicos(): HasMany
    {
        return $this->hasMany(OrdemServicoServicos::class, 'id_servicos', 'id_servico');
    }

    /**
     * Relacionamento com GrupoServico
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoServico::class, 'id_grupo', 'id_grupo');
    }

    /**
     * Alias para o relacionamento com GrupoServico
     */
    public function grupoServico(): BelongsTo
    {
        return $this->grupo();
    }

    /**
     * Relacionamento com CategoriaVeiculo
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaVeiculo::class, 'id_categoria', 'id_categoria');
    }

    /**
     * Relacionamento com Manutencao
     */
    public function manutencao(): BelongsTo
    {
        return $this->belongsTo(Manutencao::class, 'id_manutencao', 'id_manutencao');
    }

    /**
     * Relacionamento com Filial
     */
    public function filial(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    // Relacionamentos com o módulo de Compras

    /**
     * Relacionamento com Itens de Solicitação de Compra
     */
    public function itensSolicitacaoCompra(): HasMany
    {
        return $this->hasMany(ItemSolicitacaoCompra::class, 'id_servico', 'id_servico');
    }

    /**
     * Relacionamento com Itens de Pedido de Compra
     */
    public function itensPedidoCompra(): HasMany
    {
        return $this->hasMany(ItemPedidoCompra::class, 'id_servico', 'id_servico');
    }

    /**
     * Relacionamento com Itens de Contrato
     */
    public function itensContrato(): HasMany
    {
        return $this->hasMany(ItemContrato::class, 'id_servico', 'id_servico');
    }

    /**
     * Relacionamento com Itens de Nota Fiscal
     */
    public function itensNotaFiscal(): HasMany
    {
        return $this->hasMany(ItemNotaFiscal::class, 'id_servico', 'id_servico');
    }

    /**
     * Relacionamento com Itens de Orçamento através de ItemPedidoCompra
     */
    public function itensOrcamento(): HasMany
    {
        return $this->hasMany(ItemOrcamento::class, 'id_servico', 'id_servico')
            ->whereHas('itemPedidoCompra', function ($query) {
                $query->where('tipo', 'servico');
            });
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(CategoriaServico::class, 'id_servico', 'id_servico');
    }

    /**
     * Relacionamento com as peças vinculadas ao serviço (tabela pecas_servicos)
     */
    public function pecas(): HasMany
    {
        return $this->hasMany(PecasServicos::class, 'id_servico', 'id_servico');
    }

    /**
     * Permite buscar serviços ativos
     */
    public function scopeAtivo($query)
    {
        return $query->where('ativo_servico', true);
    }

    /**
     * Permite buscar serviços pelo grupo
     */
    public function scopePorGrupo($query, $idGrupo)
    {
        return $query->where('id_grupo', $idGrupo);
    }

    /**
     * Permite buscar serviços pela categoria
     */
    public function scopePorCategoria($query, $idCategoria)
    {
        return $query->where('id_categoria', $idCategoria);
    }

    /**
     * Permite buscar serviços pela manutenção
     */
    public function scopePorManutencao($query, $idManutencao)
    {
        return $query->where('id_manutencao', $idManutencao);
    }

    /**
     * Formata a descrição do serviço para exibição
     */
    public function getDescricaoCompletaAttribute()
    {
        $grupo = $this->grupo ? "[{$this->grupo->descricao_grupo}] " : '';
        return $grupo . $this->descricao_servico;
    }

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
