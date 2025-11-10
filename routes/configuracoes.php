<?php

use App\Http\Controllers\Admin\{
    BranchController,
    LogController,
    RoleController,
    SubCategoriaVeiculoController,
    TipoAcertoEstoqueController,
    TipoBorrachaPneuController,
    TipoCategoriaController,
    TipoCombustivelController,
    TipoDesenhoPneuController,
    TipoDimensaoPneuController,
    TipoEquipamentoController,
    TipoFornecedorController,
    TipoImobilizadoController,
    TipoManutencaoController,
    TipoManutencaoImobilizadoController,
    TipoMotivoSinistroController,
    TipoOcorrenciaController,
    TipoOrgaoSinistroController,
    TipoPessoalController,
    TipoReformaPneuController,
    TipoVeiculoController,
    UnidadeProdutoController,
    UserController,
    TipoSolicitacaoController,
    PermissionController,
    PermissionDiscoveryController,
    DepartamentoTransferenciaController,
    StatusCadastroImobilizadoController,
    TelefoneTransferenciaController
};
use Illuminate\Support\Facades\Route;

// Rotas de Filiais
Route::group(['prefix' => 'filiais'], function () {
    Route::get('/', [BranchController::class, 'index'])->name('filiais.index');

    Route::get('criar', [BranchController::class, 'create'])->name('filiais.create');
    Route::post('/', [BranchController::class, 'store'])->name('filiais.store');

    Route::get('{branch}/editar', [BranchController::class, 'edit'])->name('filiais.edit');
    Route::put('{branch}', [BranchController::class, 'update'])->name('filiais.update');

    Route::delete('{branch}', [BranchController::class, 'destroy'])
        ->name('filiais.destroy');
});

// Rotas de Log de Atividades
Route::prefix('log-atividades')->name('log-atividades.')->group(function () {
    Route::get('/', [LogController::class, 'index'])->name('index');
    Route::get('/dashboard', [LogController::class, 'dashboard'])->name('dashboard');
    Route::get('/export', [LogController::class, 'export'])->name('export');
    Route::get('/{log}', [LogController::class, 'show'])->name('show');
    Route::post('/cleanup', [LogController::class, 'cleanup'])->name('cleanup');
    Route::get('/api/critical-alerts', [LogController::class, 'getCriticalAlerts'])->name('critical-alerts');
});

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

// Rotas de Tipo Acerto Estoque
Route::group(['prefix' => 'tipoacertoestoque'], function () {
    Route::get('/', [TipoAcertoEstoqueController::class, 'index'])->name('tipoacertoestoque.index');
    Route::get('criar', [TipoAcertoEstoqueController::class, 'create'])->name('tipoacertoestoque.create');
    Route::get('{tipoacerto}', [TipoAcertoEstoqueController::class, 'show'])->name('tipoacertoestoque.show');

    Route::post('/', [TipoAcertoEstoqueController::class, 'store'])->name('tipoacertoestoque.store');
    Route::get('{tipoacerto}/editar', [TipoAcertoEstoqueController::class, 'edit'])->name('tipoacertoestoque.edit');
    Route::put('{tipoacerto}', [TipoAcertoEstoqueController::class, 'update'])->name('tipoacertoestoque.update');

    Route::delete('{tipoacerto}', [TipoAcertoEstoqueController::class, 'destroy'])
        ->name('tipoacertoestoque.destroy');
});

// Rotas de Tipo Borracha Pneu
Route::group(['prefix' => 'tipoborrachapneus'], function () {
    Route::get('/', [TipoBorrachaPneuController::class, 'index'])->name('tipoborrachapneus.index');
    Route::get('criar', [TipoBorrachaPneuController::class, 'create'])->name('tipoborrachapneus.create');
    Route::get('{tipoborrachapneus}', [TipoBorrachaPneuController::class, 'show'])->name('tipoborrachapneus.show');

    Route::post('/', [TipoBorrachaPneuController::class, 'store'])->name('tipoborrachapneus.store');
    Route::get('{tipoborrachapneus}/editar', [TipoBorrachaPneuController::class, 'edit'])
        ->name('tipoborrachapneus.edit');
    Route::put('{tipoborrachapneus}', [TipoBorrachaPneuController::class, 'update'])->name('tipoborrachapneus.update');

    Route::delete('{tipoborrachapneus}', [TipoBorrachaPneuController::class, 'destroy'])
        ->name('tipoborrachapneus.destroy');
});

