<?php

namespace App\Modules\Manutencao\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GerarOSServicosAuxiliar extends Model
{
    use LogsActivity;

    protected $table = 'gerar_os_servicos_auxiliar';
    protected $primaryKey = 'id_os_servicos_auxiliar';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_os_auxiliar',
        'id_servico',
        'id_mecanico'
    ];

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class, 'id_servico', 'id_servico');
    }

    public function mecanico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_mecanico', 'id');
    }

    public function os(): BelongsTo
    {
        return $this->belongsTo(GerarOrdemServicoAuxiliar::class, 'id_os_auxiliar', 'id_os_auxiliar');
    }
}
