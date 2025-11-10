<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Serviço customizado que estende PHPMailer com configurações específicas
 */
class PHPMailerService extends PHPMailer
{
   /**
    * Construtor da classe
    *
    * @param bool $exceptions
    */
   public function __construct($exceptions = null)
   {
      parent::__construct($exceptions);

      // Configurações padrão
      $this->isSMTP();
      $this->CharSet = 'UTF-8';
      $this->isHTML(true);
   }

   /**
    * Configura as opções SMTP
    *
    * @param array $config
    * @return void
    */
   public function configureSMTP(array $config)
   {
      $this->Host = $config['host'] ?? 'localhost';
      $this->Port = $config['port'] ?? 25;
      $this->SMTPAuth = $config['auth'] ?? false;
      $this->Username = $config['username'] ?? '';
      $this->Password = $config['password'] ?? '';
      $this->SMTPSecure = $config['encryption'] ?? '';
      $this->SMTPAutoTLS = $config['auto_tls'] ?? true;
      $this->SMTPDebug = $config['debug'] ?? 0;
      $this->CharSet = $config['charset'] ?? 'UTF-8';
   }

   /**
    * Envia email personalizado com dados estruturados
    *
    * @param array $emailData
    * @return bool
    * @throws Exception
    */
   public function sendCustomEmail(array $emailData): bool
   {
      try {
         // Limpar destinatários anteriores
         $this->clearAllRecipients();
         $this->clearAttachments();
         $this->clearCustomHeaders();

         // Configurar remetente
         $this->setFrom($emailData['from'], $emailData['from_name'] ?? '');

         // Configurar destinatários
         if (isset($emailData['to'])) {
            if (is_array($emailData['to'])) {
               foreach ($emailData['to'] as $email => $name) {
                  if (is_numeric($email)) {
                     $this->addAddress($name);
                  } else {
                     $this->addAddress($email, $name);
                  }
               }
            } else {
               $this->addAddress($emailData['to']);
            }
         }

         // Configurar CC se fornecido
         if (isset($emailData['cc'])) {
            if (is_array($emailData['cc'])) {
               foreach ($emailData['cc'] as $email => $name) {
                  if (is_numeric($email)) {
                     $this->addCC($name);
                  } else {
                     $this->addCC($email, $name);
                  }
               }
            } else {
               $this->addCC($emailData['cc']);
            }
         }

         // Configurar BCC se fornecido
         if (isset($emailData['bcc'])) {
            if (is_array($emailData['bcc'])) {
               foreach ($emailData['bcc'] as $email => $name) {
                  if (is_numeric($email)) {
                     $this->addBCC($name);
                  } else {
                     $this->addBCC($email, $name);
                  }
               }
            } else {
               $this->addBCC($emailData['bcc']);
            }
         }

         // Configurar assunto e corpo
         $this->Subject = $emailData['subject'];

         if (isset($emailData['is_html']) && $emailData['is_html']) {
            $this->isHTML(true);
            $this->Body = $emailData['body'];
            if (isset($emailData['alt_body'])) {
               $this->AltBody = $emailData['alt_body'];
            }
         } else {
            $this->isHTML(false);
            $this->Body = $emailData['body'];
         }

         // Adicionar anexos se fornecidos
         if (isset($emailData['attachments']) && is_array($emailData['attachments'])) {
            foreach ($emailData['attachments'] as $attachment) {
               if (is_array($attachment)) {
                  $this->addAttachment(
                     $attachment['path'],
                     $attachment['name'] ?? '',
                     $attachment['encoding'] ?? 'base64',
                     $attachment['type'] ?? ''
                  );
               } else {
                  $this->addAttachment($attachment);
               }
            }
         }

         // Enviar o email
         return $this->send();
      } catch (Exception $e) {
         throw new Exception("Erro ao enviar email: " . $e->getMessage());
      }
   }

   /**
    * Método utilitário para validar configuração SMTP
    *
    * @return bool
    */
   public function testConnection(): bool
   {
      try {
         // Configurar debug output para capturar
         $this->Debugoutput = function ($str, $level) {
            echo $str;
         };

         // Tentar conectar ao servidor SMTP
         if (!$this->smtpConnect()) {
            return false;
         }

         // Se chegou até aqui, a conexão foi bem-sucedida
         $this->smtpClose();
         return true;
      } catch (Exception $e) {
         $this->setError('Erro na conexão SMTP: ' . $e->getMessage());
         return false;
      }
   }

   /**
    * Verifica se o servidor está bloqueado por excesso de tentativas
    *
    * @return bool
    */
   public function isBlocked(): bool
   {
      $errorMsg = $this->ErrorInfo;

      // Verifica várias formas de bloqueio SMTP
      $blockedPatterns = [
         'Bloqueado por excesso',
         'Blocked by too many',
         'authentication attempts',
         '554',
         'Too many failed',
         'Temporarily blocked',
         'Rate limit exceeded',
         'Could not authenticate',
         'SMTP connect() failed'  // Quando há bloqueio, pode falhar na conexão
      ];

      foreach ($blockedPatterns as $pattern) {
         if (stripos($errorMsg, $pattern) !== false) {
            return true;
         }
      }

      return false;
   }

   /**
    * Testa especificamente se há bloqueio SMTP fazendo uma tentativa controlada
    *
    * @return bool
    */
   public function testForBlocking(): bool
   {
      try {
         // Fazer uma tentativa de conexão rápida para detectar bloqueio
         $testMailer = new PHPMailer(true);
         $testMailer->isSMTP();
         $testMailer->Host = $this->Host;
         $testMailer->Port = $this->Port;
         $testMailer->SMTPAuth = $this->SMTPAuth;
         $testMailer->Username = $this->Username;
         $testMailer->Password = $this->Password;
         $testMailer->SMTPSecure = $this->SMTPSecure;
         $testMailer->Timeout = 10;

         // Tentar conectar
         if (!$testMailer->smtpConnect()) {
            // Se contém mensagens específicas de bloqueio
            if (
               stripos($testMailer->ErrorInfo, 'Bloqueado por excesso') !== false ||
               stripos($testMailer->ErrorInfo, 'Blocked by too many') !== false ||
               stripos($testMailer->ErrorInfo, '554') !== false
            ) {
               return true;
            }
         }

         $testMailer->smtpClose();
         return false;
      } catch (Exception $e) {
         return stripos($e->getMessage(), '554') !== false ||
            stripos($e->getMessage(), 'Bloqueado') !== false ||
            stripos($e->getMessage(), 'Blocked') !== false;
      }
   }

   /**
    * Tenta enviar email com retry em caso de bloqueio
    *
    * @param array $emailData
    * @param int $maxRetries
    * @param int $waitSeconds
    * @return bool
    */
   public function sendEmailWithRetry(array $emailData, int $maxRetries = 3, int $waitSeconds = 300): bool
   {
      for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
         try {
            return $this->sendCustomEmail($emailData);
         } catch (Exception $e) {
            if ($this->isBlocked() && $attempt < $maxRetries) {
               error_log("Tentativa $attempt/$maxRetries falhou (bloqueado). Aguardando {$waitSeconds}s...");
               sleep($waitSeconds);
               continue;
            }
            throw $e;
         }
      }
      return false;
   }
}
