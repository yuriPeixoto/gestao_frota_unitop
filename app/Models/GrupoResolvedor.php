<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class GrupoResolvedor extends Model
{
    use LogsActivity;

    protected $table = 'grupo_resolvedor';
    protected $primaryKey = 'id_grupo_resolvedor';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusa',
        'data_alteracao',
        'descricao_grupo_resolvedor'
    ];
}
