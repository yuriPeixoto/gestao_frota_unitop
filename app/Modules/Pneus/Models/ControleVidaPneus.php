<?php

namespace App\Modules\Pneus\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ControleVidaPneus extends Model
{
    use LogsActivity;


    protected $table = 'controle_vida_pneu';

    protected $primaryKey = 'id_controle_vida_pneu';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'descricao_vida_pneu',
        'km_rodagem',
        'sulco_pneu_novo',
        'sulco_pneu_reformado',
        'limite_km_rodizio',
        'numero_lonas',
        'id_modelo',
        'id_desenho_pneu_m',
    ];
}