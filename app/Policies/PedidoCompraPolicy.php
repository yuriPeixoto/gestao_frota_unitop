<?php

namespace App\Policies;

use App\Modules\Compras\Models\PedidoCompra;
use App\Modules\Configuracoes\Models\User;
use Illuminate\Auth\Access\Response;

class PedidoCompraPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('visualizar_pedido_compra');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PedidoCompra $pedidoCompra): bool
    {
        // Pode ver se tem permissão geral de visualização
        if ($user->can('visualizar_pedido_compra')) {
            return true;
        }

        // Solicitante pode ver pedidos originados de suas solicitações
        if ($user->can('criar_solicitacao_compra') && $pedidoCompra->id_solicitacao) {
            $solicitacao = \App\Models\SolicitacaoCompra::find($pedidoCompra->id_solicitacao);
            if ($solicitacao && $solicitacao->id_solicitante === $user->id) {
                return true;
            }
        }

        // Comprador que criou o pedido também pode ver
        return $pedidoCompra->id_comprador === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('criar_pedido_compra');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PedidoCompra $pedidoCompra): bool
    {
        // Apenas pedidos não aprovados/enviados podem ser editados
        $editableStatus = ['rascunho', 'aguardando_aprovacao'];

        // Administrador do módulo pode editar
        if ($user->can('editar_pedido_compra') && $user->hasRole('Administrador do Módulo Compras')) {
            return true;
        }

        // Comprador só pode editar seus próprios pedidos não aprovados/enviados
        return $user->can('editar_pedido_compra') &&
            $pedidoCompra->id_comprador === $user->id &&
            in_array($pedidoCompra->status, $editableStatus);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PedidoCompra $pedidoCompra): bool
    {
        // Apenas pedidos em rascunho podem ser excluídos
        $deletableStatus = ['rascunho'];

        // Admin pode excluir pedidos em rascunho
        if ($user->can('excluir_pedido_compra') && $user->hasRole('Administrador do Módulo Compras')) {
            return in_array($pedidoCompra->status, $deletableStatus);
        }

        // Comprador só pode excluir próprios pedidos em rascunho
        return $user->can('excluir_pedido_compra') &&
            $pedidoCompra->id_comprador === $user->id &&
            in_array($pedidoCompra->status, $deletableStatus);
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, PedidoCompra $pedidoCompra): bool
    {
        // Verificar se o pedido já está aprovado
        if ($pedidoCompra->status === 'aprovado' || $pedidoCompra->status === 'enviado') {
            return false;
        }

        // Gestor de frota pode aprovar pedidos de frota
        if ($user->hasRole('Gestor de Frota')) {
            // Lógica para verificar se o pedido é relacionado à frota
            $isFrotaRelated = $this->isPedidoRelatedToFrota($pedidoCompra);

            if ($isFrotaRelated && $user->can('aprovar_pedido_compra')) {
                return true;
            }
        }

        // Verificar alçada baseada no valor do pedido
        if ($pedidoCompra->valor_total > 100000 && $user->can('aprovar_pedido_compra_nivel_4')) {
            return true;
        }

        if ($pedidoCompra->valor_total > 25000 && $pedidoCompra->valor_total <= 100000 && $user->can('aprovar_pedido_compra_nivel_3')) {
            return true;
        }

        if ($pedidoCompra->valor_total > 5000 && $pedidoCompra->valor_total <= 25000 && $user->can('aprovar_pedido_compra_nivel_2')) {
            return true;
        }

        if ($pedidoCompra->valor_total <= 5000 && $user->can('aprovar_pedido_compra_nivel_1')) {
            return true;
        }

        // Administrador do módulo pode aprovar qualquer pedido
        return $user->hasRole('Administrador do Módulo Compras') &&
            $user->can('aprovar_pedido_compra');
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, PedidoCompra $pedidoCompra): bool
    {
        // A lógica é similar à aprovação
        if ($pedidoCompra->status !== 'aguardando_aprovacao') {
            return false;
        }

        // Verificar se o usuário pode aprovar (usa a mesma lógica)
        return $this->approve($user, $pedidoCompra);
    }

    /**
     * Determine whether the user can send the model to the supplier.
     */
    public function send(User $user, PedidoCompra $pedidoCompra): bool
    {
        // Só pode enviar pedidos aprovados
        if ($pedidoCompra->status !== 'aprovado') {
            return false;
        }

        // Apenas compradores e administradores podem enviar pedidos
        return $user->can('enviar_pedido_compra') &&
            ($user->hasRole('Comprador') || $user->hasRole('Administrador do Módulo Compras'));
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, PedidoCompra $pedidoCompra): bool
    {
        // Só pode cancelar pedidos já enviados mas não finalizados
        $cancelableStatus = ['enviado', 'parcial'];

        if (!in_array($pedidoCompra->status, $cancelableStatus)) {
            return false;
        }

        // Apenas compradores e administradores podem cancelar pedidos
        return $user->can('cancelar_pedido_compra') &&
            ($user->hasRole('Comprador') || $user->hasRole('Administrador do Módulo Compras'));
    }

    /**
     * Verifica se o pedido está relacionado à frota
     */
    private function isPedidoRelatedToFrota(PedidoCompra $pedidoCompra): bool
    {
        // Verificar se há uma solicitação vinculada
        if ($pedidoCompra->id_solicitacao) {
            $solicitacao = \App\Models\SolicitacaoCompra::find($pedidoCompra->id_solicitacao);
            if ($solicitacao && $solicitacao->id_departamento) {
                // Verificar se o departamento está relacionado à frota
                $departamento = \App\Models\Departamento::find($solicitacao->id_departamento);
                if ($departamento && stripos($departamento->descricao_departamento, 'frota') !== false) {
                    return true;
                }
            }
        }

        // Verificar itens do pedido relacionados à frota
        foreach ($pedidoCompra->itens as $item) {
            // Verificar se o item é relacionado à frota
            // Lógica depende da estrutura do seu sistema
            if ($item->produto && property_exists($item->produto, 'grupo') && $item->produto->grupo) {
                if (
                    stripos($item->produto->grupo->descricao, 'veículo') !== false ||
                    stripos($item->produto->grupo->descricao, 'frota') !== false
                ) {
                    return true;
                }
            }

            if ($item->servico && property_exists($item->servico, 'grupo') && $item->servico->grupo) {
                if (
                    stripos($item->servico->grupo->descricao, 'veículo') !== false ||
                    stripos($item->servico->grupo->descricao, 'frota') !== false
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
