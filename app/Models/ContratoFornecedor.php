<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContratoFornecedor extends Model
{
    protected $table = 'contrato_fornecedor';
    protected $primaryKey = 'id_contrato_forn';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'is_valido',
        'valor_contrato',
        'doc_contrato',
        'arquivo',
        'numero_contrato',
        'descricao',
        'id_user_cadastro',
        'id_fornecedor',
        'data_inicial',
        'data_final',
        'saldo_contrato'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_inicial' => 'datetime',
        'data_final' => 'datetime',
        'is_valido' => 'boolean',
        'valor_contrato' => 'float',
        'saldo_contrato' => 'float'
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->data_inclusao = now();
        });

        static::updating(function ($model) {
            $model->data_alteracao = now();
        });
    }

    /**
     * Get the fornecedor that owns the contract.
     */
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Get the user who registered the contract.
     */
    public function userCadastro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_cadastro', 'id');
    }

    /**
     * Get the modelos associated with the contract.
     */
    public function modelos(): HasMany
    {
        return $this->hasMany(ContratoModelo::class, 'id_contrato', 'id_contrato_forn');
    }

    /**
     * Get the pedidos de compra associated with the contract.
     */
    public function pedidosCompra(): HasMany
    {
        return $this->hasMany(PedidoCompra::class, 'id_contrato_forn', 'id_contrato_forn');
    }

    /**
     * Get the contrato servicios associated with the contract.
     */
    public function servicosFornecedor(): HasMany
    {
        return $this->hasMany(ServicoFornecedor::class, 'id_contrato_forn', 'id_contrato_forn');
    }

    /**
     * Get the pecas fornecedor associated with the contract.
     */
    public function pecasFornecedor(): HasMany
    {
        return $this->hasMany(PecasFornecedor::class, 'id_contrato_forn', 'id_contrato_forn');
    }

    /**
     * Check if the contract is valid at a specific date
     *
     * @param \DateTime|string|null $date
     * @return bool
     */
    public function isValid($date = null)
    {
        $date = $date ? new \DateTime($date) : now();

        // Primeiro verifica se o contrato está marcado como válido
        if ($this->is_valido !== true) {
            return false;
        }

        // Verifica se está dentro do período de vigência
        $dataInicial = $this->data_inicial ? new \DateTime($this->data_inicial) : null;
        $dataFinal = $this->data_final ? new \DateTime($this->data_final) : null;

        $isWithinPeriod = true;

        if ($dataInicial && $date < $dataInicial) {
            $isWithinPeriod = false;
        }

        if ($dataFinal && $date > $dataFinal) {
            $isWithinPeriod = false;
        }

        return $isWithinPeriod;
    }

    /**
     * Get the remaining days until contract expiration
     *
     * @return int|null
     */
    public function getDiasRestantes()
    {
        if (!$this->data_final) {
            return null;
        }

        $dataFinal = new \DateTime($this->data_final);
        $hoje = new \DateTime();

        // Se já estiver vencido, retorna zero
        if ($hoje > $dataFinal) {
            return 0;
        }

        $diff = $hoje->diff($dataFinal);
        return $diff->days;
    }

    /**
     * Check if contract is near expiration (30 days by default)
     *
     * @param int $diasAlerta
     * @return bool
     */
    public function isProximoVencimento($diasAlerta = 30)
    {
        $diasRestantes = $this->getDiasRestantes();

        if ($diasRestantes === null) {
            return false;
        }

        return $diasRestantes <= $diasAlerta && $diasRestantes > 0;
    }

    /**
     * Indicates if the contract has expired
     *
     * @return bool
     */
    public function isVencido()
    {
        if (!$this->data_final) {
            return false;
        }

        return now() > $this->data_final;
    }

    /**
     * Scope a query to only include valid contracts
     */
    public function scopeValidos($query)
    {
        return $query->where('is_valido', true)
            ->where(function ($q) {
                $q->whereNull('data_final')
                    ->orWhere('data_final', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('data_inicial')
                    ->orWhere('data_inicial', '<=', now());
            });
    }

    /**
     * Scope a query to only include expired contracts
     */
    public function scopeVencidos($query)
    {
        return $query->where('is_valido', true)
            ->whereNotNull('data_final')
            ->where('data_final', '<', now());
    }

    /**
     * Scope a query to only include contracts near expiration
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $dias
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProximosVencimento($query, $dias = 30)
    {
        return $query->where('is_valido', true)
            ->whereNotNull('data_final')
            ->where('data_final', '>=', now())
            ->where('data_final', '<=', now()->addDays($dias));
    }
}
