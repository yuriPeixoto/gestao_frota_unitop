<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\PermissionDiscoveryService;
use Illuminate\Http\Request;

class PermissionDiscoveryController extends Controller
{
    protected $discoveryService;

    public function __construct(PermissionDiscoveryService $discoveryService)
    {
        $this->discoveryService = $discoveryService;
    }

    public function index()
    {
        // Obtém permissões descobertas (já filtradas)
        $discoveredPermissions = $this->discoveryService->discoverPermissions();

        // Agrupa as novas permissões por grupo para melhor visualização
        $groupedNewPermissions = [];
        foreach ($discoveredPermissions as $slug => $permission) {
            $groupKey = $permission['group_key'] ?? 'others';
            $groupedNewPermissions[$groupKey][] = $permission;
        }

        return view('admin.permissoes.discover', [
            'groupedNewPermissions' => $groupedNewPermissions,
            'hasNewPermissions'     => !empty($discoveredPermissions)
        ]);
    }

    public function sync(Request $request)
    {
        $results = $this->discoveryService->syncPermissions();

        $message = 'Sincronização concluída. ';
        if (!empty($results['created'])) {
            $message .= count($results['created']) . ' novas permissões foram adicionadas. ';
        } else {
            $message .= 'Nenhuma nova permissão foi necessária. ';
        }

        if (!empty($results['errors'])) {
            $message .= count($results['errors']) . ' erros encontrados.';
        }

        return redirect()
            ->route('admin.permissoes.index')
            ->with('success', [
                'title' => 'Permissões Sincronizadas',
                'message' => $message
            ]);
    }

    public function updateGroupPermissions(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:user,role',
                'id' => 'required|integer',
                'groupId' => 'required|integer',
                'granted' => 'required|boolean',
                'branchId' => 'nullable|integer'
            ]);

            $model = $validated['type'] === 'user'
                ? User::findOrFail($validated['id'])
                : Role::findOrFail($validated['id']);

            $permissions = Permission::where('permission_group_id', $validated['groupId'])->get();

            if ($validated['granted']) {
                // Adiciona todas as permissões do grupo
                foreach ($permissions as $permission) {
                    $model->permissions()->syncWithoutDetaching([
                        $permission->id => [
                            'branch_id' => $validated['branchId'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    ]);
                }
            } else {
                // Remove todas as permissões do grupo
                $model->permissions()
                    ->wherePivot('branch_id', $validated['branchId'])
                    ->whereIn('permissions.id', $permissions->pluck('id'))
                    ->detach();
            }

            return response()->json([
                'success' => true,
                'message' => 'Permissões do grupo atualizadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erro ao atualizar permissões do grupo: ' . $e->getMessage()
            ], 500);
        }
    }
}
