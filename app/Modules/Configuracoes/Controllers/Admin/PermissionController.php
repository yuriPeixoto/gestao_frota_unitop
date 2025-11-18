<?php

namespace App\Modules\Configuracoes\Controllers\Admin;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Permission;
use App\Modules\Pessoal\Models\TipoPessoal;
use App\Models\User;
use App\Models\VFilial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    public function index()
    {
        // Verificação de permissão
        // if (!auth()->user()->can('manage-permissions')) {
        //     abort(403, 'Você não tem permissão para acessar esta página.');
        // }

        $users = User::orderBy('name')->get();
        $cargos = TipoPessoal::orderBy('descricao_tipo')->get();

        // Agrupar permissões pelo nome do modelo/recurso em vez da ação
        $permissions = Permission::all()->groupBy(function ($permission) {
            return $permission->getGroupAttribute();
        });

        $departments = Departamento::orderBy('descricao_departamento')->get();
        $filiais = VFilial::orderBy('name')->get();

        return view('admin.permissoes.index', compact('users', 'cargos', 'permissions', 'departments', 'filiais'));
    }

    public function gerenciar()
    {
        // Interface amigável para gerenciamento de permissões
        $users = User::orderBy('name')->get();
        $cargos = TipoPessoal::orderBy('descricao_tipo')->get();

        return view('admin.permissoes.gerenciar', compact('users', 'cargos'));
    }

    public function assign(Request $request)
    {
        // LOG: Dados recebidos do formulário
        Log::info('=== INÍCIO ATRIBUIÇÃO DE PERMISSÕES ===', [
            'user_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        Log::info('Dados do Request', [
            'type' => $request->type,
            'target_id' => $request->target_id,
            'permissions_exists' => $request->has('permissions'),
            'permissions_count' => is_array($request->permissions) ? count($request->permissions) : 0,
            'permissions_first_10' => is_array($request->permissions) ? array_slice($request->permissions, 0, 10) : [],
            'all_request_data' => $request->except(['_token']), // Ver todos os dados exceto token
        ]);

        // Verificação de permissão
        // if (!auth()->user()->can('manage-permissions')) {
        //     abort(403, 'Você não tem permissão para atribuir permissões.');
        // }

        // CORREÇÃO: Permitir array vazio e tornar permissions opcional
        $request->validate([
            'type' => 'required|in:user,role,group,department,branch',
            'target_id' => 'required',
            'permissions' => 'array', // Removido 'required' para permitir array vazio
        ]);

        // CORREÇÃO: Garantir que permissions seja array (vazio se não existir)
        $permissions = $request->permissions ?? [];

        Log::info('Permissões após processamento', [
            'permissions_count' => count($permissions),
            'is_empty' => empty($permissions),
            'sample_permissions' => array_slice($permissions, 0, 20),
        ]);

        $targetId = null;
        $targetName = '';

        try {
            switch ($request->type) {
                case 'user':
                    $user = User::findOrFail($request->target_id);
                    $targetId = $user->id;
                    $targetName = $user->name;

                    Log::info('Antes do syncPermissions - User', [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'permissions_to_sync' => count($permissions),
                        'current_permissions_count' => $user->getDirectPermissions()->count(),
                    ]);

                    // Sincronizar permissões (agora funciona com array vazio)
                    $user->syncPermissions($permissions);

                    Log::info('Depois do syncPermissions - User', [
                        'user_id' => $user->id,
                        'new_permissions_count' => $user->fresh()->getDirectPermissions()->count(),
                        'permissions_synced' => count($permissions),
                    ]);

                    // Limpar cache específico do usuário
                    $this->clearUserSpecificCache($user->id, 'user', $targetName);
                    break;

                case 'role':
                    $cargo = TipoPessoal::findOrFail($request->target_id);
                    $targetId = $cargo->id_tipo_pessoal;
                    $targetName = $cargo->descricao_tipo;

                    $cargo->syncPermissions($permissions);

                    // Limpar cache de todos os usuários com esse cargo
                    $this->clearRoleRelatedCache($cargo, 'role', $targetName);
                    break;

                case 'group':
                    // Grupos (Roles do Spatie)
                    $group = \Spatie\Permission\Models\Role::findOrFail($request->target_id);
                    $targetId = $group->id;
                    $targetName = $group->name;

                    $group->syncPermissions($permissions);

                    // Limpar cache de todos os usuários com essa role
                    $this->clearGroupRelatedCache($group, 'group', $targetName);
                    break;

                case 'department':
                    $department = Departamento::findOrFail($request->target_id);
                    $targetId = $department->id_departamento;
                    $targetName = $department->descricao_departamento;

                    $department->syncPermissions($permissions);

                    // Limpar cache de todos os usuários do departamento
                    $this->clearDepartmentRelatedCache($department, 'department', $targetName);
                    break;

                case 'branch':
                    // Modificado para usar VFilial em vez de Branch
                    $filial = VFilial::findOrFail($request->target_id);
                    $targetId = $filial->id;
                    $targetName = $filial->name;

                    $filial->syncPermissions($permissions);

                    // Limpar cache de todos os usuários da filial
                    $this->clearBranchRelatedCache($filial, 'branch', $targetName);
                    break;
            }

            // LOG FINAL: Verificar o que realmente foi salvo no banco
            $finalCheck = null;
            switch ($request->type) {
                case 'user':
                    $finalCheck = User::find($targetId)->getDirectPermissions();
                    break;
                case 'role':
                    $finalCheck = TipoPessoal::find($targetId)->permissions;
                    break;
                case 'group':
                    $finalCheck = \Spatie\Permission\Models\Role::find($targetId)->permissions;
                    break;
                case 'department':
                    $finalCheck = Departamento::find($targetId)->permissions;
                    break;
                case 'branch':
                    $finalCheck = VFilial::find($targetId)->permissions;
                    break;
            }

            Log::info('Verificação final do banco de dados', [
                'type' => $request->type,
                'target_id' => $targetId,
                'permissions_saved_in_db' => $finalCheck ? $finalCheck->count() : 0,
                'permissions_sent_from_form' => count($permissions),
                'difference' => $finalCheck ? ($finalCheck->count() - count($permissions)) : 'N/A',
                'first_20_saved' => $finalCheck ? $finalCheck->pluck('name')->take(20)->toArray() : [],
            ]);

            Log::info('Permissões atualizadas com sucesso', [
                'type' => $request->type,
                'target_id' => $targetId,
                'target_name' => $targetName,
                'permissions_count' => count($permissions), // Agora conta correto (pode ser 0)
                'permissions_removed' => empty($permissions), // Log se foram removidas todas
                'user_id' => auth()->id(),
            ]);

            Log::info('=== FIM ATRIBUIÇÃO DE PERMISSÕES ===');

            // Mensagem mais clara sobre o que aconteceu
            $message = empty($permissions)
                ? 'Todas as permissões foram removidas com sucesso e o cache foi limpo automaticamente.'
                : 'As permissões foram atualizadas com sucesso e o cache foi limpo automaticamente.';

            return redirect()->back()->with('notification', [
                'type' => 'success',
                'title' => 'Permissões Atualizadas',
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar permissões', [
                'type' => $request->type,
                'target_id' => $request->target_id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('notification', [
                'type' => 'error',
                'title' => 'Erro ao Atualizar Permissões',
                'message' => 'Ocorreu um erro ao atualizar as permissões. Tente novamente.',
            ]);
        }
    }

    public function getTargets($type)
    {
        // Verificação de permissão
        // if (!auth()->user()->can('manage-permissions')) {
        //     abort(403, 'Você não tem permissão para obter alvos de permissões.');
        // }

        $data = [];

        switch ($type) {
            case 'user':
                $data = User::select('id', 'name')
                    ->where('is_ativo', true)
                    ->orderBy('name')
                    ->get();
                break;
            case 'role':
                // Corrigido para evitar o erro "Undefined array key id_tipo_pessoal"
                $cargos = TipoPessoal::orderBy('descricao_tipo')->get();
                $data = $cargos->map(function ($cargo) {
                    return [
                        'id' => $cargo->id_tipo_pessoal,
                        'name' => $cargo->descricao_tipo,
                    ];
                });
                break;
            case 'group':
                // Grupos (Roles do Spatie)
                $data = \Spatie\Permission\Models\Role::select('id', 'name')
                    ->orderBy('name')
                    ->get();
                break;
            case 'department':
                $departments = Departamento::orderBy('descricao_departamento')->get();
                $data = $departments->map(function ($dept) {
                    return [
                        'id' => $dept->id_departamento,
                        'name' => $dept->descricao_departamento,
                    ];
                });
                break;
            case 'branch':
                $data = VFilial::select('id', 'name')->orderBy('name')->get();
                break;
            default:
                Log::error("Tipo inválido: {$type}");

                return response()->json([], 404);
        }

        return response()->json($data);
    }

    public function getPermissions($type, $id)
    {
        $permissions = [];

        switch ($type) {
            case 'user':
                $user = User::findOrFail($id);
                $permissions = $user->getDirectPermissions()->pluck('name');
                break;
            case 'role':
                $role = TipoPessoal::findOrFail($id);
                $permissions = $role->permissions->pluck('name');
                break;
            case 'group':
                // Grupos (Roles do Spatie)
                $group = \Spatie\Permission\Models\Role::findOrFail($id);
                $permissions = $group->permissions->pluck('name');
                break;
            case 'department':
                $department = Departamento::findOrFail($id);
                $permissions = $department->permissions->pluck('name');
                break;
            case 'branch':
                $branch = VFilial::findOrFail($id);
                $permissions = $branch->permissions->pluck('name');
                break;
        }

        return response()->json([
            'permissions' => $permissions,
        ]);
    }

    /**
     * Interface para clonar permissões de um usuário para outros
     */
    public function cloneInterface()
    {
        $users = User::where('is_ativo', true)->orderBy('name')->get();

        return view('admin.permissoes.clone', compact('users'));
    }

    /**
     * Clonar permissões de um usuário para outros
     */
    public function clonePermissions(Request $request)
    {
        $request->validate([
            'source_user_id' => 'required|exists:users,id',
            'target_user_ids' => 'required|array|min:1',
            'target_user_ids.*' => 'exists:users,id',
        ]);

        try {
            $sourceUser = User::findOrFail($request->source_user_id);
            $targetUserIds = $request->target_user_ids;

            // Obter permissões diretas do usuário de origem
            $sourcePermissions = $sourceUser->getDirectPermissions()->pluck('name')->toArray();

            $clonedCount = 0;
            $errors = [];

            foreach ($targetUserIds as $targetUserId) {
                try {
                    $targetUser = User::findOrFail($targetUserId);

                    // Clonar permissões diretas
                    $targetUser->syncPermissions($sourcePermissions);

                    // Limpar cache do usuário alvo
                    PermissionHelper::clearUserPermissionsCache($targetUser->id);

                    $clonedCount++;

                    Log::info('Permissões clonadas com sucesso', [
                        'source_user' => $sourceUser->name,
                        'target_user' => $targetUser->name,
                        'permissions_count' => count($sourcePermissions),
                        'performed_by' => auth()->id(),
                    ]);
                } catch (\Exception $e) {
                    $errors[] = "Erro ao clonar para usuário ID {$targetUserId}: " . $e->getMessage();
                    Log::error('Erro ao clonar permissões', [
                        'source_user_id' => $request->source_user_id,
                        'target_user_id' => $targetUserId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Limpar cache geral do Spatie
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            if ($clonedCount > 0) {
                $message = "Permissões clonadas com sucesso para {$clonedCount} usuário(s).";
                if (! empty($errors)) {
                    $message .= ' Alguns erros ocorreram: ' . implode('; ', $errors);
                }

                return redirect()->back()->with('notification', [
                    'type' => 'success',
                    'title' => 'Clonagem Concluída',
                    'message' => $message,
                ]);
            } else {
                return redirect()->back()->with('notification', [
                    'type' => 'error',
                    'title' => 'Erro na Clonagem',
                    'message' => 'Não foi possível clonar as permissões. ' . implode('; ', $errors),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro geral ao clonar permissões', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('notification', [
                'type' => 'error',
                'title' => 'Erro ao Clonar',
                'message' => 'Ocorreu um erro ao processar a clonagem de permissões.',
            ]);
        }
    }

    /**
     * Limpa cache específico do usuário
     */
    private function clearUserSpecificCache(int $userId, string $type, string $targetName): void
    {
        try {
            // Limpar cache específico do usuário via PermissionHelper
            PermissionHelper::clearUserPermissionsCache($userId);

            // Limpar cache do Spatie Permission
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache específico do usuário', [
                'type' => $type,
                'user_id' => $userId,
                'target_name' => $targetName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Limpa cache relacionado a um cargo específico
     */
    private function clearRoleRelatedCache(TipoPessoal $cargo, string $type, string $targetName): void
    {
        try {
            // Buscar todos os usuários com esse cargo e limpar seus caches
            $users = User::where('cargo_id', $cargo->id_tipo_pessoal)->get();

            foreach ($users as $user) {
                PermissionHelper::clearUserPermissionsCache($user->id);
            }

            // Limpar cache do Spatie Permission
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache relacionado ao cargo', [
                'type' => $type,
                'cargo_id' => $cargo->id_tipo_pessoal,
                'target_name' => $targetName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Limpa cache relacionado a um departamento específico
     */
    private function clearDepartmentRelatedCache(Departamento $department, string $type, string $targetName): void
    {
        try {
            // Buscar todos os usuários do departamento e limpar seus caches
            $users = User::where('departamento_id', $department->id_departamento)->get();

            foreach ($users as $user) {
                PermissionHelper::clearUserPermissionsCache($user->id);
            }

            // Limpar cache do Spatie Permission
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache relacionado ao departamento', [
                'type' => $type,
                'department_id' => $department->id_departamento,
                'target_name' => $targetName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Limpa cache relacionado a um grupo (role do Spatie) específico
     */
    private function clearGroupRelatedCache(\Spatie\Permission\Models\Role $group, string $type, string $targetName): void
    {
        try {
            // Buscar todos os usuários com essa role e limpar seus caches
            $users = User::role($group->name)->get();

            foreach ($users as $user) {
                PermissionHelper::clearUserPermissionsCache($user->id);
            }

            // Limpar cache do Spatie Permission
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache relacionado ao grupo', [
                'type' => $type,
                'group_id' => $group->id,
                'target_name' => $targetName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Limpa cache relacionado a uma filial específica
     */
    private function clearBranchRelatedCache(VFilial $filial, string $type, string $targetName): void
    {
        try {
            // Buscar todos os usuários da filial e limpar seus caches
            $users = User::where('filial_id', $filial->id)->get();

            foreach ($users as $user) {
                PermissionHelper::clearUserPermissionsCache($user->id);
            }

            // Limpar cache do Spatie Permission
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache relacionado à filial', [
                'type' => $type,
                'filial_id' => $filial->id,
                'target_name' => $targetName,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
