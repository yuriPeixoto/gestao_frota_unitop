<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Eixos extends Model
{
    use LogsActivity;


    protected $table = 'eixos';

    protected $primaryKey = 'id_eixos';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_desenho_eixos',
        'localizacao'
    ];
}
