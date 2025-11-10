<?php

namespace App\Policies;

use App\Models\NotaFiscal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotaFiscalPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('visualizar_nota_fiscal');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NotaFiscal $notaFiscal): bool
    {
        // Pode ver se tem permissão geral
        if ($user->can('visualizar_nota_fiscal')) {
            return true;
        }

        // Comprador do pedido relacionado pode ver a nota fiscal
        if ($notaFiscal->id_pedido) {
            $pedido = \App\Models\PedidoCompra::find($notaFiscal->id_pedido);
            if ($pedido && $pedido->id_comprador === $user->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('criar_nota_fiscal');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, NotaFiscal $notaFiscal): bool
    {
        // Notas fiscais aprovadas ou pagas não podem ser editadas
        if (in_array($notaFiscal->status, ['aprovada', 'paga'])) {
            return false;
        }

        // Administrador do módulo pode editar
        if ($user->can('editar_nota_fiscal') && $user->hasRole('Administrador do Módulo Compras')) {
            return true;
        }

        // Almoxarife pode editar notas fiscais registradas ou conferidas
        if (
            $user->can('editar_nota_fiscal') && $user->hasRole('Almoxarife') &&
            in_array($notaFiscal->status, ['registrada', 'conferida'])
        ) {
            return true;
        }

        // Comprador pode editar notas fiscais registradas que estejam relacionadas aos seus pedidos
        if ($user->can('editar_nota_fiscal') && $notaFiscal->status === 'registrada' && $notaFiscal->id_pedido) {
            $pedido = \App\Models\PedidoCompra::find($notaFiscal->id_pedido);
            return $pedido && $pedido->id_comprador === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, NotaFiscal $notaFiscal): bool
    {
        // Somente notas registradas podem ser excluídas
        if ($notaFiscal->status !== 'registrada') {
            return false;
        }

        return $user->can('excluir_nota_fiscal') && $user->hasRole('Administrador do Módulo Compras');
    }

    /**
     * Determine whether the user can approve the invoice.
     */
    public function approve(User $user, NotaFiscal $notaFiscal): bool
    {
        // Só pode aprovar notas fiscais conferidas
        if ($notaFiscal->status !== 'conferida') {
            return false;
        }

        return $user->can('aprovar_nota_fiscal') &&
            ($user->hasRole('Almoxarife') || $user->hasRole('Administrador do Módulo Compras'));
    }

    /**
     * Determine whether the user can receive (check) products from the invoice.
     */
    public function receive(User $user, NotaFiscal $notaFiscal): bool
    {
        // Só pode conferir notas fiscais registradas
        if ($notaFiscal->status !== 'registrada') {
            return false;
        }

        return $user->can('aprovar_nota_fiscal') &&
            ($user->hasRole('Almoxarife') || $user->hasRole('Administrador do Módulo Compras'));
    }

    /**
     * Determine whether the user can mark the invoice as paid.
     */
    public function markAsPaid(User $user, NotaFiscal $notaFiscal): bool
    {
        // Só pode marcar como paga notas fiscais aprovadas
        if ($notaFiscal->status !== 'aprovada') {
            return false;
        }

        // Isso geralmente é feito pelo financeiro, mas no nosso caso vamos permitir para administradores
        return $user->hasRole('Administrador do Módulo Compras');
    }

    /**
     * Determine whether the user can cancel the invoice.
     */
    public function cancel(User $user, NotaFiscal $notaFiscal): bool
    {
        // Notas pagas não podem ser canceladas
        if ($notaFiscal->status === 'paga') {
            return false;
        }

        // Administrador pode cancelar
        if ($user->can('excluir_nota_fiscal') && $user->hasRole('Administrador do Módulo Compras')) {
            return true;
        }

        // Almoxarife pode cancelar notas registradas ou conferidas
        if (
            $user->can('excluir_nota_fiscal') && $user->hasRole('Almoxarife') &&
            in_array($notaFiscal->status, ['registrada', 'conferida'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can attach files to the invoice.
     */
    public function attachFile(User $user, NotaFiscal $notaFiscal): bool
    {
        // Notas pagas não podem ter anexos adicionados
        if ($notaFiscal->status === 'paga') {
            return false;
        }

        return $user->can('editar_nota_fiscal');
    }

    /**
     * Determine whether the user can associate this invoice with a purchase order.
     */
    public function associateWithPedido(User $user, NotaFiscal $notaFiscal): bool
    {
        // Só pode associar notas registradas que ainda não tenham pedido associado
        if ($notaFiscal->status !== 'registrada' || $notaFiscal->id_pedido) {
            return false;
        }

        return $user->can('editar_nota_fiscal') &&
            ($user->hasRole('Comprador') || $user->hasRole('Almoxarife') || $user->hasRole('Administrador do Módulo Compras'));
    }
}
