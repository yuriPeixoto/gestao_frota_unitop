<?php

namespace App\Modules\Configuracoes\Models;

use Illuminate\Database\Eloquent\Model;

class CargoUsuario extends Model
{
    protected $table = 'cargo_usuario';

    protected $fillable = [
        'cargo_id',
        'usuario_id',
    ];
}
