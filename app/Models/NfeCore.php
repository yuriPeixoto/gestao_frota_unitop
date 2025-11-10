<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeCore extends BaseNfeModel
{
    protected $table = 'nfe_core';

    protected $fillable = [
        'infnfe',
        'cuf',
        'cnf',
        'natop',
        'indpag',
        'mod',
        'serie',
        'nnf',
        'dhemi',
        'dhsaient',
        'tpnf',
        'iddest',
        'cmunfg',
        'tpimp',
        'tpemis',
        'cdv',
        'tpamb',
        'finnfe',
        'indfinal',
        'indpres',
        'procemi',
        'verproc',
        'vbc',
        'vicms',
        'vicmsdeson',
        'vbcst',
        'vst',
        'vprod',
        'vfrete',
        'vseg',
        'vdesc',
        'vii',
        'vipi',
        'vpis',
        'vcofins',
        'voutro',
        'vnf',
        'vtottrib',
        'infcpl',
        'signature',
        'canonicalizationmethod',
        'signaturemethod',
        'reference',
        'digestmethod',
        'digestvalue',
        'signaturevalue',
        'x509certificate',
        'id_pedido',
        'id_pedido_compras'
    ];

    protected $casts = [
        'cuf' => 'integer',
        'cnf' => 'integer',
        'indpag' => 'integer',
        'mod' => 'integer',
        'serie' => 'integer',
        'nnf' => 'integer',
        'tpnf' => 'integer',
        'iddest' => 'integer',
        'cmunfg' => 'integer',
        'tpimp' => 'integer',
        'tpemis' => 'integer',
        'cdv' => 'integer',
        'tpamb' => 'integer',
        'finnfe' => 'integer',
        'indfinal' => 'integer',
        'indpres' => 'integer',
        'procemi' => 'integer',
        'vbc' => 'float',
        'vicms' => 'float',
        'vicmsdeson' => 'float',
        'vbcst' => 'float',
        'vst' => 'float',
        'vprod' => 'float',
        'vfrete' => 'float',
        'vseg' => 'float',
        'vdesc' => 'float',
        'vii' => 'float',
        'vipi' => 'float',
        'vpis' => 'float',
        'vcofins' => 'float',
        'voutro' => 'float',
        'vnf' => 'float',
        'vtottrib' => 'float',
        'id_pedido' => 'integer',
        'id_pedido_compras' => 'integer',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function emissor()
    {
        return $this->hasOne(NfeEmissor::class, 'id_nfe');
    }

    public function destinatario()
    {
        return $this->hasOne(NfeDestinatario::class, 'id_nfe');
    }

    public function produtos()
    {
        return $this->hasMany(NfeProduto::class, 'id_nfe');
    }

    public function transportadora()
    {
        return $this->hasOne(NfeTransportadora::class, 'id_nfe');
    }

    public function faturas()
    {
        return $this->hasMany(NfeFatura::class, 'id_nfe');
    }

    public static function nfeExists($infNFe)
    {
        return static::where('infnfe', 'ILIKE', $infNFe)->exists();
    }
}
