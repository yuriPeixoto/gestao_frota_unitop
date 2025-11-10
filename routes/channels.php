<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Aqui você pode registrar todos os canais de broadcast que sua aplicação
| suporta. O closure de autorização será chamado quando o cliente tentar
| se inscrever em um canal privado ou de presença.
|
*/

// Canal pessoal do usuário
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal de notificações pessoais
Broadcast::channel('notifications.user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Canal de notificações por departamento
Broadcast::channel('notifications.department.{departmentId}', function ($user, $departmentId) {
    return (int) $user->departamento_id === (int) $departmentId;
});

// Canal de notificações por filial
Broadcast::channel('notifications.filial.{filialId}', function ($user, $filialId) {
    return $user->hasAccessToFilial((int) $filialId);
});

// Canal de notificações por cargo/tipo pessoal
Broadcast::channel('notifications.cargo.{cargoId}', function ($user, $cargoId) {
    return (int) $user->pessoal_id === (int) $cargoId;
});

// Canal de notificações por role
Broadcast::channel('notifications.role.{roleId}', function ($user, $roleId) {
    return $user->roles()->where('roles.id', $roleId)->exists();
});

// Canal de notificações globais (todos os usuários autenticados)
Broadcast::channel('notifications.global', function ($user) {
    return true; // Todos os usuários autenticados podem ouvir
});
