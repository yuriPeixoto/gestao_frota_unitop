<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemOrcamento extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table      = 'itens_orcamento';
    protected $primaryKey = 'id_item_orcamento';

    protected $fillable = [
        'id_orcamento',
        'id_item_pedido',
        'descricao',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'observacao',
        'data_inclusao',
        'data_alteracao',
    ];

    protected $dates = [
        'data_inclusao',
        'data_alteracao',
        'deleted_at',
    ];

    protected $casts = [
        'quantidade'     => 'decimal:2',
        'valor_unitario' => 'decimal:2',
        'valor_total'    => 'decimal:2',
        'data_inclusao'  => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    /**
     * Indica que o modelo não usa timestamps padrão do Laravel
     */
    public $timestamps = false;

    // Relacionamentos

    /**
     * Relacionamento com o orçamento
     */
    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class, 'id_orcamento', 'id_orcamento');
    }

    /**
     * Relacionamento com o item do pedido de compra
     */
    public function itemPedidoCompra()
    {
        return $this->belongsTo(ItemPedidoCompra::class, 'id_item_pedido', 'id_item_pedido');
    }

    // Mutators & Accessors

    /**
     * Setter para data_inclusao que define automaticamente se não informado
     */
    public function setDataInclusaoAttribute($value)
    {
        $this->attributes['data_inclusao'] = $value ?: now();
    }

    /**
     * Setter para data_alteracao que atualiza automaticamente no update
     */
    public function setDataAlteracaoAttribute($value)
    {
        if ($this->exists) {
            $this->attributes['data_alteracao'] = now();
        }
    }

    /**
     * Setter para valor_total que calcula com base na quantidade e valor unitário
     */
    public function setValorTotalAttribute($value)
    {
        // Se o valor for explicitamente definido, use-o
        if ($value !== null) {
            $this->attributes['valor_total'] = $value;
            return;
        }

        // Caso contrário, calcule com base na quantidade e valor unitário
        if (isset($this->attributes['quantidade']) && isset($this->attributes['valor_unitario'])) {
            $quantidade = $this->attributes['quantidade'];
            $valorUnitario = $this->attributes['valor_unitario'];
            $this->attributes['valor_total'] = $quantidade * $valorUnitario;
        }
    }

    // Scopes

    /**
     * Scope para buscar itens de um orçamento específico
     */
    public function scopeDoOrcamento($query, $orcamentoId)
    {
        return $query->where('id_orcamento', $orcamentoId);
    }

    /**
     * Scope para buscar itens vinculados a um item de pedido específico
     */
    public function scopeDoItemPedido($query, $itemPedidoId)
    {
        return $query->where('id_item_pedido', $itemPedidoId);
    }

    // Helpers

    /**
     * Calcula o valor total com base na quantidade e valor unitário
     */
    public function calcularValorTotal()
    {
        return $this->quantidade * $this->valor_unitario;
    }

    /**
     * Atualiza o valor total com base na quantidade e valor unitário
     */
    public function atualizarValorTotal()
    {
        $this->valor_total = $this->calcularValorTotal();
        $this->save();

        // Atualiza também o valor total do orçamento relacionado
        if ($this->orcamento) {
            $this->orcamento->atualizarValorTotal();
        }

        return $this;
    }

    /**
     * Método boot para adicionar hooks do model
     */
    protected static function boot()
    {
        parent::boot();

        // Hook para atualizar o valor total antes de salvar, se não estiver definido
        static::saving(function ($model) {
            if (!isset($model->valor_total) || $model->valor_total == 0) {
                $model->valor_total = $model->calcularValorTotal();
            }
        });

        // Hook para atualizar o valor total do orçamento após salvar ou excluir
        static::saved(function ($model) {
            if ($model->orcamento) {
                $model->orcamento->atualizarValorTotal();
            }
        });

        static::deleted(function ($model) {
            if ($model->orcamento) {
                $model->orcamento->atualizarValorTotal();
            }
        });
    }
}
