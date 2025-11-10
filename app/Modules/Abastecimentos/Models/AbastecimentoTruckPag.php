<?php

namespace App\Modules\Abastecimentos\Models;

use Illuminate\Database\Eloquent\Model;

class AbastecimentoTruckPag extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'abastecimento_truck_pag';
    protected $primaryKey = 'transacao';
    public $timestamps = false;

    protected $fillable = [
        'datatransacao',
        'hodometro',
        'valor',
        'litragem',
        'codcombustivel',
        'nomecombustivel',
        'servico',
        'tipoabastecimento',
        'codigotanque',
        'nometanque',
        'codigobomba',
        'nomebomba',
        'razaosocialposto',
        'nomefantasiaposto',
        'cnpjposto',
        'cidadeposto',
        'ufposto',
        'cartaomascarado',
        'motorista',
        'matriculamotorista',
        'cpfmotorista',
        'placa',
        'modeloveiculo',
        'anoveiculo',
        'matriculaveiculo',
        'marcaveiculo',
        'corveiculo',
        'transacaoestornada',
        'cnpjcliente',
        'situacao_faturamento',
        'id_faturamento',
        'tratado',
        'km_anterior',
        'ativo',
        'justificativa',
        'id_veiculo_unitop',
        'id_user_alteracao',
        'data_alteracao'
    ];

    protected $casts = [
        'datatransacao' => 'datetime',
        'tratado' => 'boolean',
        'ativo' => 'boolean',
        'volume' => 'float',
        'km_anterior' => 'float'
    ];
}
