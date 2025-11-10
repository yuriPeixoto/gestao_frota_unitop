<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesenhoEixos extends Model
{
    use LogsActivity;


    protected $table = 'desenho_eixos';

    protected $primaryKey = 'id_desenho_eixos';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'descricao',
    ];
}
