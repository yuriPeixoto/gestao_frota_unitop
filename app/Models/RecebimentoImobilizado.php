<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecebimentoImobilizado extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'recebimento_imobilizado';
    protected $primaryKey = 'id_recebimento_imobilizado';
    public $timestamps = false;

    protected $fillable = [
        'motivo_recebimento',
        'id_filial',
        'id_usuario',
        'id_usuario_user',
        'data_inclusao',
        'data_alteracao',
        'is_recebimento',
    ];

    protected $casts = [
        'data_inclusao'   => 'datetime',
        'data_alteracao'  => 'datetime',
    ];
}
