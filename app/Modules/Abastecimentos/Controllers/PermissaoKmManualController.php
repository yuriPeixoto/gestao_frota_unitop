<?php

namespace App\Modules\Abastecimentos\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CategoriaVeiculo;
use App\Models\Departamento;
use App\Models\Filial;
use App\Models\PermissaoKmManual;
use App\Models\TipoCategoria;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PermissaoKmManualController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = PermissaoKmManual::with(['filial', 'veiculo', 'veiculo.departamentoVeiculo', 'veiculo.categoriaVeiculo']);

        // Aplicar filtros se existirem
        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_final);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('id_departamento')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('id_departamento', $request->id_departamento);
            });
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('id_categoria')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('id_categoria', $request->id_categoria);
            });
        }

        $permissoes = $query->orderBy('id_permissao_km_manual', 'desc')->get();

        $permissaokmmanuals = $permissoes->map(function ($permissao) {
            return [
                'id' => $permissao->id_permissao_km_manual,
                'data_inclusao' => format_date($permissao->data_inclusao),
                'data_alteracao' => $permissao->data_alteracao ? format_date($permissao->data_alteracao) : '',
                'id_filial' => $permissao->filial ? $permissao->filial->name : '',
                'id_veiculo' => $permissao->veiculo ? $permissao->veiculo->placa : '',
                'id_departamento' => $permissao->veiculo && $permissao->veiculo->departamentoVeiculo ? $permissao->veiculo->departamentoVeiculo->descricao_departamento : '',
                'id_categoria' => $permissao->veiculo && $permissao->veiculo->categoriaVeiculo ? $permissao->veiculo->categoriaVeiculo->descricao_categoria : ''
            ];
        })->toArray();

        // Carregar dados para os filtros
        $filiais = Filial::select('name as label', 'id as value')->orderBy('name')->get()->toArray();
        $departamentos = Departamento::where('ativo', true)
            ->select('descricao_departamento as label', 'id_departamento as value')
            ->orderBy('descricao_departamento')
            ->get()
            ->toArray();
        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->select('placa as label', 'id_veiculo as value')
            ->orderBy('placa')
            ->get()
            ->toArray();
        $categorias = TipoCategoria::select('descricao_categoria as label', 'id_categoria as value')
            ->orderBy('descricao_categoria')
            ->get()
            ->toArray();

        $formOptions = compact('filiais', 'departamentos', 'veiculos', 'categorias');

        return view('admin.permissaokmmanuals.index', compact('permissaokmmanuals', 'formOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $formOptions = [
            'filiais' => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'departamentos' => Departamento::where('ativo', true)
                ->select('descricao_departamento as label', 'id_departamento as value')
                ->orderBy('descricao_departamento')
                ->get()
                ->toArray(),
            'categorias' => CategoriaVeiculo::select('descricao_categoria as label', 'id_categoria as value')
                ->orderBy('descricao_categoria')
                ->get()
                ->toArray(),
            'veiculos' => Veiculo::select('placa as label', 'id_veiculo as value', 'id_filial', 'id_departamento', 'id_categoria')
                ->where('situacao_veiculo', true)
                ->orderBy('label')
                ->get()
                ->toArray()
        ];

        return view('admin.permissaokmmanuals.create', compact('formOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_filial' => 'required|integer',
            'id_veiculo' => 'required',
        ], [
            'id_filial.required' => 'A filial é obrigatória',
            'id_veiculo.required' => 'Selecione pelo menos um veículo',
        ]);

        try {
            $id_veiculos = is_array($request->id_veiculo)
                ? $request->id_veiculo
                : Str::of($request->id_veiculo)->explode(',')->map(fn($id) => trim((int)$id))->toArray();

            DB::beginTransaction();

            foreach ($id_veiculos as $id_veiculo) {
                // Verificar se já existe permissão para este veículo
                $existente = PermissaoKmManual::where('id_veiculo', $id_veiculo)->first();

                if (!$existente) {
                    // Buscar dados do veículo
                    $veiculo = Veiculo::find($id_veiculo);

                    PermissaoKmManual::create([
                        'data_inclusao' => now(),
                        'id_filial' => $validated['id_filial'],
                        'id_veiculo' => $id_veiculo,
                        'id_departamento' => $veiculo->id_departamento ?? null,
                        'id_categoria' => $veiculo->id_categoria ?? null
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.permissaokmmanuals.index')->withNotification([
                'title'   => 'Sucesso!',
                'type'    => 'success',
                'message' => 'Nova(s) permissão(ões) adicionada(s) com sucesso!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar permissão: ' . $e->getMessage());

            return redirect()->back()->withInput()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro ao cadastrar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PermissaoKmManual $permissaokmmanual)
    {
        return view('admin.permissaokmmanuals.show', compact('permissaokmmanual'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PermissaoKmManual $permissaokmmanual)
    {
        $formOptions = [
            'filiais' => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'departamentos' => Departamento::where('ativo', true)
                ->select('descricao_departamento as label', 'id_departamento as value')
                ->orderBy('descricao_departamento')
                ->get()
                ->toArray(),
            'categorias' => CategoriaVeiculo::select('descricao_categoria as label', 'id_categoria as value')
                ->orderBy('descricao_categoria')
                ->get()
                ->toArray(),
            'veiculos' => Veiculo::select('placa as label', 'id_veiculo as value', 'id_filial', 'id_departamento', 'id_categoria')
                ->where('situacao_veiculo', true)
                ->orderBy('label')
                ->get()
                ->toArray()
        ];

        return view('admin.permissaokmmanuals.edit', compact('permissaokmmanual', 'formOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PermissaoKmManual $permissaokmmanual)
    {
        $validated = $request->validate([
            'id_filial' => 'required|integer',
            'id_veiculo' => 'required|integer',
        ], [
            'id_filial.required' => 'A filial é obrigatória',
            'id_veiculo.required' => 'O veículo é obrigatório',
        ]);

        try {
            // Verificar se o veículo foi alterado
            if ($permissaokmmanual->id_veiculo != $validated['id_veiculo']) {
                // Verificar se já existe permissão para o novo veículo
                $existente = PermissaoKmManual::where('id_veiculo', $validated['id_veiculo'])
                    ->where('id_permissao_km_manual', '!=', $permissaokmmanual->id_permissao_km_manual)
                    ->first();

                if ($existente) {
                    return redirect()->back()->withInput()->withNotification([
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Já existe uma permissão para este veículo.'
                    ]);
                }
            }

            // Buscar dados do veículo
            $veiculo = Veiculo::find($validated['id_veiculo']);

            $permissaokmmanual->update([
                'id_filial' => $validated['id_filial'],
                'id_veiculo' => $validated['id_veiculo'],
                'id_departamento' => $veiculo->id_departamento ?? null,
                'id_categoria' => $veiculo->id_categoria ?? null,
                'data_alteracao' => now()
            ]);

            return redirect()->route('admin.permissaokmmanuals.index')->withNotification([
                'title'   => 'Sucesso!',
                'type'    => 'success',
                'message' => 'Permissão atualizada com sucesso!'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar permissão: ' . $e->getMessage());

            return redirect()->back()->withInput()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro ao atualizar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $permissaokmmanual = PermissaoKmManual::findOrFail($id);
            $permissaokmmanual->delete();

            return response()->json([
                'notification' => [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Permissão excluída com sucesso!'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir permissão: ' . $e->getMessage());

            return response()->json([
                'notification' => [
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Erro ao excluir: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Importar todas as placas de um departamento.
     */
    public function importarPorDepartamento(Request $request)
    {
        $request->validate([
            'id_departamento' => 'required|integer',
            'id_filial' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            // Obter todos os veículos do departamento
            $veiculos = Veiculo::where('id_departamento', $request->id_departamento)
                ->where('situacao_veiculo', true)
                ->get();

            $contador = 0;

            foreach ($veiculos as $veiculo) {
                // Verificar se já existe permissão
                $existente = PermissaoKmManual::where('id_veiculo', $veiculo->id_veiculo)->first();

                if (!$existente) {
                    PermissaoKmManual::create([
                        'data_inclusao' => now(),
                        'id_filial' => $request->id_filial,
                        'id_veiculo' => $veiculo->id_veiculo,
                        'id_departamento' => $veiculo->id_departamento,
                        'id_categoria' => $veiculo->id_categoria
                    ]);

                    $contador++;
                }
            }

            if ($contador == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum veículo encontrado.'
                ]);
            }

            DB::commit();

            Log::info('importar permissões por departamento: ' . $contador . ' permissões adicionadas.');

            return response()->json([
                'success' => true,
                'message' => "Foram adicionadas {$contador} permissões com sucesso.",
                'count' => $contador
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao importar permissões por departamento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar permissões: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Importar todos os veículos de uma categoria.
     */
    public function importarPorCategoria(Request $request)
    {
        $request->validate([
            'id_categoria' => 'required|integer',
            'id_filial' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            // Obter todos os veículos da categoria
            $veiculos = Veiculo::where('id_categoria', $request->id_categoria)
                ->where('situacao_veiculo', true)
                ->get();

            $contador = 0;

            foreach ($veiculos as $veiculo) {
                // Verificar se já existe permissão
                $existente = PermissaoKmManual::where('id_veiculo', $veiculo->id_veiculo)->first();

                if (!$existente) {
                    PermissaoKmManual::create([
                        'data_inclusao' => now(),
                        'id_filial' => $request->id_filial,
                        'id_veiculo' => $veiculo->id_veiculo,
                        'id_departamento' => $veiculo->id_departamento,
                        'id_categoria' => $veiculo->id_categoria
                    ]);

                    $contador++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Foram adicionadas {$contador} permissões com sucesso.",
                'count' => $contador
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao importar permissões por categoria: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar permissões: ' . $e->getMessage()
            ], 500);
        }
    }
}
