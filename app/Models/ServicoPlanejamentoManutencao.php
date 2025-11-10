<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ServicoPlanejamentoManutencao extends Model
{
    use LogsActivity;


    protected $table = 'servico_planejamento_manutencao';

    protected $primaryKey = 'id_servico_planejamento';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_servico',
        'id_manutencao',
        'ativo',
    ];

    public function manutencao()
    {
        return $this->belongsTo(Manutencao::class, 'id_manutencao', 'id_manutencao');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'id_servico', 'id_servico');
    }
}
