<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoOperacao extends Model
{
    use LogsActivity;

    protected $table = 'tipo_operacao';
    protected $primaryKey = 'id_tipo_operacao';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'descricao_tipo_operacao', 'km_operacao'];
}
