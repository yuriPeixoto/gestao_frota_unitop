<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class ListagemAntt extends Model
{
    use LogsActivity;

    protected $table      = 'smartec_antt';
    protected $primaryKey = 'id_smartec_antt';

    protected $fillable = [
        'id_smartec_antt',
        'data_inclusao',
        'data_alteracao',
        'processo',
        'ait',
        'data_infracao',
        'codigo',
        'tipo',
        'placa',
        'situacao',
        'data_notificacao',
        'local',
        'valor_atualizado',
        'data_emissao',
        'cnpj',
        'valor',
        'impeditiva',
        'boleto'
    ];
}
