<?php


if (!function_exists('FormatDocs')) {
    /**
     * Formata uma data no formato especificado.
     *
     * @param string|null $date A data que será formatada.
     * @param string $format O formato desejado (padrão: 'd/m/Y H:i:s').
     * @return string
     */
    function FormatDocs(?string $doc, string $opcao = 'CNPJ'): string
    {
        if ($opcao == 'CNPJ') {
            // Remove qualquer formatação existente
            $cnpj = preg_replace('/[^0-9]/', '', $doc);

            // Aplica a formatação do CNPJ
            return substr($cnpj, 0, 2) . '.' .
                substr($cnpj, 2, 3) . '.' .
                substr($cnpj, 5, 3) . '/' .
                substr($cnpj, 8, 4) . '-' .
                substr($cnpj, 12, 2);
        }

        if ($opcao == 'CPF') {
            // Remove qualquer formatação existente
            $cpf = preg_replace('/[^0-9]/', '', $doc);

            // Aplica a formatação do CPF (XXX.XXX.XXX-XX)
            return substr($cpf, 0, 3) . '.' .
                substr($cpf, 3, 3) . '.' .
                substr($cpf, 6, 3) . '-' .
                substr($cpf, 9, 2);
        }

        return 'Documento Inválido';
    }
}
