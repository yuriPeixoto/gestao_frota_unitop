<?php

namespace App\Http\Controllers\Admin\API;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\VFilial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DepartamentoApiController extends Controller
{
    /**
     * Retorna um departamento específico pelo ID
     * 
     * @param int $id ID do departamento
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSingle($id)
    {
        try {
            // Usar cache para melhorar performance
            $departamento = Cache::remember("departamento_{$id}", now()->addDay(), function () use ($id) {
                return Departamento::where('id_departamento', $id)
                    ->first(['id_departamento as value', 'descricao_departamento as label']);
            });

            if (!$departamento) {
                return response()->json(['error' => 'Departamento não encontrado'], 404);
            }

            return response()->json($departamento);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar departamento:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Erro ao buscar departamento'], 500);
        }
    }
}
