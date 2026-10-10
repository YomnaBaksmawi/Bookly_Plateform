<?php

namespace App\Modules\Admin\Actions;

use App\Models\User;
use App\Modules\AuditLog\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateUserAction
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {
    }

    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {

            $oldValues = $user->only([
                'name',
                'email',
                'role',
                'status',
            ]);

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);

            $newValues = $user->fresh()->only([
                'name',
                'email',
                'role',
                'status',
            ]);

            $this->auditLogService->log(
                'update',
                $user,
                $oldValues,
                $newValues
            );

            return $user->fresh();
        });
    }
}