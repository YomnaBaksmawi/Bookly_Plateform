<?php

namespace App\Modules\AuditLog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AuditLog\Actions\SearchAuditLogsAction;
use App\Modules\AuditLog\Models\AuditLog;
use App\Modules\AuditLog\Http\Requests\AuditLogIndexRequest;
use App\Modules\AuditLog\Http\Resources\AuditLogResource;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function index( AuditLogIndexRequest $request,
        SearchAuditLogsAction $action
    ): JsonResponse {
        $logs = $action->execute(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Audit logs retrieved successfully.',
            'data' => AuditLogResource::collection($logs),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    public function show(AuditLog $auditLog): JsonResponse
    {
        $auditLog->load('user:id,name,email');

        return response()->json([
            'success' => true,
            'message' => 'Audit log retrieved successfully.',
            'data' => new AuditLogResource($auditLog),
        ]);
    }
}