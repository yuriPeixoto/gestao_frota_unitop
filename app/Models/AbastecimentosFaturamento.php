<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class AbastecimentosFaturamento extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'v_listar_abastecimentos_para_faturamento';
    protected $primaryKey = 'cod_transacao';
    public $timestamps = false;

    protected $fillable = [
        'chave_nf',
        'numero_nf',
        'data_vencimento_nf',
        'valor_nf',
        'posto_abastecimento',
        'cnpj',
        'placa',
        'data_abastecimento',
        'nomefantasiaposto',
        'tipo'
    ];

    protected $casts = [
        'data_vencimento_nf' => 'date',
        'data_abastecimento' => 'date',
        'valor_nf' => 'float'
    ];

    // Relacionamentos
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'placa', 'placa');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'cnpj', 'cnpj_fornecedor');
    }
}
