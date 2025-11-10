<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;

/*
|--------------------------------------------------------------------------
| Email Routes
|--------------------------------------------------------------------------
|
| Rotas para demonstrar o uso dos services de email integrados ao Laravel
|
*/

Route::prefix('email')->group(function () {
   // Rota usando injeção de dependência (recomendado)
   Route::post('/send-cotacao', [EmailController::class, 'sendCotacaoEmail'])
      ->name('email.send-cotacao');

   // Rota usando instanciação direta dos services
   Route::post('/send-custom', [EmailController::class, 'sendCustomEmail'])
      ->name('email.send-custom');

   // Rota usando método estático (compatibilidade legado)
   Route::post('/send-legacy', [EmailController::class, 'sendEmailLegacy'])
      ->name('email.send-legacy');
});

/*
Exemplo de uso das rotas:

POST /email/send-cotacao
{
    "host": "smtp.gmail.com",
    "port": 587,
    "username": "seu@email.com",
    "password": "suasenha",
    "from": "noreply@empresa.com",
    "to": "fornecedor@email.com",
    "subject": "Nova Cotação Disponível",
    "empresa": "Minha Empresa LTDA",
    "endereco_empresa": "Rua das Flores, 123 - Centro - São Paulo/SP",
    "numero_cotacao": "COT-2025-001",
    "nome_fornecedor": "Fornecedor XYZ"
}
*/
