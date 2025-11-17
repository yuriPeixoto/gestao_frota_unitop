<?php

namespace App\Modules\Manutencao\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicoFornecedor extends Model
{
    protected $table = 'servico_fornecedor';
    protected $primaryKey = 'id_servico_forn';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_fornecedor',
        'id_contrato_forn',
        'id_servico',
        'id_grupo_servico',
        'valor_servico',
        'id_contrato_modelo',
        'is_valido',
        'data_inclusao',
        'data_alteracao'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'is_valido' => 'boolean',
        'valor_servico' => 'float'
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
     * Get the fornecedor that owns the servico_fornecedor.
     */
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Get the servico related to this servico_fornecedor.
     */
    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class, 'id_servico', 'id_servico');
    }

    /**
     * Get the grupo servico related to this servico_fornecedor.
     */
    public function grupoServico(): BelongsTo
    {
        return $this->belongsTo(GrupoServico::class, 'id_grupo_servico', 'id_grupo');
    }

    /**
     * Get the contrato fornecedor related to this servico_fornecedor.
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(ContratoFornecedor::class, 'id_contrato_forn', 'id_contrato_forn');
    }

    /**
     * Get the contrato modelo related to this servico_fornecedor.
     */
    public function contratoModelo(): BelongsTo
    {
        return $this->belongsTo(ContratoModelo::class, 'id_contrato_modelo', 'id_contrato_modelo');
    }

    /**
     * Scope a query to only include valid services.
     */
    public function scopeValidos($query)
    {
        return $query->where('is_valido', true);
    }

    /**
     * Get all services related to a specific fornecedor
     *
     * @param int $fornecedorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByFornecedor($fornecedorId)
    {
        return self::where('id_fornecedor', $fornecedorId)
            ->where('is_valido', true)
            ->with(['servico', 'grupoServico', 'contrato', 'contratoModelo'])
            ->get();
    }

    /**
     * Get all services related to a specific contract
     *
     * @param int $contratoId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByContrato($contratoId)
    {
        return self::where('id_contrato_forn', $contratoId)
            ->where('is_valido', true)
            ->with(['servico', 'grupoServico', 'fornecedor', 'contratoModelo'])
            ->get();
    }

    /**
     * Get all services related to a specific model contract
     *
     * @param int $contratoModeloId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByContratoModelo($contratoModeloId)
    {
        return self::where('id_contrato_modelo', $contratoModeloId)
            ->where('is_valido', true)
            ->with(['servico', 'grupoServico', 'fornecedor', 'contrato'])
            ->get();
    }
}
