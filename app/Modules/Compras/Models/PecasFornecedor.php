<?php

namespace App\Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PecasFornecedor extends Model
{
    protected $table = 'pecas_fornecedor';
    protected $primaryKey = 'id_pecas_forn';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_grupo_pecas',
        'id_produto',
        'valor_produto',
        'id_contrato_forn',
        'id_fornecedor',
        'is_valido',
        'id_contrato_modelo'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'valor_produto' => 'float',
        'is_valido' => 'boolean'
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
     * Get the fornecedor that owns the peça.
     */
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Get the contrato that owns the peça.
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(ContratoFornecedor::class, 'id_contrato_forn', 'id_contrato_forn');
    }

    /**
     * Get the contrato modelo that owns the peça.
     */
    public function contratoModelo(): BelongsTo
    {
        return $this->belongsTo(ContratoModelo::class, 'id_contrato_modelo', 'id_contrato_modelo');
    }

    /**
     * Get the produto that owns the peça.
     */
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }

    /**
     * Get the grupo servico that owns the peça.
     * Note que usamos GrupoServico devido ao relacionamento no banco
     */
    public function grupoPecas(): BelongsTo
    {
        return $this->belongsTo(GrupoServico::class, 'id_grupo_pecas', 'id_grupo');
    }
}
