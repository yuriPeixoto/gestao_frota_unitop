<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoOrdemServico extends Model
{
    use LogsActivity;

    protected $table = 'tipo_ordem_servico';
    protected $primaryKey = 'id_tipo_ordem_servico';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'descricao_tipo_ordem'];

    public function OrdemServico(): HasMany
    {
        return $this->hasMany(OrdemServico::class, 'id_tipo_ordem_servico');
    }
}
