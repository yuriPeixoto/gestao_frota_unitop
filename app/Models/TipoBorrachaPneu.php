<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoBorrachaPneu extends Model
{
    use LogsActivity;

    protected $table = 'tipoborracha';
    protected $primaryKey = 'id_tipo_borracha';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'descricao_tipo_borracha'];

    public function getDescricaoTipoBorrachaAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
