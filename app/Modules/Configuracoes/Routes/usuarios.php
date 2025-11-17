<?php
/**
 * Rotas de Usuários, Cargos e Permissões
 */
use App\Modules\Configuracoes\Controllers\Admin\{
    PermissionController,
    PermissionDiscoveryController,
    RoleController,
    UserController
};
use Illuminate\Support\Facades\Route;

// Rotas de Permissões
Route::prefix('permissoes')->name('permissoes.')->group(function () {
    Route::get('/', [PermissionController::class, 'index'])->name('index');
    Route::post('/assign', [PermissionController::class, 'assign'])->name('assign');
    Route::get('/targets/{type}', [PermissionController::class, 'getTargets'])->name('targets');
    Route::get('/get-permissions/{type}/{id}', [PermissionController::class, 'getPermissions'])
        ->name('get-permissions');

    // Rotas de clonagem de permissões
    Route::get('/clone', [PermissionController::class, 'cloneInterface'])->name('clone');
    Route::post('/clone', [PermissionController::class, 'clonePermissions'])->name('clone.execute');

    // Rotas de descoberta de permissões
    Route::get('/discover', [PermissionDiscoveryController::class, 'index'])->name('discover');
    Route::post('/sync', [PermissionDiscoveryController::class, 'sync'])->name('sync');
    Route::post('/group-permissions', [PermissionDiscoveryController::class, 'updateGroupPermissions'])->name('update-group');
});

// Rotas de Cargos
Route::group(['prefix' => 'cargos'], function () {
    Route::get('/', [RoleController::class, 'index'])->name('cargos.index');
    Route::get('criar', [RoleController::class, 'create'])->name('cargos.create');
    Route::get('{role}', [RoleController::class, 'show'])->name('cargos.show');

    Route::post('/', [RoleController::class, 'store'])->name('cargos.store');
    Route::get('{role}/editar', [RoleController::class, 'edit'])->name('cargos.edit');
    Route::put('{role}', [RoleController::class, 'update'])->name('cargos.update');

    Route::delete('{role}', [RoleController::class, 'destroy'])
        ->name('cargos.destroy');
});

// Rotas de Usuários
Route::group(['prefix' => 'usuarios'], function () {
    Route::get('/', [UserController::class, 'index'])->name('usuarios.index');
    Route::get('criar', [UserController::class, 'create'])->name('usuarios.create');
    Route::get('/com-departamentos', [UserController::class, 'listWithDepartments'])
        ->name('usuarios.list-with-departments');
    Route::get('/dados-tabela', [UserController::class, 'getDadosTabela'])
        ->name('usuarios.dados-tabela');
    Route::get('{user}', [UserController::class, 'show'])->name('usuarios.show');


    Route::post('/', [UserController::class, 'store'])->name('usuarios.store');
    Route::get('{user}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
    Route::put('{user}', [UserController::class, 'update'])->name('usuarios.update');

    Route::post('{user}/relacoes', [UserController::class, 'updateRelacoes'])
        ->name('usuarios.update-relacoes');
    Route::post('{user}/clone', [UserController::class, 'cloneUser'])->name('usuarios.clone');


    Route::get('{user}/editar-departamento', [UserController::class, 'editDepartment'])
        ->name('usuarios.edit-departament');
    Route::put('{user}/atualizar-departamento', [UserController::class, 'updateDepartment'])
        ->name('usuarios.update-departament');

    Route::delete('{user}', [UserController::class, 'destroy'])
        ->name('usuarios.destroy');
});
