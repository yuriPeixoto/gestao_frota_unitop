<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModeloPneu;
use Illuminate\Http\Request;

class ModeloPneuController extends Controller
{
    public function search(Request $request)
    {
        $term = strtolower($request->get('term', ''));

        // Cache para melhorar performance
        $modeloPneu = ModeloPneu::WhereRaw('LOWER(descricao_modelo) LIKE ?', ["%{$term}%"])
            ->orderBy('descricao_modelo')
            ->limit(30) // Limite razoável de resultados
            ->get(['id_modelo_pneu as value', 'descricao_modelo as label']);


        return response()->json($modeloPneu);
    }

    /**
     * Retorna um fornecedor específico pelo ID
     * Usado para carregar o item selecionado inicialmente e para interatividade entre campos
     */
    public function getById($id)
    {
        // Cache para melhorar performance
        $modeloPneu = ModeloPneu::findOrFail($id);

        return response()->json([
            'value' => $modeloPneu->id_modelo_pneu,
            'label' => $modeloPneu->descricao_modelo,
        ]);
    }
}
