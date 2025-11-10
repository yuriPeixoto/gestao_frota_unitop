<?php


if (!function_exists('validarCPF')) {
    /**
     * Formata uma data no formato especificado.
     *
     * @param string|null $date A data que será formatada.
     * @param string $format O formato desejado (padrão: 'd/m/Y H:i:s').
     * @return string
     */
    function validarCPF(?string $cpf)
    {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verifica se tem 11 dígitos ou é uma sequência inválida
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Valida o primeiro dígito verificador
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }
}
