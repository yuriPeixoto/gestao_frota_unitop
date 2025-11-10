<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class StatusOrdemServico extends Model
{
    use LogsActivity;

    protected $table = 'status_ordem_servico';
    protected $primaryKey = 'id_status_ordem_servico';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'situacao_ordem_servico',
    ];

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function ordemServico(): HasMany
    {
        return $this->hasMany(OrdemServico::class, 'id_status_ordem_servico');
    }
}