// Rotas de Tipo Categoria
Route::group(['prefix' => 'tipocategorias'], function () {
    Route::get('/', [TipoCategoriaController::class, 'index'])->name('tipocategorias.index');
    Route::get('criar', [TipoCategoriaController::class, 'create'])->name('tipocategorias.create');
    Route::get('{tipocategoria}', [TipoCategoriaController::class, 'show'])->name('tipocategorias.show');

    Route::post('/', [TipoCategoriaController::class, 'store'])->name('tipocategorias.store');
    Route::get('{tipocategoria}/editar', [TipoCategoriaController::class, 'edit'])->name('tipocategorias.edit');
    Route::put('{tipocategoria}', [TipoCategoriaController::class, 'update'])->name('tipocategorias.update');

    Route::delete('{tipocategoria}', [TipoCategoriaController::class, 'destroy'])
        ->name('tipocategorias.destroy');
});

// Rotas de Tipo Combustivel
Route::group(['prefix' => 'tipocombustiveis'], function () {
    Route::get('/', [TipoCombustivelController::class, 'index'])->name('tipocombustiveis.index');
    Route::get('criar', [TipoCombustivelController::class, 'create'])->name('tipocombustiveis.create');
    Route::get('{tipocombustivel}', [TipoCombustivelController::class, 'show'])->name('tipocombustiveis.show');

    Route::post('/', [TipoCombustivelController::class, 'store'])->name('tipocombustiveis.store');
    Route::get('{tipocombustivel}/editar', [TipoCombustivelController::class, 'edit'])->name('tipocombustiveis.edit');
    Route::put('{tipocombustivel}', [TipoCombustivelController::class, 'update'])->name('tipocombustiveis.update');

    Route::delete('{tipocombustivel}', [TipoCombustivelController::class, 'destroy'])
        ->name('tipocombustiveis.destroy');
});

// Rotas de Tipo Desenho Pneu
Route::group(['prefix' => 'tipodesenhopneus'], function () {
    Route::get('/export-csv', [TipoDesenhoPneuController::class, 'exportCsv'])->name('tipodesenhopneus.exportCsv');
    Route::get('/export-xls', [TipoDesenhoPneuController::class, 'exportXls'])->name('tipodesenhopneus.exportXls');
    Route::get('/export-pdf', [TipoDesenhoPneuController::class, 'exportPdf'])->name('tipodesenhopneus.exportPdf');
    Route::get('/export-xml', [TipoDesenhoPneuController::class, 'exportXml'])->name('tipodesenhopneus.exportXml');

    Route::get('/', [TipoDesenhoPneuController::class, 'index'])->name('tipodesenhopneus.index');
    Route::get('criar', [TipoDesenhoPneuController::class, 'create'])->name('tipodesenhopneus.create');
    Route::get('{tipodesenhopneus}', [TipoDesenhoPneuController::class, 'show'])->name('tipodesenhopneus.show');

    Route::post('/', [TipoDesenhoPneuController::class, 'store'])->name('tipodesenhopneus.store');
    Route::get('{tipodesenhopneus}/editar', [TipoDesenhoPneuController::class, 'edit'])->name('tipodesenhopneus.edit');
    Route::put('{tipodesenhopneus}', [TipoDesenhoPneuController::class, 'update'])->name('tipodesenhopneus.update');

    Route::delete('{tipodesenhopneus}', [TipoDesenhoPneuController::class, 'destroy'])
        ->name('tipodesenhopneus.destroy');
});

