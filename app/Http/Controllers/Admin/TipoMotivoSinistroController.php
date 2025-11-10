<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoMotivoSinistro;
use Illuminate\Support\Facades\Log;

class TipoMotivoSinistroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TipoMotivoSinistro::query();

        if ($request->filled('id_motivo_cinistro')) {
            $query->where('id_motivo_cinistro', $request->id_motivo_cinistro);
        }

        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
        }

        if ($request->filled('descricao_motivo')) {
            $query->where('descricao_motivo', 'ilike', '%' . $request->descricao_motivo . '%');
        }

        $tipo = TipoMotivoSinistro::select('descricao_motivo  as value', 'descricao_motivo as label')
            ->orderBy('descricao_motivo')
            ->get();

        $tipoMotivoSinistro = $query->latest('id_motivo_cinistro')
            ->paginate(40)
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.tipomotivosinistros._table', compact('tipoMotivoSinistro', 'tipo'));
        }

        return view('admin.tipomotivosinistros.index', compact('tipoMotivoSinistro', 'tipo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipomotivosinistros.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_motivo' => 'required|string|max:500',
        ]);
        $tipomotivosinistros = new TipoMotivoSinistro();
        $tipomotivosinistros->data_inclusao = now();
        $tipomotivosinistros->descricao_motivo = $request->descricao_motivo;
        $tipomotivosinistros->save();

        return redirect()
            ->route('admin.tipomotivosinistros.index')
            ->withNotification([
                'title'   => 'Sucesso!',
                'type'    => 'success',
                'message' => 'Novo tipo de sinistro adicionado com sucesso!'
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoMotivoSinistro $tipomotivosinistros)
    {
        $tipomotivosinistros = TipoMotivoSinistro::findOrFail($tipomotivosinistros);
        return view('admin.tipomotivosinistros.show', compact('tipomotivosinistros'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoMotivoSinistro $tipomotivosinistros)
    {
        return view('admin.tipomotivosinistros.edit', compact('tipomotivosinistros'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoMotivoSinistro $tipomotivosinistros)
    {
        $validated = $request->validate([
            'descricao_motivo' => 'required|string|max:500'
        ]);

        try {
            $tipomotivosinistros->descricao_motivo = $validated['descricao_motivo'];
            $tipomotivosinistros->data_alteracao = now();
            $tipomotivosinistros->update();

            if (!$tipomotivosinistros) {
                return  redirect()
                    ->route('admin.tipomotivosinistros.index')
                    ->withNotification([
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar o Sinistro!'
                    ]);
            }

            return redirect()
                ->route('admin.tipomotivosinistros.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Sinistro editado com sucesso!'
                ]);
        } catch (\Exception $e) {
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $tipoMotivo = TipoMotivoSinistro::findOrFail($id);
            $tipoMotivo->delete();

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            LOG::INFO('ERRO AO EXCLUI O REGISTRO: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o Sinistro: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}
