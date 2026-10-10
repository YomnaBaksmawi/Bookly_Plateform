<?php

namespace App\Http\Middleware;

use App\Models\ApiCsrfToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiCsrfToken
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
         * Safe HTTP methods do not modify server state.
         */
        if (
            $request->isMethod('GET') ||
            $request->isMethod('HEAD') ||
            $request->isMethod('OPTIONS')
        ) {
            return $next($request);
        }

        /*
         * These endpoints do not require CSRF protection.
         *
         * Login/Register are called before authentication.
         * Webhook endpoints are authenticated using HMAC signature.
         */
        if (
            $request->is('api/v1/auth/login') ||
            $request->is('api/v1/auth/register') ||
            $request->is('api/v1/payments/webhook') ||
            $request->is('api/v1/payments/webhook/test-signature')
        ) {
            return $next($request);
        }

        /*
         * All remaining state-changing requests
         * require an authenticated user.
         */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'error_code' => 'UNAUTHENTICATED',
                'errors' => null,
            ], 401);
        }

        $providedToken = $request->header('X-CSRF-TOKEN');

        if (! $providedToken) {
            return response()->json([
                'success' => false,
                'message' => 'CSRF token is required.',
                'error_code' => 'MISSING_CSRF_TOKEN',
                'errors' => null,
            ], 419);
        }

        $csrfToken = ApiCsrfToken::where(
            'user_id',
            $user->id
        )->first();

        if (
            ! $csrfToken ||
            $csrfToken->expires_at->isPast() ||
            ! hash_equals(
                $csrfToken->token_hash,
                hash('sha256', $providedToken)
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired CSRF token.',
                'error_code' => 'INVALID_CSRF_TOKEN',
                'errors' => null,
            ], 419);
        }

        return $next($request);
    }
}