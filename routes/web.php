<?php

use App\Http\Controllers\Admin\ArquivoController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartamentoController;
use App\Http\Controllers\Admin\EmpresaController;
use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\GrupoServicoController;
use App\Http\Controllers\Admin\ModeloVeiculoController;
use App\Http\Controllers\Admin\MunicipioController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SinistroController;
use App\Http\Controllers\Admin\SubgrupoServicoController;
use App\Http\Controllers\Admin\TelefoneController;
use App\Http\Controllers\Admin\UnidadeProdutoController;
use App\Http\Controllers\Auth\FilialController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('admin/api/departamentos/single/{id}', [App\Http\Controllers\Admin\API\DepartamentoApiController::class, 'getSingle']);
Route::get('admin/api/filiais/single/{id}', [App\Http\Controllers\Admin\API\FilialApiController::class, 'getSingle']);

Route::get('/api/user/filiais-by-email', [FilialController::class, 'getFilialsByEmail'])
    ->name('api.user.filiais-by-email');
Route::get('/api/user/filiais-by-matricula', [FilialController::class, 'getFilialsByMatricula']);

Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('admin.dashboard');

Route::get('/dashboard/current-time', [DashboardController::class, 'getCurrentTime'])
    ->middleware(['auth'])
    ->name('dashboard.current-time');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rotas da Autenticação em 2 fatores
    Route::post('/profile/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
        ->name('two-factor.enable');

    Route::post('/profile/two-factor-authentication/confirm', [TwoFactorAuthenticationController::class, 'confirm'])
        ->name('two-factor.confirm');

    Route::delete(
        '/profile/two-factor-authentication',
        [TwoFactorAuthenticationController::class, 'destroy']
    )->name('two-factor.disable');

    Route::post(
        '/profile/two-factor-recovery-codes',
        [TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes']
    )->name('two-factor.recovery-codes');
    Route::post(
        '/profile/two-factor-recovery-codes',
        [TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes']
    )->name('two-factor.recovery-codes');

    Route::post(
        '/profile/two-factor-recovery-codes/downloadCodes',
        [TwoFactorAuthenticationController::class, 'downloadCodes']
    )->name('two-factor.download-codes');
    Route::post(
        '/profile/two-factor-recovery-codes/downloadCodes',
        [TwoFactorAuthenticationController::class, 'downloadCodes']
    )->name('two-factor.download-codes');

    Route::post('two-factor/disable', [TwoFactorAuthenticationController::class, 'disable'])
        ->name('two-factor.disable');
});

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => ['auth', '2fa', 'auto.permission'],
], function () {
    // TODO checar se essas rotas pertencem à categoria 'configuracoes'

    // Rota para Dashboard de Multas
    Route::get('/dashboard-multas', [App\Http\Controllers\Admin\DashboardMultasController::class, 'index'])
        ->name('dashboard-multas.index');

    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

    // Rotas de Departamento
    Route::group(['prefix' => 'departamentos'], function () {
        Route::get('/', [DepartamentoController::class, 'index'])->name('departamentos.index');
        Route::get('criar', [DepartamentoController::class, 'create'])->name('departamentos.create');
        Route::get('{departamento}', [DepartamentoController::class, 'show'])->name('departamentos.show');

        Route::post('/', [DepartamentoController::class, 'store'])->name('departamentos.store');
        Route::get('{departamento}/editar', [DepartamentoController::class, 'edit'])
            ->name('departamentos.edit');
        Route::put('{departamento}', [DepartamentoController::class, 'update'])
            ->name('departamentos.update');

        Route::delete('{departamento}', [DepartamentoController::class, 'destroy'])
            ->name('departamentos.destroy');
    });

    // Rotas Empresa
    Route::group(['prefix' => 'empresas'], function () {
        Route::get('/', [EmpresaController::class, 'index'])->name('empresas.index');
        Route::get('criar', [EmpresaController::class, 'create'])->name('empresas.create');
        Route::get('{empresa}', [EmpresaController::class, 'show'])->name('empresas.show');

        Route::post('/', [EmpresaController::class, 'store'])->name('empresas.store');
        Route::get('{empresa}/editar', [EmpresaController::class, 'edit'])
            ->name('empresas.edit');
        Route::put('{empresa}', [EmpresaController::class, 'update'])
            ->name('empresas.update');

        Route::delete('{empresa}', [EmpresaController::class, 'destroy'])
            ->name('empresas.destroy');
    });

    Route::prefix('api')->group(function () {


        // Municipio
        Route::get('/municipio/search', [MunicipioController::class, 'search'])->name('api.municipio.search');
        Route::get('/municipio/single/{id}', [MunicipioController::class, 'getById'])->name('api.municipio.single');
    });

    // Rotas de Fornecedor
    Route::group(['prefix' => 'fornecedores'], function () {
        Route::get('/', [FornecedorController::class, 'index'])->name('fornecedores.index');
        Route::get('criar/{fornecedorId?}', [FornecedorController::class, 'create'])
            ->name('fornecedores.create');
        Route::get('search', [FornecedorController::class, 'search'])->name('fornecedores.search');
        Route::get('fornecedores/single/{id}', [FornecedorController::class, 'single'])->name('fornecedores.single');

        Route::post('/', [FornecedorController::class, 'store'])->name('fornecedores.store');
        Route::post('/getCNPJ', [FornecedorController::class, 'getFornecedores'])->name('fornecedores.getCNPJ');
        Route::get('{fornecedores}/editar', [FornecedorController::class, 'edit'])->name('fornecedores.edit');
        Route::put('{fornecedores}', [FornecedorController::class, 'update'])->name('fornecedores.update');

        Route::delete('{fornecedores}', [FornecedorController::class, 'destroy'])->name('fornecedores.destroy');
    });

    // Rotas de Unidade de Grupos de servicos
    Route::group(['prefix' => 'gruposervicos'], function () {
        Route::get('/', [GrupoServicoController::class, 'index'])->name('gruposervicos.index');
        Route::get('criar', [GrupoServicoController::class, 'create'])->name('gruposervicos.create');
        Route::get('{grupo}', [GrupoServicoController::class, 'show'])->name('gruposervicos.show');

        Route::post('/', [GrupoServicoController::class, 'store'])->name('gruposervicos.store');
        Route::get('{grupo}/editar', [GrupoServicoController::class, 'edit'])
            ->name('gruposervicos.edit');
        Route::put('{grupo}', [GrupoServicoController::class, 'update'])
            ->name('gruposervicos.update');

        Route::delete('{grupo}', [GrupoServicoController::class, 'destroy'])
            ->name('gruposervicos.destroy');
    });

    // Rotas de Modelo Veiculos
    Route::group(['prefix' => 'modeloveiculos'], function () {
        Route::get('/', [ModeloVeiculoController::class, 'index'])->name('modeloveiculos.index');
        Route::get('criar', [ModeloVeiculoController::class, 'create'])->name('modeloveiculos.create');
        Route::get('{modeloveiculo}', [ModeloVeiculoController::class, 'show'])->name('modeloveiculos.show');

        Route::post('/', [ModeloVeiculoController::class, 'store'])->name('modeloveiculos.store');
        Route::get('{modeloveiculo}/editar', [ModeloVeiculoController::class, 'edit'])->name('modeloveiculos.edit');
        Route::put('{modeloveiculo}', [ModeloVeiculoController::class, 'update'])->name('modeloveiculos.update');

        Route::delete('{modeloveiculo}', [ModeloVeiculoController::class, 'destroy'])
            ->name('modeloveiculos.destroy');
    });

    // TODO ver se essa rota ainda será necessária
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Rotas de Unidade de Subgrupo de servicos
    Route::group(['prefix' => 'subgruposervicos'], function () {
        Route::get('/', [SubgrupoServicoController::class, 'index'])->name('subgruposervicos.index');
        Route::get('criar', [SubgrupoServicoController::class, 'create'])->name('subgruposervicos.create');
        Route::get('{subgruposervico}', [SubgrupoServicoController::class, 'show'])->name('subgruposervicos.show');

        Route::post('/', [SubgrupoServicoController::class, 'store'])->name('subgruposervicos.store');
        Route::get('{subgruposervico}/editar', [SubgrupoServicoController::class, 'edit'])
            ->name('subgruposervicos.edit');
        Route::put('{subgruposervico}', [SubgrupoServicoController::class, 'update'])
            ->name('subgruposervicos.update');

        Route::delete('{subgruposervico}', [SubgrupoServicoController::class, 'destroy'])
            ->name('subgruposervicos.destroy');
    });

    // Rotas Empresa
    Route::group(['prefix' => 'empresas'], function () {
        Route::get('/', [EmpresaController::class, 'index'])->name('empresas.index');
        Route::get('criar', [EmpresaController::class, 'create'])->name('empresas.create');
        Route::get('{empresa}', [EmpresaController::class, 'show'])->name('empresas.show');

        Route::post('/', [EmpresaController::class, 'store'])->name('empresas.store');
        Route::get('{empresa}/editar', [EmpresaController::class, 'edit'])
            ->name('empresas.edit');
        Route::put('{empresa}', [EmpresaController::class, 'update'])
            ->name('empresas.update');

        Route::delete('{empresa}', [EmpresaController::class, 'destroy'])
            ->name('empresas.destroy');
    });

    // TODO checar se esta rota está correta, está duplicando a de cima

    // Rotas de Subcategoria de veiculos
    Route::group(['prefix' => 'empresas'], function () {
        Route::get('/', [EmpresaController::class, 'index'])->name('empresas.index');
        Route::get('criar', [EmpresaController::class, 'create'])->name('empresas.create');
        Route::get('{empresa}', [EmpresaController::class, 'show'])->name('empresas.show');

        Route::post('/', [EmpresaController::class, 'store'])->name('empresas.store');
        Route::get('{empresa}/editar', [EmpresaController::class, 'edit'])
            ->name('empresas.edit');
        Route::put('{empresa}', [EmpresaController::class, 'update'])
            ->name('empresas.update');

        Route::delete('{empresa}', [EmpresaController::class, 'destroy'])
            ->name('empresas.destroy');
    });

    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Rotas de Unidade de Produtos
    // Route::group(['prefix' => 'unidadeprodutos'], function () {
    //     Route::get('/', [UnidadeProdutoController::class, 'index'])->name('unidadeprodutos.index');
    //     Route::get('criar', [UnidadeProdutoController::class, 'create'])->name('unidadeprodutos.create');
    //     Route::get('{unidadeproduto}', [UnidadeProdutoController::class, 'show'])->name('unidadeprodutos.show');

    //     Route::post('/', [SinistroController::class, 'store'])->name('sinistros.store');
    //     Route::get('{sinistro}/editar', [SinistroController::class, 'edit'])->name('sinistros.edit');
    //     Route::put('{sinistro}', [SinistroController::class, 'update'])->name('sinistros.update');
    //     Route::post('/admin/sinistros/store-historico', [SinistroController::class, 'storeHistorico'])
    //         ->name('admin.sinistros.store-historico');

    //     Route::delete('{sinitro}', [SinistroController::class, 'destroy'])
    //         ->name('sinistros.destroy');
    // });

    // Rotas de Arquivos
    Route::get('/arquivo/{path}', [ArquivoController::class, 'show'])->where('path', '.*')->name('arquivo.show');


    // Rotas para Contratos-Modelo
    Route::get('contratosmodelo/{id}', [FornecedorController::class, 'getContratoModelo']);
    Route::post('contratosmodelo', [FornecedorController::class, 'storeContratoModelo']);
    Route::put('contratosmodelo/{id}', [FornecedorController::class, 'storeContratoModelo']);
    Route::delete('contratosmodelo/{id}', [FornecedorController::class, 'destroyContratoModelo']);
    Route::post('contratosmodelo/{id}/clonar', [FornecedorController::class, 'cloneContratoModelo']);

    // Rotas para Serviços
    Route::get('grupos/{id}/servicos', [FornecedorController::class, 'getServicos']);
    Route::get('servicos/{id}', [FornecedorController::class, 'getServicoFornecedor']);
    Route::post('servicos', [FornecedorController::class, 'storeServicoFornecedor']);
    Route::put('servicos/{id}', [FornecedorController::class, 'storeServicoFornecedor']);
    Route::delete('servicos/{id}', [FornecedorController::class, 'destroyServicoFornecedor']);

    // Rotas para Peças
    Route::get('grupos-pecas/{id}/produtos', [FornecedorController::class, 'getProdutos']);
    Route::get('pecas/{id}', [FornecedorController::class, 'getPecaFornecedor']);
    Route::post('pecas', [FornecedorController::class, 'storePecaFornecedor']);
    Route::put('pecas/{id}', [FornecedorController::class, 'storePecaFornecedor']);
    Route::delete('pecas/{id}', [FornecedorController::class, 'destroyPecaFornecedor']);

    // Listar todos os telefones de um fornecedor
    Route::get('/telefones', [TelefoneController::class, 'index']);

    // Obter um telefone específico
    Route::get('/telefones/{id}', [TelefoneController::class, 'show']);

    // Adicionar um novo telefone
    Route::post('/telefones', [TelefoneController::class, 'store']);

    // Atualizar um telefone existente
    Route::put('/telefones/{id}', [TelefoneController::class, 'update']);

    // Excluir um telefone
    Route::delete('/telefones/{id}', [TelefoneController::class, 'destroy']);

    // Rotas para Contratos-Modelo
    Route::get('contratosmodelo/{id}', [FornecedorController::class, 'getContratoModelo']);
    Route::post('contratosmodelo', [FornecedorController::class, 'storeContratoModelo']);
    Route::put('contratosmodelo/{id}', [FornecedorController::class, 'storeContratoModelo']);
    Route::delete('contratosmodelo/{id}', [FornecedorController::class, 'destroyContratoModelo']);
    Route::post('contratosmodelo/{id}/clonar', [FornecedorController::class, 'cloneContratoModelo']);

    // Rotas para Serviços
    Route::get('grupos/{id}/servicos', [FornecedorController::class, 'getServicos']);
    Route::get('servicos/{id}', [FornecedorController::class, 'getServicoFornecedor']);
    Route::post('servicos', [FornecedorController::class, 'storeServicoFornecedor']);
    Route::put('servicos/{id}', [FornecedorController::class, 'storeServicoFornecedor']);
    Route::delete('servicos/{id}', [FornecedorController::class, 'destroyServicoFornecedor']);

    // Rotas para Peças
    Route::get('grupos-pecas/{id}/produtos', [FornecedorController::class, 'getProdutos']);
    Route::get('pecas/{id}', [FornecedorController::class, 'getPecaFornecedor']);
    Route::post('pecas', [FornecedorController::class, 'storePecaFornecedor']);
    Route::put('pecas/{id}', [FornecedorController::class, 'storePecaFornecedor']);
    Route::delete('pecas/{id}', [FornecedorController::class, 'destroyPecaFornecedor']);

    // Rotas para telefones temporários (devem estar ANTES do grupo de API)
    Route::get('/telefones/temp', [TelefoneController::class, 'getTempTelefones']);
    Route::post('/telefones/temp', [TelefoneController::class, 'storeTempTelefones']);

    // Rotas de API para telefones
    Route::prefix('api')->group(function () {
        // Listar todos os telefones de um fornecedor
        Route::get('/telefones', [TelefoneController::class, 'index']);

        // Obter um telefone específico
        Route::get('/telefones/{id}', [TelefoneController::class, 'show']);

        // Adicionar um novo telefone
        Route::post('/telefones', [TelefoneController::class, 'store']);

        // Atualizar um telefone existente
        Route::put('/telefones/{id}', [TelefoneController::class, 'update']);

        // Excluir um telefone
        Route::delete('/telefones/{id}', [TelefoneController::class, 'destroy']);
    });

    // Módulo de Abastecimentos (estrutura modular)
    require __DIR__ . '/modules/abastecimentos.php';
    require __DIR__ . '/atrelamentoveiculos.php';
    // Módulo de Certificados e Vencimentário (estrutura modular)
    require __DIR__ . '/modules/certificados.php';
    require __DIR__ . '/compras.php';
    require __DIR__ . '/configuracoes.php';
    require __DIR__ . '/console.php';
    require __DIR__ . '/controlemanutancaofrota.php';
    // require __DIR__ . '/descartepneus.php';
    // require __DIR__ . '/devolucaosaidaestoque.php';
    require __DIR__ . '/estoque.php';
    require __DIR__ . '/ipvaveiculo.php';
    require __DIR__ . '/lancIpvalicenciamentoseguro.php';
    require __DIR__ . '/licenciamentoveiculo.php';
    require __DIR__ . '/listagemoslacamentoservico.php';
    require __DIR__ . '/listagemoslacamentoservicorateio.php';
    require __DIR__ . '/manutencao.php';
    require __DIR__ . '/manutencaocategoria.php';
    // Relatórios de Abastecimentos movidos para o módulo modular
    require __DIR__ . '/manutencaopreordemservicofinalizada.php';
    require __DIR__ . '/manutencaoservico.php';
    require __DIR__ . '/manutencaoservicos.php';
    require __DIR__ . '/multas.php';
    // Módulo de Pessoal (estrutura modular)
    require __DIR__ . '/modules/pessoal.php';
    // Rotas de Contratos de Fornecedores (temporariamente no pessoal.php original, mover para módulo de Compras)
    require __DIR__ . '/pessoal.php';
    require __DIR__ . '/pneus.php';
    require __DIR__ . '/relacaodespesasveiculos.php';
    require __DIR__ . '/manutencaoservicos.php';
    require __DIR__ . '/listagemoslacamentoservico.php';
    require __DIR__ . '/listagemoslacamentoservicorateio.php';
    require __DIR__ . '/statusordemservico.php';
    require __DIR__ . '/seguroobrigatorio.php';
    require __DIR__ . '/servicofornecedor.php';
    // Módulo de Sinistros (estrutura modular)
    require __DIR__ . '/modules/sinistros.php';
    require __DIR__ . '/veiculos.php';
    require __DIR__ . '/anexos.php';
    // Módulo de Imobilizados (estrutura modular)
    require __DIR__ . '/modules/imobilizados.php';
    require __DIR__ . '/relatoriosmanutencao.php';
    require __DIR__ . '/relatorios.php';
    require __DIR__ . '/relatoriospneus.php';
    require __DIR__ . '/relatoriopessoal.php';
    require __DIR__ . '/relatorioveiculo.php';
    require __DIR__ . '/relatoriosestoque.php';
    require __DIR__ . '/relatoriocompras.php';
    // Relatórios de Sinistros movidos para o módulo modular
    require __DIR__ . '/relatoriogerenciais.php';
    require __DIR__ . '/trocafiliais.php';
    require __DIR__ . '/relatorio_premiacao.php';
});

require __DIR__ . '/auth.php';
// Módulo de Checklist (estrutura modular - API Bridge + React Dashboard)
require __DIR__ . '/modules/checklist.php';
require __DIR__ . '/notifications.php';
require __DIR__ . '/tickets.php';

Route::get('admin/ajax-get-veiculo-dados', [App\Http\Controllers\Admin\AbastecimentoController::class, 'ajaxGetVeiculoDados'])
    ->middleware(['auth', '2fa']);
