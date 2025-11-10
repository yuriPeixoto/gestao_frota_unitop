<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InconsistenciaAts extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'v_inconsistencias_ats';

    public $timestamps = false;

    protected $fillable = [
        'id_veiculo',
        'id_abastecimento_integracao',
        'data_inclusao',
        'placa',
        'descricao_bomba',
        'mensagem',
        'volume',
        'km_abastecimento',
        'tratado',
        'descricao_departamento',
        'id_filial',
        'nomefilial',
        'tipo_combustivel'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'volume' => 'float',
        'km_abastecimento' => 'integer',
        'tratado' => 'boolean'
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
     * Verifica se a inconsistência é do tipo "Sem estoque de combustível"
     */
    public function isSemEstoque()
    {
        return $this->mensagem === 'Sem estoque de combustível, inserir Nota Fiscal';
    }

    /**
     * Obtém o ID do abastecimento ATS relacionado
     */
    public function getAbastecimentoAtsId()
    {
        // Consulta a tabela original para obter o id_abastecimento_ats
        $abastecimento = DB::connection('pgsql')->table('abastecimento_integracao')
            ->where('id_abastecimento_integracao', $this->id_abastecimento_integracao)
            ->first();

        return $abastecimento ? $abastecimento->id_abastecimento_ats : null;
    }

    public function getKMAnterior()
    {
        $abastecimento = DB::connection('pgsql')->table('abastecimento_integracao')
            ->where('id_abastecimento_integracao', $this->id_abastecimento_integracao)
            ->first();

        if (!$abastecimento) {
            return null;
        }

        $kmAnterior = DB::connection('pgsql')->table('abastecimento_integracao')
            ->where('id_abastecimento_integracao', $this->id_abastecimento_integracao)
            ->where('id_veiculo', $abastecimento->id_veiculo)
            ->value('km_anterior');

        return $kmAnterior;
    }
}
