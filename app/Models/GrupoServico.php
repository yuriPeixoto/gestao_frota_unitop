<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoServico extends Model
{
    use LogsActivity;

    protected $table = 'grupo_servico';

    protected $primaryKey = 'id_grupo';

    public $timestamps = false;

    protected $fillable = ['data_inclusao', 'data_alteracao', 'descricao_grupo'];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    public function getDescricaoGrupoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function subgrupoServico(): HasMany
    {
        return $this->hasMany(SubgrupoServico::class, 'ig_grupo', 'id_grupo');
    }
}
