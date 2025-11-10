<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->name('login');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::get('two-factor/verificacao', [TwoFactorAuthenticationController::class, 'verificacao'])
        ->name('two-factor.verificacao');

    Route::post('two-factor/login', [TwoFactorAuthenticationController::class, 'login'])
        ->name('two-factor.login');
});

Route::middleware('auth:sanctum')->get('/check-session', function () {
    return response()->json(['status' => 'active']);
});

// Endpoint para dashboard de checklist obter dados do usuário da sessão
Route::middleware('auth')->get('/auth/me', function () {
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Usuário não autenticado'
        ], 401);
    }

    // Carregar relações necessárias
    $user->load(['filial', 'departamento']);

    return response()->json([
        'success' => true,
        'data' => [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'matricula' => $user->matricula,
                'cpf' => $user->cpf,
                'is_superuser' => $user->is_superuser ?? false,
                'is_admin' => $user->is_superuser ?? false,
                'filial_id' => $user->filial_id,
                'departamento_id' => $user->departamento_id,
                'pessoal_id' => $user->pessoal_id,
                'avatar' => $user->avatar,
                'filial' => $user->filial ? [
                    'id' => $user->filial->id,
                    'name' => $user->filial->name ?? $user->filial->nome,
                ] : null,
                'departamento' => $user->departamento ? [
                    'id' => $user->departamento->id_departamento,
                    'name' => $user->departamento->descricao_departamento ?? $user->departamento->nome,
                ] : null,
            ]
        ]
    ]);
});
