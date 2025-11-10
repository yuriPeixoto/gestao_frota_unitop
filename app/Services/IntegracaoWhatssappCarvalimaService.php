<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class IntegracaoWhatssappCarvalimaService
{
    public static function enviarMensagem($mensagem, $nome, $numero)
    {

        $tokem = 'ad8d672c-b196-4f2e-bf24-aaa8523ef258';
        $accountId = 2;
        $whatsappId = 95;
        $messageTimeout = 600;
        $queued = true;
        $from = 'UNITOP';
        $url = 'https://api.sacflow.io/api/send-text';

        $body = json_encode([
            'accountId' => $accountId,
            'whatsappId' => $whatsappId,
            'message' => $mensagem,
            'messageTimeout' => $messageTimeout,
            'from' => $from,
            'contact' => [
                'name' => $nome,
                'phone' => $numero,
            ],
            'queued' => true,
        ]);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $tokem",
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if (curl_errno($ch)) {
            Log::error('WhatsApp Service - Erro cURL', [
                'error' => curl_error($ch),
                'errno' => curl_errno($ch)
            ]);
            echo 'Erro na requisição: ' . curl_error($ch);
        }

        curl_close($ch);

        return $response;
    }
}
