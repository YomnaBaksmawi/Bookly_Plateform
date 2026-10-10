<?php

namespace App\Services;

use App\Models\IdempotencyKey;
use Illuminate\Http\Request;

class IdempotencyService
{
    public function find(Request $request): ?IdempotencyKey
    {
        $key = $request->header('Idempotency-Key');

        if (! $key) {
            return null;
        }

        return IdempotencyKey::query()
            ->where('key', $key)
            ->where('user_id', $request->user()->id)
            ->where('request_path', $request->path())
            ->where('expires_at', '>', now())
            ->first();
    }

    public function store(Request $request, int $statusCode, array $response): void
    {
        $key = $request->header('Idempotency-Key');

        if (! $key) {
            return;
        }

        IdempotencyKey::create([
            'key' => $key,
            'user_id' => $request->user()->id,
            'request_path' => $request->path(),
            'response_code' => $statusCode,
            'response_body' => $response,
            'expires_at' => now()->addHours(24),
        ]);
    }
}