// Rotas de Tipo Dimensão Pneu
Route::group(['prefix' => 'tipodimensaopneus'], function () {
    Route::get('/', [TipoDimensaoPneuController::class, 'index'])->name('tipodimensaopneus.index');
    Route::get('criar', [TipoDimensaoPneuController::class, 'create'])->name('tipodimensaopneus.create');
    Route::get('{tipodimensaopneus}', [TipoDimensaoPneuController::class, 'show'])->name('tipodimensaopneus.show');

    Route::post('/', [TipoDimensaoPneuController::class, 'store'])->name('tipodimensaopneus.store');
    Route::get('{tipodimensaopneus}/editar', [TipoDimensaoPneuController::class, 'edit'])
        ->name('tipodimensaopneus.edit');
    Route::put('{tipodimensaopneus}', [TipoDimensaoPneuController::class, 'update'])->name('tipodimensaopneus.update');

    Route::delete('{tipodimensaopneus}', [TipoDimensaoPneuController::class, 'destroy'])
        ->name('tipodimensaopneus.destroy');
});

// Rotas de Tipo Equipamento
Route::group(['prefix' => 'tipoequipamentos'], function () {
    Route::get('/', [TipoEquipamentoController::class, 'index'])->name('tipoequipamentos.index');
    Route::get('criar', [TipoEquipamentoController::class, 'create'])->name('tipoequipamentos.create');
    Route::get('{tipoequipamento}', [TipoEquipamentoController::class, 'show'])->name('tipoequipamentos.show');

    Route::post('/', [TipoEquipamentoController::class, 'store'])->name('tipoequipamentos.store');
    Route::get('{tipoequipamento}/editar', [TipoEquipamentoController::class, 'edit'])
        ->name('tipoequipamentos.edit');
    Route::put('{tipoequipamento}', [TipoEquipamentoController::class, 'update'])
        ->name('tipoequipamentos.update');

    Route::delete('{tipoequipamento}', [TipoEquipamentoController::class, 'destroy'])
        ->name('tipoequipamentos.destroy');
});

// Rotas de Tipo Fornecedores
Route::group(['prefix' => 'tipofornecedores'], function () {
    Route::get('/', [TipoFornecedorController::class, 'index'])->name('tipofornecedores.index');
    Route::get('criar', [TipoFornecedorController::class, 'create'])->name('tipofornecedores.create');
    Route::get('{tipofornecedor}', [TipoFornecedorController::class, 'show'])->name('tipofornecedores.show');

    Route::post('/', [TipoFornecedorController::class, 'store'])->name('tipofornecedores.store');
    Route::get('{tipofornecedor}/editar', [TipoFornecedorController::class, 'edit'])
        ->name('tipofornecedores.edit');
    Route::put('{tipofornecedor}', [TipoFornecedorController::class, 'update'])
        ->name('tipofornecedores.update');

    Route::delete('{tipofornecedor}', [TipoFornecedorController::class, 'destroy'])
        ->name('tipofornecedores.destroy');
});

// Rotas de Tipo Imobilizado
Route::group(['prefix' => 'tipoimobilizados'], function () {
    Route::get('/', [TipoImobilizadoController::class, 'index'])->name('tipoimobilizados.index');
    Route::get('criar', [TipoImobilizadoController::class, 'create'])->name('tipoimobilizados.create');
    Route::get('{tipoimobilizados}', [TipoImobilizadoController::class, 'show'])->name('tipoimobilizados.show');

    Route::post('/', [TipoImobilizadoController::class, 'store'])->name('tipoimobilizados.store');
    Route::get('{tipoimobilizados}/editar', [TipoImobilizadoController::class, 'edit'])->name('tipoimobilizados.edit');
    Route::put('{tipoimobilizados}', [TipoImobilizadoController::class, 'update'])->name('tipoimobilizados.update');

    Route::delete('{tipoimobilizados}', [TipoImobilizadoController::class, 'destroy'])
        ->name('tipoimobilizados.destroy');
});

