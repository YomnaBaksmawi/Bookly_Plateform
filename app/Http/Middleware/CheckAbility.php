<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAbility
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        if (! $request->user() || ! $request->user()->currentAccessToken()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $userAbilities = $request->user()->currentAccessToken()->abilities;

        foreach ($abilities as $ability) {
            if (! in_array($ability, $userAbilities)) {
                return response()->json([
                    'message' => "Missing required ability: {$ability}",
                ], 403);
            }
        }

        return $next($request);
    }
}
