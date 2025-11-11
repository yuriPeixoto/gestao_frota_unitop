<?php

namespace App\Modules\Imobilizados\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class StatusCadastroImobilizado extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'status_cadastro_imobilizado';
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
