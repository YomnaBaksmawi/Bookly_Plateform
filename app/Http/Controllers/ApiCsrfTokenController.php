<?php

namespace App\Http\Controllers;

use App\Models\ApiCsrfToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiCsrfTokenController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $user = request()->user();

        $token = Str::random(64);

        ApiCsrfToken::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addMinutes(120),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'csrf_token' => $token,
            ],
        ])->cookie(
            'XSRF-TOKEN',
            $token,
            120,
            '/',
            null,
            request()->isSecure(),
            false,
            false,
            'lax'
        );
    }
}