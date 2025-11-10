<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use App\Models\Endereco;

class Municipio extends Model
{
    use LogsActivity;

    protected $table = 'municipio';
    protected $primaryKey = 'id_municipio';
    public $timestamps = false;
    protected $fillable = ['nome_municipio', 'uf', 'pais', 'cod_ibge', 'id_uf', 'sigla',  'data_inclusao', 'data_alteracao'];

    public function endereco()
    {
        return $this->hasMany(Endereco::class, 'id_municipio');
    }

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class, 'id_municipio');
    }

    public function getDescricaoEmpresaAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function estado()
    {
        return $this->hasMany(Estado::class, 'id_uf');
    }
}
