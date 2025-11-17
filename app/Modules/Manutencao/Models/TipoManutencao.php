<?php

namespace App\Modules\Manutencao\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoManutencao extends Model
{
    use LogsActivity;

    protected $table = 'tipomanutencao';
    protected $primaryKey = 'id_tipo_manutencao';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'tipo_manutencao_descricao'];

    public function getDescricaoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
