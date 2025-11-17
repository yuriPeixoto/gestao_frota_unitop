<?php

namespace App\Modules\Veiculos\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModeloVeiculo extends Model
{
    use LogsActivity;

    protected $table = 'modelo_veiculo';
    protected $primaryKey = 'id_modelo_veiculo';
    public $timestamps = false;
    protected $fillable = ['descricao_modelo_veiculo', 'ano', 'mulcombustivel', 'ativo', 'marca', 'data_inclusao', 'data_alteracao'];

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function veiculo(): HasMany
    {
        return $this->hasMany(Veiculo::class, 'id_modelo_veiculo');
    }
}
