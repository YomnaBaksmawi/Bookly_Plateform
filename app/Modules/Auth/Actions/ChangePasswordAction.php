<?php

namespace App\Modules\Auth\Actions;

use App\Models\User;
use App\Modules\Auth\Exceptions\InvalidCurrentPasswordException;
use Illuminate\Support\Facades\Hash;

class ChangePasswordAction
{
    public function execute( User $user, string $currentPassword, string $newPassword   ): void {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new InvalidCurrentPasswordException;
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}