// Rotas de Tipo Manutencao
Route::group(['prefix' => 'tipomanutencoes'], function () {
    Route::get('/', [TipoManutencaoController::class, 'index'])->name('tipomanutencoes.index');
    Route::get('criar', [TipoManutencaoController::class, 'create'])->name('tipomanutencoes.create');
    Route::get('{tipomanutencoes}', [TipoManutencaoController::class, 'show'])->name('tipomanutencoes.show');

    Route::post('/', [TipoManutencaoController::class, 'store'])->name('tipomanutencoes.store');
    Route::get('{tipomanutencoes}/editar', [TipoManutencaoController::class, 'edit'])
        ->name('tipomanutencoes.edit');
    Route::put('{tipomanutencoes}', [TipoManutencaoController::class, 'update'])
        ->name('tipomanutencoes.update');

    Route::delete('{tipomanutencoes}', [TipoManutencaoController::class, 'destroy'])
        ->name('tipomanutencoes.destroy');
});

// Rotas TipoManutencaoImobilizados
Route::group(['prefix' => 'tipomanutencaoimobilizados'], function () {
    Route::get('/', [TipoManutencaoImobilizadoController::class, 'index'])->name('tipomanutencaoimobilizados.index');
    Route::get('criar', [TipoManutencaoImobilizadoController::class, 'create'])
        ->name('tipomanutencaoimobilizados.create');
    Route::get('{tipomanutencaoimobilizado}', [TipoManutencaoImobilizadoController::class, 'show'])
        ->name('tipomanutencaoimobilizados.show');

    Route::post('/', [TipoManutencaoImobilizadoController::class, 'store'])->name('tipomanutencaoimobilizados.store');
    Route::get('{tipomanutencaoimobilizado}/editar', [TipoManutencaoImobilizadoController::class, 'edit'])
        ->name('tipomanutencaoimobilizados.edit');
    Route::put('{tipomanutencaoimobilizado}', [TipoManutencaoImobilizadoController::class, 'update'])
        ->name('tipomanutencaoimobilizados.update');

    Route::delete('{tipomanutencaoimobilizado}', [TipoManutencaoImobilizadoController::class, 'destroy'])
        ->name('tipomanutencaoimobilizados.destroy');
});

// Rotas de Tipo Motivo de Sinistros
Route::group(['prefix' => 'tipomotivosinistros'], function () {
    Route::get('/', [TipoMotivoSinistroController::class, 'index'])->name('tipomotivosinistros.index');
    Route::get('criar', [TipoMotivoSinistroController::class, 'create'])->name('tipomotivosinistros.create');
    Route::get('{tipomotivosinistro}', [TipoMotivoSinistroController::class, 'show'])->name('tipomotivosinistros.show');

    Route::post('/', [TipoMotivoSinistroController::class, 'store'])->name('tipomotivosinistros.store');
    Route::get('{tipomotivosinistros}/editar', [TipoMotivoSinistroController::class, 'edit'])
        ->name('tipomotivosinistros.edit');
    Route::put('{tipomotivosinistros}', [TipoMotivoSinistroController::class, 'update'])
        ->name('tipomotivosinistros.update');

    Route::delete('{tipomotivosinistros}', [TipoMotivoSinistroController::class, 'destroy'])
        ->name('tipomotivosinistros.destroy');
});

// Rotas de Tipo Ocorrencia
Route::group(['prefix' => 'tipoocorrencias'], function () {
    Route::get('/', [TipoOcorrenciaController::class, 'index'])->name('tipoocorrencias.index');
    Route::get('criar', [TipoOcorrenciaController::class, 'create'])->name('tipoocorrencias.create');
    Route::get('{tipoocorrencias}', [TipoOcorrenciaController::class, 'show'])->name('tipoocorrencias.show');

    Route::post('/', [TipoOcorrenciaController::class, 'store'])->name('tipoocorrencias.store');
    Route::get('{tipoocorrencias}/editar', [TipoOcorrenciaController::class, 'edit'])
        ->name('tipoocorrencias.edit');
    Route::put('{tipoocorrencias}', [TipoOcorrenciaController::class, 'update'])
        ->name('tipoocorrencias.update');

    Route::delete('{tipoocorrencias}', [TipoOcorrenciaController::class, 'destroy'])
        ->name('tipoocorrencias.destroy');
});

