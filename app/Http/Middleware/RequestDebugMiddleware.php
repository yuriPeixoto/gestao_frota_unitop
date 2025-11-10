<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestDebugMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('Request Debug', [
            'url' => $request->url(),
            'method' => $request->method(),
            'route' => $request->route()?->getName(),
            'user_id' => Auth::id(),
            'middleware' => $request->route()?->middleware(),
        ]);

        $response = $next($request);

        Log::info('Response Debug', [
            'status' => $response->status(),
            'content_type' => $response->headers->get('Content-Type'),
        ]);

        return $response;
    }
}
