<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeDestinatario extends BaseNfeModel
{
    protected $table = 'nfe_destinatario';

    protected $fillable = [
        'id_nfe',
        'cnpj',
        'cpf',
        'xnome',
        'xlgr',
        'nro',
        'xbairro',
        'cmun',
        'xmun',
        'uf',
        'cep',
        'cpais',
        'xpais',
        'fone',
        'indiedest',
        'email'
    ];

    protected $casts = [
        'id_nfe' => 'integer',
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

    public function hasCnpj()
    {
        return !empty($this->cnpj);
    }

    public function hasCpf()
    {
        return !empty($this->cpf);
    }
}
