<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoOcorrencia extends Model
{
    use LogsActivity;

    protected $table = 'tipoocorrencia';
    protected $primaryKey = 'id_tipo_ocorrencia';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'descricao_ocorrencia'];

    public function getDescricaoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
