<?php

namespace App\Modules\Manutencao\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaPlanejamentoManutencao extends Model
{
    use LogsActivity;

    protected $table = 'categoria_planejamento_manutencao';
    protected $primaryKey = 'id_manutencao_categoria';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_categoria',
        'hora_gerar_os_automatica',
        'km_gerar_os_automatica',
        'horas_frequencia',
        'km_frequencia',
        'dias_frequencia',
        'eventos_frequencia',
        'litros_frequencia',
        'horas_tolerancia',
        'km_tolerancia',
        'dia_tolerancia',
        'eventos_tolerancia',
        'litros_tolerancia',
        'hora_alerta',
        'km_alerta',
        'dias_alerta',
        'eventos_alerta',
        'litros_alerta',
        'hora_adiantamento',
        'km_adiantamento',
        'dias_adiantamento',
        'eventos_adiantamento',
        'litros_adiantamento',
        'horas_tempo_previsto',
        'dias_previstos',
        'id_planejamento',
    ];

    public function planejamento()
    {
        return $this->belongsTo(PlanejamentoManutencao::class, 'id_planejamento', 'id_planejamento_manutencao');
    }

    public function tipoCategoria()
    {
        return $this->belongsTo(TipoCategoria::class, 'id_categoria', 'id_categoria');
    }
}