// Rotas de Tipo Orgão Sinistros
Route::group(['prefix' => 'tipoorgaosinistros'], function () {
    Route::get('/', [TipoOrgaoSinistroController::class, 'index'])->name('tipoorgaosinistros.index');
    Route::get('criar', [TipoOrgaoSinistroController::class, 'create'])->name('tipoorgaosinistros.create');
    Route::get('{tipomotivosinistro}', [TipoOrgaoSinistroController::class, 'show'])->name('tipoorgaosinistros.show');

    Route::post('/', [TipoOrgaoSinistroController::class, 'store'])->name('tipoorgaosinistros.store');
    Route::get('{tipoorgaosinistros}/editar', [TipoOrgaoSinistroController::class, 'edit'])
        ->name('tipoorgaosinistros.edit');
    Route::put('{tipoorgaosinistros}', [TipoOrgaoSinistroController::class, 'update'])
        ->name('tipoorgaosinistros.update');

    Route::delete('{tipoorgaosinistros}', [TipoOrgaoSinistroController::class, 'destroy'])
        ->name('tipoorgaosinistros.destroy');
});

// Rotas de Tipo solicitação
Route::group(['prefix' => 'tiposolicitacao'], function () {
    Route::get('/', [TipoSolicitacaoController::class, 'index'])->name('tiposolicitacao.index');
    Route::get('criar', [TipoSolicitacaoController::class, 'create'])->name('tiposolicitacao.create');

    Route::post('/', [TipoSolicitacaoController::class, 'store'])->name('tiposolicitacao.store');
    Route::get('{tiposolicitacao}/editar', [TipoSolicitacaoController::class, 'edit'])
        ->name('tiposolicitacao.edit');
    Route::put('{tiposolicitacao}', [TipoSolicitacaoController::class, 'update'])
        ->name('tiposolicitacao.update');

    Route::delete('{tiposolicitacao}', [TipoSolicitacaoController::class, 'destroy'])
        ->name('tiposolicitacao.destroy');
});

// Rotas de StatusCadastroImobilizado
Route::group(['prefix' => 'statuscadastroimobilizado'], function () {
    Route::get('/', [StatusCadastroImobilizadoController::class, 'index'])->name('statuscadastroimobilizado.index');
    Route::get('criar', [StatusCadastroImobilizadoController::class, 'create'])->name('statuscadastroimobilizado.create');

    Route::post('/', [StatusCadastroImobilizadoController::class, 'store'])->name('statuscadastroimobilizado.store');
    Route::get('{statuscadastroimobilizado}/editar', [StatusCadastroImobilizadoController::class, 'edit'])
        ->name('statuscadastroimobilizado.edit');
    Route::put('{statuscadastroimobilizado}', [StatusCadastroImobilizadoController::class, 'update'])
        ->name('statuscadastroimobilizado.update');

    Route::delete('{statuscadastroimobilizado}', [StatusCadastroImobilizadoController::class, 'destroy'])
        ->name('statuscadastroimobilizado.destroy');
});

// Rotas de telefonetransferencia
Route::group(['prefix' => 'telefonetransferencia'], function () {
    Route::get('/', [TelefoneTransferenciaController::class, 'index'])->name('telefonetransferencia.index');
    Route::get('criar', [TelefoneTransferenciaController::class, 'create'])->name('telefonetransferencia.create');
    Route::get('{telefonetransferencia}/editar', [TelefoneTransferenciaController::class, 'edit'])
        ->name('telefonetransferencia.edit');


    Route::post('/', [TelefoneTransferenciaController::class, 'store'])->name('telefonetransferencia.store');

    Route::put('{telefonetransferencia}', [TelefoneTransferenciaController::class, 'update'])
        ->name('telefonetransferencia.update');


    Route::delete('{telefonetransferencia}', [TelefoneTransferenciaController::class, 'destroy'])
        ->name('telefonetransferencia.destroy');
});

