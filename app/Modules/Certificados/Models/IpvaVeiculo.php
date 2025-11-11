<?php

namespace App\Modules\Certificados\Models;

use App\Traits\LogsActivity;
use App\Traits\ToggleIsActiveOnSoftDelete;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IpvaVeiculo extends Model
{
    use LogsActivity, SoftDeletes, ToggleIsActiveOnSoftDelete;

    protected $table = 'ipvaveiculo';
    protected $primaryKey = 'id_ipva_veiculo';
    public $activeField = 'is_ativo';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'cota_ipva',
        'data_base_vencimento',
        'valor_previsto_ipva',
        'data_pagamento_ipva',
        'valor_pago_ipva',
        'id_veiculo',
        'quantidade_parcelas',
        'status_ipva',
        'is_ativo',
        'ano_validade',
        'valor_desconto_ipva',
        'valor_juros_ipva',
    ];

    public function scopeAtivos($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeInativos($query)
    {
        return $query->onlyTrashed();
    }

    public function scopeTodos($query)
    {
        return $query->withTrashed();
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function parcelas()
    {
        return $this->hasMany(ParcelasIpva::class, 'id_ipva_veiculo');
    }

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
