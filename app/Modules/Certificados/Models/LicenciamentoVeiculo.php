<?php

namespace App\Modules\Certificados\Models;

use App\Traits\LogsActivity;
use App\Traits\ToggleIsActiveOnSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LicenciamentoVeiculo extends Model
{
    use LogsActivity, ToggleIsActiveOnSoftDelete, SoftDeletes;

    protected $table = 'licenciamentoveiculo';
    protected $primaryKey = 'id_licenciamento';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'ano_licenciamento',
        'data_emissao_crlv',
        'crlv',
        'data_vencimento',
        'valor_previsto_valor',
        'valor_pago_licenciamento',
        'encargo_detran',
        'valor_multas',
        'id_veiculo',
        'is_ativo',
        'situacao',
        'placa',
        'deleted_at'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_emissao_crlv' => 'datetime',
        'data_vencimento' => 'datetime',
        'deleted_at'
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

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor_previsto_valor, 2, ',', '.');
    }

    public function setValorFormatadoAttribute($value)
    {
        $cleanValue = str_replace(['R$', '.', ' '], '', $value);
        $cleanValue = str_replace(',', '.', $cleanValue);

        $this->attributes['valor_previsto_valor'] = (float) $cleanValue;
    }

    public function getValorPagoFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor_pago_licenciamento, 2, ',', '.');
    }

    public function setValorPagoFormatadoAttribute($value)
    {
        $cleanValue = str_replace(['R$', '.', ' '], '', $value);
        $cleanValue = str_replace(',', '.', $cleanValue);

        $this->attributes['valor_pago_licenciamento'] = (float) $cleanValue;
    }
}
