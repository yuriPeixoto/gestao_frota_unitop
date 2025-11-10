<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RouteLoggerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName() ?? 'unnamed';

        Log::info("Route {$routeName} accessed", [
            'user' => auth()->user()?->only(['id', 'name']),
            'permissions' => auth()->user()?->permissions->pluck('slug'),
            'url' => $request->url(),
            'method' => $request->method(),
            'route_parameters' => $request->route()?->parameters(),
            'input' => $request->all()
        ]);

        $response = $next($request);

        Log::info("Route {$routeName} completed", [
            'status' => $response->status(),
            'content_type' => $response->headers->get('Content-Type')
        ]);

        return $response;
    }
}
