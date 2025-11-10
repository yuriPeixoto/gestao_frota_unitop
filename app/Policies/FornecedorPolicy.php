<?php

namespace App\Policies;

use App\Models\Fornecedor;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FornecedorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('visualizar_fornecedor');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Fornecedor $fornecedor): bool
    {
        // Fornecedor inativo só pode ser visto por administradores e compradores
        if (!$fornecedor->ativo) {
            return $user->can('visualizar_fornecedor') &&
                ($user->hasRole('Comprador') || $user->hasRole('Administrador do Módulo Compras'));
        }

        // Fornecedor ativo pode ser visto por qualquer pessoa com permissão
        return $user->can('visualizar_fornecedor');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('criar_fornecedor');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Fornecedor $fornecedor): bool
    {
        return $user->can('editar_fornecedor');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Fornecedor $fornecedor): bool
    {
        // Fornecedor não pode ser excluído se tiver relacionamentos
        if ($this->hasRelatedRecords($fornecedor)) {
            return false;
        }

        return $user->can('excluir_fornecedor');
    }

    /**
     * Determine whether the user can deactivate/activate the supplier.
     */
    public function toggleActive(User $user, Fornecedor $fornecedor): bool
    {
        return $user->can('editar_fornecedor');
    }

    /**
     * Verifica se o fornecedor possui registros relacionados que impedem exclusão
     */
    private function hasRelatedRecords(Fornecedor $fornecedor): bool
    {
        // Verificar pedidos de compra associados
        $hasPedidos = \App\Models\PedidoCompra::where('id_fornecedor', $fornecedor->id)->exists();
        if ($hasPedidos) {
            return true;
        }

        // Verificar contratos associados
        $hasContratos = \App\Models\Contrato::where('id_fornecedor', $fornecedor->id)->exists();
        if ($hasContratos) {
            return true;
        }

        // Verificar orçamentos associados
        $hasOrcamentos = \App\Models\Orcamento::where('id_fornecedor', $fornecedor->id)->exists();
        if ($hasOrcamentos) {
            return true;
        }

        // Verificar notas fiscais associadas
        $hasNotas = \App\Models\NotaFiscal::where('id_fornecedor', $fornecedor->id)->exists();
        if ($hasNotas) {
            return true;
        }

        // Se não encontrou nenhum relacionamento, pode excluir
        return false;
    }
}
