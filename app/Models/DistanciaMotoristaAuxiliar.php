<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistanciaMotoristaAuxiliar extends Model
{
    protected $table = 'distanciamotoristaauxiliar';
    protected $primaryKey = 'id_distanciamotoristaauxiliar';
    public $timestamps = false;

    protected $fillable = [
        'id_distanciamotoristaauxiliar',
        'distancia',
        'idveiculo',
        'idmotorista',
        'data_',
        'ativo',
        'data_atrativa',
        'consumo',
        'media',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'idveiculo', 'id_veiculo');
    }
    public function motorista()
    {
        return $this->belongsTo(Motorista::class, 'idmotorista', 'idobtermotorista');
    }
}
