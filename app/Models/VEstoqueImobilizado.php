<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class VEstoqueImobilizado extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'v_estoque_imobilizado';

    protected $primaryKey = 'id_produto_unitop';

    protected $fillable = [
        'descricao_produto',
        'id_filial',
        'descricao_filial',
        'id_departamento',
        'descricao_departamento',
        'status',
        'quantidade_imobilizado',
        'valor_medio',
        'total',
    ];
}
