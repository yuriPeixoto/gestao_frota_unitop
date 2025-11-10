<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeFatura extends BaseNfeModel
{
    protected $table = 'nfe_fatura';

    protected $fillable = [
        'id_nfe',
        'nfat',
        'vorig',
        'vliq',
        'ndup',
        'vdup',
        'dvenc'
    ];

    protected $casts = [
        'id_nfe' => 'integer',
        'nfat' => 'integer',
        'vorig' => 'float',
        'vliq' => 'float',
        'ndup' => 'integer',
        'vdup' => 'float',
        'dvenc' => 'date',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function nfe()
    {
        return $this->belongsTo(NfeCore::class, 'id_nfe');
    }
}
