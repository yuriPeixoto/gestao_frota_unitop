<?php

namespace App\Http\Controllers;

use App\Services\EmailSenderService;
use App\Services\HTMLBodyService;
use App\Services\PHPMailerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller exemplo mostrando como usar os services de email
 */
class EmailController extends Controller
{
    protected $emailSenderService;

    public function __construct(EmailSenderService $emailSenderService)
    {
        $this->emailSenderService = $emailSenderService;
    }

    /**
     * Envia email de cotação usando injeção de dependência
     */
    public function sendCotacaoEmail(Request $request): JsonResponse
    {
        $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'required|string',
            'password' => 'required|string',
            'from' => 'required|email',
            'to' => 'required|email',
            'subject' => 'required|string',
            'empresa' => 'required|string',
            'endereco_empresa' => 'required|string',
            'numero_cotacao' => 'required|string',
            'nome_fornecedor' => 'nullable|string',
        ]);

        $result = $this->emailSenderService->sendEmail(
            $request->host,
            $request->port,
            $request->username,
            $request->password,
            $request->from,
            $request->to,
            $request->subject,
            $request->empresa,
            $request->endereco_empresa,
            $request->numero_cotacao,
            $request->nome_fornecedor
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Email enviado com sucesso!',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar email.',
            ], 500);
        }
    }

    /**
     * Exemplo de uso direto dos services (método alternativo)
     */
    public function sendCustomEmail(Request $request): JsonResponse
    {
        // Instanciar services diretamente
        $phpMailerService = new PHPMailerService;
        $htmlBodyService = new HTMLBodyService;

        // Configurar SMTP
        $smtpConfig = [
            'host' => $request->host,
            'port' => $request->port,
            'auth' => true,
            'username' => $request->username,
            'password' => $request->password,
            'encryption' => 'tls',
            'charset' => 'UTF-8',
            'debug' => 0,
            'auto_tls' => true,
        ];

        $phpMailerService->configureSMTP($smtpConfig);

        // Gerar corpo do email
        $htmlBody = $htmlBodyService->generateBody(
            $request->empresa,
            $request->endereco_empresa,
            $request->numero_cotacao,
            $request->nome_fornecedor
        );

        // Preparar dados do email
        $emailData = [
            'from' => $request->from,
            'to' => $request->to,
            'subject' => $request->subject,
            'body' => $htmlBody,
            'is_html' => true,
        ];

        try {
            $result = $phpMailerService->sendCustomEmail($emailData);

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Email enviado com sucesso!' : 'Erro ao enviar email.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Método estático para compatibilidade com código legado
     */
    public static function sendEmailLegacy(Request $request): JsonResponse
    {
        $result = EmailSenderService::sendEmailStatic(
            $request->host,
            $request->port,
            $request->username,
            $request->password,
            $request->from,
            $request->to,
            $request->subject,
            $request->empresa,
            $request->endereco_empresa,
            $request->numero_cotacao,
            $request->nome_fornecedor
        );

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Email enviado com sucesso!' : 'Erro ao enviar email.',
        ]);
    }
}
