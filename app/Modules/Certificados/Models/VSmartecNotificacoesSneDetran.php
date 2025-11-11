<?php

namespace App\Modules\Certificados\Models;

use Illuminate\Database\Eloquent\Model;

class VSmartecNotificacoesSneDetran extends Model
{
    protected $table = 'v_smartec_notificacoes_sne_detran';

    public $timestamps = false;

    protected $fillable = [
        'id_smartec_notificacoes_sne_detran',
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
        'gravidade',
        'valor_infracao',
        'data_pagamento',
        'comprovante_pagamento',
        'valor_pago',
        'status_desconto_40',
        'id_solicitante_desconto',
        'data_solicitacao_desconto',
        'confirmacao_pagamento_manual'
    ];
}
