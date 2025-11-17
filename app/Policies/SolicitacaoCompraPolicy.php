<?php

namespace App\Policies;

use App\Modules\Compras\Models\SolicitacaoCompra;
use App\Modules\Configuracoes\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolicitacaoCompraPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $hasPermission = $user->hasPermission('visualizar_solicitacao_compra');

        Log::info('SolicitacaoCompraPolicy::viewAny', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'has_permission' => $hasPermission ? 'SIM' : 'NÃO',
            'is_superuser' => $user->is_superuser ? 'SIM' : 'NÃO',
            'result' => $hasPermission ? 'PERMITIDO' : 'NEGADO'
        ]);

        return $hasPermission;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SolicitacaoCompra $solicitacaoCompra): bool
    {
        // Pode ver se tem permissão geral de visualização
        if ($user->hasPermission('visualizar_solicitacao_compra')) {
            return true;
        }

        // Solicitante pode ver apenas suas próprias solicitações
        return $user->hasPermission('criar_solicitacao_compra') &&
            $solicitacaoCompra->id_solicitante === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $hasPermission = $user->hasPermission('criar_solicitacao_compra');

        Log::info('SolicitacaoCompraPolicy::create', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'has_permission' => $hasPermission ? 'SIM' : 'NÃO',
            'is_superuser' => $user->is_superuser ? 'SIM' : 'NÃO',
            'result' => $hasPermission ? 'PERMITIDO' : 'NEGADO'
        ]);

        return $hasPermission;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        $hasPermission = $user->hasPermission('editar_solicitacao_compra');

        return $hasPermission;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SolicitacaoCompra $solicitacaoCompra): bool
    {
        // Apenas solicitações não aprovadas podem ser excluídas
        $deletableStatus = ['nova', 'em_analise'];

        // Admin pode excluir qualquer solicitação
        if (
            $user->hasPermission('excluir_solicitacao_compra') &&
            $user->hasRole('Administrador do Módulo Compras')
        ) {
            return true;
        }

        // Solicitante só pode excluir próprias solicitações não aprovadas
        return $user->hasPermission('excluir_solicitacao_compra') &&
            $solicitacaoCompra->id_solicitante === $user->id &&
            in_array($solicitacaoCompra->situacao_compra, $deletableStatus);
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, SolicitacaoCompra $solicitacaoCompra): bool
    {

        // Verificar se a solicitação já está aprovada
        if ($solicitacaoCompra->aprovado_reprovado === 'S') {
            return false;
        }

        // Gestor de frota pode aprovar solicitações de frota
        if ($user->hasRole('Gestor de Frota')) {
            // Lógica para verificar se a solicitação é relacionada à frota
            $isFrotaRelated = $this->isSolicitacaoRelatedToFrota($solicitacaoCompra);

            if ($isFrotaRelated && $user->hasPermission('aprovar_solicitacao_compra')) {
                return true;
            }
        }

        // Aprovador de Solicitação
        if ($user->hasRole('Aprovador de Solicitação') && $user->hasPermission('aprovar_solicitacao_compra')) {
            // Verificar se é responsável pelo departamento da solicitação
            // Esta lógica pode variar conforme estrutura do sistema
            return $user->hasPermission('aprovar_solicitacao_compra');
        }

        // Administrador do módulo pode aprovar qualquer solicitação
        return $user->hasRole('Administrador do Módulo Compras') &&
            $user->hasPermission('aprovar_solicitacao_compra');
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, SolicitacaoCompra $solicitacaoCompra): bool
    {
        // A lógica é similar à aprovação
        if ($solicitacaoCompra->aprovado_reprovado === 'N') {
            return false;
        }

        // Gestor de frota
        if ($user->hasRole('Gestor de Frota')) {
            $isFrotaRelated = $this->isSolicitacaoRelatedToFrota($solicitacaoCompra);

            if ($isFrotaRelated && $user->hasPermission('rejeitar_solicitacao_compra')) {
                return true;
            }
        }

        // Aprovador de Solicitação
        if ($user->hasRole('Aprovador de Solicitação') && $user->hasPermission('rejeitar_solicitacao_compra')) {
            return $user->hasPermission('rejeitar_solicitacao_compra');
        }

        // Administrador do módulo
        return $user->hasRole('Administrador do Módulo Compras') &&
            $user->hasPermission('rejeitar_solicitacao_compra');
    }

    /**
     * Determine whether the user can assume the model.
     */
    public function assumir(User $user, SolicitacaoCompra $solicitacaoCompra): bool
    {
        // Administradores podem assumir qualquer solicitação
        if ($user->hasRole('Administrador do Módulo Compras')) {
            return true;
        }

        // Compradores podem assumir solicitações aguardando início
        if ($user->hasRole('Comprador') && $solicitacaoCompra->podeSerAssumida()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can send for approval.
     */
    public function enviarAprovacao(User $user, SolicitacaoCompra $solicitacaoCompra): bool
    {
        // Apenas o solicitante pode enviar para aprovação
        return $user->id === $solicitacaoCompra->id_solicitante &&
            $solicitacaoCompra->podeSerEnviadaParaAprovacao();
    }

    /**
     * Determine whether the user can validate the solicitation.
     */
    public function validar(User $user, SolicitacaoCompra $solicitacaoCompra): bool
    {
        // Apenas o solicitante pode validar cotações
        return $user->id === $solicitacaoCompra->id_solicitante &&
            $solicitacaoCompra->situacao_compra === SolicitacaoCompra::STATUS_AGUARDANDO_VALIDACAO_SOLICITANTE;
    }

    /**
     * Determine whether the user can finalize the solicitation.
     */
    public function finalizar(User $user, SolicitacaoCompra $solicitacaoCompra): bool
    {
        // Administradores podem finalizar qualquer solicitação
        if ($user->hasRole('Administrador do Módulo Compras')) {
            return true;
        }

        // Compradores podem finalizar solicitações que assumiram
        if (
            $user->hasRole('Comprador') &&
            $user->id === $solicitacaoCompra->id_comprador &&
            $solicitacaoCompra->podeGerarPedido()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Verifica se a solicitação está relacionada à frota
     */
    private function isSolicitacaoRelatedToFrota(SolicitacaoCompra $solicitacaoCompra): bool
    {
        // Verificar se o departamento está relacionado à frota
        if ($solicitacaoCompra->departamento) {
            if (stripos($solicitacaoCompra->departamento->descricao_departamento, 'frota') !== false) {
                return true;
            }
        }

        // Verificar se os itens estão relacionados à frota
        foreach ($solicitacaoCompra->itens as $item) {
            // Verificar produto
            if ($item->produto && $item->produto->grupo) {
                if (
                    stripos($item->produto->grupo->descricao, 'veículo') !== false ||
                    stripos($item->produto->grupo->descricao, 'frota') !== false
                ) {
                    return true;
                }
            }

            // Verificar serviço
            if ($item->servico && $item->servico->grupo) {
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
