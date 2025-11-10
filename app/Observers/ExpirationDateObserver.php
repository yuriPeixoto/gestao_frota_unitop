<?php

namespace App\Observers;

use App\Models\CertificadoVeiculos;
use Carbon\Carbon;

class ExpirationDateObserver
{
    /**
     * Handle the CertificadoVeiculos "created" event.
     */
    public function created(CertificadoVeiculos $certificadoVeiculos): void
    {
        $this->checkExpirationDate($certificadoVeiculos);
    }

    /**
     * Handle the CertificadoVeiculos "updated" event.
     */
    public function updated(CertificadoVeiculos $certificadoVeiculos): void
    {
        $this->checkExpirationDate($certificadoVeiculos);
    }

    protected function checkExpirationDate(CertificadoVeiculos $certificadoVeiculos): void
    {
        $today = Carbon::today();
        $expirationDate = Carbon::parse($certificadoVeiculos->data_vencimento);

        if ($expirationDate->lt($today)) {
            // Data de vencimento é anterior à data atual
            $certificadoVeiculos->update([
                'situacao' => 'Vencido',
            ]);
        }
    }
}
