<?php

namespace App\Http\Middleware;

use App\Services\IdempotencyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    public function __construct(
        private IdempotencyService $idempotencyService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $existing = $this->idempotencyService->find($request);

        if ($existing) {
            return response()->json(
                $existing->response_body,
                $existing->response_code
            );
        }

        $response = $next($request);

        if ($response->isSuccessful()) {
            $this->idempotencyService->store(
                $request,
                $response->getStatusCode(),
                json_decode($response->getContent(), true)
            );
        }

        return $response;
    }
}