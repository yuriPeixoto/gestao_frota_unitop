<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoCertificado extends Model
{
    use LogsActivity;

    protected $table      = 'tipocertificado';
    protected $primaryKey = 'id_tipo_certificado';
    public $timestamps    = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'descricao_certificado',
        'orgao_certificado'
    ];

    protected $casts = [
        'data_inclusao'  => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function certificados()
    {
        return $this->hasMany(CertificadoVeiculos::class, 'id_tipo_certificado');
    }

    /**
     * Retorna a descrição formatada para exibição
     */
    public function getDescricaoCertificadoFormatadaAttribute()
    {
        return mb_strtoupper($this->descricao_certificado ?? '', 'UTF-8');
    }

    /**
     * Retorna o orgão formatado para exibição
     */
    public function getOrgaoCertificadoFormatadoAttribute()
    {
        return mb_strtoupper($this->orgao_certificado ?? '', 'UTF-8');
    }
}
