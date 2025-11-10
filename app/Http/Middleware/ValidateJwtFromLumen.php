<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Middleware para validar JWT tokens vindos do backend Lumen (Checklist)
 *
 * Este middleware permite que o app mobile use o mesmo token JWT
 * para acessar recursos no Gestão Frota (Laravel)
 */
class ValidateJwtFromLumen
{
    /**
     * Chave secreta do JWT (DEVE ser a mesma do Lumen!)
     */
    private function getJwtSecret()
    {
        return env('LUMEN_JWT_SECRET', env('APP_KEY'));
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            Log::info('[JWT Middleware] ===== INICIANDO VALIDAÇÃO =====', [
                'url' => $request->url(),
                'method' => $request->method(),
            ]);

            // Obter token do header Authorization
            $authHeader = $request->header('Authorization');

            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                Log::warning('[JWT Middleware] ❌ Token não fornecido ou formato inválido', [
                    'has_header' => $authHeader ? 'sim' : 'não',
                    'header_preview' => $authHeader ? substr($authHeader, 0, 20) . '...' : null
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Token não fornecido'
                ], 401);
            }

            $token = substr($authHeader, 7); // Remove "Bearer "

            Log::info('[JWT Middleware] ✅ Token extraído do header', [
                'token_length' => strlen($token),
                'token_preview' => substr($token, 0, 30) . '...' . substr($token, -10)
            ]);

            // Decodificar JWT
            $payload = $this->decodeJwt($token);

            if (!$payload) {
                Log::warning('[JWT Middleware] ❌ Falha ao decodificar token');

                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido ou expirado'
                ], 401);
            }

            Log::info('[JWT Middleware] ✅ Token decodificado com sucesso', [
                'user_id' => $payload->user_id ?? 'N/A',
                'email' => $payload->email ?? 'N/A',
                'exp' => $payload->exp ?? 'N/A',
                'type' => $payload->type ?? 'N/A'
            ]);

            // Buscar usuário no banco
            $user = DB::table('users')
                ->where('id', $payload->user_id)
                ->where('is_ativo', true)
                ->first();

            if (!$user) {
                Log::warning('[JWT Middleware] ❌ Usuário não encontrado ou inativo', [
                    'user_id' => $payload->user_id
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado ou inativo'
                ], 401);
            }

            Log::info('[JWT Middleware] ✅ Usuário encontrado', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name
            ]);

            // Criar objeto de usuário mockado para o Laravel
            $userModel = (object) [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'departamento_id' => $user->departamento_id,
                'filial_id' => $user->filial_id,
                'pessoal_id' => $user->pessoal_id,
            ];

            // Adicionar usuário ao request para uso nos controllers
            $request->attributes->set('jwt_user', $userModel);
            $request->attributes->set('jwt_user_id', $user->id);

            Log::info('[JWT Middleware] Token validado', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return $next($request);

        } catch (\Exception $e) {
            Log::error('[JWT Middleware] Erro ao validar token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao validar token: ' . $e->getMessage()
            ], 401);
        }
    }

    /**
     * Decodificar e validar JWT
     */
    private function decodeJwt($token)
    {
        try {
            Log::info('[JWT Middleware] 🔍 Iniciando decodificação do JWT');

            // Separar partes do JWT
            $parts = explode('.', $token);

            if (count($parts) !== 3) {
                Log::warning('[JWT Middleware] ❌ JWT malformado - não tem 3 partes', [
                    'parts_count' => count($parts)
                ]);
                return null;
            }

            [$header, $payload, $signature] = $parts;

            Log::info('[JWT Middleware] ✅ JWT dividido em 3 partes');

            // Verificar assinatura
            $secret = $this->getJwtSecret();
            Log::info('[JWT Middleware] 🔑 Secret obtida', [
                'secret_length' => strlen($secret),
                'secret_preview' => substr($secret, 0, 10) . '...'
            ]);

            $validSignature = hash_hmac(
                'sha256',
                $header . '.' . $payload,
                $secret,
                true
            );

            $validSignature = $this->base64UrlEncode($validSignature);

            if ($signature !== $validSignature) {
                Log::warning('[JWT Middleware] ❌ Assinatura inválida', [
                    'signature_received' => substr($signature, 0, 20) . '...',
                    'signature_expected' => substr($validSignature, 0, 20) . '...'
                ]);
                return null;
            }

            Log::info('[JWT Middleware] ✅ Assinatura válida');

            // Decodificar payload
            $payloadDecoded = $this->base64UrlDecode($payload);
            Log::info('[JWT Middleware] 📦 Payload decodificado (base64)', [
                'payload_length' => strlen($payloadDecoded),
                'payload_preview' => substr($payloadDecoded, 0, 100)
            ]);

            $payloadData = json_decode($payloadDecoded);

            if (!$payloadData) {
                Log::warning('[JWT Middleware] ❌ Falha ao decodificar JSON do payload', [
                    'json_error' => json_last_error_msg()
                ]);
                return null;
            }

            Log::info('[JWT Middleware] ✅ Payload JSON decodificado', [
                'payload_keys' => array_keys((array) $payloadData)
            ]);

            // Verificar expiração
            if (isset($payloadData->exp) && $payloadData->exp < time()) {
                Log::warning('[JWT Middleware] ❌ Token expirado', [
                    'exp' => $payloadData->exp,
                    'exp_date' => date('Y-m-d H:i:s', $payloadData->exp),
                    'now' => time(),
                    'now_date' => date('Y-m-d H:i:s')
                ]);
                return null;
            }

            Log::info('[JWT Middleware] ✅ Token não expirado', [
                'expires_in' => isset($payloadData->exp) ? ($payloadData->exp - time()) . ' segundos' : 'N/A'
            ]);

            // Verificar tipo de token
            if (isset($payloadData->type) && $payloadData->type !== 'access') {
                Log::warning('[JWT Middleware] ❌ Tipo de token inválido', [
                    'type' => $payloadData->type,
                    'expected' => 'access'
                ]);
                return null;
            }

            Log::info('[JWT Middleware] ✅ Tipo de token válido');

            return $payloadData;

        } catch (\Exception $e) {
            Log::error('[JWT Middleware] Erro ao decodificar JWT', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Base64 URL-safe encode
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL-safe decode
     */
    private function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Obter usuário autenticado via JWT do request
     */
    public static function getJwtUser(Request $request)
    {
        return $request->attributes->get('jwt_user');
    }

    /**
     * Obter ID do usuário autenticado via JWT do request
     */
    public static function getJwtUserId(Request $request)
    {
        return $request->attributes->get('jwt_user_id');
    }
}
