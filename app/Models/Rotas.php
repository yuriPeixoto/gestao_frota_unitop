<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rotas extends Model
{

    protected $connection = 'pgsql';
    protected $table = 'rotas';
    protected $primaryKey = 'id_rotas';
    public $timestamps = false;


    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'sigla',
        'setor',
        'doca',
        'id_tipo_operacao',
        'filial_origem',
        'distancia_km',
        'destino',
        'horario_saida',
        'horario_chegada',
        'dias_frequencia',
        'horario_descarga'
    ];
}
