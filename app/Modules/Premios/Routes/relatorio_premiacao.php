<?php

use App\Modules\Premios\Controllers\Admin\DeflatoresCarvalimaController;
use App\Modules\Premios\Controllers\Admin\DeflatoresEventosMotoristasController;
use App\Modules\Premios\Controllers\Admin\FeriadoPremioSuperacaoController;
use App\Modules\Premios\Controllers\Admin\FranquiaPremiroMensalController;
use App\Modules\Premios\Controllers\Admin\FranquiaPremiroRvController;
use App\Modules\Premios\Controllers\Admin\FranquiPremiroRvController;
use App\Modules\Premios\Controllers\Admin\ManutencaoPremioController;
use App\Modules\Premios\Controllers\Admin\PremioSuperacaoController;
use App\Modules\Premios\Controllers\Relatorios\RelatorioConferenciaPremioRvMensal;
use App\Http\Controllers\Admin\RelatorioConferenciaTabelaoController;
use App\Http\Controllers\Admin\RelatorioExtratoMotoristaRhController;
use App\Http\Controllers\Admin\RelatorioMotoristasNaoCalcudados;
use App\Modules\Premios\Controllers\Relatorios\RelatorioPremiacaoMotoristaController;
use App\Modules\Premios\Controllers\Relatorios\RelatorioPremioConferenciaController;
use App\Modules\Premios\Controllers\Relatorios\RelatorioPremioDeflatoresController;
use App\Http\Controllers\Admin\RelatorioValoresExcedentesController;
use App\Http\Controllers\Admin\RelatorioVeiculosSemLogin;
use App\Modules\Premios\Controllers\Admin\TipoOperacaoController;
use App\Models\JornadaFeriado;
use App\Modules\Premios\Models\PremioSuperacao;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'tipooperacao'], function () {
    Route::get('/', [TipoOperacaoController::class, 'index'])->name('tipooperacao.index');
    Route::get('criar', [TipoOperacaoController::class, 'create'])->name('tipooperacao.create');
    Route::post('/', [TipoOperacaoController::class, 'store'])->name('tipooperacao.store');
    Route::get('/editar/{id}', [TipoOperacaoController::class, 'edit'])->name('tipooperacao.edit');
    Route::put('/{id}', [TipoOperacaoController::class, 'update'])->name('tipooperacao.update');
    Route::delete('/{id}', [TipoOperacaoController::class, 'destroy'])->name('tipooperacao.delete');
});

Route::group(['prefix' => 'deflatoreseventospormotoristas'], function () {
    Route::get('/', [DeflatoresEventosMotoristasController::class, 'index'])->name('deflatoreseventospormotoristas.index');
    Route::get('criar', [DeflatoresEventosMotoristasController::class, 'create'])->name('deflatoreseventospormotoristas.create');
    Route::post('/', [DeflatoresEventosMotoristasController::class, 'store'])->name('deflatoreseventospormotoristas.store');
    Route::get('/editar/{id}', [DeflatoresEventosMotoristasController::class, 'edit'])->name('deflatoreseventospormotoristas.edit');
    Route::put('/{id}', [DeflatoresEventosMotoristasController::class, 'update'])->name('deflatoreseventospormotoristas.update');
    Route::delete('/{id}', [DeflatoresEventosMotoristasController::class, 'destroy'])->name('deflatoreseventospormotoristas.delete');
});

Route::group(['prefix' => 'deflatorescarvalima'], function () {
    Route::get('/', [DeflatoresCarvalimaController::class, 'index'])->name('deflatorescarvalima.index');
    Route::get('criar', [DeflatoresCarvalimaController::class, 'create'])->name('deflatorescarvalima.create');
    Route::post('/', [DeflatoresCarvalimaController::class, 'store'])->name('deflatorescarvalima.store');
    Route::get('/editar/{id}', [DeflatoresCarvalimaController::class, 'edit'])->name('deflatorescarvalima.edit');
    Route::put('/{id}', [DeflatoresCarvalimaController::class, 'update'])->name('deflatorescarvalima.update');
    Route::delete('/{id}', [DeflatoresCarvalimaController::class, 'destroy'])->name('deflatorescarvalima.delete');
});

