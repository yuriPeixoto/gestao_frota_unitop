<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoDespesas extends Model
{
    use LogsActivity;

    protected $table = 'tipo_despesas';

    protected $primaryKey = 'id_tipo_despesas';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'descricao_despesas',
    ];

}