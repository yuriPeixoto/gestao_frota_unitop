<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoVeiculo extends Model
{
    use LogsActivity;

    protected $table = 'tipo_veiculo';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['descricao', 'data_inclusao', 'data_alteracao'];

    public function getDescricaoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function veiculo()
    {
        return   $this->hasMany(Veiculo::class, 'id_tipo_veiculo');
    }
}
