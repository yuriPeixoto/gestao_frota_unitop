<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\User;
use App\Models\VFilial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FilialController extends Controller
{
    /**
     * Busca filiais do usuário por email
     */
    public function getFilialsByEmail(Request $request): JsonResponse
    {
        try {
            $email = $request->query('email');

            if (! $email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email é obrigatório',
                    'filiais' => [],
                ]);
            }

            $user = User::where('email', $email)
                ->where('is_ativo', true)
                ->first();

            if (! $user) {

                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                    'filiais' => [],
                ]);
            }

            // Se for superusuário, tem acesso a todas as filiais
            if ($user->is_superuser) {
                $filiais = VFilial::select('id', 'name')->orderBy('name')->get()->toArray();

                Log::debug('FilialController.getFilialsByEmail: Superusuário '.$user->id.' - Encontradas '.count($filiais).' filiais.');

                return response()->json([
                    'success' => true,
                    'message' => 'Filiais encontradas',
                    'filiais' => $filiais,
                    'filial_principal_id' => $user->filial_id,
                    'user_name' => $user->name,
                ]);
            }

            // Buscar filiais do usuário (secundárias via pivot)
            $filiaisPivot = $user->filiais()
                ->select('filiais.id', 'filiais.name')
                ->get()
                ->toArray();

            // Incluir a filial principal salva em users.filial_id (se existir)
            $filiais = $filiaisPivot;
            if (! empty($user->filial_id)) {
                $principal = VFilial::query()->where('id', $user->filial_id)->first(['id', 'name']);
                if ($principal) {
                    $filiais[] = ['id' => $principal->id, 'name' => $principal->name];
                }
            }

            // Remover duplicatas por id
            $filiais = array_values(array_reduce($filiais, function ($carry, $item) {
                $carry[$item['id']] = $item;

                return $carry;
            }, []));

            Log::debug('FilialController.getFilialsByEmail: Encontradas '.count($filiais).' filiais');

            return response()->json([
                'success' => true,
                'message' => 'Filiais encontradas',
                'filiais' => $filiais,
                'filial_principal_id' => $user->filial_id, // Filial principal do usuário
                'user_name' => $user->name,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'filiais' => [],
            ], 500);
        }
    }

    /**
     * Busca filiais do usuário por matrícula
     */
    public function getFilialsByMatricula(Request $request): JsonResponse
    {
        try {
            $matricula = $request->query('matricula');

            if (! $matricula) {
                return response()->json([
                    'success' => false,
                    'message' => 'Matrícula é obrigatória',
                    'filiais' => [],
                ]);
            }

            // Garantir que a matrícula seja um número inteiro
            if (! is_numeric($matricula)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Matrícula deve ser um número',
                    'filiais' => [],
                ]);
            }

            $user = User::where('matricula', (int) $matricula)
                ->where('is_ativo', true)
                ->first();

            if (! $user) {

                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                    'filiais' => [],
                ]);
            }

            // Se for superusuário, tem acesso a todas as filiais
            if ($user->is_superuser) {
                $filiais = VFilial::select('id', 'name')->orderBy('name')->get()->toArray();

                Log::debug('FilialController.getFilialsByMatricula: Superusuário '.$user->id.' - Encontradas '.count($filiais).' filiais.');

                return response()->json([
                    'success' => true,
                    'message' => 'Filiais encontradas',
                    'filiais' => $filiais,
                    'filial_principal_id' => $user->filial_id,
                    'user_name' => $user->name,
                ]);
            }

            // Buscar filiais do usuário (secundárias via pivot)
            $filiaisPivot = $user->filiais()
                ->select('filiais.id', 'filiais.name')
                ->get()
                ->toArray();

            // Incluir a filial principal salva em users.filial_id (se existir)
            $filiais = $filiaisPivot;
            if (! empty($user->filial_id)) {
                $principal = VFilial::query()->where('id', $user->filial_id)->first(['id', 'name']);
                if ($principal) {
                    $filiais[] = ['id' => $principal->id, 'name' => $principal->name];
                }
            }

            // Remover duplicatas por id
            $filiais = array_values(array_reduce($filiais, function ($carry, $item) {
                $carry[$item['id']] = $item;

                return $carry;
            }, []));

            Log::debug('FilialController.getFilialsByMatricula: Encontradas '.count($filiais).' filiais para a matrícula.');

            return response()->json([
                'success' => true,
                'message' => 'Filiais encontradas',
                'filiais' => $filiais,
                'filial_principal_id' => $user->filial_id, // Filial principal do usuário
                'user_name' => $user->name, // Adicionar nome para feedback visual
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'filiais' => [],
            ], 500);
        }
    }
}
