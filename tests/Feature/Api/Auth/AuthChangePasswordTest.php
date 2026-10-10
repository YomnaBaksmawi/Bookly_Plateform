<?php

use App\Http\Middleware\VerifyApiCsrfToken;
use App\Models\User;
use App\Modules\Auth\Actions\GenerateTokenAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([VerifyApiCsrfToken::class]);
    Cache::flush();
});

function authPassToken(User $user): string
{
    return (new GenerateTokenAction)->execute($user, 'auth_token');
}

it('changes the password and makes the new password work', function () {
    $user = User::factory()->create([
        'role' => 'customer',
        'status' => 'active',
        'password' => Hash::make('OldPass123'),
    ]);
    $token = authPassToken($user);

    $this->app['auth']->forgetGuards();

    $this->flushHeaders();

    $this->withToken($token)->postJson('/api/v1/auth/change-password', [
        'current_password' => 'OldPass123',
        'password' => 'NewPass456',
        'password_confirmation' => 'NewPass456',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Password changed successfully');

    $fresh = $user->fresh();
    $this->assertTrue(Hash::check('NewPass456', $fresh->password));
    $this->assertFalse(Hash::check('OldPass123', $fresh->password));
});

it('rejects changing the password with a wrong current password', function () {
    $user = User::factory()->create([
        'role' => 'customer',
        'status' => 'active',
        'password' => Hash::make('OldPass123'),
    ]);
    $token = authPassToken($user);

    $this->app['auth']->forgetGuards();

    $this->withToken($token)->postJson('/api/v1/auth/change-password', [
        'current_password' => 'NotThePassword',
        'password' => 'NewPass456',
        'password_confirmation' => 'NewPass456',
    ])
        ->assertStatus(422)
        ->assertJsonPath('error_code', 'INVALID_CURRENT_PASSWORD');
});

it('verifies an email through the signed verification link', function () {
    $user = User::factory()->unverified()->create([
        'role' => 'customer',
        'status' => 'active',
    ]);

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->getJson($url)
        ->assertOk()
        ->assertJsonPath('message', 'Email verified successfully');

    $this->assertNotNull($user->fresh()->email_verified_at);
});

it('rejects a verification link with a wrong email hash', function () {
    $user = User::factory()->unverified()->create([
        'role' => 'customer',
        'status' => 'active',
    ]);

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => 'wrong-hash',
    ]);

    $this->getJson($url)
        ->assertStatus(403)
        ->assertJsonPath('message', 'Invalid verification link');

    $this->assertNull($user->fresh()->email_verified_at);
});