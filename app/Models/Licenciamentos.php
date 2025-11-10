<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Licenciamentos extends Model
{

    protected $table = 'v_smartec_licenciamento';

    public $timestamps = false;

    protected $fillable = [
        'placa',
        'renavam',
        'tipo',
        'uf',
        'mes',
        'valor',
        'guia',
        'status',
        'ano',
        'url'
    ];
}
