<?php

namespace App\Modules\Abastecimentos\Models;

use Illuminate\Database\Eloquent\Model;

class AbastecimentoItem extends Model
{
    protected $table = 'abastecimento_itens';
    protected $primaryKey = 'id_abastecimentos_itens';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_abastecimento',
        'data_abastecimento',
        'id_combustivel',
        'id_bomba',
        'litros',
        'km_veiculo',
        'valor_unitario',
        'valor_total',
        'litros_abastecido',
        'horimetro',
        'km_veiculo_abastecido',
        'km_anterior'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_abastecimento' => 'datetime',
        'litros' => 'float',
        'valor_unitario' => 'float',
        'valor_total' => 'float',
        'litros_abastecido' => 'float',
        'km_veiculo_abastecido' => 'float'
    ];

    // Relacionamentos
    public function abastecimento()
    {
        return $this->belongsTo(AbastecimentoManual::class, 'id_abastecimento', 'id_abastecimento');
    }

    public function bomba()
    {
        return $this->belongsTo(Bomba::class, 'id_bomba', 'id_bomba');
    }

    public function combustivel()
    {
        return $this->belongsTo(TipoCombustivel::class, 'id_combustivel', 'id_tipo_combustivel');
    }
}
