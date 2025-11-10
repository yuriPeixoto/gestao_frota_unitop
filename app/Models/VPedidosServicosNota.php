<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VPedidosServicosNota extends Model
{
    protected $table = 'v_pedidos_servicos_nota';
    public $timestamps = false;

    protected $primaryKey = null;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'data_inclusao' => 'datetime',
        'valor_total_desconto' => 'float'
    ];

    // Scopes
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('data_inclusao', [$start, $end]);
    }

    public function scopeByPlacas($query, $placas)
    {
        return $query->whereIn('placa', (array)$placas);
    }

    public function scopeByFornecedor($query, $fornecedorId)
    {
        return $query->where('id_fornecedor', $fornecedorId);
    }

    public function scopeByPedidos($query, $pedidoIds)
    {
        return $query->whereIn('id_pedido_compras', (array)$pedidoIds);
    }

    public function scopeByOs($query, $osId)
    {
        return $query->where('id_ordem_servico', $osId);
    }

    // Accessors
    public function getCnpjFornecedorAttribute()
    {
        return $this->fornecedor->cnpj_fornecedor ?? $this->fornecedor->cpf_fornecedor ?? '';
    }

    public function getDataFormatadaAttribute()
    {
        return $this->data_inclusao?->format('d/m/Y H:i');
    }

    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor_total_desconto, 2, ',', '.');
    }

    // Relationships
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor');
    }
}
