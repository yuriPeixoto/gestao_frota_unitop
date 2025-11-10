<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeEmissor extends BaseNfeModel
{
    protected $table = 'nfe_emissor';

    protected $fillable = [
        'id_nfe',
        'cnpj',
        'ie',
        'crt',
        'xnome',
        'xfant',
        'xlgr',
        'nro',
        'xbairro',
        'cmun',
        'xmun',
        'uf',
        'cep',
        'cpais',
        'xpais',
        'fone'
    ];

    protected $casts = [
        'id_nfe' => 'integer',
        'ie' => 'integer',
        'crt' => 'integer',
        'nro' => 'integer',
        'cmun' => 'integer',
        'cep' => 'integer',
        'cpais' => 'integer',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function nfe()
    {
        return $this->belongsTo(NfeCore::class, 'id_nfe');
    }
}
