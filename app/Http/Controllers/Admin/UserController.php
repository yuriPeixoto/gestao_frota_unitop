<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Departamento;
use App\Models\Role;
use App\Models\TipoPessoal;
use App\Models\User;
use App\Models\VFilial;
use App\Models\Telefone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Exibe a lista de usuários com opções de filtragem e ordenação
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $orderBy = $request->get('order_by');

        $orderDir = 'asc';

        if (empty($orderBy) || ! in_array($orderBy, ['name', 'email', 'created_at', 'last_login_at'])) {
            $orderBy = 'name';
        }
        if (Auth::user()->filial_id != 1) {
            $query = User::with(['filial', 'departamento', 'filiais'])
                ->where('filial_id', Auth::user()->filial_id)
                ->orderBy($orderBy, $orderDir);
        } else {
            $query = User::with(['filial', 'departamento', 'filiais'])
                ->orderBy($orderBy, $orderDir);
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                    ->orWhere('cpf', 'like', "%{$search}%")
                    ->orWhere('matricula', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filial_id')) {
            $filialId = $request->get('filial_id');
            $query->where(function ($q) use ($filialId) {
                $q->where('filial_id', $filialId)
                    ->orWhereHas('filiais', function ($query) use ($filialId) {
                        $query->where('filiais.id', $filialId);
                    });
            });
        }

        if ($request->filled('departamento_id')) {
            $query->where('departamento_id', $request->get('departamento_id'));
        }

        $users = $query->paginate(15)->withQueryString();
        $filiais = VFilial::orderBy('name')->get(['id as value', 'name as label']);
        $departamentos = Departamento::orderBy('descricao_departamento')
            ->get(['id_departamento as value', 'descricao_departamento as label']);

        return view('admin.usuarios.index', compact('users', 'filiais', 'departamentos'));
    }

    /**
     * Exibe o formulário para criar um novo usuário
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $filiais = VFilial::orderBy('name')->get(['id as value', 'name as label']);
        $departamentos = Departamento::orderBy('descricao_departamento')
            ->get(['id_departamento as value', 'descricao_departamento as label']);
        $cargos = TipoPessoal::orderBy('descricao_tipo')
            ->get(['id_tipo_pessoal as value', 'descricao_tipo as label']);

        $grupos = Role::orderBy('name')
            ->get(['id as value', 'name as label']);

        return view('admin.usuarios.create', compact('filiais', 'departamentos', 'cargos', 'grupos'));
    }

    /**
     * Armazena um novo usuário no banco de dados
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'filial_id' => 'nullable|exists:filiais,id',
            'filiais' => 'nullable|array',
            'filiais.*' => 'nullable|exists:filiais,id',
            'departamento_id' => 'nullable|exists:departamento,id_departamento',
            'pessoal_id' => 'nullable|exists:tipopessoal,id_tipo_pessoal',
            'cpf' => 'nullable|string|max:14|unique:users,cpf',
            'matricula' => 'nullable|integer|unique:users,matricula',
            'is_superuser' => 'boolean',
            'is_ativo' => 'boolean',
            'telefone_fixo' => 'nullable|string|max:30',
            'telefone_celular' => 'nullable|string|max:30',
        ]);

        $addressData = $request->validate([
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|size:2',
            'zip_code' => 'nullable|string|max:10',
        ]);

        DB::beginTransaction();

        try {
            $addressId = null;
            if (! empty(array_filter($addressData))) {
                $address = Address::create($addressData);
                $addressId = $address->id;
            }

            $userData['password'] = Hash::make($userData['password']);
            $userData['has_password_updated'] = true;
            $userData['address_id'] = $addressId;

            if (! Auth::user()->is_superuser) {
                $userData['is_superuser'] = false;
            }

            $filiais = $request->input('filiais', []);

            // Normalizar o array de filiais se necessário
            if (is_string($filiais)) {
                $filiais = json_decode($filiais, true);
            }

            $filiais = array_map(function ($item) {
                if (is_array($item)) {
                    return (int) ($item[0] ?? null);
                }

                return (int) $item;
            }, $filiais);

            $filiais = array_filter($filiais);

            $filialPrincipal = $this->determinarFilialPrincipal($filiais);

            if ($filialPrincipal !== null) {
                $userData['filial_id'] = $filialPrincipal;
            }

            $user = User::create($userData);

            // Criar telefones se informados
            $telFixo = $request->input('telefone_fixo');
            $telCel = $request->input('telefone_celular');
            if (!empty($telFixo) || !empty($telCel)) {
                Telefone::create([
                    'telefone_fixo' => $telFixo,
                    'telefone_celular' => $telCel,
                    'user_id' => $user->id,
                ]);
            }

            // Sincronizar roles
            if ($request->filled('roles')) {
                $roleIds = $this->normalizeRoles($request->input('roles'));

                // Converter para inteiros para garantir busca por ID
                $roleIds = array_map('intval', $roleIds);

                // Limpar permissões diretas antes de sincronizar roles (precaução)
                $user->permissions()->detach();

                Log::info('Usuário criado - roles sincronizadas', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'roles' => $roleIds,
                    'action' => 'role_sync_create'
                ]);

                // Sincronizar usando IDs como inteiros
                $user->syncRoles($roleIds);
            }

            // Refresh do modelo para garantir que o ID esteja disponível
            $user->refresh();

            if (! empty($filiais)) {
                $user->filiais()->sync($filiais);
            } elseif (! empty($userData['filial_id'])) {
                $user->filiais()->sync([$userData['filial_id']]);
            }

            DB::commit();

            return redirect()
                ->route('admin.usuarios.index')
                ->with('notification', [
                    'title' => 'Usuário criado',
                    'type' => 'success',
                    'message' => 'Usuário criado com sucesso!',
                    'duration' => 3000,
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar usuário: ' . $e->getMessage(), [
                'user_data' => $userData,
                'address_data' => $addressData,
                'exception' => $e,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('notification', [
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => 'Erro ao criar usuário: ' . $e->getMessage(),
                    'duration' => 3000,
                ]);
        }
    }

    /**
     * Exibe os detalhes de um usuário específico
     *
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        $user->load(['address', 'filial', 'departamento', 'tipoPessoal', 'telefones']);

        $activityLogs = $user->activityLogs()
            ->excludeFieldsUpdates(['last_login_at', 'created_at', 'updated_at'])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        if (request()->ajax()) {
            return view('admin.usuarios.show-partial', compact('user', 'activityLogs'))->render();
        }

        return view('admin.usuarios.show', compact('user', 'activityLogs'));
    }

    /**
     * Exibe o formulário para editar um usuário existente
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(User $user)
    {
        if ($user->is_superuser && ! Auth::user()->is_superuser) {
            return redirect()
                ->route('admin.usuarios.index')
                ->with('notification', [
                    'title' => 'Acesso negado',
                    'type' => 'error',
                    'message' => 'Apenas super usuários podem editar outros super usuários',
                    'duration' => 3000,
                ]);
        }

        $filiais = VFilial::orderBy('name')->get(['id as value', 'name as label']);
        $departamentos = Departamento::orderBy('descricao_departamento')
            ->get(['id_departamento as value', 'descricao_departamento as label']);
        $cargos = TipoPessoal::orderBy('descricao_tipo')
            ->get(['id_tipo_pessoal as value', 'descricao_tipo as label']);

        $grupos = Role::orderBy('name')
            ->get(['id as value', 'name as label']);

        return view('admin.usuarios.edit', compact('user', 'filiais', 'departamentos', 'cargos', 'grupos'));
    }

    /**
     * Atualiza os dados de um usuário existente
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        if ($user->is_superuser && ! Auth::user()->is_superuser) {
            return redirect()
                ->route('admin.usuarios.index')
                ->with('notification', [
                    'title' => 'Acesso negado',
                    'type' => 'error',
                    'message' => 'Apenas super usuários podem editar outros super usuários',
                    'duration' => 3000,
                ]);
        }

        $userData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'departamento_id' => ['nullable', 'exists:departamento,id_departamento'],
            'pessoal_id' => ['nullable', 'exists:tipopessoal,id_tipo_pessoal'],
            'cpf' => ['nullable', 'string', 'max:14', 'unique:users,cpf,' . $user->id],
            'matricula' => ['nullable', 'integer', 'unique:users,matricula,' . $user->id],
            'is_superuser' => ['boolean'],
            'is_ativo' => ['boolean'],
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'telefone_fixo' => 'nullable|string|max:30',
            'telefone_celular' => 'nullable|string|max:30',
        ]);

        $addressData = $request->validate([
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|size:2',
            'zip_code' => 'nullable|string|max:10',
        ]);

        $filiais = $request->input('filiais', []);

        // Carregar o relacionamento address para verificar se existe
        $user->load('address');

        DB::beginTransaction();

        try {
            if (empty($userData['password'])) {
                unset($userData['password']);
            } else {
                $userData['password'] = Hash::make($userData['password']);
                $userData['has_password_updated'] = true;
            }

            if (! Auth::user()->is_superuser) {
                unset($userData['is_superuser']);
            }

            $hasAddressData = ! empty(array_filter($addressData));

            if ($hasAddressData) {
                if ($user->address) {
                    $user->address->update($addressData);
                } else {
                    $address = Address::create($addressData);
                    $userData['address_id'] = $address->id;
                }
            } elseif ($user->address_id && ! $hasAddressData) {
                $userData['address_id'] = null;
            }

            if (is_string($filiais)) {
                $filiais = json_decode($filiais, true);
            }

            $filiais = array_map(function ($item) {
                if (is_array($item)) {
                    return (int) ($item[0] ?? null);
                }

                return (int) $item;
            }, $filiais);

            $filiais = array_filter($filiais);

            $filialPrincipal = $this->determinarFilialPrincipal($filiais);

            if ($filialPrincipal !== null) {
                $userData['filial_id'] = $filialPrincipal;
            } else {
                $userData['filial_id'] = null;
            }

            $user->update($userData);

            // Upsert telefones
            $telFixo = $request->input('telefone_fixo');
            $telCel = $request->input('telefone_celular');
            $telefone = Telefone::where('user_id', $user->id)->first();
            if (!empty($telFixo) || !empty($telCel)) {
                if ($telefone) {
                    $telefone->update([
                        'telefone_fixo' => $telFixo,
                        'telefone_celular' => $telCel,
                    ]);
                } else {
                    Telefone::create([
                        'telefone_fixo' => $telFixo,
                        'telefone_celular' => $telCel,
                        'user_id' => $user->id,
                    ]);
                }
            } else if ($telefone) {
                // Remover registro se ambos vazios
                $telefone->delete();
            }

            // Sincronizar roles
            if ($request->filled('roles')) {
                $roleIds = $this->normalizeRoles($request->input('roles'));

                // Converter para inteiros para garantir busca por ID
                $roleIds = array_map('intval', $roleIds);

                // Limpar todas as permissões diretas antes de sincronizar roles
                $user->permissions()->detach();

                Log::info('Permissões diretas removidas do usuário', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'action' => 'role_sync_update'
                ]);

                // Sincronizar usando IDs como inteiros
                $user->syncRoles($roleIds);

                Log::info('Roles sincronizadas para usuário', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'roles' => $roleIds
                ]);
            } else {
                // Se não há roles selecionadas, limpar tudo
                $user->permissions()->detach();
                $user->syncRoles([]);

                Log::info('Todas as permissões e roles removidas do usuário', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'action' => 'clear_all_update'
                ]);
            }

            if (! empty($filiais)) {
                $user->filiais()->sync($filiais);
            } elseif (! empty($userData['filial_id'])) {
                $user->filiais()->sync([$userData['filial_id']]);
            } else {
                $user->filiais()->sync([]);
            }

            DB::commit();

            return redirect()
                ->route('admin.usuarios.index')
                ->with('notification', [
                    'title' => 'Usuário atualizado',
                    'type' => 'success',
                    'message' => 'Usuário atualizado com sucesso!',
                    'duration' => 3000,
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar usuário: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'user_data' => $userData,
                'address_data' => $addressData,
                'filiais' => $filiais,
                'exception' => $e,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('notification', [
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => 'Erro ao atualizar usuário: ' . $e->getMessage(),
                    'duration' => 3000,
                ]);
        }
    }

    public function cloneUser(string $id)
    {
        LOG::DEBUG('Clonando usuário com ID: ' . $id);
        DB::beginTransaction();

        try {
            $originalUser = User::findOrFail($id);

            $clonedUser = $originalUser->replicate();
            $clonedUser->email = 'clone_' . time() . '_' . $originalUser->email;
            $clonedUser->password = Hash::make('password_temp'); // Senha temporária
            $clonedUser->save();

            $this->clonePermissionsViaDatabase($originalUser->id, $clonedUser->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuário clonado com sucesso, o novo usuário tem o email: ' . $clonedUser->email . ' e senha: password_temp',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao clonar usuário: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exibe a página de listagem de usuários com departamentos
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function listWithDepartments()
    {
        $users = User::with(['departamento', 'tipoPessoal', 'filial'])
            ->orderBy('name')
            ->get();

        $departamentos = Departamento::orderBy('descricao_departamento')
            ->get(['id_departamento as value', 'descricao_departamento as label']);

        $cargos = TipoPessoal::orderBy('descricao_tipo')
            ->get(['id_tipo_pessoal as value', 'descricao_tipo as label']);

        $grupos = Role::orderBy('name')
            ->get(['id as value', 'name as label']);

        $filiais = VFilial::orderBy('name')
            ->get(['id as value', 'name as label']);

        return view('admin.usuarios.list-with-departments', compact(
            'users',
            'departamentos',
            'cargos',
            'filiais',
            'grupos'
        ));
    }

    /**
     * Atualiza as relações de departamento, cargo e filial de um usuário
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRelacoes(Request $request, User $user)
    {
        $validated = $request->validate([
            'departamento_id' => 'required|exists:departamento,id_departamento',
            'id_tipo_pessoal' => 'required|exists:tipopessoal,id_tipo_pessoal',
            'filial_id' => 'required|exists:filiais,id',
        ]);

        try {
            $user->update([
                'departamento_id' => $validated['departamento_id'],
                'pessoal_id' => $validated['id_tipo_pessoal'],
                'filial_id' => $validated['filial_id'],
            ]);

            return response()->json([
                'message' => 'Relações do usuário atualizadas com sucesso!',
                'user' => $user->load(['departamento', 'tipoPessoal', 'filial']),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar relações do usuário: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'validated_data' => $validated,
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Erro ao atualizar relações: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retorna dados do usuário autenticado para uso em API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        try {
            $user = User::select('users.*')
                ->with(['departamento:id_departamento,descricao_departamento', 'tipoPessoal:id_tipo_pessoal,descricao_tipo', 'filial:id,name'])
                ->where('users.email', $request->user()->email)
                ->first();

            return response()->json($user);
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados do usuário: ' . $e->getMessage(), [
                'user_email' => $request->user()->email,
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Erro ao obter dados do usuário: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Determina a filial principal com base nas regras de negócio.
     *
     * @param  array  $filiais  IDs das filiais selecionadas
     * @return int|null ID da filial principal ou null se não houver filiais
     */
    private function determinarFilialPrincipal(array $filiais)
    {
        // Se não houver filiais selecionadas, retornar null
        if (empty($filiais)) {
            return null;
        }

        // Normalizar filiais para garantir que são inteiros
        $filiais = array_map(function ($filial) {
            if (is_array($filial)) {
                return (int) ($filial[0] ?? null);
            }

            return (int) $filial;
        }, $filiais);

        // Remover valores nulos/vazios
        $filiais = array_filter($filiais);

        if (empty($filiais)) {
            return null;
        }

        // Constante para o ID da filial Matriz
        $MATRIZ_ID = 1;

        // Se a Matriz (ID 1) estiver entre as filiais selecionadas, ela é a principal
        if (in_array($MATRIZ_ID, $filiais)) {
            return $MATRIZ_ID;
        }

        // Caso contrário, a primeira filial selecionada é a principal
        return $filiais[0];
    }

    /**
     * Clone permissões usando acesso direto à tabela model_has_permissions
     * Útil para clonagem em massa ou quando a API do Spatie não é suficiente
     *
     * @param  Model|int  $sourceUser  Usuário de origem
     * @param  Model|int  $targetUser  Usuário de destino
     * @return array Resultado da operação
     */
    public function clonePermissionsViaDatabase($sourceUser, $targetUser): array
    {
        $sourceUser = $this->resolveUser($sourceUser);
        $targetUser = $this->resolveUser($targetUser);

        if (! $sourceUser || ! $targetUser) {
            return [
                'success' => false,
                'message' => 'Usuário de origem ou destino não encontrado',
                'data' => null,
            ];
        }

        $sourceModelType = get_class($sourceUser);
        $sourceId = $sourceUser->getKey();

        $targetModelType = get_class($targetUser);
        $targetId = $targetUser->getKey();

        try {
            $permissions = DB::connection('pgsql')->table('model_has_permissions')
                ->where('model_type', $sourceModelType)
                ->where('model_id', $sourceId)
                ->get(['permission_id']);

            if ($permissions->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'Nenhuma permissão encontrada para clonar',
                    'data' => ['count' => 0],
                ];
            }

            $existingPermissions = DB::connection('pgsql')->table('model_has_permissions')
                ->where('model_type', $targetModelType)
                ->where('model_id', $targetId)
                ->pluck('permission_id')
                ->toArray();

            $newPermissions = $permissions->filter(function ($item) use ($existingPermissions) {
                return ! in_array($item->permission_id, $existingPermissions);
            });

            $insertData = [];
            foreach ($newPermissions as $permission) {
                $insertData[] = [
                    'permission_id' => $permission->permission_id,
                    'model_type' => $targetModelType,
                    'model_id' => $targetId,
                ];
            }

            if (count($insertData) > 0) {
                DB::connection('pgsql')->table('model_has_permissions')->insert($insertData);
            }

            return [
                'success' => true,
                'message' => 'Permissões clonadas com sucesso via banco de dados',
                'data' => [
                    'totalFound' => $permissions->count(),
                    'alreadyAssigned' => count($existingPermissions),
                    'newlyAssigned' => count($insertData),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao clonar permissões: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Método auxiliar para resolver o usuário (aceita ID ou instância do modelo)
     */
    private function resolveUser($user)
    {
        if (is_numeric($user)) {
            return User::find($user);
        }

        if ($user instanceof Model) {
            return $user;
        }

        return null;
    }

    /**
     * Normaliza array de roles que pode vir como array de arrays
     *
     * @return array
     */
    private function normalizeRoles(array $roles)
    {
        $normalizedRoles = [];
        foreach ($roles as $role) {
            if (is_array($role)) {
                $normalizedRoles[] = $role[0] ?? null;
            } else {
                $normalizedRoles[] = $role;
            }
        }

        // Filtrar valores nulos/vazios
        return array_filter($normalizedRoles);
    }

    public function search(Request $request)
    {
        try {
            $term = strtolower($request->get('term'));

            $users = Cache::remember('users_search_' . $term, now()->addMinutes(30), function () use ($term) {
                return User::whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                    ->orderBy('name')
                    ->limit(30)
                    ->get(['id as value', 'name as label']);
            });

            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar usuários: ' . $e->getMessage());

            return response()->json(['error' => 'Erro ao buscar usuários.'], 500);
        }
    }

    public function getById($id)
    {
        $users = Cache::remember('user' . $id, now()->addHours(24), function () use ($id) {
            return User::findOrFail($id);
        });

        return response()->json([
            'value' => $users->id,
            'label' => $users->name,
        ]);
    }


    public function trocarFilial(Request $request)
    {
        $request->validate([
            'filial_id' => 'required|exists:filiais,id',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Busca as filiais permitidas para o usuário via tabela user_filial
        $filiaisPermitidas = \App\Models\UserFilial::where('user_id', $user->id)
            ->pluck('filial_id')
            ->toArray();

        // Se o usuário não for superuser, valida se a filial está na lista
        if (!$user->is_superuser && !in_array($request->filial_id, $filiaisPermitidas)) {
            abort(403, 'Você não tem permissão para acessar essa filial.');
        }

        // Atualiza a filial ativa do usuário
        $user->update(['filial_id' => $request->filial_id]);

        return redirect()
            ->route('admin.dashboard')
            ->with(['success' => 'Filial Alterada com Sucesso!']);
    }
}
