<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VSmartecLicenca extends Model
{
    protected $table = 'v_smartec_licenca';

    public $timestamps = false;

    protected $fillable = [
        'renavam',
        'placa',
        'numerocertificado',
        'datainspecap',
        'datavencimento',
        'url',
        'tipo',
        'status',
        'id',
        'licenca_tabela',
    ];
}
