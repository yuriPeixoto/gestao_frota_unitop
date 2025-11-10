<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CheckPermission;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Role::query();

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('name')) {
            $query->where('name', 'ilike',  '%' . $request->name . '%');
        }

        if ($request->filled('description')) {
            $query->where('description', 'ilike',  '%' . $request->description . '%');
        }

        // Usar whereRaw para data_inclusao
        if ($request->filled('data_de_criacao')) {
            $query->whereRaw("created_at::date >= ?", [$request->data_de_criacao]);
        }

        if ($request->filled('data_de_criacao_final')) {
            $query->whereRaw("created_at::date <= ?", [$request->data_de_criacao_final]);
        }

        if ($request->filled('ativo')) {
            $query->where('is_ativo', $request->is_ativo);
        }

        $roles = $query->latest('name')
            ->where('is_ativo', '!=', false)
            ->with('permissions') // Carrega as permissões antecipadamente
            ->paginate(40)
            ->through(function ($role) {
                // Acessa as permissões já carregadas (não usa permissions())
                $role->permissions_list = $role->permissions->pluck('name')->implode(', ');
                return $role;
            })
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.cargos._table', compact('roles'));
        }

        return view('admin.cargos.index', array_merge(
            compact('roles')
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $permissions = Permission::all();
        return view('admin.cargos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'        => 'required',
                'description' => 'required',
                'is_ativo' => 'required'
            ]);
            // dd($validated);

            Role::create([
                'name'         => $validated['name'],
                'description'  => $validated['description'],
                'is_ativo'     => $validated['is_ativo'],
                'guard_name'   => 'web', // <-- valor obrigatório
            ]);

            return redirect()->route('admin.cargos.index')
                ->with('notification', [
                    'type' => 'success',
                    'title' => 'Sucesso!',
                    'message' => 'Registro gravado com sucesso.',
                    'duration' => 5000
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gravar o registro: ' . $e->getMessage());
            return back()->with('notification', [
                'type' => 'error',
                'title' => 'Erro!',
                'message' => 'Falha ao gravar registro: ' . $e->getMessage(),
                'duration' => 8000
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $roleId = json_decode($role, true)['id'];
        $role = Role::where('id', $roleId)->first();
        return view('admin.cargos.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        try {
            $validated = $request->validate([
                'name'        => 'required',
                'description' => 'required',
                'is_ativo' => 'required'
            ]);
            $role->updated_at = now();
            // $role->id_user = Auth::user()->id;
            $role->update($validated);

            return redirect()->route('admin.cargos.index')
                ->with('notification', [
                    'type' => 'success',
                    'title' => 'Sucesso!',
                    'message' => 'Registro atualizado com sucesso.',
                    'duration' => 5000
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gravar o registro: ' . $e->getMessage());
            return back()->with('notification', [
                'type' => 'error',
                'title' => 'Erro!',
                'message' => 'Falha ao atualizar registro: ' . $e->getMessage(),
                'duration' => 8000
            ]);
        }
    }
}
