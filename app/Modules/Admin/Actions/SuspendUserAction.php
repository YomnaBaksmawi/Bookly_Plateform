<?php

namespace App\Modules\Admin\Actions;

use App\Models\User;
use App\Modules\AuditLog\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class SuspendUserAction
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {
    }

    public function execute(User $user): User
    {
        if ($user->is(Auth::user())) {
    abort(403, 'You cannot suspend your own account.');
}
        return DB::transaction(function () use ($user) {  
            
            $oldValues = ['status' => $user->status, ];

            $user->update([
                'status' => 'suspended',
            ]);

            $this->auditLogService->log(
                'suspend',
                $user,
                $oldValues,
                ['status' => 'suspended']
            );

            return $user->fresh();
        });
    }
}