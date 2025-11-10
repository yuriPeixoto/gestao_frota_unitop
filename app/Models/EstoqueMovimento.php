<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstoqueMovimento extends Model
{
    protected $table = 'estoque_movimentos';
    protected $primaryKey = 'id_movimento';
    public $timestamps = false;

    protected $fillable = [
        'id_estoque_item',
        'tipo_movimento',
        'quantidade',
        'origem',
        'destino',
        'id_referencia',
        'id_usuario',
        'observacao',
        'data_movimento'
    ];

    protected $casts = [
        'quantidade' => 'float',
        'data_movimento' => 'datetime',
    ];

    /**
     * Relacionamento com o modelo EstoqueItem
     */
    public function estoqueItem(): BelongsTo
    {
        return $this->belongsTo(EstoqueItem::class, 'id_estoque_item', 'id_estoque_item');
    }

    /**
     * Relacionamento com o modelo User
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    /**
     * Escopo para movimentos de entrada
     */
    public function scopeEntradas($query)
    {
        return $query->where('tipo_movimento', 'entrada');
    }

    /**
     * Escopo para movimentos de saída
     */
    public function scopeSaidas($query)
    {
        return $query->where('tipo_movimento', 'saida');
    }

    /**
     * Escopo para movimentos de ajuste
     */
    public function scopeAjustes($query)
    {
        return $query->where('tipo_movimento', 'ajuste');
    }

    /**
     * Escopo para movimentos de transferência
     */
    public function scopeTransferencias($query)
    {
        return $query->where('tipo_movimento', 'transferencia');
    }

    /**
     * Escopo para movimentos por tipo de origem
     */
    public function scopePorOrigem($query, $origem)
    {
        return $query->where('origem', $origem);
    }

    /**
     * Escopo para movimentos por tipo de destino
     */
    public function scopePorDestino($query, $destino)
    {
        return $query->where('destino', $destino);
    }

    /**
     * Escopo para movimentos por tipo de referência
     */
    public function scopePorReferencia($query, $idReferencia)
    {
        return $query->where('id_referencia', $idReferencia);
    }

    /**
     * Atributo que retorna o sinal do movimento (positivo para entrada, negativo para saída)
     */
    public function getValorMovimentoAttribute()
    {
        if ($this->tipo_movimento == 'entrada' || ($this->tipo_movimento == 'ajuste' && $this->quantidade >= 0)) {
            return $this->quantidade;
        }

        return -1 * $this->quantidade;
    }

    /**
     * Método para registrar entrada no estoque
     */
    public static function registrarEntrada($idEstoqueItem, $quantidade, $origem = null, $idReferencia = null, $observacao = null)
    {
        return self::create([
            'id_estoque_item' => $idEstoqueItem,
            'tipo_movimento' => 'entrada',
            'quantidade' => $quantidade,
            'origem' => $origem,
            'id_referencia' => $idReferencia,
            'id_usuario' => auth()->id(),
            'observacao' => $observacao,
            'data_movimento' => now(),
        ]);
    }

    /**
     * Método para registrar saída no estoque
     */
    public static function registrarSaida($idEstoqueItem, $quantidade, $destino = null, $idReferencia = null, $observacao = null)
    {
        return self::create([
            'id_estoque_item' => $idEstoqueItem,
            'tipo_movimento' => 'saida',
            'quantidade' => $quantidade,
            'destino' => $destino,
            'id_referencia' => $idReferencia,
            'id_usuario' => auth()->id(),
            'observacao' => $observacao,
            'data_movimento' => now(),
        ]);
    }

    /**
     * Método para registrar ajuste no estoque
     */
    public static function registrarAjuste($idEstoqueItem, $quantidade, $observacao = null)
    {
        return self::create([
            'id_estoque_item' => $idEstoqueItem,
            'tipo_movimento' => 'ajuste',
            'quantidade' => $quantidade,
            'id_usuario' => auth()->id(),
            'observacao' => $observacao,
            'data_movimento' => now(),
        ]);
    }

    /**
     * Método para registrar transferência entre estoques
     */
    public static function registrarTransferencia($idEstoqueItemOrigem, $idEstoqueItemDestino, $quantidade, $observacao = null)
    {
        // Registrar saída do estoque de origem
        $saida = self::create([
            'id_estoque_item' => $idEstoqueItemOrigem,
            'tipo_movimento' => 'transferencia',
            'quantidade' => $quantidade,
            'destino' => 'transferencia',
            'id_referencia' => $idEstoqueItemDestino,
            'id_usuario' => auth()->id(),
            'observacao' => $observacao,
            'data_movimento' => now(),
        ]);

        // Registrar entrada no estoque de destino
        $entrada = self::create([
            'id_estoque_item' => $idEstoqueItemDestino,
            'tipo_movimento' => 'transferencia',
            'quantidade' => $quantidade,
            'origem' => 'transferencia',
            'id_referencia' => $idEstoqueItemOrigem,
            'id_usuario' => auth()->id(),
            'observacao' => $observacao,
            'data_movimento' => now(),
        ]);

        return ['saida' => $saida, 'entrada' => $entrada];
    }
}
