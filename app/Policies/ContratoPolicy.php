<?php

namespace App\Policies;

use App\Models\Contrato;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContratoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('visualizar_contrato');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Contrato $contrato): bool
    {
        // Contrato inativo só pode ser visto por administradores e compradores
        if (!$contrato->ativo) {
            return $user->can('visualizar_contrato') &&
                ($user->hasRole('Comprador') || $user->hasRole('Administrador do Módulo Compras'));
        }

        // Contrato ativo pode ser visto por qualquer pessoa com permissão
        return $user->can('visualizar_contrato');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('criar_contrato');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Contrato $contrato): bool
    {
        // Não permitir atualização de contratos encerrados (data_fim no passado)
        if ($contrato->data_fim && $contrato->data_fim < now()) {
            return false;
        }

        return $user->can('editar_contrato');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Contrato $contrato): bool
    {
        // Verificar se o contrato tem pedidos relacionados
        if ($this->hasRelatedPedidos($contrato)) {
            return false;
        }

        return $user->can('excluir_contrato');
    }

    /**
     * Determine whether the user can renew the contract.
     */
    public function renew(User $user, Contrato $contrato): bool
    {
        // Só pode renovar contratos próximos do vencimento ou recém vencidos (até 30 dias)
        $hoje = now();
        $limite = $hoje->copy()->addDays(30);

        if ($contrato->data_fim < $hoje->subDays(30) || $contrato->data_fim > $limite) {
            return false;
        }

        return $user->can('editar_contrato') &&
            ($user->hasRole('Comprador') || $user->hasRole('Administrador do Módulo Compras'));
    }

    /**
     * Determine whether the user can terminate the contract.
     */
    public function terminate(User $user, Contrato $contrato): bool
    {
        // Só pode encerrar contratos ativos que ainda não venceram
        if (!$contrato->ativo || ($contrato->data_fim && $contrato->data_fim < now())) {
            return false;
        }

        return $user->can('editar_contrato') &&
            $user->hasRole('Administrador do Módulo Compras');
    }

    /**
     * Determine whether the user can add items to the contract.
     */
    public function addItems(User $user, Contrato $contrato): bool
    {
        // Só pode adicionar itens a contratos ativos que não venceram
        if (!$contrato->ativo || ($contrato->data_fim && $contrato->data_fim < now())) {
            return false;
        }

        return $user->can('editar_contrato');
    }

    /**
     * Determine whether the user can remove items from the contract.
     */
    public function removeItems(User $user, Contrato $contrato): bool
    {
        // Só pode remover itens de contratos ativos que não venceram
        if (!$contrato->ativo || ($contrato->data_fim && $contrato->data_fim < now())) {
            return false;
        }

        // Verificar se algum item está sendo usado em pedidos
        if ($this->hasItemsInUse($contrato)) {
            return false;
        }

        return $user->can('editar_contrato');
    }

    /**
     * Verifica se o contrato possui pedidos relacionados
     */
    private function hasRelatedPedidos(Contrato $contrato): bool
    {
        // Lógica para verificar se há pedidos relacionados ao contrato
        // Isso depende de como é o relacionamento entre contrato e pedido no sistema
        return \App\Models\PedidoCompra::where('id_contrato', $contrato->id)->exists();
    }

    /**
     * Verifica se algum item do contrato está sendo usado em pedidos
     */
    private function hasItemsInUse(Contrato $contrato): bool
    {
        // Buscar itens do contrato
        $itensContrato = \App\Models\ItemContrato::where('id_contrato', $contrato->id)->get();

        foreach ($itensContrato as $item) {
            // Verificar se o item está sendo usado em algum pedido
            // Isso depende da estrutura do seu sistema
            $itemEmUso = \App\Models\ItemPedidoCompra::where('id_item_contrato', $item->id)->exists();

            if ($itemEmUso) {
                return true;
            }
        }

        return false;
    }
}
