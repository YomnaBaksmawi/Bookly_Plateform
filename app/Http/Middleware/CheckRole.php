<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = $request->user();

        // Check if user is active
        if (! $user->canPerformAction()) {
            return response()->json(['message' => 'Account is suspended or inactive.'], 403);
        }

        // Check if user has any of the required roles
        if (! empty($roles) && ! in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Insufficient permissions. Required roles: '.implode(', ', $roles),
            ], 403);
        }

        return $next($request);
    }
}
