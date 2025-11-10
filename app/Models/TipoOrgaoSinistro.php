<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoOrgaoSinistro extends Model
{
    use LogsActivity;

    protected $table = 'tipoorgaosinistro';
    protected $primaryKey = 'id_tipo_orgao';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'descricao_tipo_orgao'];

    public function getDescricaoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function sinistro(): HasMany
    {
        return $this->hasMany(Sinistro::class, 'id_tipo_orgao');
    }
}
