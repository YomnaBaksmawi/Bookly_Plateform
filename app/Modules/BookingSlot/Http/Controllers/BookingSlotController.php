<?php

namespace App\Modules\BookingSlot\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingSlotController extends Controller
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