<?php

namespace App\Modules\Pneus\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoDesenhoPneu extends Model
{
    use LogsActivity;

    protected $table = 'desenhopneu';
    protected $primaryKey = 'id_desenho_pneu';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'descricao_desenho_pneu', 'numero_sulcos', 'quantidade_lona_pneu', 'dias_calibragem'];

    public function getDescricaoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
