<?php

namespace App\Modules\Certificados\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ParcelasIpva extends Model
{
    use LogsActivity;

    protected $table = 'parcelasipva';
    protected $primaryKey = 'id_parcelas_ipva';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'numero_parcela',
        'data_vencimento',
        'valor_parcela',
        'id_ipva_veiculo',
        'valor_pagamento',
        'data_pagamento',
        'valor_desconto',
        'valor_juros'
    ];

    public function ipva()
    {
        return $this->belongsTo(IpvaVeiculo::class, 'id_ipva_veiculo');
    }

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
