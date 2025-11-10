<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalcularPremioMensaleRV extends Model
{
    protected $table = 'calcular_premio_mensal_e_rv';
    protected $primaryKey = 'id_premio_superacao';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'placa_sascar',
        'id_mot_unitop',
        'id_veiculo_unitop',
        'id_veiculo_sascar',
        'km_inicial',
        'distancia_percorrida',
        'consumo_combustivel',
        'media_consumo',
        'id_franquia_carvalima',
        'valor_franquia',
        'data_inicial',
        'data_final',
        'saldo_credito_debito',
        'id_usuario',
        'data_tratativa',
        'tradado',
        'tipoequipamento',
        'observacao_alteraco_km',
        'is_km_alterado',
        'id_categoria',
        'id_subcategoria',
        'id_operacao',
        'nome_motorista',
        'tipo_calculo',
        'id_gestao_viagem',
        'id_filial',
        'cod_premio',
        'km_medio_sugerido',
        'valor_calculado_media_sugerida_rv'
    ];
}
