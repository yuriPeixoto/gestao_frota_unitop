<?php

use App\Models\VFilial;
use Illuminate\Support\Facades\Log;

if (! function_exists('GetterFilial')) {
    /**
     * Formata uma data no formato especificado.
     *
     * @param  string|null  $date  A data que será formatada.
     * @param  string  $format  O formato desejado (padrão: 'd/m/Y H:i:s').
     */
    function GetterFilial($nome = null)
    {
        $filial = VFilial::find(session('user_branch_id'));

        if (! $filial) {
            // Fallback para primeira filial disponível ou valor padrão
            $filial = VFilial::first();
            if (! $filial) {
                return $nome == 'nome' ? 'Sem filial' : '0';
            }
        }

        // Verifica se o nome da filial foi passado como parâmetro
        if ($nome == 'nome') {
            return $filial->name;
        }

        // Caso contrário, retorna o id da filial
        return (string) $filial->id;
    }
}
