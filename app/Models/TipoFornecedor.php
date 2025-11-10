<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use App\Models\Fornecedor;

class TipoFornecedor extends Model
{
    use LogsActivity;

    protected $table = 'tipofornecedor';
    protected $primaryKey = 'id_tipo_fornecedor';
    public $timestamps = false;
    protected $fillable = ['descricao_tipo', 'data_inclusao', 'data_alteracao'];

    public function fornecedores()
    {
        return $this->hasMany(Fornecedor::class, 'id_tipo_fornecedor');
    }

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
