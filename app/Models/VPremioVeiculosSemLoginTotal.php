<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VPremioVeiculosSemLoginTotal extends Model
{
    protected $table = 'v_premio_veiculos_sem_login_total';

    public $timestamps = false;
    protected $guarded = [
        'id_veiculo',
        'id_premio_superacao'
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }
}
