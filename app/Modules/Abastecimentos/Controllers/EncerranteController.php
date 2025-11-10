<?php

namespace App\Modules\Abastecimentos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Abastecimentos\Models\Encerrante;
use App\Modules\Abastecimentos\Models\Bomba;
use App\Modules\Abastecimentos\Models\Tanque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EncerranteController extends Controller
{
    public function index()
    {
        $encerrantes = Encerrante::with(['bomba', 'tanque', 'conferente'])
            ->orderBy('id_encerrante', 'desc')
            ->paginate(15);

        $bombas = Bomba::orderBy('descricao_bomba')->get();
        $tanques = Tanque::orderBy('tanque')->get();

        return view('admin.encerrantes.index', compact('encerrantes', 'bombas', 'tanques'));
    }

    public function create()
    {
        // Filtrar tanques pela filial do usuário (se não for Matriz)
        $tanquesQuery = Tanque::orderBy('tanque');

        if (Auth::user()->filial_id != 1) {
            $tanquesQuery->where('id_filial', Auth::user()->filial_id);
        }

        $tanques = $tanquesQuery->get();

        return view('admin.encerrantes.create', compact('tanques'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_bomba' => 'required|exists:bomba,id_bomba',
            'id_tanque' => 'required|exists:tanque,id_tanque',
            'data_hora_abertura' => 'required|date',
            'data_hora_encerramento' => 'required|date|after:data_hora_abertura',
            'encerrante_abertura' => 'required|integer|min:0',
            'encerrante_fechamento' => 'required|integer|min:0|gt:encerrante_abertura'
        ]);

        $validated['usuario'] = Auth::id();
        $validated['id_filial'] = Auth::user()->filial_id;

        $encerrante = Encerrante::create($validated);

        return redirect()
            ->route('admin.encerrantes.index')
            ->with('success', 'Encerrante registrado com sucesso.');
    }

    public function edit(Encerrante $encerrante)
    {
        // Filtrar tanques pela filial do usuário (se não for Matriz)
        $tanquesQuery = Tanque::orderBy('tanque');

        if (Auth::user()->filial_id != 1) {
            $tanquesQuery->where('id_filial', Auth::user()->filial_id);
        }

        $tanques = $tanquesQuery->get();

        return view('admin.encerrantes.edit', compact('encerrante', 'tanques'));
    }

    public function update(Request $request, Encerrante $encerrante)
    {
        $validated = $request->validate([
            'id_bomba' => 'required|exists:bomba,id_bomba',
            'id_tanque' => 'required|exists:tanque,id_tanque',
            'data_hora_abertura' => 'required|date',
            'data_hora_encerramento' => 'required|date|after:data_hora_abertura',
            'encerrante_abertura' => 'required|integer|min:0',
            'encerrante_fechamento' => 'required|integer|min:0|gt:encerrante_abertura'
        ]);

        $encerrante->update($validated);

        return redirect()
            ->route('admin.encerrantes.index')
            ->with('success', 'Encerrante atualizado com sucesso.');
    }

    public function destroy(Encerrante $encerrante)
    {
        try {
            $encerrante->delete();
            return response()->json([
                'success' => true,
                'message' => 'Encerrante excluído com sucesso.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir encerrante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método AJAX para carregar bombas por tanque (cascata)
     * Usado nas views create/edit
     */
    public function bombasPorTanque($tanqueId)
    {
        try {
            $bombas = Bomba::where('id_tanque', $tanqueId)
                ->where('is_ativo', true)
                ->orderBy('descricao_bomba')
                ->get(['id_bomba', 'descricao_bomba']);

            return response()->json($bombas);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }
}
