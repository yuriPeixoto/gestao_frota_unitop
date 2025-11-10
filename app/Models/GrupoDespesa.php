<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoDespesa extends Model
{
    protected $table = 'grupo_despesa';
    protected $primaryKey = 'id_grupo_despesa';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'descricao_grupo',
        'id_user',
        'id_user_edicao',
        'is_ativo'
    ];
}
