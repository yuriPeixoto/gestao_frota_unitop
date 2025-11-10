<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class DetalheMulta extends Model
{
    use LogsActivity;

    protected $table = 'detalhe_multa';
    protected $primaryKey = 'id_detalhe_multa';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'prazo_indicacao_condutor',
        'id_motivo_multa',
        'prazo_defesa',
        'prazo_para_pagamento',
        'prazo_para_recurso',
        'data_indeferimento_recurso',
        'data_envio_departamento',
        'data_envio_financeiro',
        'data_pagamento',
        'data_recebimento_notificacao',
        'data_inicio_recurso',
        'responsavel_recurso'
    ];

    public function motivoMulta(): BelongsTo
    {
        return $this->belongsTo(Multa::class, 'id_motivo_multa');
    }

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