Route::group(['prefix' => 'jornadaferiado'], function () {
    Route::get('/', [FeriadoPremioSuperacaoController::class, 'index'])->name('jornadaferiado.index');
    Route::get('criar', [FeriadoPremioSuperacaoController::class, 'create'])->name('jornadaferiado.create');
    Route::post('/', [FeriadoPremioSuperacaoController::class, 'store'])->name('jornadaferiado.store');
    Route::get('/editar/{id}', [FeriadoPremioSuperacaoController::class, 'edit'])->name('jornadaferiado.edit');
    Route::put('/{id}', [FeriadoPremioSuperacaoController::class, 'update'])->name('jornadaferiado.update');
    Route::delete('/{id}', [FeriadoPremioSuperacaoController::class, 'destroy'])->name('jornadaferiado.delete');
});

Route::group(['prefix' => 'franquiapremiorv'], function () {
    Route::get('/', [FranquiaPremiroRvController::class, 'index'])->name('franquiapremiorv.index');
    Route::get('criar', [FranquiaPremiroRvController::class, 'create'])->name('franquiapremiorv.create');
    Route::post('/', [FranquiaPremiroRvController::class, 'store'])->name('franquiapremiorv.store');
    Route::get('/editar/{id}', [FranquiaPremiroRvController::class, 'edit'])->name('franquiapremiorv.edit');
    Route::put('/{id}', [FranquiaPremiroRvController::class, 'update'])->name('franquiapremiorv.update');
    Route::delete('/{id}', [FranquiaPremiroRvController::class, 'destroy'])->name('franquiapremiorv.delete');
    Route::post('clonar/{id}', [FranquiaPremiroRvController::class, 'clonarFranquia'])->name('franquiapremiorv.clonar');
    Route::post('desativar/{id}', [FranquiaPremiroRvController::class, 'desativarFranquia'])->name('franquiapremiorv.desativar');
});

Route::group(['prefix' => 'franquiapremiosmensal'], function () {
    Route::get('/', [FranquiaPremiroMensalController::class, 'index'])->name('franquiapremiosmensal.index');
    Route::get('criar', [FranquiaPremiroMensalController::class, 'create'])->name('franquiapremiosmensal.create');
    Route::post('/', [FranquiaPremiroMensalController::class, 'store'])->name('franquiapremiosmensal.store');
    Route::get('/editar/{id}', [FranquiaPremiroMensalController::class, 'edit'])->name('franquiapremiosmensal.edit');
    Route::put('/{id}', [FranquiaPremiroMensalController::class, 'update'])->name('franquiapremiosmensal.update');
    Route::delete('/{id}', [FranquiaPremiroMensalController::class, 'destroy'])->name('franquiapremiosmensal.delete');
    Route::post('clonar/{id}', [FranquiaPremiroMensalController::class, 'clonarFranquia'])->name('franquiapremiosmensal.clonar');
    Route::post('desativar/{id}', [FranquiaPremiroMensalController::class, 'desativarFranquia'])->name('franquiapremiosmensal.desativar');
});

Route::group(['prefix' => 'premiosuperacao'], function () {
    Route::get('/', [PremioSuperacaoController::class, 'index'])->name('premiosuperacao.index');
    Route::get('criar', [PremioSuperacaoController::class, 'create'])->name('premiosuperacao.create');
    Route::post('/', [PremioSuperacaoController::class, 'store'])->name('premiosuperacao.store');
    Route::get('/editar/{id}', [PremioSuperacaoController::class, 'edit'])->name('premiosuperacao.edit');
    Route::put('/{id}', [PremioSuperacaoController::class, 'update'])->name('premiosuperacao.update');
    Route::delete('/{id}', [PremioSuperacaoController::class, 'destroy'])->name('premiosuperacao.delete');
    Route::post('reprocessar/{id}', [PremioSuperacaoController::class, 'reprocessar'])->name('premiosuperacao.reprocessar');
    Route::post('finalizar/{id}', [PremioSuperacaoController::class, 'finalizarPremio'])->name('premiosuperacao.finalizar');
    Route::post('confirmar/{id}', [PremioSuperacaoController::class, 'confirmarPagamento'])->name('premiosuperacao.confirmar');
});


