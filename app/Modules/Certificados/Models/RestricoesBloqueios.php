<?php

namespace App\Modules\Certificados\Models;

use Illuminate\Database\Eloquent\Model;

class RestricoesBloqueios extends Model
{
    protected $table = 'smartec_restricoes';
    protected $primaryKey = 'id_smart_restricoes';

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'restricoes',
        'comunicacaovenda',
        'agentefinanceiro',
        'datainclusao',
        'detranrestricoes',
        'renajudrestricoes',
        'tipobloqueio',
        'protocolo',
        'anoprotocola',
        'processo',
        'anoprocesso',
        'oficio',
        'municipio_bloqueio',
        'motivo',
        'municipio_renajud',
        'data_renajud',
        'hora_renajud',
        'tipo_renajud',
        'codigotribunal',
        'codigoorgaojudicial',
        'processo_restricao',
        'nomeorgaojudicial',
        'placa'
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'placa');
    }
}
