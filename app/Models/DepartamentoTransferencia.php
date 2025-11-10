<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class DepartamentoTransferencia extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'departamento_transferencia';
    protected $primaryKey = 'id_departamento_transferencia';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'departamento',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    public function transferencias()
    {
        return $this->hasMany(TransferenciaImobilizadoVeiculo::class, 'id_filial');
    }
}
