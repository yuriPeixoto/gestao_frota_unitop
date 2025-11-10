<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use LogsActivity;

    protected $table = 'empresa';
    protected $primaryKey = 'idempresa';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'nomefantasia',
        'apelido',
        'cnpj',
        'email',
        'inscricaoestadual',
        'numero',
        'bairro',
        'uf',
        'cep',
        'municipio',
        'logradouro',
        'matriz',
        'status',
        'filial',
        'telefone',
        'inscricaoestadual',
        'inscricaomunicipal',
        'logo',
        'rntrc',
        'situacao_rntrc',
        'municipio_uf_rntrc',
        'tipo_transporte_rntrc',
        'data_cadastro_rntrc'
    ];
}
