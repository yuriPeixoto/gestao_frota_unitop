<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioDeparmanto extends Model
{
    protected $table = 'usuario_deparmanto';

    protected $fillable = [
        'id_usuario_departamento',
        'id_user',
        'id_departamento',
        'id_cargo',
    ];

    public function usuarios()
    {
        return $this->hasMany(Departamento::class, 'filial_id');
    }

    public function user()
    {
        return User::find($this->departamento_id);
    }
}
