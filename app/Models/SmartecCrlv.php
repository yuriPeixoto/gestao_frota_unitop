<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class SmartecCrlv extends Model
{
    use LogsActivity;

    protected $table = 'smartec_crlv';
    protected $primaryKey = 'id_smartec_crlv';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'renavam',
        'licenciamento',
        'uf',
        'municipio',
        'url'
    ];

    protected $casts = [
        'data_inclusao'  => 'datetime',
        'data_alteracao' => 'datetime',
    ];
}
