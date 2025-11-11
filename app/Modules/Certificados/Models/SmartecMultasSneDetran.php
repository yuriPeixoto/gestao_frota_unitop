<?php

namespace App\Modules\Certificados\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class SmartecMultasSneDetran extends Model
{
    use LogsActivity;

    protected $table = 'smartec_multas_sne_detran';
    protected $primaryKey = 'id_smartec_multas_sne_detran';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'placa',
        'renavam',
        'identificador_smartec',
        'ait',
        'ait_sne',
        'renainf',
        'guia',
        'ait_originario',
        'data_infracao',
        'hora',
        'local_infracao',
        'valor_a_pagar',
        'valor_com_desconto',
        'codigo_municipio',
        'municipio',
        'uf',
        'descricao',
        'codigo_infracao',
        'desdobramento',
        'pontuacao',
        'codigo_orgao',
        'orgao',
        'vencimento_infracao',
        'data_pesquisa',
        'boleto',
        'codigo_boleto',
        'situacao_boleto',
        'descricao_boleto',
        'boleto_valor',
        'linha_digitivel',
        'boleto_vencimento',
        'confirmacao_pagamento',
        'penalidade',
        'motorista_nome',
        'motorista_matricula',
        'valor_desconto',
        'orgao_adesao_sne',
        'desconto',
        'status_desconto_40',
        'id_solicitante_desconto',
        'data_solicitacao_desconto'
    ];
}
