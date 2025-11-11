<?php

namespace App\Modules\Imobilizados\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoImobilizado extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'tipo_imobilizados';
    protected $primaryKey = 'id_tipo_imobilizados';
    public $timestamps = false;
    protected $fillable = ['descricao_tipo_imobilizados', 'data_inclusao', 'data_alteracao'];

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