// Rotas de telefonetransferencia
Route::group(['prefix' => 'departamentotransferencia'], function () {
    Route::get('/', [DepartamentoTransferenciaController::class, 'index'])->name('departamentotransferencia.index');
    Route::get('criar', [DepartamentoTransferenciaController::class, 'create'])->name('departamentotransferencia.create');

    Route::post('/', [DepartamentoTransferenciaController::class, 'store'])->name('departamentotransferencia.store');
    Route::get('{departamentotransferencia}/editar', [DepartamentoTransferenciaController::class, 'edit'])
        ->name('departamentotransferencia.edit');
    Route::put('{departamentotransferencia}', [DepartamentoTransferenciaController::class, 'update'])
        ->name('departamentotransferencia.update');

    Route::delete('{departamentotransferencia}', [DepartamentoTransferenciaController::class, 'destroy'])
        ->name('departamentotransferencia.destroy');
});


// Rotas de Tipo Pessoal
Route::group(['prefix' => 'tipopessoal'], function () {
    Route::get('/', [TipoPessoalController::class, 'index'])->name('tipopessoal.index');
    Route::get('criar', [TipoPessoalController::class, 'create'])->name('tipopessoal.create');
    Route::get('{tipopessoal}', [TipoPessoalController::class, 'show'])->name('tipopessoal.show');

    Route::post('/', [TipoPessoalController::class, 'store'])->name('tipopessoal.store');
    Route::get('{tipopessoal}/editar', [TipoPessoalController::class, 'edit'])
        ->name('tipopessoal.edit');
    Route::put('{tipopessoal}', [TipoPessoalController::class, 'update'])
        ->name('tipopessoal.update');

    Route::delete('{tipopessoal}', [TipoPessoalController::class, 'destroy'])
        ->name('tipopessoal.destroy');
});

// Rotas de Tipo Reforma Pneu
Route::group(['prefix' => 'tiporeformapneus'], function () {
    Route::get('/', [TipoReformaPneuController::class, 'index'])->name('tiporeformapneus.index');
    Route::get('criar', [TipoReformaPneuController::class, 'create'])->name('tiporeformapneus.create');
    Route::get('{tiporeformapneus}', [TipoReformaPneuController::class, 'show'])->name('tiporeformapneus.show');

    Route::post('/', [TipoReformaPneuController::class, 'store'])->name('tiporeformapneus.store');
    Route::get('{tiporeformapneus}/editar', [TipoReformaPneuController::class, 'edit'])->name('tiporeformapneus.edit');
    Route::put('{tiporeformapneus}', [TipoReformaPneuController::class, 'update'])->name('tiporeformapneus.update');

    Route::delete('{tiporeformapneus}', [TipoReformaPneuController::class, 'destroy'])
        ->name('tiporeformapneus.destroy');
});

// Rotas de Tipo Veiculo
Route::group(['prefix' => 'tipoveiculos'], function () {
    Route::get('/', [TipoVeiculoController::class, 'index'])->name('tipoveiculos.index');
    Route::get('criar', [TipoVeiculoController::class, 'create'])->name('tipoveiculos.create');
    Route::get('{tipoveiculo}', [TipoVeiculoController::class, 'show'])->name('tipoveiculos.show');

    Route::post('/', [TipoVeiculoController::class, 'store'])->name('tipoveiculos.store');
    Route::get('{tipoveiculo}/editar', [TipoVeiculoController::class, 'edit'])
        ->name('tipoveiculos.edit');
    Route::put('{tipoveiculo}', [TipoVeiculoController::class, 'update'])
        ->name('tipoveiculos.update');

    Route::delete('{tipoveiculo}', [TipoVeiculoController::class, 'destroy'])
        ->name('tipoveiculos.destroy');
});