Route::group(['prefix' => 'manutencaopremio'], function () {
    Route::get('/', [ManutencaoPremioController::class, 'index'])->name('manutencaopremio.index');
    Route::get('criar', [ManutencaoPremioController::class, 'create'])->name('manutencaopremio.create');
    Route::post('/', [ManutencaoPremioController::class, 'store'])->name('manutencaopremio.store');
    Route::get('/editar/{id}', [ManutencaoPremioController::class, 'edit'])->name('manutencaopremio.edit');
    Route::get('show', [ManutencaoPremioController::class, 'show'])->name('manutencaopremio.show');
    Route::put('/{id}', [ManutencaoPremioController::class, 'update'])->name('manutencaopremio.update');
    Route::delete('/{id}', [ManutencaoPremioController::class, 'destroy'])->name('manutencaopremio.delete');
    Route::get('/editar_motorista/{id}', [ManutencaoPremioController::class, 'editarMotorista'])->name('manutencaopremio.editarmotorista');
    Route::post('/atualizatmotorista/{id}', [ManutencaoPremioController::class, 'update_motorista'])->name('manutencaopremio.updatemotorista');
    Route::get('modalDistancia', [ManutencaoPremioController::class, 'modalDistancia'])->name('manutencaopremio.modal');
    Route::get('modalKm', [ManutencaoPremioController::class, 'modalKm'])->name('manutencaopremio.modalkm');
    Route::get('/editKm/{id}', [ManutencaoPremioController::class, 'editKm'])->name('manutencaopremio.editKm');
    Route::post('/update_km/{id}', [ManutencaoPremioController::class, 'update_motorista'])->name('manutencaopremio.updatemotorista');
});

Route::group(['prefix' => 'relatorioveiculosemlogin'], function () {
    Route::get('/', [RelatorioVeiculosSemLogin::class, 'index'])->name('relatorioveiculosemlogin.index');
    Route::post('/gerarpdf', [RelatorioVeiculosSemLogin::class, 'gerarPdf'])->name('relatorioveiculosemlogin.gerarpdf');
    Route::post('/gerarexcel', [RelatorioVeiculosSemLogin::class, 'gerarExcel'])->name('relatorioveiculosemlogin.gerarexcel');
});

Route::group(['prefix' => 'relatoriopremiodeflatores'], function () {
    Route::get('/', [RelatorioPremioDeflatoresController::class, 'index'])->name('relatoriopremiodeflatores.index');
    Route::post('/gerarpdf', [RelatorioPremioDeflatoresController::class, 'gerarPdf'])->name('relatoriopremiodeflatores.gerarpdf');
    Route::post('/gerarexcel', [RelatorioPremioDeflatoresController::class, 'gerarExcel'])->name('relatoriopremiodeflatores.gerarexcel');
});

Route::group(['prefix' => 'relatoriomotoristanaocalculado'], function () {
    Route::get('/', [RelatorioMotoristasNaoCalcudados::class, 'index'])->name('relatoriomotoristanaocalculado.index');
    Route::post('/gerarpdf', [RelatorioMotoristasNaoCalcudados::class, 'gerarPdf'])->name('relatoriomotoristanaocalculado.gerarpdf');
    Route::post('/gerarexcel', [RelatorioMotoristasNaoCalcudados::class, 'gerarExcel'])->name('relatoriomotoristanaocalculado.gerarexcel');
});

Route::group(['prefix' => 'relatorioconferenciapremiorvmensal'], function () {
    Route::get('/', [RelatorioConferenciaPremioRvMensal::class, 'index'])->name('relatorioconferenciapremiorvmensal.index');
    Route::post('/gerarpdf', [RelatorioConferenciaPremioRvMensal::class, 'gerarPdf'])->name('relatorioconferenciapremiorvmensal.gerarpdf');
    Route::post('/gerarexcel', [RelatorioConferenciaPremioRvMensal::class, 'gerarExcel'])->name('relatorioconferenciapremiorvmensal.gerarexcel');
    Route::post('/gerarconferenciamensal', [RelatorioConferenciaPremioRvMensal::class, 'gerarConferencialMensal'])->name('relatorioconferenciapremiorvmensal.gerarconferenciamensal');
    Route::post('/gerarconferenciamensalrv', [RelatorioConferenciaPremioRvMensal::class, 'gerarConferenciaMensalERV'])->name('relatorioconferenciapremiorvmensal.gerarconferenciamensalrv');
});

