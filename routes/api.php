<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedJwtController;
use App\Http\Controllers\Admin\DepartamentoController;
use App\Modules\Configuracoes\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ChecklistController;
use App\Http\Controllers\Admin\ChecklistRespostaController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/signIn', [AuthenticatedJwtController::class, 'signIn']);

Route::post('/signOut', [AuthenticatedJwtController::class, 'signOut']);

Route::get('/departamento', [DepartamentoController::class, 'findAll'])->middleware('auth:sanctum');

Route::get('/user', [UserController::class, 'user'])->middleware('auth:sanctum');

Route::get('/checklists', [ChecklistController::class, 'checklists'])->middleware('auth:sanctum');

Route::get('/coluna-checklist/{id}', [ChecklistController::class, 'colunaChecklist'])->middleware('auth:sanctum');

Route::post('/checklist-resposta', [ChecklistRespostaController::class, 'store'])->middleware('auth:sanctum');