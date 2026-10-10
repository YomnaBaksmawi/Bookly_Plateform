<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\Actions\CreateUserAction;
use App\Modules\Auth\Actions\GenerateTokenAction;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Exceptions\UserSuspendedException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Modules\Auth\Actions\ChangePasswordAction;
class AuthService
{
    public function __construct(
        private readonly CreateUserAction $createUserAction,
        private readonly GenerateTokenAction $generateTokenAction,
        private readonly ChangePasswordAction $changePasswordAction
    ) {}

    public function register(array $data): array
    {
        $user = $this->createUserAction->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'status' => UserStatus::ACTIVE->value,
        ]);
$user->sendEmailVerificationNotification();
        $token = $this->generateTokenAction->execute($user, 'auth_token');

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $credentials): array
    {
        if (! Auth::attempt($credentials)) {
            throw new InvalidCredentialsException;
        }

        $user = User::where('email', $credentials['email'])->first();

        if (! $user->canPerformAction()) {
            throw new UserSuspendedException;
        }

        $token = $this->generateTokenAction->execute($user, 'auth_token');

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function updateProfile(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $user->fresh();
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }
    public function changePassword(User $user,array $data): void {
    $this->changePasswordAction->execute(
        $user,
        $data['current_password'],
        $data['password'] );
}
}
