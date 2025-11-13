<?php

namespace App\Modules\Premios\Models;

use Illuminate\Database\Eloquent\Model;

class VPremioMotoristaTotal extends Model
{
    protected $table = 'v_premio_motoristas_total';

    public $timestamps = false;
    protected $guarded = [
        'id_mot_unitop',
        'cod_premio'
    ];
}
