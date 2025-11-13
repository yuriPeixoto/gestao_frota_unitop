<?php

namespace App\Modules\Premios\Models;

use Illuminate\Database\Eloquent\Model;

class DistanciasSemMotoristaPremio extends Model
{
    protected $table = 'distancias_sem_motorista_premio';
    public $timestamp = false;
    protected $primaryKey = 'id_distancia_sem';
    protected $fillable = [
        'data_inclusao',
        'id_motorista',
        'id_veiculo',
        'subcategoria',
        'km_sem_mot',
        'media',
        'id_franquia',
        'tipo_distancia',
        'data_inicial',
        'data_final',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }
    public function motorista()
    {
        return $this->belongsTo(Motorista::class, 'id_motorista', 'idobtermotorista');
    }
}
