<?php

namespace App\Modules\Admin\Actions;

use App\Models\User;
use App\Modules\AuditLog\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class CreateUserAction 
{
    public function __construct(private AuditLogService $auditLogService)
    {}
public function execute(array $data): User
{
    return DB::transaction(function () use ($data){
        $user=User::create([
                   'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'status' => $data['status'] ?? 'active',
        ]);
        $this->auditLogService->log('create',$user,null,$user->only(['id','name','email','role','status']));
        return $user->fresh();
    });
}
    }