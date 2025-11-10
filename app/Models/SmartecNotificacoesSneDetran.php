<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class SmartecNotificacoesSneDetran extends Model
{
    use LogsActivity;

    protected $table = 'smartec_notificacoes_sne_detran';
    protected $primaryKey = 'id_smartec_notificacoes_sne_detran';
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
        'ait_originaria',
        'renainf_originaria',
        'data_infracao',
        'hora',
        'local',
        'codigo_municipio',
        'municipio',
        'uf',
        'descricao',
        'codigo_infracao',
        'desdobramento',
        'pontuacao',
        'codigo_orgao',
        'orgao_autuador',
        'prazo_indicacao',
        'data_pesquisa',
        'notificacao',
        'boleto',
        'codigo_boleto',
        'situacao_boleto',
        'descricao_boleto',
        'boleto_valor',
        'linha_digitivel',
        'boleto_vencimento',
        'confirmacao_pagamento',
        'motorista_nome',
        'motorista_matricula',
        'valor_a_pagar',
        'status_desconto_40',
        'id_solicitante_desconto',
        'data_solicitacao_desconto'
    ];
}
