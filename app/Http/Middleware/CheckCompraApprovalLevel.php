<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckCompraApprovalLevel
{
    /**
     * Middleware para verificar se o usuário tem a alçada necessária para aprovar um pedido de compra
     * com base no valor do pedido.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $modelType = 'pedido'): Response
    {
        // Verifica se o usuário está autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $pedidoId = $request->route('id') ?? $request->input('pedido_id');

        // Se não houver ID de pedido, seguir para a próxima middleware
        if (!$pedidoId) {
            return $next($request);
        }

        try {
            // Determina qual modelo buscar com base no tipo
            if ($modelType === 'pedido') {
                $pedido = \App\Models\PedidoCompra::findOrFail($pedidoId);
                $valor = $pedido->valor_total;
            } elseif ($modelType === 'solicitacao') {
                $solicitacao = \App\Models\SolicitacaoCompra::findOrFail($pedidoId);
                // Para solicitação, podemos ter que calcular o valor total
                $valor = $solicitacao->itens->sum(function ($item) {
                    return $item->quantidade * $item->valor_unitario;
                });
            } else {
                // Tipo de modelo não reconhecido
                abort(500, 'Tipo de modelo não suportado para verificação de alçada');
            }

            // Verifica a alçada com base no valor
            if (!$this->checkApprovalLevel($user, $valor, $pedidoId)) {
                // Redirecionar com mensagem de erro
                return redirect()->back()->with('error', 'Você não possui alçada para aprovar este pedido.');
            }

            return $next($request);
        } catch (\Exception $e) {
            // Em caso de erro, logar e retornar para a página anterior
            Log::error('Erro ao verificar alçada: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocorreu um erro ao verificar sua alçada de aprovação.');
        }
    }

    /**
     * Verifica se o usuário tem a alçada necessária para aprovar um valor específico
     *
     * @param \App\Modules\Configuracoes\Models\User $user
     * @param float $valor
     * @param int|null $pedidoId
     * @return bool
     */
    private function checkApprovalLevel($user, float $valor, ?int $pedidoId = null): bool
    {
        // Verificar permissões de alçada em ordem decrescente
        if ($valor > 100000 && $user->can('aprovar_pedido_compra_nivel_4')) {
            return true;
        }

        if ($valor > 25000 && $valor <= 100000 && $user->can('aprovar_pedido_compra_nivel_3')) {
            return true;
        }

        if ($valor > 5000 && $valor <= 25000 && $user->can('aprovar_pedido_compra_nivel_2')) {
            return true;
        }

        if ($valor <= 5000 && $user->can('aprovar_pedido_compra_nivel_1')) {
            return true;
        }

        // Se o usuário tem a permissão geral de aprovação (sem alçada específica)
        // Isso é útil para administradores ou casos especiais
        if ($user->can('aprovar_pedido_compra')) {
            return true;
        }

        // Se for gestor de frota e o pedido for relacionado à frota
        if ($this->isPedidoFrota($user, $pedidoId) && $user->can('aprovar_pedido_compra')) {
            return true;
        }

        return false;
    }

    /**
     * Verifica se o pedido está relacionado à frota e o usuário é um gestor de frota
     *
     * @param \App\Modules\Configuracoes\Models\User $user
     * @param int|null $pedidoId
     * @return bool
     */
    private function isPedidoFrota($user, ?int $pedidoId): bool
    {
        // Se o usuário não é um gestor de frota, retorna falso imediatamente
        if (!$user->hasRole('Gestor de Frota')) {
            return false;
        }

        // Se não há pedido, retorna falso
        if (!$pedidoId) {
            return false;
        }

        try {
            $pedido = \App\Models\PedidoCompra::find($pedidoId);
            if (!$pedido) {
                return false;
            }

            // Verifica se o pedido está relacionado à frota
            // Esta lógica pode variar dependendo de como os pedidos de frota são identificados
            // Exemplo: por departamento, por tipo de pedido, por grupo de produtos, etc.
            $isFrota = false;

            // Se houver uma solicitação vinculada, verificar se ela é da frota
            if ($pedido->id_solicitacao) {
                $solicitacao = \App\Models\SolicitacaoCompra::find($pedido->id_solicitacao);
                if ($solicitacao && $solicitacao->id_departamento) {
                    // Verificar se o departamento está relacionado à frota (exemplo)
                    // Aqui você deve ajustar conforme a estrutura do seu sistema
                    $departamento = \App\Models\Departamento::find($solicitacao->id_departamento);
                    if ($departamento && stripos($departamento->descricao_departamento, 'frota') !== false) {
                        $isFrota = true;
                    }
                }
            }

            // Verificar itens do pedido relacionados à frota
            // Exemplo: verificar se os produtos/serviços são de categorias relacionadas à frota
            if (!$isFrota && $pedido->itens) {
                foreach ($pedido->itens as $item) {
                    // Verificar se o item é relacionado à frota
                    // Isso depende da estrutura do seu sistema
                    // Por exemplo, verificar grupo do produto/serviço
                    if ($item->produto && $item->produto->grupo) {
                        if (
                            stripos($item->produto->grupo->descricao, 'veículo') !== false ||
                            stripos($item->produto->grupo->descricao, 'frota') !== false
                        ) {
                            $isFrota = true;
                            break;
                        }
                    }

                    if ($item->servico && $item->servico->grupo) {
                        if (
                            stripos($item->servico->grupo->descricao, 'veículo') !== false ||
                            stripos($item->servico->grupo->descricao, 'frota') !== false
                        ) {
                            $isFrota = true;
                            break;
                        }
                    }
                }
            }

            return $isFrota;
        } catch (\Exception $e) {
            Log::error('Erro ao verificar se pedido é de frota: ' . $e->getMessage());
            return false;
        }
    }
}
