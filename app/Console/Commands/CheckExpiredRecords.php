<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CertificadoVeiculos;
use Carbon\Carbon;

class CheckExpiredRecords extends Command
{
    protected $signature = 'check:expired';
    protected $description = 'Verifica e atualiza registros com datas vencidas';

    public function handle()
    {
        $today = Carbon::today();

        $expiredRecords = CertificadoVeiculos::where('data_vencimento', '<', $today)
            ->whereNotin('situacao', ['Vencido', 'Cancelado'])
            ->get();

        foreach ($expiredRecords as $record) {
            $record->update([
                'situacao' => 'Vencido',
                // outros campos
            ]);
        }

        $this->info(count($expiredRecords) . ' registros atualizados como vencidos.');
    }
}
