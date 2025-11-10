<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manutencao extends Model
{
    use LogsActivity;

    protected $table = 'manutencao';
    protected $primaryKey = 'id_manutencao';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_tipo_manutencao',
        'descricao_manutencao',
        'ativar',
        'km_configuracao',
        'tempo_configuracao',
        'horas',
        'eventos',
        'combustivel',
        'auxiliar'
    ];

    public function osAuxiliarManutencao(): HasMany
    {
        return $this->hasMany(GerarOSManutencoesAuxiliar::class, 'id_manutencao', 'id_manutencao');
    }

    public function tipoManutencao()
    {
        return $this->belongsTo(TipoManutencao::class, 'id_tipo_manutencao', 'id_tipo_manutencao');
    }
}
