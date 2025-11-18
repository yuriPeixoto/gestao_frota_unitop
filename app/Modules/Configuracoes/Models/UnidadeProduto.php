<?php

namespace App\Modules\Configuracoes\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class UnidadeProduto extends Model
{
    use LogsActivity;
    protected $table = 'unidadeproduto';
    protected $primaryKey = 'id_unidade_produto';
    public $timestamps = false;
    protected $fillable = ['descricao_unidade', 'data_inclusao', 'data_alteracao'];
    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
