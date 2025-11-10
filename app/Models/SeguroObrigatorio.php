<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Traits\ToggleIsActiveOnSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class SeguroObrigatorio extends Model
{
    use LogsActivity, SoftDeletes, ToggleIsActiveOnSoftDelete;

    protected $table      = 'seguroobrigatorioveiculo';
    protected $primaryKey = 'id_seguro_obrigatorio_veiculo';
    public $timestamps    = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'data_vencimento',
        'valor_seguro_previsto',
        'numero_bilhete',
        'data_pagamento',
        'valor_seguro_pago',
        'id_veiculo',
        'ano_validade',
        'is_ativo',
        'situacao',
        'deleted_at',
    ];

    public function scopeAtivos($query)
    {
        return $query->where('is_ativo', true)->whereNull('deleted_at');
    }

    public function scopeInativos($query)
    {
        return $query->where('is_ativo', false)->orWhereNotNull('deleted_at');
    }

    public function scopeTodos($query)
    {
        return $query->withTrashed();
    }

    public function getValorSeguroPrevistoAttribute($value)
    {
        if (!is_null($value) && $value !== '') {
            return 'R$ ' . number_format((float)$value, 2, ',', '.');
        }
        return $value;
    }

    public function setValorSeguroPrevistoAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['valor_seguro_previsto'] = 0;
            return;
        }

        $value = (string)$value;
        $value = trim($value);

        $value = preg_replace('/[^0-9,.]/', '', $value);

        if (strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (strpos($value, '.') !== false) {
            $parts = explode('.', $value);
            if (count($parts) > 2) {
                $value = str_replace('.', '', $value);
            }
        }

        $value = preg_replace('/[^0-9.]/', '', $value);

        if (is_numeric($value)) {
            $this->attributes['valor_seguro_previsto'] = (float)$value;
        } else {
            Log::debug('Valor não é numérico após conversão: "' . $value . '", definindo como 0');
            $this->attributes['valor_seguro_previsto'] = 0;
        }
    }

    public function getValorSeguroPagoAttribute($value)
    {
        if (!is_null($value) && $value !== '') {
            return 'R$ ' . number_format((float)$value, 2, ',', '.');
        }
        return $value;
    }

    public function setValorSeguroPagoAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['valor_seguro_pago'] = 0;
            return;
        }

        $value = (string)$value;
        $value = trim($value);

        $value = preg_replace('/[^0-9,.]/', '', $value);

        if (strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (strpos($value, '.') !== false) {
            $parts = explode('.', $value);
            if (count($parts) > 2) {
                $value = str_replace('.', '', $value);
            }
        }

        $value = preg_replace('/[^0-9.]/', '', $value);

        if (is_numeric($value)) {
            $this->attributes['valor_seguro_pago'] = (float)$value;
        } else {
            Log::debug('Valor não é numérico após conversão: "' . $value . '", definindo como 0');
            $this->attributes['valor_seguro_pago'] = 0;
        }
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
