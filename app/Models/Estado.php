<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use App\Models\Fornecedor;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estado extends Model
{
    use LogsActivity;

    protected $table = 'estado';
    protected $primaryKey = 'id_uf';
    public $timestamps = false;
    protected $fillable = ['uf', 'nome', 'codigo_ibge'];

    public function fornecedores(): HasMany
    {
        return $this->hasMany(Fornecedor::class, 'id_uf');
    }

    public function autorizacaoTransito()
    {
        return $this->hasMany(CertificadoVeiculos::class, 'id_uf');
    }

    public function getDescricaoEmpresaAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function filiais()
    {
        return $this->hasMany(Filial::class, 'id_uf', 'id_uf');
    }
}
