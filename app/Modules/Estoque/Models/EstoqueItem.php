<?php

namespace App\Modules\Estoque\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstoqueItem extends Model
{
    protected $table = 'estoque_itens';

    protected $primaryKey = 'id_estoque_item';

    public $timestamps = false;

    protected $fillable = [
        'id_estoque',
        'id_produto',
        'quantidade_atual',
        'quantidade_minima',
        'quantidade_maxima',
        'localizacao',
        'data_ultima_entrada',
        'data_ultima_saida',
        'data_inclusao',
        'data_alteracao',
        'ativo',
    ];

    protected $casts = [
        'quantidade_atual' => 'float',
        'quantidade_minima' => 'float',
        'quantidade_maxima' => 'float',
        'data_ultima_entrada' => 'datetime',
        'data_ultima_saida' => 'datetime',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento com o modelo Estoque
     */
    public function estoque(): BelongsTo
    {
        return $this->belongsTo(Estoque::class, 'id_estoque', 'id_estoque');
    }

    /**
     * Relacionamento com o modelo Produto
     */
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }

    /**
     * Verifica se o item está com estoque baixo
     */
    public function isEstoqueBaixo(): bool
    {
        return $this->quantidade_atual <= $this->quantidade_minima;
    }

    /**
     * Verifica se o item possui quantidade suficiente para atender uma solicitação
     */
    public function hasQuantidadeSuficiente(float $quantidade): bool
    {
        return $this->quantidade_atual >= $quantidade;
    }

    /**
     * Registra entrada de produto no estoque
     */
    public function registrarEntrada(float $quantidade, string $origem = 'compra', ?int $id_referencia = null): bool
    {
        if ($quantidade <= 0) {
            return false;
        }

        $this->quantidade_atual += $quantidade;
        $this->data_ultima_entrada = now();
        $this->data_alteracao = now();

        $resultado = $this->save();

        // Registrar o movimento
        if ($resultado) {
            EstoqueMovimento::create([
                'id_estoque_item' => $this->id_estoque_item,
                'tipo_movimento' => 'entrada',
                'quantidade' => $quantidade,
                'origem' => $origem,
                'id_referencia' => $id_referencia,
                'data_movimento' => now(),
            ]);
        }

        return $resultado;
    }

    /**
     * Registra saída de produto do estoque
     */
    public function registrarSaida(float $quantidade, string $destino = 'requisicao', ?int $id_referencia = null): bool
    {
        if ($quantidade <= 0 || $quantidade > $this->quantidade_atual) {
            return false;
        }

        $this->quantidade_atual -= $quantidade;
        $this->data_ultima_saida = now();
        $this->data_alteracao = now();

        $resultado = $this->save();

        // Registrar o movimento
        if ($resultado) {
            EstoqueMovimento::create([
                'id_estoque_item' => $this->id_estoque_item,
                'tipo_movimento' => 'saida',
                'quantidade' => $quantidade,
                'destino' => $destino,
                'id_referencia' => $id_referencia,
                'data_movimento' => now(),
            ]);
        }

        return $resultado;
    }

    /**
     * Escopo para itens com estoque baixo
     */
    public function scopeEstoqueBaixo($query)
    {
        return $query->whereRaw('quantidade_atual <= quantidade_minima')
            ->where('ativo', true);
    }

    /**
     * Escopo para itens ativos
     */
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }
}
