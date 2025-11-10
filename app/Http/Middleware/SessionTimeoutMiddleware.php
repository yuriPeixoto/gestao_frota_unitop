<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // if (Auth::guest()) {
        //     return $next($request);
        // }

        // $lastActivity = Session::get('last_activity_timestamp');
        // $timeout = config('session.inactivity_timeout', 86400);

        // if ($lastActivity && (time() - $lastActivity) > $timeout) {
        //     Auth::logout();
        //     $request->session()->invalidate();
        //     $request->session()->regenerateToken();

        //     return redirect()->route('login')
        //         ->with('status', 'Sua sessão expirou devido à inatividade. Por favor, faça login novamente.');
        // }

        // Session::put('last_activity_timestamp', time());

        // return $next($request);
    }
}
