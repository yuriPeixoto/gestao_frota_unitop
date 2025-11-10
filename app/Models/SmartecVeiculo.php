<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class SmartecVeiculo extends Model
{
    use LogsActivity;

    protected $table = 'smartec_veiculo';
    protected $primaryKey = 'id_smartec_veiculo';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'placa',
        'renavam',
        'chassi',
        'municipio',
        'uf',
        'tipo',
        'combustivel',
        'cor',
        'marca',
        'ano_fabricacao',
        'cpf_cnpj',
        'proprietario',
        'licenciamento_vigente',
        'exercicio_licenciamento',
        'restricoes',
        'ativo',
        'observacao',
        'integrado'
    ];

    protected $casts = [
        'data_inclusao'  => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'placa');
    }
}
