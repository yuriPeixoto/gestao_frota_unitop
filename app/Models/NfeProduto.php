<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeProduto extends BaseNfeModel
{
    protected $table = 'nfe_produtos';

    protected $fillable = [
        'id_nfe',
        'nitem',
        'cprod',
        'cean',
        'xprod',
        'ncm',
        'cfop',
        'ucom',
        'qcom',
        'vuncom',
        'vprod',
        'ceantrib',
        'utrib',
        'qtrib',
        'vuntrib',
        'indtot',
        'vtottrib',
        'orig',
        'csosn',
        'pcredsn',
        'vcredicmssn',
        'clenq',
        'cnpjprod',
        'cenq',
        'cst_1',
        'cst_2',
        'cst_3',
        'picms',
        'vicms',
        'ppis',
        'vpis',
        'vbc_1',
        'vbc_2',
        'vbc_3',
        'pcofins',
        'vcofins',
        'vbcufdest',
        'vbcfcpufdest',
        'pfcpufdest',
        'picmsufdest',
        'picmsinter',
        'picmsinterpart',
        'vicmsufdest',
        'vicmsufremet',
        'infadprod',
        'modbc',
        'vdesc'
    ];

    protected $casts = [
        'id_nfe' => 'integer',
        'nitem' => 'integer',
        'ncm' => 'integer',
        'cfop' => 'integer',
        'qcom' => 'float',
        'vuncom' => 'float',
        'vprod' => 'float',
        'qtrib' => 'float',
        'vuntrib' => 'float',
        'indtot' => 'integer',
        'vtottrib' => 'float',
        'orig' => 'integer',
        'csosn' => 'integer',
        'pcredsn' => 'float',
        'vcredicmssn' => 'float',
        'clenq' => 'integer',
        'cnpjprod' => 'integer',
        'cenq' => 'integer',
        'cst_1' => 'integer',
        'cst_2' => 'integer',
        'cst_3' => 'integer',
        'picms' => 'float',
        'vicms' => 'float',
        'ppis' => 'float',
        'vpis' => 'float',
        'vbc_1' => 'float',
        'vbc_2' => 'float',
        'vbc_3' => 'float',
        'pcofins' => 'float',
        'vcofins' => 'float',
        'vbcufdest' => 'float',
        'vbcfcpufdest' => 'float',
        'pfcpufdest' => 'float',
        'picmsufdest' => 'float',
        'picmsinter' => 'float',
        'picmsinterpart' => 'float',
        'vicmsufdest' => 'float',
        'vicmsufremet' => 'float',
        'modbc' => 'integer',
        'vdesc' => 'float',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function nfe()
    {
        return $this->belongsTo(NfeCore::class, 'id_nfe');
    }
}
