<?php

namespace App\Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class TipoSolicitacao extends Model
{
    use LogsActivity;

    protected $table = 'tipo_solicitacao';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'descricao',
        'data_inclusao',
        'data_alteracao'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];
}
