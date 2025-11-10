<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModeloVeiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ModeloVeiculoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $modeloveiculos = ModeloVeiculo::orderBy('id_modelo_veiculo', 'asc')->get();

        $modeloveiculosData = $modeloveiculos->map(function ($modeloveiculo) {
            return [
                'id'        => $modeloveiculo->id_modelo_veiculo,
                'descricao' => $modeloveiculo->descricao_modelo_veiculo,
                'ano'                      => $modeloveiculo->ano,
                'multicombustivel'         => $modeloveiculo->multicombustivel,
                'ativo'                    => $modeloveiculo->ativo,
                'marca'                    => $modeloveiculo->marca,
            ];
        })->toArray();

        return view('admin.modeloveiculos.index', compact('modeloveiculosData'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.modeloveiculos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_modelo_veiculo' => 'required|string|max:500',
            'ano' => 'required|integer',
            'multicombustivel' => 'required|boolean',
            'ativo' => 'required|boolean',
            'marca' => 'required|string'

        ]);
        $data_inclusao = date('Y-m-d H:i:s');
        $modeloveiculo = new ModeloVeiculo();
        $modeloveiculo->data_inclusao = $data_inclusao;
        $modeloveiculo->descricao_modelo_veiculo = $request->descricao_modelo_veiculo;
        $modeloveiculo->ano = $request->ano;
        $modeloveiculo->multicombustivel = $request->multicombustivel;
        $modeloveiculo->ativo = $request->ativo;
        $modeloveiculo->marca = $request->marca;
        $modeloveiculo->save();
        return redirect()
            ->route('admin.modeloveiculos.index')
            ->withNotification([
                'title'   => 'Modelo de veículo criado',
                'type'    => 'success',
                'message' => 'Modelo de veículo criado com sucesso!'
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ModeloVeiculo $modeloveiculo)
    {
        $modeloveiculos = ModeloVeiculo::findOrFail($modeloveiculo);
        return view('admin.modeloveiculos.show', compact('modeloveiculos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ModeloVeiculo $modeloveiculo)
    {
        return view('admin.modeloveiculos.edit', compact('modeloveiculo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ModeloVeiculo $modeloveiculo)
    {
        $validated = $request->validate([
            'descricao_modelo_veiculo' => 'required|string|max:500',
            'ano' => 'required|integer',
            'multicombustivel' => 'required|boolean',
            'ativo' => 'required|boolean',
            'marca' => 'required|string'
        ]);

        try {
            $updated = $modeloveiculo->update([
                'descricao_modelo_veiculo' => $validated['descricao_modelo_veiculo'],
                'ano' => $validated['ano'],
                'multicombustivel' => $validated['multicombustivel'],
                'ativo' => $validated['ativo'],
                'marca' => $validated['marca'],
                'data_alteracao' => now()
            ]);

            if (!$updated) {
                return back()->withErrors('Não foi possível atualizar o registro.');
            }

            return redirect()
                ->route('admin.modeloveiculos.index')
                ->withNotification([
                    'title'   => 'Modelo de veículo atualizado',
                    'type'    => 'success',
                    'message' => 'Modelo de veículo atualizado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro na atualização:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withNotification([
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Erro ao atualizar o modelo de veículo: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $modeloVeiculo = ModeloVeiculo::findOrFail($id);
            $modeloVeiculo->delete();

            return response()->json([
                'notification' => [
                    'title'   => 'Modelo de veículo excluído',
                    'type'    => 'success',
                    'message' => 'Modelo de veículo excluído com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o modelo de veículo: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $term = strtolower($request->get('term'));

        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        // Cache para melhorar performance
        $modelos = Cache::remember('modelo_veiculo_search_' . $term, now()->addMinutes(30), function () use ($term) {
            return ModeloVeiculo::select('id_modelo_veiculo', 'descricao_modelo_veiculo', 'ano')
                ->where('ativo', true)
                ->whereRaw('LOWER(descricao_modelo_veiculo) LIKE ?', ["%{$term}%"])
                ->orderBy('descricao_modelo_veiculo')
                ->limit(30)
                ->get()
                ->map(function ($m) {
                    $suffix = '';
                    if (! empty($m->ano)) {
                        $suffix = ' - ' . $m->ano;
                    }

                    return [
                        'label' => $m->descricao_modelo_veiculo . $suffix,
                        'value' => $m->id_modelo_veiculo
                    ];
                })->toArray();
        });


        return response()->json($modelos);
    }

    /**
     * Buscar um modelo de veículo pelo ID
     */
    public function getById($id)
    {
        $modelo = ModeloVeiculo::where('ativo', true)->where('id_modelo_veiculo', $id)->first();

        if (! $modelo) {
            return response()->json([], 404);
        }

        return response()->json([
            'value' => $modelo->id_modelo_veiculo,
            'label' => $modelo->descricao_modelo_veiculo,
            'ano'   => $modelo->ano,
            'marca' => $modelo->marca,
        ]);
    }
}
