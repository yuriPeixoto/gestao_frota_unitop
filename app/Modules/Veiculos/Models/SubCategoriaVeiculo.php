<?php

namespace App\Modules\Veiculos\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class SubCategoriaVeiculo extends Model
{
    use LogsActivity;

    protected $table = 'subcategoria';
    protected $primaryKey = 'id_subcategoria';
    public $timestamps = false;
    protected $fillable = ['descricao_subcategoria', 'data_inclusao', 'data_alteracao'];

    public function getDescricaoSubCategoriaAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
    //
}
