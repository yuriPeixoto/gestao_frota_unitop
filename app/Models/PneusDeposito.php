<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PneusDeposito extends Model
{
    protected $table = 'pneudeposito';
    protected $primaryKey = 'id_deposito_pneu';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_pneu',
        'datahora_processamento',
        'descricao_destino',
        'destinacao_solicitada'
    ];

    public function pneus(): BelongsTo
    {
        return $this->belongsTo(Pneu::class, 'id_pneu', 'id_pneu');
    }

    /**
     * Retorna a diferença em horas entre agora e a data_inclusao
     */
    public function diffHours()
    {
        if (empty($this->data_inclusao)) {
            return 0;
        }

        return \Carbon\Carbon::parse($this->data_inclusao)->diffInHours(now());
    }

    /**
     * Retorna o texto do badge: 'Xh' se <48h, ou 'Y dias' se >=48h, ou null se sem data
     */
    public function diasEmDepositoBadge()
    {
        $hours = $this->diffHours();

        if ($hours <= 0) {
            return null;
        }

        // Arredondar para número inteiro para apresentar horas "redondas"
        $hoursRounded = (int) round($hours);

        if ($hoursRounded < 48) {
            return $hoursRounded . 'h';
        }

        return floor($hoursRounded / 24) . ' dias';
    }

    /**
     * Classe CSS aplicada na linha quando maior que 24 horas
     */
    public function rowClass()
    {
        // Usar valor arredondado para a lógica visual também
        return round($this->diffHours()) > 24 ? 'bg-red-50 text-red-700' : '';
    }
}
