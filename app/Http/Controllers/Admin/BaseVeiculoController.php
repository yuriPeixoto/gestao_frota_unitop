<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BaseVeiculo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class BaseVeiculoController extends Controller
{
    /**
     * Buscar bases de veículo (autocomplete)
     */
    public function search(Request $request)
    {
        $term = strtolower($request->get('term'));

        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        // Cache para melhorar performance
        $bases = Cache::remember('base_veiculo_search_' . $term, now()->addMinutes(30), function () use ($term) {
            return BaseVeiculo::select('id_base_veiculo', 'descricao_base')
                ->whereRaw('LOWER(descricao_base) LIKE ?', ["%{$term}%"])
                ->orderBy('descricao_base')
                ->limit(30)
                ->get()
                ->map(function ($b) {
                    return [
                        'label' => $b->descricao_base,
                        'value' => $b->id_base_veiculo
                    ];
                })->toArray();
        });

        return response()->json($bases);
    }

    /**
     * Buscar uma base de veículo pelo ID
     */
    public function getById($id)
    {
        $base = BaseVeiculo::where('id_base_veiculo', $id)->first();

        if (!$base) {
            return response()->json([], 404);
        }

        return response()->json([
            'value' => $base->id_base_veiculo,
            'label' => $base->descricao_base,
        ]);
    }
}
