<?php

namespace App\Providers;

use App\Modules\Compras\Models\Contrato;
use App\Modules\Compras\Models\Fornecedor;
use App\Models\NotaFiscal;
use App\Modules\Compras\Models\Orcamento;
use App\Modules\Compras\Models\PedidoCompra;
use App\Modules\Compras\Models\SolicitacaoCompra;
use App\Policies\ContratoPolicy;
use App\Policies\FornecedorPolicy;
use App\Policies\NotaFiscalPolicy;
use App\Policies\OrcamentoPolicy;
use App\Policies\PedidoCompraPolicy;
use App\Policies\SolicitacaoCompraPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        SolicitacaoCompra::class => SolicitacaoCompraPolicy::class,
        PedidoCompra::class => PedidoCompraPolicy::class,
        Orcamento::class => OrcamentoPolicy::class,
        Fornecedor::class => FornecedorPolicy::class,
        Contrato::class => ContratoPolicy::class,
        NotaFiscal::class => NotaFiscalPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Registrar as políticas definidas no array $policies
        $this->registerPolicies();

        // Definir Gates adicionais para ações específicas do módulo de compras

        // Gate para acesso ao Dashboard de Compras
        Gate::define('acessar-dashboard-compras', function ($user) {
            return $user->can('visualizar_relatorios_compras');
        });

        // Gate para enviar pedidos de compra
        Gate::define('enviar-pedido-compra', function ($user, PedidoCompra $pedido) {
            return $user->can('enviar_pedido_compra') &&
                ($user->hasRole('Comprador') || $user->hasRole('Administrador do Módulo Compras'));
        });

        // Gate para selecionar orçamento vencedor
        Gate::define('selecionar-orcamento', function ($user, Orcamento $orcamento) {
            return $user->can('aprovar_orcamento') &&
                ($user->hasRole('Comprador') || $user->hasRole('Administrador do Módulo Compras'));
        });
    }
}
