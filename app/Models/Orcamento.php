<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orcamento extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'orcamentos';
    protected $primaryKey = 'id_orcamento';
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_fornecedor',
        'data_orcamento',
        'valor_total',
        'prazo_entrega',
        'validade',
        'observacao',
        'anexo',
        'selecionado',
        'data_inclusao',
        'data_alteracao',
    ];

    protected $dates = [
        'data_orcamento',
        'validade',
        'data_inclusao',
        'data_alteracao',
    ];

    protected $casts = [
        'data_orcamento' => 'date',
        'validade' => 'date',
        'valor_total' => 'decimal:2',
        'selecionado' => 'boolean',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

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
     * Relacionamento com os itens do orçamento
     */
    public function itens()
    {
        return $this->hasMany(ItemOrcamento::class, 'id_orcamento', 'id_orcamento');
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

    // Scopes

    /**
     * Scope para buscar orçamentos selecionados
     */
    public function scopeSelecionado($query)
    {
        return $query->where('selecionado', true);
    }

    /**
     * Scope para buscar orçamentos de um fornecedor específico
     */
    public function scopeDoFornecedor($query, $fornecedorId)
    {
        return $query->where('id_fornecedor', $fornecedorId);
    }

    /**
     * Scope para buscar orçamentos de um pedido específico
     */
    public function scopeDoPedido($query, $pedidoId)
    {
        return $query->where('id_pedido', $pedidoId);
    }

    /**
     * Scope para buscar orçamentos válidos (dentro do prazo de validade)
     */
    public function scopeValidos($query)
    {
        return $query->where('validade', '>=', now()->format('Y-m-d'));
    }

    // Helpers

    /**
     * Verifica se o orçamento está dentro do prazo de validade
     */
    public function isValido()
    {
        return $this->validade >= now();
    }

    /**
     * Verifica se o orçamento está selecionado como vencedor
     */
    public function isSelecionado()
    {
        return (bool) $this->selecionado;
    }

    /**
     * Calcula o valor total do orçamento com base nos itens
     */
    public function calcularValorTotal()
    {
        return $this->itens->sum('valor_total');
    }

    /**
     * Atualiza o valor total com base nos itens
     */
    public function atualizarValorTotal()
    {
        $this->valor_total = $this->calcularValorTotal();
        $this->save();

        return $this;
    }

    /**
     * Marca este orçamento como selecionado e desmarca os demais do mesmo pedido
     */
    public function selecionar()
    {
        self::where('id_pedido', $this->id_pedido)
            ->where('id_orcamento', '!=', $this->id_orcamento)
            ->update(['selecionado' => false]);

        $this->selecionado = true;
        $this->save();

        return $this;
    }
}
