<?php

namespace App\Modules\Veiculos\Models;

use Illuminate\Database\Eloquent\Model;

class VUltimasPreventivasVeiculo extends Model
{
    protected $table = 'v_ultimas_preventivas_veiculo';

    public $timestamps = false;

    protected $fillable = [
        'descricao_categoria',
        'id_veiculo',
        'placa',
        'id_departamento',
        'descricao_departamento',
        'id_filial',
        'nome_filial',
        'id_manutencao',
        'descricao_manutencao',
        'id_tipo_manutencao',
        'tipo_manutencao_discricao',
        'ultkm',
        'datault',
        'km_frequencia',
        'dias_frequencia',
        'id_categoria',
        'km_atual',
        'kmavencer',
        'datavencer',
        'dias_vencidos',
    ];
}
