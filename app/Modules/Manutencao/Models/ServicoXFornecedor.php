<?php

namespace App\Modules\Manutencao\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;


class ServicoXFornecedor extends Model
{
    use LogsActivity;


    protected $table = 'servicoxfornecedor';

    protected $primaryKey = 'id_precoservicoxfornecedor';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_servico',
        'id_fornecedor',
        'valor_servico_fornecedor',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'id_servico');
    }
}
