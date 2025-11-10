<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class DadosPessoalSinistro extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'dados_pessoal_sinistro';
    protected $primaryKey = 'id_dados_pessoal_sinistro';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'nome_pessoal', 'telefone', 'cpf', 'id_sinistro'];

    public function sinistro()
    {
        return $this->belongsTo(Sinistro::class, 'id_sinistro');
    }

    /*public function getAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }*/
}
