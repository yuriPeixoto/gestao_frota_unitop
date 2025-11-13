<?php

namespace App\Modules\Premios\Models;

use Illuminate\Database\Eloquent\Model;

class VPremioDistanciaSemLogin extends Model
{
    protected $table = 'v_premio_distancia_sem_login';
    protected $primaryKey = 'id_distauxiliar';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'data_',
        'placa',
        'id_veiculo',
        'id_distauxiliar',
        'distancia',
        'id_premio_superacao',
    ];

    // public function veiculo()
    // {
    //     return $this->belongsTo(Veiculo::class, 'id_veiculo');
    // }

    public function distanciaAuxiliar()
    {
        return $this->belongsTo(DistanciaMotoristaAuxiliar::class, 'id_distauxiliar', 'id_distanciamotoristaauxiliar');
    }
}