Route::group(['prefix' => 'relatorioconferenciatabelao'], function () {
    Route::get('/', [RelatorioConferenciaTabelaoController::class, 'index'])->name('relatorioconferenciatabelao.index');
    Route::post('/gerarpdf', [RelatorioConferenciaTabelaoController::class, 'gerarPdf'])->name('relatorioconferenciatabelao.gerarpdf');
    Route::post('/gerarexcel', [RelatorioConferenciaTabelaoController::class, 'gerarExcel'])->name('relatorioconferenciatabelao.gerarexcel');
    Route::post('/gerarconferenciamensal', [RelatorioConferenciaTabelaoController::class, 'gerarConferencialMensal'])->name('relatorioconferenciatabelao.gerarconferenciamensal');
    Route::post('/gerarconferenciamensalrv', [RelatorioConferenciaTabelaoController::class, 'gerarConferenciaMensalERV'])->name('relatorioconferenciatabelao.gerarconferenciamensalrv');
});

Route::group(['prefix' => 'relatoriopremioconferencia'], function () {
    Route::get('/', [RelatorioPremioConferenciaController::class, 'index'])->name('relatoriopremioconferencia.index');
    Route::post('/gerarpdf', [RelatorioPremioConferenciaController::class, 'gerarPdf'])->name('relatoriopremioconferencia.gerarpdf');
    Route::post('/gerarexcel', [RelatorioPremioConferenciaController::class, 'gerarExcel'])->name('relatoriopremioconferencia.gerarexcel');
    Route::post('/gerarpdfsecundo', [RelatorioPremioConferenciaController::class, 'gerarPdfSecundo'])->name('relatoriopremioconferencia.gerarpdfsecundo');
});

Route::group(['prefix' => 'relatorioextratomotoristarh'], function () {
    Route::get('/', [RelatorioExtratoMotoristaRhController::class, 'index'])->name('relatorioextratomotoristarh.index');
    Route::post('/gerarpdf', [RelatorioExtratoMotoristaRhController::class, 'gerarPdf'])->name('relatorioextratomotoristarh.gerarpdf');
    Route::post('/gerarexcel', [RelatorioExtratoMotoristaRhController::class, 'gerarExcel'])->name('relatorioextratomotoristarh.gerarexcel');
    Route::post('/gerarpdfsecundo', [RelatorioExtratoMotoristaRhController::class, 'gerarRelatorioPrevia'])->name('relatorioextratomotoristarh.gerarpdfsecundo');
});

Route::group(['prefix' => 'relatoriovaloresexcedentes'], function () {
    Route::get('/', [RelatorioValoresExcedentesController::class, 'index'])->name('relatoriovaloresexcedentes.index');
    Route::post('/gerarpdf', [RelatorioValoresExcedentesController::class, 'gerarPdf'])->name('relatoriovaloresexcedentes.gerarpdf');
    Route::post('/gerarexcel', [RelatorioValoresExcedentesController::class, 'gerarExcel'])->name('relatoriovaloresexcedentes.gerarexcel');
});

Route::group(['prefix' => 'relatoriopremiacaomotorista'], function () {
    Route::get('/', [RelatorioPremiacaoMotoristaController::class, 'index'])->name('relatoriopremiacaomotorista.index');
    Route::post('/gerarpdf', [RelatorioPremiacaoMotoristaController::class, 'gerarPdf'])->name('relatoriopremiacaomotorista.gerarpdf');
    Route::post('/gerarexcel', [RelatorioPremiacaoMotoristaController::class, 'gerarExcel'])->name('relatoriopremiacaomotorista.gerarexcel');
});

Route::prefix('api')->group(function () {
    Route::get('/api/manutencaopremio/search', [ManutencaoPremioController::class, 'search'])->name('api.manutencaopremio.search');
    Route::get('/api/manutencaopremio/single/{id}', [ManutencaoPremioController::class, 'getById'])->name('api.manutencaopremio.single');
});
