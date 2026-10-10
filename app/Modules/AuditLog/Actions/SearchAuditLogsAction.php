<?php

namespace App\Modules\AuditLog\Actions;

use App\Modules\AuditLog\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchAuditLogsAction
{
    public function execute(array $filters): LengthAwarePaginator
    {
        return AuditLog::query()
            ->with('user:id,name,email')

            ->when(
                !empty($filters['search']),
                function ($query) use ($filters) {
                    $search = $filters['search'];

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('action', 'ILIKE', "%{$search}%")
                            ->orWhere('auditable_type', 'ILIKE', "%{$search}%")
                            ->orWhere('ip_address', 'ILIKE', "%{$search}%");
                    });
                }
            )

            ->when(
                !empty($filters['action']),
                fn ($query) =>
                    $query->where('action', $filters['action'])
            )

            ->when(
                !empty($filters['auditable_type']),
                fn ($query) =>
                    $query->where(
                        'auditable_type',
                        $filters['auditable_type']
                    )
            )

            ->when(
                !empty($filters['auditable_id']),
                fn ($query) =>
                    $query->where(
                        'auditable_id',
                        $filters['auditable_id']
                    )
            )

            ->when(
                !empty($filters['user_id']),
                fn ($query) =>
                    $query->where(
                        'user_id',
                        $filters['user_id']
                    )
            )

            ->when(
                !empty($filters['from']),
                fn ($query) =>
                    $query->where(
                        'created_at',
                        '>=',
                        $filters['from']
                    )
            )

            ->when(
                !empty($filters['to']),
                fn ($query) =>
                    $query->where(
                        'created_at',
                        '<=',
                        $filters['to']
                    )
            )

            ->latest('created_at')
            ->paginate($filters['per_page'] ?? 20);
    }
}