<?php

namespace App\Policies;

use App\Models\RelacaoSolicitacaoPeca;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RelacaoSolicitacaoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('visualizar_requisicao_material') || $user->can('ver_requisicaomaterial');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RelacaoSolicitacaoPeca $relacaoSolicitacaoPeca): bool
    {
        // Pode ver se tem permissão geral de visualização
        if ($user->can('visualizar_requisicao_material')) {
            return true;
        }

        // Usuário que criou a requisição pode visualizar
        if ($relacaoSolicitacaoPeca->id_usuario_abertura === $user->id) {
            return true;
        }

        // Aprovador pode visualizar
        if ($user->can('aprovar_requisicao_material')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('criar_requisicao_material');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RelacaoSolicitacaoPeca $relacaoSolicitacaoPeca): bool
    {
        // Só pode editar se tem permissão
        if (!$user->can('editar_requisicao_material')) {
            return false;
        }

        // Usuário que criou a requisição pode editar se ainda não foi aprovada
        if (
            $relacaoSolicitacaoPeca->id_usuario_abertura === $user->id &&
            $relacaoSolicitacaoPeca->podeSerEnviadaParaAprovacao()
        ) {
            return true;
        }

        // Aprovadores podem editar
        if ($user->can('aprovar_requisicao_material')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RelacaoSolicitacaoPeca $relacaoSolicitacaoPeca): bool
    {
        // Só pode excluir se tem permissão
        if (!$user->can('excluir_requisicao_material')) {
            return false;
        }

        // Só pode excluir requisições que ainda não foram aprovadas
        if (!$relacaoSolicitacaoPeca->podeSerEnviadaParaAprovacao()) {
            return false;
        }

        // Usuário que criou pode excluir
        if ($relacaoSolicitacaoPeca->id_usuario_abertura === $user->id) {
            return true;
        }

        // Aprovadores podem excluir
        if ($user->can('aprovar_requisicao_material')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, RelacaoSolicitacaoPeca $relacaoSolicitacaoPeca): bool
    {
        // Verificar se tem permissão para aprovar
        if (!$user->can('aprovar_requisicao_material')) {
            return false;
        }

        // Verificar se a requisição pode ser aprovada
        if (!$relacaoSolicitacaoPeca->podeSerEnviadaParaAprovacao()) {
            return false;
        }

        // Verificar se é aprovador de requisição
        if ($user->hasRole('Aprovador de Requisição')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, RelacaoSolicitacaoPeca $relacaoSolicitacaoPeca): bool
    {
        // Verificar se tem permissão para rejeitar
        if (!$user->can('rejeitar_requisicao_material')) {
            return false;
        }

        // Verificar se a requisição pode ser rejeitada (mesma lógica da aprovação)
        if (!$relacaoSolicitacaoPeca->podeSerEnviadaParaAprovacao()) {
            return false;
        }

        // Verificar se é aprovador de requisição
        if ($user->hasRole('Aprovador de Requisição')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, RelacaoSolicitacaoPeca $relacaoSolicitacaoPeca): bool
    {
        // Verificar se tem permissão para cancelar
        if (!$user->can('cancelar_requisicao_material')) {
            return false;
        }

        // Usuário que criou pode cancelar
        if ($relacaoSolicitacaoPeca->id_usuario_abertura === $user->id) {
            return true;
        }

        // Aprovadores podem cancelar
        if ($user->can('aprovar_requisicao_material')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can finalize the model.
     */
    public function finalize(User $user, RelacaoSolicitacaoPeca $relacaoSolicitacaoPeca): bool
    {
        // Verificar se tem permissão para finalizar
        if (!$user->can('finalizar_requisicao_material')) {
            return false;
        }

        // Geralmente quem pode aprovar também pode finalizar
        if ($user->can('aprovar_requisicao_material')) {
            return true;
        }

        return false;
    }
}
