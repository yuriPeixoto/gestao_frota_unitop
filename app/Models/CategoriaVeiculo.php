<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class CategoriaVeiculo extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'categoria_veiculo';
    protected $primaryKey = 'id_categoria';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'descricao_categoria',
        'ativo'
    ];
}
