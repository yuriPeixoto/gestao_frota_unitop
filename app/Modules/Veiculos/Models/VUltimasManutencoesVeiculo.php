<?php

namespace App\Modules\Veiculos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VUltimasManutencoesVeiculo extends Model
{
    protected $table = 'v_ultimas_manutencoes_veiculo';

    public $timestamps = false;

    protected $fillable = [
        'id_ordem_servico',
        'id_veiculo',
        'data_inclusao',
        'id_filial',
        'nome_filial',
        'placa',
        'descricao_categoria',
        'id_manutencao',
        'descricao_manutencao',
        'tipo_manutencao',
        'km_manutencao',
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }

}
