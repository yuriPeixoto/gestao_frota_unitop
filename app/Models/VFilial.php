<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VFilial extends Model
{
    protected $table = 'filiais';

    protected $connection = 'pgsql';

    protected $fillable = [
        'id',
        'name',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'filial_id');
    }

    public function fornecedor(): HasMany
    {
        return $this->hasMany(Fornecedor::class, 'id_filial');
    }

    public function sinistro(): HasMany
    {
        return $this->hasMany(Sinistro::class, 'id_filial');
    }

    public function metaTipoEquipamento()
    {
        return $this->hasMany(MetaPorTipoEquipamento::class, 'id_filial');
    }

    /*
    |--------------------------------------------------------------------------
    | Relações do Módulo de Compras
    |--------------------------------------------------------------------------
    */

    /**
     * Solicitações de compra desta filial
     */
    public function solicitacoesCompra(): HasMany
    {
        return $this->hasMany(SolicitacaoCompra::class, 'id_filial', 'id');
    }

    /**
     * Pedidos de compra com entrega nesta filial
     */
    public function pedidosCompraEntrega(): HasMany
    {
        return $this->hasMany(PedidoCompra::class, 'id_filial_entrega', 'id');
    }

    /**
     * Pedidos de compra com faturamento nesta filial
     */
    public function pedidosCompraFaturamento(): HasMany
    {
        return $this->hasMany(PedidoCompra::class, 'id_filial_faturamento', 'id');
    }

    /**
     * Todos os pedidos de compra relacionados a esta filial (entrega ou faturamento)
     */
    public function pedidosCompra()
    {
        return PedidoCompra::where('id_filial_entrega', $this->id)
            ->orWhere('id_filial_faturamento', $this->id);
    }

    /**
     * Notas fiscais relacionadas a esta filial
     */
    public function notasFiscais(): HasMany
    {
        return $this->hasMany(NotaFiscal::class, 'id_filial', 'id');
    }

    /**
     * Fornecedores vinculados a esta filial
     */
    public function fornecedores(): HasMany
    {
        return $this->hasMany(Fornecedor::class, 'id_filial', 'id');
    }

    /**
     * Contratos relacionados a esta filial
     */
    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class, 'id_filial', 'id');
    }

    /**
     * Obtém os estoques desta filial
     */
    public function estoques()
    {
        return $this->hasMany(Estoque::class, 'id_filial', 'id');
    }

    /**
     * Verifica a disponibilidade de um produto específico nesta filial
     *
     * @param int $idProduto ID do produto
     * @return int Quantidade disponível
     */
    public function verificarDisponibilidadeProduto(int $idProduto): int
    {
        return $this->estoques()
            ->whereHas('produtos', function ($query) use ($idProduto) {
                $query->where('id_produto', $idProduto);
            })
            ->sum('quantidade_atual_produto');
    }

    /**
     * Produtos com baixo estoque nesta filial
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function produtosBaixoEstoque()
    {
        return Produto::whereHas('estoques', function ($query) {
            $query->where('id_filial', $this->id)
                ->whereRaw('quantidade_atual_produto <= estoque_minimo');
        })
            ->get();
    }

    /**
     * Solicitações de compra pendentes de aprovação desta filial
     */
    public function solicitacoesPendentes()
    {
        return $this->solicitacoesCompra()
            ->where('status', 'aguardando_aprovacao')
            ->orderBy('data_solicitacao');
    }

    /**
     * Pedidos de compra pendentes de aprovação desta filial
     */
    public function pedidosPendentes()
    {
        return $this->pedidosCompra()
            ->where('status', 'aguardando_aprovacao')
            ->orderBy('data_pedido');
    }

    /**
     * Orçamentos relacionados à filial
     */
    public function orcamentos()
    {
        // Orçamentos dos pedidos relacionados a esta filial
        return Orcamento::whereHas('pedidoCompra', function ($query) {
            $query->where('id_filial_entrega', $this->id)
                ->orWhere('id_filial_faturamento', $this->id);
        });
    }

    public function recCombustivel(): HasMany
    {
        return $this->hasMany(RecebimentoCombustivel::class, 'id_filial');
    }

    public function filialBaixa(): HasMany
    {
        return $this->hasMany(TransferenciaPneus::class, 'filial_baixa');
    }
}
