<?php

namespace App\Modules\Compras\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemContrato extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'itens_contrato';
    protected $primaryKey = 'id_item_contrato';

    protected $fillable = [
        'id_contrato',
        'tipo',
        'id_produto',
        'id_servico',
        'descricao',
        'valor_unitario',
        'quantidade_estimada',
        'unidade_medida',
        'ativo',
        'data_inclusao',
        'data_alteracao',
    ];

    protected $casts = [
        'valor_unitario' => 'decimal:2',
        'quantidade_estimada' => 'decimal:2',
        'ativo' => 'boolean',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    /**
     * Obtém o contrato ao qual o item pertence
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'id_contrato', 'id_contrato');
    }

    /**
     * Obtém o produto relacionado, se for um item do tipo produto
     */
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }

    /**
     * Obtém o serviço relacionado, se for um item do tipo serviço
     */
    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class, 'id_servico', 'id_servico');
    }

    /**
     * Calcula o valor total do item (valor unitário * quantidade)
     *
     * @return float
     */
    public function getValorTotalAttribute(): float
    {
        return $this->valor_unitario * $this->quantidade_estimada;
    }

    /**
     * Escopo para itens ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Escopo para itens de contratos vigentes
     */
    public function scopeDeContratosVigentes($query)
    {
        return $query->whereHas('contrato', function ($query) {
            $query->vigentes();
        });
    }

    /**
     * Escopo para itens do tipo produto
     */
    public function scopeProdutos($query)
    {
        return $query->where('tipo', 'produto');
    }

    /**
     * Escopo para itens do tipo serviço
     */
    public function scopeServicos($query)
    {
        return $query->where('tipo', 'servico');
    }

    /**
     * Verifica se o item é um produto
     *
     * @return bool
     */
    public function isProduto(): bool
    {
        return $this->tipo === 'produto';
    }

    /**
     * Verifica se o item é um serviço
     *
     * @return bool
     */
    public function isServico(): bool
    {
        return $this->tipo === 'servico';
    }
}
