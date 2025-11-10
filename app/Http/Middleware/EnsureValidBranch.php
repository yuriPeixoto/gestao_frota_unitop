<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidBranch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user->current_branch_id) {
            $allowedBranches = $user->getAllowedBranches();

            if ($allowedBranches->isEmpty()) {
                return redirect()->route('login')
                    ->with('error', 'Você não tem acesso a nenhuma filial.');
            }

            if ($allowedBranches->count() === 1) {
                $user->update(['current_branch_id' => $allowedBranches->first()->id]);
            } else {
                return redirect()->route('branch.select');
            }
        }

        return $next($request);
    }
}
