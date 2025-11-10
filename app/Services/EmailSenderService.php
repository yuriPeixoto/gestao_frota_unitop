<?php

namespace App\Services;

use App\Services\PHPMailerService;
use App\Services\HTMLBodyService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

/**
 * Serviço para envio de emails usando PHPMailer
 */
class EmailSenderService
{
  protected $phpMailerService;
  protected $htmlBodyService;

  public function __construct(PHPMailerService $phpMailerService, HTMLBodyService $htmlBodyService)
  {
    $this->phpMailerService = $phpMailerService;
    $this->htmlBodyService = $htmlBodyService;
  }

  /**
   * Envia email de cotação com fallback automático
   *
   * @param string $host
   * @param int $port
   * @param string $username
   * @param string $password
   * @param string $from
   * @param string $to
   * @param string $subject
   * @param string $empresa
   * @param string $enderecoEmpresa
   * @param string $numeroCotacao
   * @param string $nomeFornecedor
   * @return bool
   */
  public function sendEmail($host, $port, $username, $password, $from, $to, $subject, $empresa, $enderecoEmpresa, $numeroCotacao, $nomeFornecedor = null)
  {
    // Primeira tentativa com servidor principal
    $result = $this->attemptSendEmail($host, $port, $username, $password, $from, $to, $subject, $empresa, $enderecoEmpresa, $numeroCotacao, $nomeFornecedor, 'principal');

    if ($result) {
      return true;
    }

    // Se falhou e há fallback configurado, tentar servidor alternativo
    $config = config('cotacao-email');
    if ($config['smtp_fallback']['enabled'] && $this->phpMailerService->isBlocked()) {
      Log::info("Servidor principal bloqueado, tentando fallback para: {$to}");

      return $this->attemptSendEmail(
        $config['smtp_fallback']['host'],
        $config['smtp_fallback']['port'],
        $config['smtp_fallback']['username'],
        $config['smtp_fallback']['password'],
        $config['smtp_fallback']['username'], // Usar username como from no fallback
        $to,
        $subject,
        $empresa,
        $enderecoEmpresa,
        $numeroCotacao,
        $nomeFornecedor,
        'fallback'
      );
    }

    return false;
  }

  /**
   * Tenta enviar email com configuração específica
   */
  private function attemptSendEmail($host, $port, $username, $password, $from, $to, $subject, $empresa, $enderecoEmpresa, $numeroCotacao, $nomeFornecedor, $serverType = 'principal')
  {
    try {
      // Configurar SMTP
      $smtpConfig = [
        'host' => $host,
        'port' => $port,
        'auth' => true,
        'username' => $username,
        'password' => $password,
        'encryption' => 'tls',
        'charset' => 'UTF-8',
        'debug' => 0,
        'auto_tls' => true
      ];

      $this->phpMailerService->configureSMTP($smtpConfig);

      // Gerar corpo do email
      $htmlBody = $this->htmlBodyService->generateBody($empresa, $enderecoEmpresa, $numeroCotacao, $nomeFornecedor);

      // Preparar dados do email
      $emailData = [
        'from' => $from,
        'to' => $to,
        'subject' => $subject,
        'body' => $htmlBody,
        'is_html' => true
      ];

      // Enviar email com retry automático
      $result = $this->phpMailerService->sendEmailWithRetry($emailData, 2, 60); // 2 tentativas, 60s de espera

      if ($result) {
        Log::info("Email de cotação enviado com sucesso para: {$to} via servidor {$serverType}");
        return true;
      } else {
        $errorInfo = $this->phpMailerService->ErrorInfo;

        // Tentar detectar bloqueio com teste específico se o método padrão falhar
        $isBlocked = $this->phpMailerService->isBlocked();
        if (!$isBlocked && stripos($errorInfo, 'SMTP connect() failed') !== false) {
          $isBlocked = $this->phpMailerService->testForBlocking();
        }

        Log::error("Falha ao enviar email de cotação para: {$to} via servidor {$serverType}", [
          'error' => $errorInfo,
          'is_blocked' => $isBlocked
        ]);
        return false;
      }
    } catch (Exception $e) {
      Log::error("Erro ao enviar email de cotação via servidor {$serverType}: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Método estático para compatibilidade com código legado
   */
  public static function sendEmailStatic($host, $port, $username, $password, $from, $to, $subject, $empresa, $enderecoEmpresa, $numeroCotacao, $nomeFornecedor = null)
  {
    $service = new static(new PHPMailerService(), new HTMLBodyService());
    return $service->sendEmail($host, $port, $username, $password, $from, $to, $subject, $empresa, $enderecoEmpresa, $numeroCotacao, $nomeFornecedor);
  }
}
