<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoReformaPneu extends Model
{
    use LogsActivity;

    protected $table = 'tiporeforma';
    protected $primaryKey = 'id_tipo_reforma';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'descricao_tipo_reforma'];

    public function getDescricaoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}