<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VListarPedidosNf extends Model
{
    protected $connection = 'pgsql';
    /**
     * Nome da tabela associada ao model.
     *
     * @var string
     */
    protected $table = 'v_listar_pedidos_nf';

    /**
     * Indica se o model deve ser timestampable.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Campos que devem ser tratados como datas.
     *
     * @var array
     */
    protected $dates = [
        'data_inclusao',
        'data_solicitacao',
        'data_emissao'
    ];

    /**
     * Conversões de atributos.
     *
     * @var array
     */
    protected $casts = [
        'valor_nota_fiscal' => 'float',
        'valorservico' => 'float',
    ];

    /**
     * Relação com o fornecedor.
     */
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Relação com a ordem de serviço.
     */
    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'os', 'id_ordem_servico');
    }

    /**
     * Relação com o solicitante.
     */
    public function solicitante()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    /**
     * Relação com o pedido de compra.
     */
    public function pedidoCompra()
    {
        return $this->belongsTo(PedidoCompra::class, 'id_pedido_compras', 'id_pedido_compras');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
    /**
     * Escopo para filtrar por data de inclusão.
     */
    public function scopePorDataInclusao($query, $dataInicio, $dataFim = null)
    {
        if (!$dataFim) {
            $dataFim = $dataInicio . ' 23:59:59';
            $dataInicio = $dataInicio . ' 00:00:00';
        }

        return $query->whereBetween('data_inclusao', [$dataInicio, $dataFim]);
    }

    /**
     * Escopo para filtrar por fornecedor.
     */
    public function scopePorFornecedor($query, $idFornecedor)
    {
        return $query->where('id_fornecedor', $idFornecedor);
    }

    /**
     * Escopo para filtrar por pedido.
     */
    public function scopePorPedido($query, $idPedido)
    {
        return $query->where('id_pedido_compras', $idPedido);
    }

    /**
     * Escopo para filtrar por ordem de serviço.
     */
    public function scopePorOs($query, $idOs)
    {
        return $query->where('os', $idOs);
    }

    /**
     * Escopo para filtrar por placa.
     */
    public function scopePorPlaca($query, $placa)
    {
        return $query->where('placa', 'like', "%$placa%");
    }

    /**
     * Escopo para filtrar por filial.
     */
    public function scopePorFilial($query, $filial)
    {
        return $query->where('filial', 'like', "%$filial%");
    }

    /**
     * Escopo para filtrar por número de nota fiscal.
     */
    public function scopePorNumeroNf($query, $numeroNf)
    {
        if (is_array($numeroNf)) {
            return $query->whereIn('numero_nf', $numeroNf);
        }
        return $query->where('numero_nf', 'like', "%$numeroNf%");
    }

    /**
     * Escopo para filtrar por chave de nota fiscal.
     */
    public function scopePorChaveNf($query, $chaveNf)
    {
        if (is_array($chaveNf)) {
            return $query->whereIn('chave_nf', $chaveNf);
        }

        return $query->where('chave_nf', 'like', "%$chaveNf%");
    }

    /**
     * Escopo para filtrar por solicitação.
     */
    public function scopePorSolicitacao($query, $idSolicitacao)
    {
        return $query->where('id_solicitacao', $idSolicitacao);
    }

    /**
     * Escopo para filtrar por tipo de pedido.
     */
    public function scopePorTipoPedido($query, $tipoPedido)
    {
        return $query->where('tipo_pedido', $tipoPedido);
    }
}
