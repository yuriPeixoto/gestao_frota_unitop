<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GerarOrdemServicoAuxiliar extends Model
{
    use LogsActivity;

    protected $table = 'gerar_ordem_servico_auxiliar';
    protected $primaryKey = 'id_os_auxiliar';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'data_abertura',
        'id_departamento',
        'id_repcionista',
        'processado',
        'local_manutencao',
        'id_filial',
        'id_mecanico',
        'id_fornecedor'
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor');
    }

    public function osAuxiliarVeiculos(): HasMany
    {
        return $this->hasMany(GerarOSVeiculosAuxiliar::class, 'id_os_auxiliar');
    }

    public function osAuxiliarServicos(): HasMany
    {
        return $this->hasMany(GerarOSServicosAuxiliar::class, 'id_os_auxiliar');
    }

    public function osAuxiliarManutencoes(): HasMany
    {
        return $this->hasMany(GerarOSManutencoesAuxiliar::class, 'id_os_auxiliar');
    }
}
