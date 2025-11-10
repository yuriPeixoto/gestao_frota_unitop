<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemNotaFiscal extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'itens_nota_fiscal';
    protected $primaryKey = 'id_item_nota';

    protected $fillable = [
        'id_nota_fiscal',
        'id_item_pedido',
        'descricao',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'unidade_medida',
        'cfop',
        'ncm',
        'valor_icms',
        'valor_ipi',
        'aliquota_icms',
        'aliquota_ipi',
        'codigo_produto_fornecedor',
        'data_inclusao',
        'data_alteracao',
    ];

    protected $dates = [
        'data_inclusao',
        'data_alteracao',
        'deleted_at',
    ];

    protected $casts = [
        'quantidade' => 'decimal:2',
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'valor_icms' => 'decimal:2',
        'valor_ipi' => 'decimal:2',
        'aliquota_icms' => 'decimal:2',
        'aliquota_ipi' => 'decimal:2',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    /**
     * Indica que o modelo não usa timestamps padrão do Laravel
     */
    public $timestamps = false;

    // Relacionamentos

    /**
     * Relacionamento com a nota fiscal
     */
    public function notaFiscal()
    {
        return $this->belongsTo(NotaFiscal::class, 'id_nota_fiscal', 'id_nota_fiscal');
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

    /**
     * Setter para valor_icms que calcula com base no valor total e alíquota
     */
    public function setValorIcmsAttribute($value)
    {
        // Se o valor for explicitamente definido, use-o
        if ($value !== null) {
            $this->attributes['valor_icms'] = $value;
            return;
        }

        // Caso contrário, calcule com base no valor total e alíquota
        if (isset($this->attributes['valor_total']) && isset($this->attributes['aliquota_icms'])) {
            $valorTotal = $this->attributes['valor_total'];
            $aliquota = $this->attributes['aliquota_icms'] / 100; // Converter porcentagem para decimal
            $this->attributes['valor_icms'] = $valorTotal * $aliquota;
        }
    }

    /**
     * Setter para valor_ipi que calcula com base no valor total e alíquota
     */
    public function setValorIpiAttribute($value)
    {
        // Se o valor for explicitamente definido, use-o
        if ($value !== null) {
            $this->attributes['valor_ipi'] = $value;
            return;
        }

        // Caso contrário, calcule com base no valor total e alíquota
        if (isset($this->attributes['valor_total']) && isset($this->attributes['aliquota_ipi'])) {
            $valorTotal = $this->attributes['valor_total'];
            $aliquota = $this->attributes['aliquota_ipi'] / 100; // Converter porcentagem para decimal
            $this->attributes['valor_ipi'] = $valorTotal * $aliquota;
        }
    }

    // Scopes

    /**
     * Scope para buscar itens de uma nota fiscal específica
     */
    public function scopeDaNotaFiscal($query, $notaFiscalId)
    {
        return $query->where('id_nota_fiscal', $notaFiscalId);
    }

    /**
     * Scope para buscar itens vinculados a um item de pedido específico
     */
    public function scopeDoItemPedido($query, $itemPedidoId)
    {
        return $query->where('id_item_pedido', $itemPedidoId);
    }

    /**
     * Scope para buscar itens com CFOP específico
     */
    public function scopeComCfop($query, $cfop)
    {
        return $query->where('cfop', $cfop);
    }

    /**
     * Scope para buscar itens com NCM específico
     */
    public function scopeComNcm($query, $ncm)
    {
        return $query->where('ncm', $ncm);
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
     * Calcula o valor do ICMS com base no valor total e alíquota
     */
    public function calcularValorIcms()
    {
        if (!$this->aliquota_icms) {
            return 0;
        }

        return $this->valor_total * ($this->aliquota_icms / 100);
    }

    /**
     * Calcula o valor do IPI com base no valor total e alíquota
     */
    public function calcularValorIpi()
    {
        if (!$this->aliquota_ipi) {
            return 0;
        }

        return $this->valor_total * ($this->aliquota_ipi / 100);
    }

    /**
     * Atualiza os valores calculados (total, ICMS, IPI)
     */
    public function atualizarValores()
    {
        $this->valor_total = $this->calcularValorTotal();
        $this->valor_icms = $this->calcularValorIcms();
        $this->valor_ipi = $this->calcularValorIpi();
        $this->save();

        // Atualiza também o valor total da nota fiscal relacionada
        if ($this->notaFiscal) {
            $this->notaFiscal->atualizarValorTotal();
        }

        return $this;
    }

    /**
     * Método boot para adicionar hooks do model
     */
    protected static function boot()
    {
        parent::boot();

        // Hook para atualizar os valores calculados antes de salvar
        static::saving(function ($model) {
            if (!isset($model->valor_total) || $model->valor_total == 0) {
                $model->valor_total = $model->calcularValorTotal();
            }

            if (!isset($model->valor_icms) || $model->valor_icms == 0) {
                $model->valor_icms = $model->calcularValorIcms();
            }

            if (!isset($model->valor_ipi) || $model->valor_ipi == 0) {
                $model->valor_ipi = $model->calcularValorIpi();
            }
        });

        // Hook para atualizar o valor total da nota fiscal após salvar ou excluir
        static::saved(function ($model) {
            if ($model->notaFiscal) {
                $model->notaFiscal->atualizarValorTotal();
            }
        });

        static::deleted(function ($model) {
            if ($model->notaFiscal) {
                $model->notaFiscal->atualizarValorTotal();
            }
        });
    }
}
