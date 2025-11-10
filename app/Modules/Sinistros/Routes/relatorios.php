<?php

use App\Http\Controllers\Admin\DepartamentoController;
use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\MotoristaController;
use App\Http\Controllers\Admin\PessoalController;
use App\Http\Controllers\Admin\ProdutosImobilizadosController;
use App\Modules\Sinistros\Controllers\Relatorios\RelatorioSinistro;
use App\Modules\Sinistros\Controllers\Relatorios\RelatorioSinistroGeral;
use App\Modules\Sinistros\Controllers\Relatorios\RelatorioSinistroll;
use App\Modules\Sinistros\Controllers\SinistroController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'relatoriosinistro'], function () {
    Route::get('/', [RelatorioSinistro::class, 'index'])->name('relatoriosinistro.index');
    Route::post('/gerarpdf', [RelatorioSinistro::class, 'gerarPdf'])->name('relatoriosinistro.gerarpdf');
    Route::post('/gerarpdftotalizado', [RelatorioSinistro::class, 'gerarPdfTotalizado'])->name('relatoriosinistro.gerarpdftotalizado');
    Route::post('/gerarexcel', [RelatorioSinistro::class, 'gerarExcel'])->name('relatoriosinistro.gerarexcel');
});

Route::group(['prefix' => 'relatoriogeralsinistro'], function () {
    Route::get('/', [RelatorioSinistroGeral::class, 'index'])->name('relatoriogeralsinistro.index');
    Route::post('/gerarpdf', [RelatorioSinistroGeral::class, 'gerarPdf'])->name('relatoriogeralsinistro.gerarpdf');
    Route::post('/gerarpdftotalizado', [RelatorioSinistroGeral::class, 'gerarPdfTotalizado'])->name('relatoriogeralsinistro.gerarpdftotalizado');
    Route::post('/gerarexcel', [RelatorioSinistroGeral::class, 'gerarExcel'])->name('relatoriogeralsinistro.gerarexcel');
});

Route::group(['prefix' => 'relatoriosinistroll'], function () {
    Route::get('/', [RelatorioSinistroll::class, 'index'])->name('relatoriosinistroll.index');
    Route::post('/gerarpdf', [RelatorioSinistroll::class, 'gerarPdf'])->name('relatoriosinistroll.gerarpdf');
    Route::post('/gerarpdftotalizado', [RelatorioSinistroll::class, 'gerarPdfTotalizado'])->name('relatoriosinistroll.gerarpdftotalizado');
    Route::post('/gerarexcel', [RelatorioSinistroll::class, 'gerarExcel'])->name('relatoriosinistroll.gerarexcel');
});

Route::prefix('api')->group(function () {
    // Fornecedor
    Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
    Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');

    //Usuarios
    Route::get('/users/search', [UserController::class, 'search'])->name('api.users.search');
    Route::get('/users/single/{id}', [UserController::class, 'getById'])->name('api.users.single');

    // Departamento
    Route::get('/departamento/search', [DepartamentoController::class, 'search'])->name('api.departamento.search');
    Route::get('/departamento/single/{id}', [DepartamentoController::class, 'getById'])->name('api.departamento.single');

    // Produtos Imobilizados
    Route::get('/produtosimobilizados/search', [ProdutosImobilizadosController::class, 'search'])->name('api.produtosimobilizados.search');
    Route::get('/produtosimobilizados/single/{id}', [ProdutosImobilizadosController::class, 'getById'])->name('api.produtosimobilizados.single');

    // Pessoal
    Route::get('/pessoal/search', [PessoalController::class, 'search'])->name('api.pessoal.search');
    Route::get('/pessoal/single/{id}', [PessoalController::class, 'getById'])->name('api.pessoal.single');

    //Motorista
    Route::get('/motorista/search', [MotoristaController::class, 'search'])->name('api.motorista.search');
    Route::get('/motorista/single/{id}', [MotoristaController::class, 'getById'])->name('api.motorista.single');

    //Sinistro
    Route::get('/sinistro/search', [SinistroController::class, 'search'])->name('api.sinistro.search');
    Route::get('/sinistro/single/{id}', [SinistroController::class, 'getById'])->name('api.sinistro.single');
});
