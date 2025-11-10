<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VPremioUnionPlacasDistancia extends Model
{
    protected $table = 'v_premio_union_placas_distancia_';

    public $timestamps = false;
    protected $fillable = [
        'data_inicial',
        'data_final',
        'id_veiculo',
        'placa',
        'id_motorista',
        'nome',
        'id_subcategoria',
        'subcategoria',
        'km_rodado',
        'media',
        'n_rv',
        'tipo',
        'tipo_operacao',
        'id_franquia',
        'id_tipo_operacao',
        'franquia_dados'

    ];
}
