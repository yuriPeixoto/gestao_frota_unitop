<?php

use Carbon\Carbon;

if (!function_exists('sanitizeToDouble')) {
    /**
     * Sanitiza o número para double
     */
    function sanitizeToDouble($number): float
    {
        if ($number === null || $number === '') {
            return 0;
        }

        // Remove caracteres não numéricos exceto ponto e vírgula
        $cleaned = preg_replace('/[^0-9.,\-]/', '', $number);

        // Converte vírgula para ponto (padrão brasileiro para decimal)
        $cleaned = str_replace(',', '.', $cleaned);

        return is_numeric($cleaned) ? (float) $cleaned : 0;
    }
}
