<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeTransportadora extends BaseNfeModel
{
    protected $table = 'nfe_transportadora';

    protected $fillable = [
        'id_nfe',
        'modfrete',
        'xnome',
        'xender',
        'xmun',
        'uf',
        'qvol',
        'marca',
        'nvol',
        'pesol',
        'pesob'
    ];

    protected $casts = [
        'id_nfe' => 'integer',
        'modfrete' => 'integer',
        'qvol' => 'integer',
        'pesol' => 'float',
        'pesob' => 'float',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function nfe()
    {
        return $this->belongsTo(NfeCore::class, 'id_nfe');
    }
}
