<?php

namespace App\Modules\Veiculos\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ControlesVeiculo extends Model
{
    use LogsActivity;

    protected $table = 'controlesveiculo';
    protected $primaryKey = 'id_controle_veiculo';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'is_controle_manutencao',
        'is_controla_licenciamento',
        'is_controla_seguro_obrigatorio',
        'is_controla_ipva',
        'is_controla_pneu',
        'is_considera_para_rateio',
        'id_veiculo',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }
}
