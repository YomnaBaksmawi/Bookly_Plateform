<?php

namespace App\Modules\Admin\Actions;

use App\Models\User;
use App\Modules\AuditLog\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteUserAction
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {
    }

    public function execute(User $user): void
    {
        if ($user->is(Auth::user())) {
            abort(403, 'You cannot delete your own account.');
        }

        DB::transaction(function () use ($user) {

            $oldValues = $user->only([
                'name',
                'email',
                'role',
                'status',
            ]);

            $this->auditLogService->log(
                'delete',
                $user,
                $oldValues,
                null
            );

            $user->delete();
        });
    }
}