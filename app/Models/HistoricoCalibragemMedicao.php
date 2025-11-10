<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricoCalibragemMedicao extends Model
{
    protected $table = 'historicocalibragemmedicao';
    protected $primaryKey = 'id_calibragem_medicao';
    public $timestamps = false;
    protected $fillable = [
        //'id_calibragem_medicao',
        'data_inclusao',
        'data_alteracao',
        'id_veiculo',
        'id_pneu',
        'data_medicao',
        'libras',
        'milimetro',
        'id_calibragem_pneu',
    ];
}
