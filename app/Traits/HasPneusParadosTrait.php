<?php

namespace App\Traits;

use App\Models\PneusDeposito;

trait HasPneusParadosTrait
{
    /**
     * Retorna true se existirem pneus no depósito parados por mais de 24 horas
     */
    public function hasPneusParadosMais24Horas(): bool
    {
        $threshold = now()->subHours(24);

        return PneusDeposito::select('pneudeposito.*')
            ->join('pneu as p', 'pneudeposito.id_pneu', '=', 'p.id_pneu')
            ->whereNull('pneudeposito.datahora_processamento')
            ->where('pneudeposito.data_inclusao', '<=', $threshold)
            ->where('p.id_filial', GetterFilial())
            ->exists();
    }
}
