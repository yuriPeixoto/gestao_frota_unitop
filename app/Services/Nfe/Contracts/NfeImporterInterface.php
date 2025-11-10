<?php

namespace App\Services\Nfe\Contracts;

interface NfeImporterInterface
{
    /**
     * Executa o importador com as configurações padrão.
     *
     * @return array Resultado da execução
     *               Deve conter a chave 'success' (bool)
     */
    public function execute(): array;

    /**
     * Processa todos os arquivos na fila.
     *
     * @return array Resultado do processamento
     *               Deve conter as chaves 'success' (bool), 'processed' (int), 'failed' (int)
     */
    public function processQueue(): array;

    /**
     * Processa um único arquivo.
     *
     * @param string $file Caminho completo para o arquivo
     * @return array Resultado do processamento
     *               Deve conter as chaves:
     *               - 'success' (bool): Indica se o processamento foi bem-sucedido
     *               - 'error' (string, opcional): Mensagem de erro em caso de falha
     *               - 'retry' (bool, opcional): Indica se deve tentar processar novamente
     */
    public function processFile(string $file): array;
}
