<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoDescarte;
use Illuminate\Http\Request;

class TipoDescarteController extends Controller
{
    public function search(Request $request)
    {
        $term = strtolower($request->get('term'));
        // Cache para melhorar performance
        $tipoDescarte = TipoDescarte::whereRaw('LOWER(descricao_tipo_descarte) LIKE ?', ["%{$term}%"])
            ->orderBy('descricao_tipo_descarte')
            ->limit(30) // Limite razoável de resultados
            ->get(['id_tipo_descarte as value', 'descricao_tipo_descarte as label']);


        return response()->json($tipoDescarte);
    }

    /**
     * Retorna um fornecedor específico pelo ID
     * Usado para carregar o item selecionado inicialmente e para interatividade entre campos
     */
    public function getById($id)
    {
        // Cache para melhorar performance
        $tipoDescarte = TipoDescarte::findOrFail($id);

        return response()->json([
            'value' => $tipoDescarte->id_tipo_descarte,
            'label' => $tipoDescarte->descricao_tipo_descarte,
        ]);
    }
}
