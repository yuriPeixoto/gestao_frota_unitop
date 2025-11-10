<?php

namespace App\Policies;

use App\Models\Orcamento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrcamentoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('visualizar_orcamento');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Orcamento $orcamento): bool
    {
        // Pode ver se tem permissão geral de visualização
        if ($user->can('visualizar_orcamento')) {
            return true;
        }

        // Comprador que registrou o orçamento também pode ver
        if ($orcamento->id_comprador && $orcamento->id_comprador === $user->id) {
            return true;
        }

        // Aprovadores de pedido podem ver orçamentos relacionados
        if ($user->can('aprovar_pedido_compra') && $orcamento->id_pedido) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('criar_orcamento');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Orcamento $orcamento): bool
    {
        // Orcamentos já selecionados não podem ser editados
        if ($orcamento->selecionado) {
            return false;
        }

        // Administrador do módulo pode editar qualquer orçamento não selecionado
        if ($user->can('editar_orcamento') && $user->hasRole('Administrador do Módulo Compras')) {
            return true;
        }

        // Comprador que registrou pode editar orçamentos não selecionados
        return $user->can('editar_orcamento') &&
            $orcamento->id_comprador &&
            $orcamento->id_comprador === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Orcamento $orcamento): bool
    {
        // Orçamentos selecionados não podem ser excluídos
        if ($orcamento->selecionado) {
            return false;
        }

        // Administrador do módulo pode excluir orçamentos não selecionados
        if ($user->can('excluir_orcamento') && $user->hasRole('Administrador do Módulo Compras')) {
            return true;
        }

        // Comprador que registrou pode excluir seus orçamentos não selecionados
        return $user->can('excluir_orcamento') &&
            $orcamento->id_comprador &&
            $orcamento->id_comprador === $user->id;
    }

    /**
     * Determine whether the user can select this quotation as winner.
     */
    public function select(User $user, Orcamento $orcamento): bool
    {
        // Orçamento já selecionado não pode ser selecionado novamente
        if ($orcamento->selecionado) {
            return false;
        }

        // Verificar se tem permissão para aprovar orçamentos
        if (!$user->can('aprovar_orcamento')) {
            return false;
        }

        // Verificar se é comprador ou administrador
        return $user->hasRole('Comprador') || $user->hasRole('Administrador do Módulo Compras');
    }

    /**
     * Determine whether the user can reject this quotation.
     */
    public function reject(User $user, Orcamento $orcamento): bool
    {
        // Orçamento já selecionado não pode ser rejeitado
        if ($orcamento->selecionado) {
            return false;
        }

        // Verificar se tem permissão para rejeitar orçamentos
        if (!$user->can('rejeitar_orcamento')) {
            return false;
        }

        // Verificar se é comprador ou administrador
        return $user->hasRole('Comprador') || $user->hasRole('Administrador do Módulo Compras');
    }

    /**
     * Determine whether the user can see quotation comparison.
     */
    public function compareQuotations(User $user, int $pedidoId): bool
    {
        // Verificar se tem permissão para visualizar orçamentos
        if (!$user->can('visualizar_orcamento')) {
            return false;
        }

        // Buscar o pedido associado
        $pedido = \App\Models\PedidoCompra::find($pedidoId);
        if (!$pedido) {
            return false;
        }

        // Comprador do pedido pode ver comparativos
        if ($pedido->id_comprador === $user->id) {
            return true;
        }

        // Aprovador pode ver comparativos
        if ($user->can('aprovar_pedido_compra')) {
            return true;
        }

        // Administrador do módulo também pode ver
        return $user->hasRole('Administrador do Módulo Compras');
    }
}
