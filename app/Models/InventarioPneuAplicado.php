<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioPneuAplicado extends Model
{

    protected $table = 'inventario_pneu_aplicado';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_pneu',
        'id_veiculo_antigo',
        'id_veiculo',
        'id_pneu',
        'id_usuario_ajuste',
        'id_abertura_inventario_pneu_aplicado',
        'is_finalizado',
        'verificado'

    ];
}
