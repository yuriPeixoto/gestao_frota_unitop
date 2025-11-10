<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contrato extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'contratos';
    protected $primaryKey = 'id_contrato';

    protected $fillable = [
        'id_fornecedor',
        'numero_contrato',
        'descricao',
        'data_inicio',
        'data_fim',
        'valor_total',
        'ativo',
        'observacoes',
        'arquivo',
        'data_inclusao',
        'data_alteracao',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'valor_total' => 'decimal:2',
    ];

    /**
     * Obtém o fornecedor associado ao contrato
     */
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Obtém os itens associados ao contrato
     */
    public function itens(): HasMany
    {
        return $this->hasMany(ItemContrato::class, 'id_contrato', 'id_contrato');
    }

    /**
     * Verifica se o contrato está vigente
     *
     * @return bool
     */
    public function isVigente(): bool
    {
        $hoje = now()->startOfDay();
        return $this->ativo &&
            $hoje->gte($this->data_inicio) &&
            $hoje->lte($this->data_fim);
    }

    /**
     * Verifica se o contrato está próximo de vencer
     *
     * @param int $dias Dias para considerar como próximo de vencer (padrão: 30)
     * @return bool
     */
    public function isProximoVencimento(int $dias = 30): bool
    {
        if (!$this->ativo || now()->gt($this->data_fim)) {
            return false;
        }

        $diasRestantes = now()->startOfDay()->diffInDays($this->data_fim, false);
        return $diasRestantes >= 0 && $diasRestantes <= $dias;
    }

    /**
     * Retorna os dias restantes para o vencimento do contrato
     *
     * @return int|null Retorna null se o contrato já venceu
     */
    public function diasRestantes(): ?int
    {
        if (now()->gt($this->data_fim)) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->data_fim, false);
    }

    /**
     * Escopo para contratos vigentes
     */
    public function scopeVigentes($query)
    {
        $hoje = now()->format('Y-m-d');
        return $query->where('ativo', true)
            ->where('data_inicio', '<=', $hoje)
            ->where('data_fim', '>=', $hoje);
    }

    /**
     * Escopo para contratos próximos do vencimento
     *
     * @param int $dias Dias para considerar como próximo de vencer (padrão: 30)
     */
    public function scopeProximosVencimento($query, int $dias = 30)
    {
        $hoje = now()->format('Y-m-d');
        $limite = now()->addDays($dias)->format('Y-m-d');

        return $query->where('ativo', true)
            ->where('data_inicio', '<=', $hoje)
            ->where('data_fim', '>=', $hoje)
            ->where('data_fim', '<=', $limite);
    }
}
