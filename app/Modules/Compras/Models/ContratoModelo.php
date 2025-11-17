<?php

namespace App\Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContratoModelo extends Model
{
    protected $table = 'contrato_modelo';
    protected $primaryKey = 'id_contrato_modelo';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_modelo',
        'id_contrato',
        'ativo',
        'id_user'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'ativo' => 'boolean'
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
     * Get the contrato relacionado.
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(ContratoFornecedor::class, 'id_contrato', 'id_contrato_forn');
    }

    /**
     * Get the modelo relacionado.
     */
    public function modelo(): BelongsTo
    {
        return $this->belongsTo(ModeloVeiculo::class, 'id_modelo', 'id_modelo_veiculo');
    }

    /**
     * Get the user relacionado.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    /**
     * Get the pecas fornecedor that use this contrato modelo.
     */
    public function pecasFornecedor(): HasMany
    {
        return $this->hasMany(PecasFornecedor::class, 'id_contrato_modelo', 'id_contrato_modelo');
    }

    /**
     * Get the servicos fornecedor that use this contrato modelo.
     */
    public function servicosFornecedor(): HasMany
    {
        return $this->hasMany(ServicoFornecedor::class, 'id_contrato_modelo', 'id_contrato_modelo');
    }

    /**
     * Scope a query to only include active contratos modelo.
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Check if the contract is associated with a valid contract
     *
     * @return bool
     */
    public function hasValidContract()
    {
        return $this->contrato && $this->contrato->isValid();
    }

    /**
     * Get all models related to a specific fornecedor's contracts
     *
     * @param int $fornecedorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByFornecedor($fornecedorId)
    {
        return self::whereHas('contrato', function ($query) use ($fornecedorId) {
            $query->where('id_fornecedor', $fornecedorId)
                ->where('is_valido', true);
        })->with(['modelo', 'contrato'])->get();
    }

    /**
     * Get all active models associated with valid contracts
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveWithValidContracts()
    {
        return self::where('ativo', true)
            ->whereHas('contrato', function ($query) {
                $query->where('is_valido', true)
                    ->where(function ($q) {
                        $q->whereNull('data_final')
                            ->orWhere('data_final', '>=', now());
                    });
            })
            ->with(['modelo', 'contrato', 'contrato.fornecedor'])
            ->get();
    }
}
