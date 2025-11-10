<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GerarOSManutencoesAuxiliar extends Model
{
    use LogsActivity;

    protected $table = 'gerar_os_manutencoes_auxiliar';
    protected $primaryKey = 'id_os_manutencoes_auxiliar';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_os_auxiliar',
        'id_manutencao'
    ];

    public function manutencao(): BelongsTo
    {
        return $this->belongsTo(Manutencao::class, 'id_manutencao', 'id_manutencao');
    }

    public function os(): BelongsTo
    {
        return $this->belongsTo(GerarOrdemServicoAuxiliar::class, 'id_os_auxiliar', 'id_os_auxiliar');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }
}
