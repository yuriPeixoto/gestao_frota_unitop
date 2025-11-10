<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoManutencaoImobilizado extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'tipo_manutencao_imobilizado';
    protected $primaryKey = 'id_tipo_manutencao_imobilizado';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'descricao'];

    public function getDescricaoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