// Rotas de Tipo de Subcategoria de veiculos
Route::group(
    ['prefix' => 'subcategoriaveiculos'],
    function () {
        Route::get('/', [SubCategoriaVeiculoController::class, 'index'])->name('subcategoriaveiculos.index');
        Route::get('criar', [SubCategoriaVeiculoController::class, 'create'])->name('subcategoriaveiculos.create');
        Route::get('{subcategoriaveiculo}', [SubCategoriaVeiculoController::class, 'show'])
            ->name('subcategoriaveiculos.show');

        Route::post('/', [SubCategoriaVeiculoController::class, 'store'])->name('subcategoriaveiculos.store');
        Route::get('{subcategoriaveiculo}/editar', [SubCategoriaVeiculoController::class, 'edit'])
            ->name('subcategoriaveiculos.edit');
        Route::put('{subcategoriaveiculo}', [SubCategoriaVeiculoController::class, 'update'])
            ->name('subcategoriaveiculos.update');

        Route::delete('{subcategoriaveiculo}', [SubCategoriaVeiculoController::class, 'destroy'])
            ->name('subcategoriaveiculos.destroy');
    }
);

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

// Rotas de Tipo Equipamento
Route::group(['prefix' => 'tipoequipamentos'], function () {
    Route::get('/', [TipoEquipamentoController::class, 'index'])->name('tipoequipamentos.index');
    Route::get('criar', [TipoEquipamentoController::class, 'create'])->name('tipoequipamentos.create');
    Route::get('{tipoequipamento}', [TipoEquipamentoController::class, 'show'])->name('tipoequipamentos.show');

    Route::post('/', [TipoEquipamentoController::class, 'store'])->name('tipoequipamentos.store');
    Route::get('{tipoequipamento}/editar', [TipoEquipamentoController::class, 'edit'])
        ->name('tipoequipamentos.edit');
    Route::put('{tipoequipamento}', [TipoEquipamentoController::class, 'update'])
        ->name('tipoequipamentos.update');

    Route::delete('{tipoequipamento}', [TipoEquipamentoController::class, 'destroy'])
        ->name('tipoequipamentos.destroy');
});

// Rotas de Tipo Reforma Pneu
Route::group(['prefix' => 'tiporeformapneus'], function () {
    Route::get('/', [TipoReformaPneuController::class, 'index'])->name('tiporeformapneus.index');
    Route::get('criar', [TipoReformaPneuController::class, 'create'])->name('tiporeformapneus.create');
    Route::get('{tiporeformapneus}', [TipoReformaPneuController::class, 'show'])->name('tiporeformapneus.show');


    Route::post('/', [TipoReformaPneuController::class, 'store'])->name('tiporeformapneus.store');
    Route::get('{tiporeformapneus}/editar', [TipoReformaPneuController::class, 'edit'])
        ->name('tiporeformapneus.edit');
    Route::get('/api/{tiporeformapneus}', [TipoReformaPneuController::class, 'getTipoReforma'])->name('tiporeformapneus.api');

    Route::put('{tiporeformapneus}', [TipoReformaPneuController::class, 'update'])->name('tiporeformapneus.update');

    Route::delete('{tiporeformapneus}', [TipoReformaPneuController::class, 'destroy'])
        ->name('tiporeformapneus.destroy');
});

// Rotas TipoManutencaoImobilizados
Route::group(['prefix' => 'tipomanutencaoimobilizados'], function () {
    Route::get('/', [TipoManutencaoImobilizadoController::class, 'index'])->name('tipomanutencaoimobilizados.index');
    Route::get('criar', [TipoManutencaoImobilizadoController::class, 'create'])
        ->name('tipomanutencaoimobilizados.create');
    Route::get('{tipomanutencaoimobilizado}', [TipoManutencaoImobilizadoController::class, 'show'])
        ->name('tipomanutencaoimobilizados.show');

    Route::post('/', [TipoManutencaoImobilizadoController::class, 'store'])->name('tipomanutencaoimobilizados.store');
    Route::get('{tipomanutencaoimobilizado}/editar', [TipoManutencaoImobilizadoController::class, 'edit'])
        ->name('tipomanutencaoimobilizados.edit');
    Route::put('{tipomanutencaoimobilizado}', [TipoManutencaoImobilizadoController::class, 'update'])
        ->name('tipomanutencaoimobilizados.update');

    Route::delete('{tipomanutencaoimobilizado}', [TipoManutencaoImobilizadoController::class, 'destroy'])
        ->name('tipomanutencaoimobilizados.destroy');
});

