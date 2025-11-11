<?php

namespace App\Modules\Imobilizados\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogsTransferenciaImobilizadoVeiculo extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'logs_transferencia_imobilizado_veiculo';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'data_solicitante',
        'usuario_solicitante',
        'data_trafego',
        'usuario_trafego',
        'situacao_trafego',
        'data_juridico',
        'usuario_juridico',
        'situacao_juridico',
        'data_frota',
        'usuario_frota',
        'situacao_frota',
        'data_patrimonio',
        'usuario_patrimonio',
        'situacao_patrimonio',
        'data_reprova',
        'usuario_reprova',
        'situacao_reprova',
        'id_transferencia_imobilizado_veiculo',
        'data_pendente',
        'usuario_pendente',
        'situacao_pendente',
    ];

    protected $casts = [
        'data_solicitante' => 'datetime',
        'data_trafego' => 'datetime',
        'data_juridico' => 'datetime',
        'data_frota' => 'datetime',
        'data_patrimonio' => 'datetime',
        'data_pendente' => 'datetime',
        'data_reprova' => 'datetime',
    ];

    public function userSolicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_solicitante', 'id');
    }

    public function userTrafego(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_trafego', 'id');
    }

    public function userJuridico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_juridico', 'id');
    }

    public function userFrota(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_frota', 'id');
    }

    public function userPatrimonio(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_patrimonio', 'id');
    }

    public function userReprova(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_reprova', 'id');
    }

    public function userPendente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_pendente', 'id');
    }

    public function transferencia()
    {
        return $this->belongsTo(TransferenciaImobilizadoVeiculo::class, 'id_transferencia_imobilizado_veiculo');
    }
}
