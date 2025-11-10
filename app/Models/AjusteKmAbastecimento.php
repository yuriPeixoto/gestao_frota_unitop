<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AjusteKmAbastecimento extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'ajustekmabastecimento';
    protected $primaryKey = 'id_ajuste_km_abastecimento';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'data_abastecimento',
        'id_veiculo',
        'km_abastecimento',
        'id_permissao_km_manual',
        'tipo_combustivel'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_abastecimento' => 'datetime',
        'km_abastecimento' => 'integer',
    ];

    // Relacionamentos
    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }

    public function permissaoKmManual(): BelongsTo
    {
        return $this->belongsTo(PermissaoKmManual::class, 'id_permissao_km_manual', 'id_permissao_km_manual');
    }
}
