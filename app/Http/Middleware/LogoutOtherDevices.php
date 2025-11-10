<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogoutOtherDevices
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rotas que não devem executar o middleware
        $skipRoutes = ['/', 'login', 'logout', 'dashboard/current-time'];
        $isSkipRoute = false;

        foreach ($skipRoutes as $route) {
            if ($request->is($route)) {
                $isSkipRoute = true;
                break;
            }
        }

        // Para rotas de autenticação (login/logout), apenas passar adiante sem executar a lógica do middleware
        if ($isSkipRoute || $request->isMethod('POST') && $request->is('login')) {
            return $next($request);
        }

        // Só executar a lógica se o usuário estiver autenticado
        if (Auth::check()) {
            try {
                $currentSessionId = session()->getId();

                // Verificar se o session ID não está vazio
                if (empty($currentSessionId)) {
                    return $next($request);
                }

                // Aguardar um pequeno delay após login para evitar conflito
                if ($request->session()->has('just_logged_in')) {
                    $request->session()->forget('just_logged_in');
                    return $next($request);
                }

                // Remover outras sessões de forma mais segura
                $deleted = DB::connection('pgsql')
                    ->table('sessions')
                    ->where('user_id', Auth::id())
                    ->where('id', '!=', $currentSessionId)
                    ->where('last_activity', '<', time() - 300) // Apenas sessões inativas há mais de 5 minutos
                    ->delete();

                if ($deleted > 0) {
                    Log::debug('LogoutOtherDevices: ' . $deleted . ' sessões antigas removidas');
                }
            } catch (\Exception $e) {
                // Log error but don't break the request
                Log::error('LogoutOtherDevices middleware error: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}
