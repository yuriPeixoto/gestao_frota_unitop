<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InconsistenciaTruckPag extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'v_inconsistencias_truck_pag_conversao';
    protected $primaryKey = 'id_abastecimento_integracao';
    public $timestamps = false;

    protected $fillable = [
        'id_veiculo',
        'id_abastecimento_integracao',
        'data_inclusao',
        'placa',
        'descricao_bomba',
        'mensagem',
        'tipo_servico',
        'volume',
        'km_abastecimento',
        'km_rodado',
        'id_filial',
        'name',
        'id_departamento',
        'descricao_departamento'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'volume' => 'float',
        'km_abastecimento' => 'integer',
        'km_rodado' => 'integer'
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }

    public function filial()
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    /**
     * Obtém a transação original do TruckPag
     */
    public function getTransacao()
    {
        return DB::connection('pgsql')->table('abastecimento_truck_pag')
            ->where('transacao', $this->id_abastecimento_integracao)
            ->first();
    }
}
