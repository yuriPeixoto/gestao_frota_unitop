<?php

namespace App\Modules\Manutencao\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanejamentoManutencao extends Model
{
    use LogsActivity;


    protected $table = 'planejamentomanutencao';

    protected $primaryKey = 'id_planejamento_manutencao';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_manutencao',
        'status_planejamento',
    ];

    public function manutencao()
    {
        return $this->belongsTo(Manutencao::class, 'id_manutencao', 'id_manutencao');
    }

    // No arquivo PlanejamentoManutencao.php
    public function categorias()
    {
        return $this->hasMany(CategoriaPlanejamentoManutencao::class, 'id_planejamento', 'id_planejamento_manutencao');
    }

    public function servicosPlanejamento()
    {
        return $this->hasMany(ServicoPlanejamentoManutencao::class, 'id_planejamento_manutencao', 'id_servico_planejamento')
            ->with('servico'); // Para trazer a descrição do serviço
    }
}
