<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VeiculoXPneu extends Model
{
    use LogsActivity;

    protected $table = 'veiculo_x_pneu';
    protected $primaryKey = 'id_veiculo_pneu';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_veiculo',
        'eixos_veiculos',
        'situacao'
    ];

    // ✅ RELACIONAMENTOS
    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function pneusAplicados(): HasMany
    {
        return $this->hasMany(PneusAplicados::class, 'id_veiculo_x_pneu');
    }

    public function pneusAplicadosAtivos(): HasMany
    {
        return $this->hasMany(PneusAplicados::class, 'id_veiculo_x_pneu')
            ->whereNull('deleted_at');
    }

    // ✅ SCOPES
    public function scopeAtivos($query)
    {
        return $query->where('situacao', true);
    }

    public function scopeInativos($query)
    {
        return $query->where('situacao', false);
    }

    public function scopePorVeiculo($query, $idVeiculo)
    {
        return $query->where('id_veiculo', $idVeiculo);
    }

    // ✅ MÉTODOS HELPER
    public function isAtivo()
    {
        return $this->situacao === true || $this->situacao === 1;
    }

    public function getTotalPneusAplicados()
    {
        return $this->pneusAplicadosAtivos()->count();
    }

    public function getPneusPorLocalizacao()
    {
        return $this->pneusAplicadosAtivos()
            ->with('pneu')
            ->get()
            ->groupBy('localizacao');
    }
}