// Rotas de Tipo Veiculo
Route::group(['prefix' => 'tipoveiculos'], function () {
    Route::get('/', [TipoVeiculoController::class, 'index'])->name('tipoveiculos.index');
    Route::get('criar', [TipoVeiculoController::class, 'create'])->name('tipoveiculos.create');
    Route::get('{tipoveiculo}', [TipoVeiculoController::class, 'show'])->name('tipoveiculos.show');

    Route::post('/', [TipoVeiculoController::class, 'store'])->name('tipoveiculos.store');
    Route::get('{tipoveiculo}/editar', [TipoVeiculoController::class, 'edit'])
        ->name('tipoveiculos.edit');
    Route::put('{tipoveiculo}', [TipoVeiculoController::class, 'update'])
        ->name('tipoveiculos.update');

    Route::delete('{tipoveiculo}', [TipoVeiculoController::class, 'destroy'])
        ->name('tipoveiculos.destroy');
});

// Rotas de Tipo Motivo de Sinistros
Route::group(['prefix' => 'tipomotivosinistros'], function () {
    Route::get('/', [TipoMotivoSinistroController::class, 'index'])->name('tipomotivosinistros.index');
    Route::get('criar', [TipoMotivoSinistroController::class, 'create'])->name('tipomotivosinistros.create');
    Route::get('{tipomotivosinistro}', [TipoMotivoSinistroController::class, 'show'])->name('tipomotivosinistros.show');

    Route::post('/', [TipoMotivoSinistroController::class, 'store'])->name('tipomotivosinistros.store');
    Route::get('{tipomotivosinistros}/editar', [TipoMotivoSinistroController::class, 'edit'])
        ->name('tipomotivosinistros.edit');
    Route::put('{tipomotivosinistros}', [TipoMotivoSinistroController::class, 'update'])
        ->name('tipomotivosinistros.update');

    Route::delete('{tipomotivosinistros}', [TipoMotivoSinistroController::class, 'destroy'])
        ->name('tipomotivosinistros.destroy');
});

// Rotas de Tipo Orgão Sinistros
Route::group(['prefix' => 'tipoorgaosinistros'], function () {
    Route::get('/', [TipoOrgaoSinistroController::class, 'index'])->name('tipoorgaosinistros.index');
    Route::get('criar', [TipoOrgaoSinistroController::class, 'create'])->name('tipoorgaosinistros.create');
    Route::get('{tipomotivosinistro}', [TipoOrgaoSinistroController::class, 'show'])->name('tipoorgaosinistros.show');
});



// Rotas de Tipo Unidade de Produtos
Route::group(['prefix' => 'unidadeprodutos'], function () {
    Route::get('/', [UnidadeProdutoController::class, 'index'])->name('unidadeprodutos.index');
    Route::get('criar', [UnidadeProdutoController::class, 'create'])->name('unidadeprodutos.create');
    Route::get('{unidadeprodutos}', [UnidadeProdutoController::class, 'show'])->name('unidadeprodutos.show');

    Route::post('/', [UnidadeProdutoController::class, 'store'])->name('unidadeprodutos.store');
    Route::get('{unidadeprodutos}/editar', [UnidadeProdutoController::class, 'edit'])
        ->name('unidadeprodutos.edit');
    Route::put('{unidadeprodutos}', [UnidadeProdutoController::class, 'update'])
        ->name('unidadeprodutos.update');

    Route::delete('{unidadeprodutos}', [UnidadeProdutoController::class, 'destroy'])
        ->name('unidadeprodutos.destroy');
});
