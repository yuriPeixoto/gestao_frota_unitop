<?php

namespace App\Modules\Abastecimentos\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbastecimentoIntegracao extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'abastecimento_integracao';
    protected $primaryKey = 'id_abastecimento_integracao';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'descricao_bomba',
        'placa',
        'descricao_veiculo',
        'volume',
        'fluxometre',
        'data_inicio',
        'data_fim',
        'fluxo_inicial',
        'fluxo_final',
        'km_abastecimento',
        'id_veiculo',
        'tratado',
        'tipo_combustivel',
        'valor_litro',
        'id_abastecimento_ats',
        'km_anterior',
        'ativo',
        'justificativa',
        'vlrmedio',
        'id_veiculo_unitop',
        'vlrunitario_interno',
        'is_tanque_zerado',
        'created_at',
        'id_user_tratado',
        'data_tratado'
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function bomba()
    {
        return $this->belongsTo(Bomba::class, 'descricao_bomba', 'descricao_bomba');
    }

    public function entradaAfericao()
    {
        return $this->hasMany(EntradaAfericaoAbastecimento::class, 'id_abastecimento_integracao');
    }
}
