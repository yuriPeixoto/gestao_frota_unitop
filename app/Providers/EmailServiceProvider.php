<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\EmailSenderService;
use App\Services\PHPMailerService;
use App\Services\HTMLBodyService;
use App\Services\SmtpProviderService;

/**
 * Service Provider para registrar os services de email
 */
class EmailServiceProvider extends ServiceProvider
{
   /**
    * Register any application services.
    */
   public function register(): void
   {
      // Registrar HTMLBodyService como singleton
      $this->app->singleton(HTMLBodyService::class, function ($app) {
         return new HTMLBodyService();
      });

      // Registrar PHPMailerService
      $this->app->bind(PHPMailerService::class, function ($app) {
         return new PHPMailerService();
      });

      // Registrar SmtpProviderService
      $this->app->bind(SmtpProviderService::class, function ($app) {
         return new SmtpProviderService();
      });

      // Registrar EmailSenderService com dependências
      $this->app->bind(EmailSenderService::class, function ($app) {
         return new EmailSenderService(
            $app->make(PHPMailerService::class),
            $app->make(HTMLBodyService::class)
         );
      });
   }

   /**
    * Bootstrap any application services.
    */
   public function boot(): void
   {
      //
   }
}
