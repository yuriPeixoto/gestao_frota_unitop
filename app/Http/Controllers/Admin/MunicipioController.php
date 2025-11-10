<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Municipio;
use Illuminate\Support\Facades\Cache;

class MunicipioController extends Controller
{
    /**
     * Busca municípios para o smart-select
     */
    public function search(Request $request)
    {
        $term = strtolower($request->get('term'));

        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        $municipios = Cache::remember('municipio_search_' . $term, now()->addMinutes(30), function () use ($term) {
            return Municipio::whereRaw('LOWER(nome_municipio) LIKE ?', ["%{$term}%"])
                ->orderBy('nome_municipio')
                ->limit(30)
                ->get(['id_municipio as value', 'nome_municipio as label']);
        });

        return response()->json($municipios);
    }

    /**
     * Busca um município específico pelo ID
     */
    public function single($id)
    {
        $municipio = Municipio::select('id_municipio as value', 'nome_municipio as label')
            ->findOrFail($id);

        return response()->json($municipio);
    }
}
