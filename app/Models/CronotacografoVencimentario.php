<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class CronotacografoVencimentario extends Model
{
    use LogsActivity;

    protected $table = 'smartec_cronotacografo';
    protected $primaryKey = 'id_smartec_cronotagografo';
    public $timestamps = false;
    protected $fillable = [
        'id_smartec_cronotagografo',
        'data_inclusao',
        'data_alteracao',
        'placa',
        'renavam',
        'vencimento',
        'status',
        'emissao',
        'documento',
        'documento_n',
        'gru_n',
        'marca',
        'modelo',
        'serie',
        'gru_pagamento',
        'gru_emissao',
        'gru_vencimento',
        'gru_valor',
        'data_pesquisa_gru',
        'certificado',
        'gru_url',
        'linha_digitavel',
        'data_pagamento_gru',
        'gru_erro',
        'gru_erro_mensagem'
    ];
}
