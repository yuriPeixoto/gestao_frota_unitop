<?php

use Carbon\Carbon;

if (!function_exists('format_date')) {
    /**
     * Formata uma data no formato especificado.
     *
     * @param string|null $date A data que será formatada.
     * @param string $format O formato desejado (padrão: 'd/m/Y H:i:s').
     * @return string
     */
    function format_date(?string $date, string $outputFormat = 'd/m/Y H:i:s'): string
    {
        // Retorna logo se não há data
        if (!$date || $date === 'Data não informada' || $date === 'Data inválida') {
            return $date ?: 'Data não informada';
        }

        try {
            // Tenta diferentes formatos comuns
            $formats = ['d/m/Y H:i:s', 'Y-m-d H:i:s', 'd/m/Y', 'Y-m-d'];

            foreach ($formats as $format) {
                $dateTime = DateTime::createFromFormat($format, $date);
                if ($dateTime !== false) {
                    return $dateTime->format($outputFormat);
                }
            }

            return 'Data inválida';
        } catch (\Exception $e) {
            return 'Data inválida';
        }
    }
}