<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class VeiculoNaoTracionado extends Model
{
    use LogsActivity;

    protected $table = 'veiculonaotracionado';

    protected $primaryKey = 'id_veiculo_nao_tracionado';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'modelo_carroceria',
        'marca_carroceria',
        'tara_nao_tracionado',
        'lotacao_nao_tracionado',
        'ano_carroceria',
        'refrigeracao_carroceria',
        'comprimento_carroceria',
        'largura_carroceria',
        'altura_carroceria',
        'capacidade_volumetrica_1',
        'capacidade_volumetrica_2',
        'capacidade_volumetrica_3',
        'capacidade_volumetrica_4',
        'capacidade_volumetrica_5',
        'capacidade_volumetrica_6',
        'capacidade_volumetrica_7',
        'id_veiculo',
    ];
}
