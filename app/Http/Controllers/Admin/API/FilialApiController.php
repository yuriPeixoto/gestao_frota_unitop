<?php

namespace App\Http\Controllers\Admin\API;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Departamento;
use App\Models\VFilial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FilialApiController extends Controller
{
    /**
     * Retorna uma filial específica pelo ID
     * 
     * @param int $id ID da filial
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSingle($id)
    {
        try {
            // Usar cache para melhorar performance
            $filial = Cache::remember("filial_{$id}", now()->addDay(), function () use ($id) {
                return VFilial::where('id', $id)
                    ->first(['id as value', 'name as label']);
            });

            if (!$filial) {
                return response()->json(['error' => 'Filial não encontrada'], 404);
            }

            return response()->json($filial);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar filial:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Erro ao buscar filial'], 500);
        }
    }
}
