<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class PneuDeposito extends Model
{
    use LogsActivity;

    protected $table = 'pneudeposito';
    protected $primaryKey = 'id_deposito_pneu';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_pneu',
        'datahora_processamento',
        'descricao_destino',
        'destinacao_solicitada'
    ];
}