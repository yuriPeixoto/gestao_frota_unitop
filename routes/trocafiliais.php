<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'trocafilial'], function () {
    Route::post('/troca-filial', [UserController::class, 'trocarFilial'])->name('trocafilial.filial')->middleware('auth');;
});
