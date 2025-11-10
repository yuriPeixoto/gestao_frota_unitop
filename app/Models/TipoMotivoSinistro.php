<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoMotivoSinistro extends Model
{
    use LogsActivity;

    protected $table = 'motivosinistro';
    protected $primaryKey = 'id_motivo_cinistro';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'descricao_motivo'];

    public function getDescricaoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
