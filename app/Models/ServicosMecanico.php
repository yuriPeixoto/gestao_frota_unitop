<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ServicosMecanico extends Model
{
    use LogsActivity;

    protected $table = 'servicos_mecanico';
    protected $primaryKey = 'id_servico_mecanico';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_mecanico',
        'id_servico',
        'data_inicial_diagnostico',
        'data_final_diagnostico',
        'data_final_servicos',
        'id_os',
        'status_servico',
        'id_veiculo',
        'id_mec_inicial',
        'id_mec_final',
        'id_user_mecanico',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }


    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_mecanico', 'id_fornecedor');
    }

    public function pessoal()
    {
        return $this->belongsTo(Pessoal::class, 'id_user_mecanico');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'id_servico');
    }
}
