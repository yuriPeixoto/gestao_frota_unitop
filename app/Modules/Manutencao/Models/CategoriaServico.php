<?php

namespace App\Modules\Manutencao\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class CategoriaServico   extends Model
{
    use LogsActivity;

    protected $table = 'categoria_servico';
    protected $primaryKey = 'id_categoria_servico';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_categoria',
        'descricao_categoria',
        'id_servico',
    ];

    public function categoria()
    {
        return $this->belongsTo(Servico::class, 'id_categoria', 'id_categoria');
    }
    public function Servico()
    {
        return $this->belongsTo(Servico::class, 'id_servico', 'id_servico');
    }
}
