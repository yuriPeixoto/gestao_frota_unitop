<?php

namespace App\Modules\Premios\Models;

use Illuminate\Database\Eloquent\Model;

class VPremioMotoristaKmValor extends Model
{
    protected $table = 'v_premio_motoristas_km_valor';
    protected $primaryKey = 'id_mot_unitop';
    public $timestamps = false;
    protected $guarded = [
        'cod_premio',
        'id_mot_unitop',
        'nome_motorista',
        'filial',
        'data_inicial',
        'data_final',
        'distancia',
        'valor_premio',
        'qtd_placas',
    ];
}
