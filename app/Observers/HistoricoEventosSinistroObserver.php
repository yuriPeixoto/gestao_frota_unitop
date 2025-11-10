<?php

namespace App\Observers;

use App\Models\HistoricoEventosSinistro;
use App\Models\Sinistro;

class HistoricoEventosSinistroObserver
{
    /**
     * Handle the HistoricoEventosSinistro "created" event.
     */
    public function created(HistoricoEventosSinistro $historicoEventosSinistro): void
    {
        // Verificar se a situação é "Finalizado"
        if (strtolower(trim($historicoEventosSinistro->descricao_situacao ?? '')) === 'finalizado') {
            $this->atualizarStatusSinistro($historicoEventosSinistro->id_sinistro, 'Finalizado');
        }
    }

    /**
     * Handle the HistoricoEventosSinistro "updated" event.
     */
    public function updated(HistoricoEventosSinistro $historicoEventosSinistro): void
    {
        // Verificar se a situação foi alterada para "Finalizado"
        if (strtolower(trim($historicoEventosSinistro->descricao_situacao ?? '')) === 'finalizado') {
            $this->atualizarStatusSinistro($historicoEventosSinistro->id_sinistro, 'Finalizado');
        }
    }

    /**
     * Atualiza o status do sinistro
     *
     * @param  int  $idSinistro
     * @param  string  $novoStatus
     * @return void
     */
    protected function atualizarStatusSinistro($idSinistro, $novoStatus)
    {
        $sinistro = Sinistro::find($idSinistro);
        if ($sinistro) {
            $sinistro->status = $novoStatus;
            $sinistro->data_alteracao = now();
            $sinistro->save();
        }
    }
}
