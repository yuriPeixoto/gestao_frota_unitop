<?php

namespace App\Modules\Manutencao\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class SubgrupoServico extends Model
{
    use LogsActivity;

    protected $table = 'subgrupo_servico';
    protected $primaryKey = 'id_subgrupo';
    public $timestamps = false;
    protected $fillable = ['descricao_subgrupo', 'data_inclusao', 'data_alteracao', 'ig_grupo'];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    public function getDescricaoSubgrupoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function grupoServico()
    {
        return $this->belongsTo(GrupoServico::class, 'ig_grupo', 'id_grupo');
    }
}
