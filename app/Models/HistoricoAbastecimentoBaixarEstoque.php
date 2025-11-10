<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class HistoricoAbastecimentoBaixarEstoque extends Model
{
    use LogsActivity;

    protected $table = 'historico_abastecimento_baixar_estoque';
    protected $primaryKey = 'id_historico_abastecimento_baixar_estoque';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'descricao_bomba',
        'placa',
        'descricao_veiculo',
        'volume',
        'vlrunit',
        'id_ats',
        'id_tanque',
        'id_veiculo_unitop',
        'data_abastecimento'
    ];

    public function sinistro()
    {
        return $this->belongsTo(Sinistro::class, 'id_sinistro');
    }
}
