<?php

namespace App\Modules\Admin\Actions;

use App\Models\User;
use App\Modules\AuditLog\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class ActivateUserAction
{
    public function __construct( private AuditLogService $auditLogService ) {
    }

    public function execute(User $user):User
    {
return DB::transaction(function() use ($user) {
$oldValues =['status' =>$user->status];
$user->update([ 'status'=>'active'
]);
$this->auditLogService->log(
    'activate',
    $user,
    $oldValues,
    ['status' =>'active']
);
return $user->fresh();
});
    }
        
}
