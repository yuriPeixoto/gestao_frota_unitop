<?php

namespace App\Services\Nfe\Traits;

trait EmailSanitizerTrait
{
    /**
     * Sanitiza uma string de emails, suportando múltiplos endereços.
     *
     * @param string|null $emailString
     * @return string|null
     */
    protected function sanitizeEmails(?string $emailString): ?string
    {
        // Se vazio, retorna null para evitar processamento desnecessário
        if (empty($emailString)) {
            return null;
        }

        // Remove espaços em branco extras no início e fim
        $emailString = trim($emailString);

        // Remove caracteres de controle e caracteres não imprimíveis que podem causar problemas
        $emailString = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $emailString);

        // Padroniza os delimitadores para facilitar o processamento
        // Substitui vírgulas, espaços e outros separadores comuns por ponto e vírgula
        $emailString = preg_replace('/[,\s]+/', ';', $emailString);

        // Garante que não haja múltiplos delimitadores consecutivos
        $emailString = preg_replace('/;+/', ';', $emailString);

        // Se não tiver delimitador, trata como email único
        if (strpos($emailString, ';') === false) {
            return $this->validateSingleEmail($emailString);
        }

        // Divide os emails por ponto e vírgula
        $emails = explode(';', $emailString);

        // Lista para armazenar emails válidos
        $validEmails = [];

        foreach ($emails as $email) {
            $validEmail = $this->validateSingleEmail($email);
            if ($validEmail !== null) {
                $validEmails[] = $validEmail;
            }
        }

        // Se nenhum email válido, retorna null
        if (empty($validEmails)) {
            return null;
        }

        // Remove duplicatas e junta com ponto e vírgula
        $result = implode(';', array_unique($validEmails));

        // Limita o tamanho total para evitar problemas com o banco de dados
        return mb_substr($result, 0, 255);
    }

    /**
     * Valida e sanitiza um único endereço de email.
     *
     * @param string $email
     * @return string|null Email sanitizado ou null se inválido
     */
    private function validateSingleEmail(string $email): ?string
    {
        // Remove espaços em branco
        $email = trim($email);

        if (empty($email)) {
            return null;
        }

        // Validação básica de formato (deve conter '@' e pelo menos um '.' após o '@')
        if (!preg_match('/^[^@]+@[^@]+\.[^@.]+/', $email)) {
            return null;
        }

        // Validação mais específica usando filter_var
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Tentativa de recuperação para casos específicos
            // Remove caracteres especiais comuns que podem afetar a validação
            $sanitized = preg_replace('/[^\p{L}\p{N}@._-]/u', '', $email);

            // Verifica novamente após sanitização
            if (!filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
                return null;
            }

            $email = $sanitized;
        }

        // Faz a conversão para minúsculas para garantir consistência
        return strtolower($email);
    }

    /**
     * Verifica se uma string é uma lista de emails válida.
     *
     * @param string|null $emailString
     * @return bool
     */
    protected function isValidEmailList(?string $emailString): bool
    {
        return $this->sanitizeEmails($emailString) !== null;
    }

    /**
     * Conta o número de emails válidos em uma string.
     *
     * @param string|null $emailString
     * @return int
     */
    protected function countEmails(?string $emailString): int
    {
        $sanitized = $this->sanitizeEmails($emailString);
        if ($sanitized === null) {
            return 0;
        }

        return substr_count($sanitized, ';') + 1;
    }

    /**
     * Extrai o primeiro email válido de uma lista.
     *
     * @param string|null $emailString
     * @return string|null
     */
    protected function getFirstEmail(?string $emailString): ?string
    {
        $sanitized = $this->sanitizeEmails($emailString);
        if ($sanitized === null) {
            return null;
        }

        $emails = explode(';', $sanitized);
        return reset($emails) ?: null;
    }
}
