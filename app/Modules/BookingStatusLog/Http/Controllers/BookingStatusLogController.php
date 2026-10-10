<?php

namespace App\Modules\BookingStatusLog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingStatusLogController extends Controller
{
    public function __construct()
    {
        // Add middleware if needed
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => []
        ]);
    }
}