<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoStatusPreOs extends Model
{
    use LogsActivity;

    protected $table = 'tipostatus_pre_os';
    protected $primaryKey = 'id_tipostatus_pre_os';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao', 
        'data_alteracao',
        'descricao_tipo_status',
    ];

}
