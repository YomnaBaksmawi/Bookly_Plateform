<?php

namespace App\Modules\Auth\Actions;

use App\Models\User;
use App\Modules\Auth\Enums\UserRole;

class GenerateTokenAction
{
    public function execute(User $user, string $tokenName): string
    {
        $abilities = $this->getTokenAbilities($user->role);

        return $user->createToken($tokenName, $abilities)->plainTextToken;
    }

    /**
     * Get token abilities based on user role
     */
    private function getTokenAbilities(string $role): array
    {
        return match ($role) {
            UserRole::ADMIN->value => [
                'admin:read',
                'admin:write',
                'provider:read',
                'provider:write',
                'customer:read',
                'customer:write',
                'booking:read',
                'booking:write',
                'booking:cancel',
                'service:read',
                'service:write',
                'location:read',
                'location:write',
                'working_hour:write',
                 'location:read',
                 'working_hour:read',
                 'location:write',
                'schedule_exception:read',
                'schedule_exception:write',
                 'public_holiday:read',
                'public_holiday:write' 
            ],
            UserRole::PROVIDER->value => [
                'provider:read',
                'provider:write',
                'booking:read',
                'booking:write',
                'booking:cancel',
                'service:read',
                'service:write',
                'location:read',
                'location:write',
                'working_hour:write',
                'working_hour:read',
                'schedule_exception:read',
               'schedule_exception:write',
                'public_holiday:read',
                'public_holiday:write'           
 ],
            UserRole::CUSTOMER->value => [
                'customer:read',
                'customer:write',
                'booking:read',
                'booking:write',
                'service:read',
                'location:read',
                'working_hour:read',
                'schedule_exception:read',
                'public_holiday:read',
                'payment:write'
            ],
            default => ['customer:read', 'customer:write'],
        };
    }
}
