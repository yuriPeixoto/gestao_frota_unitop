<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoAcertoEstoque extends Model
{
    use LogsActivity;

    protected $table = 'tipo_acerto_estoque';
    protected $primaryKey = 'id_tipo_acerto_estoque';
    public $timestamps = false;
    protected $fillable = ['descricao_tipo_acerto', 'data_inclusao', 'data_alteracao'];

    public function getDescricaoTipoAcertoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
