<?php

namespace App\Modules\Compras\Models;

use App\Traits\FilterTrait;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class PedidoCompra extends Model
{
    use FilterTrait, LogsActivity;

    protected $connection = 'pgsql';

    protected $table = 'pedido_compras';

    protected $primaryKey = 'id_pedido_compras';

    public $timestamps = false;

    protected $fillable = [
        'id_orcamento',
        'data_inclusao',
        'data_alteracao',
        'situacao_pedido',
        'valor_total',
        'valor_total_desconto',
        'id_fornecedor',
        'id_comprador',
        'situacao',
        'cotacoes',
        'id_solicitacoes_compras',
        'observacao_pedido',
        'id_filial',
        'id_aprovador_pedido',
        'tipo_pedido',
        'nota_servico_processado',
        'is_processada',
        'pre_pedido',
        'id_pedido_geral',
        'filial_faturamento',
        'filial_entrega',
        'is_liberado',
        'valor_total_sem_percentual',
        'justificativa',
        'pedido_faturado',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'valor_total' => 'float',
        'valor_total_desconto' => 'float',
        'valor_total_sem_percentual' => 'float',
        'nota_servico_processado' => 'boolean',
        'is_processada' => 'boolean',
        'pre_pedido' => 'boolean',
        'is_liberado' => 'boolean',
        'pedido_faturado' => 'boolean',
    ];

    protected $dates = [
        'data_inclusao',
        'data_alteracao',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->data_inclusao = now();
        });

        static::updating(function ($model) {
            $model->data_alteracao = now();
        });
    }

    /**
     * Get the solicitação that originated the pedido
     */
    public function solicitacaoCompra()
    {
        return $this->belongsTo(SolicitacaoCompra::class, 'id_solicitacoes_compras', 'id_solicitacoes_compras');
    }

    /**
     * Get the situação do pedido
     */
    public function situacaoPedido()
    {
        return $this->belongsTo(SituacaoPedido::class, 'situacao_pedido', 'id_situacao_pedido');
    }

    /**
     * Get the comprador
     */
    public function comprador()
    {
        return $this->belongsTo(User::class, 'id_comprador');
    }

    /**
     * Get the aprovador
     */
    public function aprovador()
    {
        return $this->belongsTo(User::class, 'id_aprovador_pedido');
    }

    /**
     * Get the fornecedor
     */
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Get the filial
     */
    public function filial()
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    /**
     * Get the filial de faturamento
     */
    public function filialFaturamento()
    {
        return $this->belongsTo(VFilial::class, 'filial_faturamento', 'id');
    }

    /**
     * Get the filial de entrega
     */
    public function filialEntrega()
    {
        return $this->belongsTo(VFilial::class, 'filial_entrega', 'id');
    }

    /**
     * Get the itens do pedido
     */
    public function itens()
    {
        return $this->hasMany(ItensPedidos::class, 'id_pedido_compras', 'id_pedido_compras');
    }

    /**
     * Get the orçamentos
     */
    public function orcamentos()
    {
        return $this->hasMany(Orcamento::class, 'id_pedido', 'id_pedido_compras');
    }

    /**
     * Get the notas fiscais
     */
    public function notasFiscais()
    {
        return $this->hasMany(NfOrdemServico::class, 'id_pedido', 'id_pedido_compras');
    }

    /**
     * Get the ordens de serviço relacionadas
     */
    public function ordensServico()
    {
        return $this->belongsToMany(
            OrdemServico::class,
            'pedidos_ordem_aux',
            'id_pedido_compras',
            'id_ordem_servico'
        );
    }

    /**
     * Scope para pedidos pendentes de aprovação
     */
    public function scopePendentesAprovacao($query)
    {
        // Pendente de aprovação: não liberado, não cancelado e não é pré-pedido
        // situacao_pedido referencia tabela situacao_pedido; consideramos CANCELADO via join-lazy or numeric code 6 quando usado em agregações
        return $query->where('is_liberado', false)
            ->where('pre_pedido', false)
            ->where(function ($q) {
                // Excluir explicitamente pedidos faturados e processados
                $q->whereNull('pedido_faturado')->orWhere('pedido_faturado', false);
            });
    }

    /**
     * Scope para pedidos aprovados
     */
    public function scopeAprovados($query)
    {
        return $query->where('is_liberado', true);
    }

    /**
     * Scope para pedidos finalizados
     */
    public function scopeFinalizados($query)
    {
        return $query->where('pedido_faturado', true);
    }

    /**
     * Scope para pedidos em processamento
     */
    public function scopeEmProcessamento($query)
    {
        return $query->where('is_liberado', true)
            ->where('pedido_faturado', false);
    }

    /**
     * Scope para pré-pedidos
     */
    public function scopePrePedidos($query)
    {
        return $query->where('pre_pedido', true);
    }

    /**
     * Obter o status formatado para display
     */
    public function getStatusAttribute()
    {

        if ($this->pedido_faturado) {
            return 'Finalizado';
        }

        if ($this->pre_pedido) {
            return 'Pré-Pedido';
        }

        if ($this->is_liberado && $this->situacaoPedido->descricao_situacao_pedido === 'APROVADO') {
            return 'Aprovado';
        }

        if ($this->situacaoPedido && $this->situacaoPedido->descricao_situacao_pedido === 'CANCELADO') {
            return 'Cancelado';
        }

        if ($this->situacaoPedido && $this->situacaoPedido->descricao_situacao_pedido === 'ENVIADO') {
            return 'Enviado';
        }

        return 'Pendente';
    }

    /**
     * Obter a classe CSS para o status
     */
    public function getStatusClassAttribute()
    {
        $status = $this->status;

        return match ($status) {
            'Aprovado' => 'bg-green-100 text-green-800',
            'Enviado' => 'bg-orange-100 text-orange-800',
            'Pré-Pedido' => 'bg-purple-100 text-purple-800',
            'Cancelado' => 'bg-gray-100 text-gray-800',
            'Finalizado' => 'bg-blue-100 text-blue-800',
            'Pendente' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Verificar se o pedido pode ser editado
     */
    public function podeSerEditado()
    {
        return ! $this->is_liberado && ! $this->pedido_faturado && ! $this->pre_pedido;
    }

    /**
     * Verificar se o pedido pode ser aprovado
     */
    public function podeSerAprovado()
    {
        return ! $this->is_liberado && ! $this->pedido_faturado
            && $this->situacaoPedido->descricao_situacao_pedido !== 'CANCELADO';
    }

    public function podeSerEnviado()
    {
        return $this->is_liberado && ! $this->pedido_faturado
            && $this->situacaoPedido->descricao_situacao_pedido !== 'ENVIADO';
    }

    /**
     * Verificar se o pedido pode ser cancelado
     */
    public function podeSerCancelado()
    {
        return ! $this->pedido_faturado;
    }

    /**
     * Verificar se o pedido pode ser finalizado
     */
    public function podeSerFinalizado()
    {
        return $this->is_liberado && ! $this->pedido_faturado;
    }

    /**
     * Verificar se o pedido é de produto
     */
    public function isProduto()
    {
        return $this->tipo_pedido === 1;
    }

    /**
     * Verificar se o pedido é de serviço
     */
    public function isServico()
    {
        return $this->tipo_pedido === 2;
    }

    /**
     * Gerar número do pedido a partir do ID
     */
    public function getNumeroAttribute()
    {
        return str_pad($this->id_pedido_compras, 6, '0', STR_PAD_LEFT);
    }
}
