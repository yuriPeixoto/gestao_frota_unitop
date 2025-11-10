<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VPremioDeflatores extends Model
{
    protected $table = 'v_premio_deflatores';
    protected $primaryKey = 'nome';
    public $timestamps = false;
    protected $fillable = [
        'nome',
        'placa',
        'dmais1',
        'bafometro',
        'celular',
        'cinto',
        'exvelocidade',
        'sinistro',
        'totaldesconto',
        'data_evento',
    ];
}
