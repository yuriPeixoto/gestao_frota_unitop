<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Notification Routes
|--------------------------------------------------------------------------
|
| Rotas para o sistema de notificações em tempo real
|
*/

// Rota pública para receber notificações de sistemas externos (API Lumen)
// Protegida por token de API no header X-API-Token
Route::post('/api/notifications/send', [NotificationController::class, 'sendFromExternalSystem'])
    ->name('api.notifications.send-external');

// ✅ NOVO: Rotas API para Mobile App (autenticação via JWT do Lumen)
Route::middleware(['jwt.lumen'])->prefix('api/mobile/notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'getNotificationsForMobile'])
        ->name('api.mobile.notifications.get');

    Route::get('/unread-count', [NotificationController::class, 'getUnreadCountForMobile'])
        ->name('api.mobile.notifications.unread-count');

    Route::post('/{id}/read', [NotificationController::class, 'markAsReadForMobile'])
        ->name('api.mobile.notifications.mark-read');

    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsReadForMobile'])
        ->name('api.mobile.notifications.mark-all-read');

    Route::delete('/{id}', [NotificationController::class, 'deleteForMobile'])
        ->name('api.mobile.notifications.delete');

    // 🧪 Endpoint de teste - criar notificação
    Route::post('/test/create', [NotificationController::class, 'createTestNotification'])
        ->name('api.mobile.notifications.test');
});

Route::middleware(['auth', '2fa'])->group(function () {
    // Central de notificações
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    // API de notificações
    Route::prefix('api/notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'getNotifications'])
            ->name('api.notifications.get');

        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])
            ->name('api.notifications.unread-count');

        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('api.notifications.mark-read');

        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->name('api.notifications.mark-all-read');

        Route::delete('/{id}', [NotificationController::class, 'delete'])
            ->name('api.notifications.delete');
    });

    // Configurações de notificação
    Route::get('/notifications/settings', [NotificationController::class, 'settings'])
        ->name('notifications.settings');

    Route::post('/notifications/settings', [NotificationController::class, 'updateSettings'])
        ->name('notifications.settings.update');
});
