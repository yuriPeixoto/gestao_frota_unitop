<?php

namespace App\Modules\Abastecimentos\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbastecimentoAtsTruckpagManual extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'v_abastecimento_tela_edicao_abastecimento_competo';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'descricao_bomba',
        'tipocombustivel',
        'placa',
        'data_inicio',
        'volume',
        'km_anterior',
        'km_abastecimento',
        'km_rodado',
        'media',
        'valor_litro',
        'valor_total',
        'nome_filial',
        'descricao_departamento',
        'descricao_tipo',
        'descricao_categoria',
        'is_terceiro',
        'tratado',
        'tipo',
        'id_veiculo',
        'id_tipo_combustivel',
        'id_categoria',
        'id_tipo_equipamento',
        'id_filial'
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'is_terceiro' => 'boolean',
        'tratado' => 'boolean',
        'volume' => 'float',
        'km_anterior' => 'float',
        'km_abastecimento' => 'float',
        'km_rodado' => 'float',
        'media' => 'float',
        'valor_litro' => 'float',
        'valor_total' => 'float'
    ];

    // Relacionamentos
    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }

    public function tipoCategoria(): BelongsTo
    {
        return $this->belongsTo(TipoCategoria::class, 'id_categoria', 'id_categoria');
    }

    public function tipoCombustivel(): BelongsTo
    {
        return $this->belongsTo(TipoCombustivel::class, 'id_tipo_combustivel', 'id_tipo_combustivel');
    }

    public function tipoEquipamento(): BelongsTo
    {
        return $this->belongsTo(TipoEquipamento::class, 'id_tipo_equipamento', 'id_tipo_equipamento');
    }

    public function filial(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    // Formatters e Accessors
    public function getFormattedDataInicioAttribute()
    {
        return $this->data_inicio ? $this->data_inicio->format('d/m/Y H:i') : null;
    }

    public function getFormattedVolumeAttribute()
    {
        return number_format($this->volume, 2, ',', '.');
    }

    public function getFormattedKmRodadoAttribute()
    {
        return number_format($this->km_rodado, 2, ',', '.');
    }

    public function getFormattedMediaAttribute()
    {
        return number_format($this->media, 2, ',', '.');
    }

    public function getFormattedValorLitroAttribute()
    {
        return 'R$ ' . number_format($this->valor_litro, 2, ',', '.');
    }

    public function getFormattedValorTotalAttribute()
    {
        return 'R$ ' . number_format($this->valor_total, 2, ',', '.');
    }

    // Status functions
    public function isAts(): bool
    {
        return $this->tipo === 'ABASTECIMENTO VIA ATS';
    }

    public function isTruckpag(): bool
    {
        return $this->tipo === 'ABASTECIMENTO VIA TRUCKPAG';
    }

    public function isManual(): bool
    {
        return $this->tipo === 'ABASTECIMENTO MANUAL';
    }

    public function canSendToInconsistency(): bool
    {
        return $this->isAts() || $this->isTruckpag();
    }

    // Scopes
    public function scopeWithinPeriod($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('data_inicio', [$start, $end]);
    }

    public function scopeByVehicle($query, $vehicleId)
    {
        return $query->where('id_veiculo', $vehicleId);
    }

    public function scopeByFuelType($query, $fuelTypeId)
    {
        return $query->where('id_tipo_combustivel', $fuelTypeId);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('id_filial', $branchId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('id_categoria', $categoryId);
    }

    public function scopeByEquipmentType($query, $equipmentTypeId)
    {
        return $query->where('id_tipo_equipamento', $equipmentTypeId);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('data_inicio', 'desc');
    }
}
