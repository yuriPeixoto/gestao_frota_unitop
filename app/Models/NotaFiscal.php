<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaFiscal extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'notas_fiscais';
    protected $primaryKey = 'id_nota_fiscal';

    protected $fillable = [
        'id_pedido',
        'id_fornecedor',
        'numero_nota',
        'serie',
        'chave_nfe',
        'data_emissao',
        'data_recebimento',
        'valor_total',
        'valor_produtos',
        'valor_frete',
        'valor_seguro',
        'valor_desconto',
        'valor_outras_despesas',
        'valor_icms',
        'valor_ipi',
        'valor_pis',
        'valor_cofins',
        'anexo',
        'observacao',
        'status',
        'data_inclusao',
        'data_alteracao',
    ];

    protected $dates = [
        'data_emissao',
        'data_recebimento',
        'data_inclusao',
        'data_alteracao',
        'deleted_at',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_recebimento' => 'date',
        'valor_total' => 'decimal:2',
        'valor_produtos' => 'decimal:2',
        'valor_frete' => 'decimal:2',
        'valor_seguro' => 'decimal:2',
        'valor_desconto' => 'decimal:2',
        'valor_outras_despesas' => 'decimal:2',
        'valor_icms' => 'decimal:2',
        'valor_ipi' => 'decimal:2',
        'valor_pis' => 'decimal:2',
        'valor_cofins' => 'decimal:2',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    /**
     * Indica que o modelo não usa timestamps padrão do Laravel
     */
    public $timestamps = false;

    // Relacionamentos

    /**
     * Relacionamento com o pedido de compra
     */
    public function pedidoCompra()
    {
        return $this->belongsTo(PedidoCompra::class, 'id_pedido', 'id_pedido_compras');
    }

    /**
     * Relacionamento com o fornecedor
     */
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Relacionamento com os itens da nota fiscal
     */
    public function itens()
    {
        return $this->hasMany(ItemNotaFiscal::class, 'id_nota_fiscal', 'id_nota_fiscal');
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
     * Getter para anexo que retorna a URL completa
     */
    public function getAnexoUrlAttribute()
    {
        if (!$this->anexo) {
            return null;
        }

        return asset('storage/' . $this->anexo);
    }

    // Scopes

    /**
     * Scope para buscar notas fiscais com status específico
     */
    public function scopeComStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para buscar notas fiscais pendentes de recebimento
     */
    public function scopePendentes($query)
    {
        return $query->whereNull('data_recebimento')->orWhere('status', 'pendente');
    }

    /**
     * Scope para buscar notas fiscais recebidas
     */
    public function scopeRecebidas($query)
    {
        return $query->whereNotNull('data_recebimento')->where('status', 'recebida');
    }

    /**
     * Scope para buscar notas fiscais de um fornecedor específico
     */
    public function scopeDoFornecedor($query, $fornecedorId)
    {
        return $query->where('id_fornecedor', $fornecedorId);
    }

    /**
     * Scope para buscar notas fiscais de um pedido específico
     */
    public function scopeDoPedido($query, $pedidoId)
    {
        return $query->where('id_pedido', $pedidoId);
    }

    /**
     * Scope para buscar notas fiscais por período de emissão
     */
    public function scopePorPeriodoEmissao($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_emissao', [$dataInicio, $dataFim]);
    }

    /**
     * Scope para buscar notas fiscais por período de recebimento
     */
    public function scopePorPeriodoRecebimento($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_recebimento', [$dataInicio, $dataFim]);
    }

    // Helpers

    /**
     * Calcula o valor total da nota fiscal com base nos itens
     */
    public function calcularValorTotal()
    {
        $valorProdutos = $this->itens->sum('valor_total');
        $valorTotal = $valorProdutos;

        // Adiciona valores adicionais
        if ($this->valor_frete) {
            $valorTotal += $this->valor_frete;
        }

        if ($this->valor_seguro) {
            $valorTotal += $this->valor_seguro;
        }

        if ($this->valor_outras_despesas) {
            $valorTotal += $this->valor_outras_despesas;
        }

        // Subtrai desconto
        if ($this->valor_desconto) {
            $valorTotal -= $this->valor_desconto;
        }

        return $valorTotal;
    }

    /**
     * Atualiza o valor total com base nos itens e outros valores
     */
    public function atualizarValorTotal()
    {
        $this->valor_produtos = $this->itens->sum('valor_total');
        $this->valor_total = $this->calcularValorTotal();
        $this->save();

        return $this;
    }

    /**
     * Marca a nota fiscal como recebida
     */
    public function marcarComoRecebida($dataRecebimento = null)
    {
        $this->data_recebimento = $dataRecebimento ?: now();
        $this->status = 'recebida';
        $this->save();

        return $this;
    }

    /**
     * Verifica se a nota fiscal foi recebida
     */
    public function isRecebida()
    {
        return !is_null($this->data_recebimento) && $this->status === 'recebida';
    }

    /**
     * Método boot para adicionar hooks do model
     */
    protected static function boot()
    {
        parent::boot();

        // Hook para atualizar valores totais antes de salvar
        static::saving(function ($model) {
            // Se não houver valor total ou estiver zerado, recalcule
            if (!isset($model->valor_total) || $model->valor_total == 0) {
                $model->valor_total = $model->calcularValorTotal();
            }
        });
    }
}
