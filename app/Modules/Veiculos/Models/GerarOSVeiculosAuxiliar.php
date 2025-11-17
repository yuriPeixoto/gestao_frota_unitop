<?php

namespace App\Modules\Veiculos\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GerarOSVeiculosAuxiliar extends Model
{
    use LogsActivity;

    protected $table = 'gerar_os_veiculos_auxiliar';
    protected $primaryKey = 'id_os_veiculos_auxiliar';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_os_auxiliar',
        'id_veiculo',
        'km_horimetro'
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }

    public function os(): BelongsTo
    {
        return $this->belongsTo(GerarOrdemServicoAuxiliar::class, 'id_os_auxiliar', 'id_os_auxiliar');
    }
}
