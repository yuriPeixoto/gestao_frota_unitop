<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Motorista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MotoristaController extends Controller
{
    public function search(Request $request)
    {
        $term = strtolower($request->get('term'));

        $motoristas = Cache::remember('motoristas_search_' . $term, now()->addMinutes(30), function () use ($term) {
            return Motorista::whereRaw('LOWER(nome) LIKE ?', ["%{$term}%"])
                ->orderBy('nome')
                ->limit(30)
                ->get(['idobtermotorista as value', 'nome as label']);
        });

        return response()->json($motoristas);
    }

    public function getById($id)
    {
        // Cache para melhorar performance
        $motorista = Cache::remember('motorista_' . $id, now()->addHours(24), function () use ($id) {
            return Motorista::findOrFail($id);
        });

        return response()->json([
            'value' => $motorista->idobtermotorista,
            'label' => $motorista->nome,
        ]);
    }
}
