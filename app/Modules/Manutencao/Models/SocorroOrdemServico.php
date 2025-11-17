<?php

namespace App\Modules\Manutencao\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class SocorroOrdemServico extends Model
{
    use LogsActivity;

    protected $table = 'socorro_ordem_servico';
    protected $primaryKey = 'id_socorro_ordem_servico';
    public $timestamps = false;

    protected $fillable = [
        'id_veiculo',
        'id_socorrista',
        'local_socorro',
        'id_ordem_servico'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    protected $dates = [
        'data_inclusao',
        'data_alteracao',
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'id_ordem_servico');
    }

    public function socorrista()
    {
        return $this->belongsTo(Pessoal::class, 'id_socorrista', 'id_pessoal');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'local_socorro', 'id_municipio');
    }
}
