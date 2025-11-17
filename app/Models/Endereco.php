<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use App\Models\Municipio;
use App\Modules\Compras\Models\Fornecedor;
use app\Models\Pessoal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Endereco extends Model
{
    use LogsActivity;

    protected $table = 'endereco';
    protected $primaryKey = 'id_endereco';
    public $timestamps = false;
    protected $fillable = [
        'rua',
        'cep',
        'complemento',
        'numero',
        'data_inclusao',
        'data_alteracao',
        'bairro',
        'id_municipio',
        'id_pessoal_endereco',
        'id_fornecedor_endereco',
        'id_uf'
    ];

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'id_municipio');
    }
    public function uf()
    {
        return $this->belongsTo(Estado::class, 'id_uf');
    }
    public function fornecedor()
    {
        return $this->hasMany(Fornecedor::class, 'id_fornecedor_endereco', 'id_fornecedor');
    }

    public function pessoal(): BelongsTo
    {
        return $this->belongsTo(Pessoal::class, 'id_pessoal_endereco', 'id_pessoal');
    }

    public function getDescricaoEmpresaAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
