<?php

namespace App\Modules\Veiculos\Models;

use Illuminate\Database\Eloquent\Model;

class VveiculoCompraeBaixa extends Model
{
    protected $connection = 'pgsql';
    // necessário para o soft delete identificar o campo

    protected $table = 'v_veiculo_compraebaixa';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;
    protected $fillable = [
        "placa",
        "chassi",
        "renavam",
        "descricao_modelo_veiculo",
        "ano_fabricacao",
        "id_filial",
        "descricao_departamento",
        "data_compra",
        "data_venda",
    ];

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial');
    }
}
