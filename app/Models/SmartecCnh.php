<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class SmartecCnh extends Model
{
    use LogsActivity;

    protected $table = 'smartec_cnh';
    protected $primaryKey = 'id_smartec_cnh';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'cpf',
        'cnh',
        'nome',
        'pontuacao',
        'portaria',
        'impedimento',
        'vencimento',
        'data_pesquisa',
        'uf',
        'renach',
        'data_nascimento',
        'cedula',
        'data1habilitacao',
        'rg',
        'uf_nascimento',
        'municipio_nascimento',
        'municipio',
        'cod_seguranca',
        'categoria_cnh',
        'grupo_condutor',
        'apelido',
        'vencimento_toxicologico',
        'matricula'
    ];

    protected $casts = [
        'data_inclusao'  => 'datetime',
        'data_alteracao' => 'datetime',
        'vencimento' => 'datetime',
    ];

    public function setCpfAttribute($value)
    {
        $this->attributes['cpf'] = preg_replace('/\D/', '', $value);
    }

    // Mutator para CNH
    public function setCnhAttribute($value)
    {
        $this->attributes['cnh'] = preg_replace('/\D/', '', $value);
    }

    // Mutator para RG
    public function setRgAttribute($value)
    {
        $this->attributes['rg'] = preg_replace('/\D/', '', $value);
    }
}
