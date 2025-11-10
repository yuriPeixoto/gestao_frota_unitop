<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ToggleIsActiveOnSoftDelete;
use Illuminate\Support\Facades\Log;

class CertificadoVeiculos extends Model
{
    use LogsActivity;
    use SoftDeletes;
    use ToggleIsActiveOnSoftDelete;

    protected $activeField = 'is_ativo';

    protected $table = 'certificadoveiculo';
    protected $primaryKey = 'id_certificado_veiculo';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_veiculo',
        'id_tipo_certificado',
        'data_vencimento',
        'numero_certificado',
        'valor_certificado',
        'data_certificacao',
        'uf',
        'id_uf',
        'chassi',
        'renavam',
        'caminho_arquivo',
        'situacao',
        'is_ativo',
        'deleted_at'
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_certificacao' => 'date',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'deleted_at' => 'datetime',
        'valor_certificado' => 'float'
    ];

    // Solicitado pelo cliente, pois o registro não deve ser excluído fisicamente
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


    // No modelo CertificadoVeiculos
    public function getValorCertificadoAttribute($value)
    {
        if (!is_null($value) && $value !== '') {
            return 'R$ ' . number_format((float)$value, 2, ',', '.');
        }
        return $value;
    }

    public function setValorCertificadoAttribute($value)
    {

        if (empty($value)) {
            $this->attributes['valor_certificado'] = 0;
            return;
        }

        $value = (string)$value;

        $value = trim($value);
        Log::debug('Após trim: "' . $value . '"');

        if (strpos($value, 'R$') !== false) {
            $value = str_replace('R$', '', $value);
            $value = trim($value);
            Log::debug('Após remover R$ e trim: "' . $value . '"');

            $value = str_replace('.', '', $value);
            Log::debug('Após remover pontos: "' . $value . '"');

            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '.', $value);
        }

        $value = preg_replace('/[^0-9.]/', '', $value);

        if (is_numeric($value)) {
            $floatValue = (float)$value;
            $this->attributes['valor_certificado'] = $floatValue;
        } else {
            Log::debug('Valor ainda não é numérico: "' . $value . '", definindo como 0');
            $this->attributes['valor_certificado'] = 0;
        }
    }

    public function getValorCertificadoFormatadoAttribute()
    {
        return 'R$ ' . number_format((float)$this->valor_certificado, 2, ',', '.');
    }

    public function tipocertificado()
    {
        return $this->belongsTo(TipoCertificado::class, 'id_tipo_certificado');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function uf()
    {
        return $this->belongsTo(Estado::class, 'id_uf');
    }

    public function getDescricaoEmpresaAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
