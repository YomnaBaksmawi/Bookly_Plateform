<?php

use App\Http\Middleware\VerifyApiCsrfToken;
use App\Models\User;
use App\Modules\Auth\Actions\GenerateTokenAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([VerifyApiCsrfToken::class]);
    Cache::flush();
});

function authApiToken(User $user): string
{
    return (new GenerateTokenAction)->execute($user, 'auth_token');
}

it('registers a customer and returns a usable bearer token', function () {
    $email = 'register-'.Str::uuid().'@example.com';

    $this->postJson('/api/v1/auth/register', [
        'name' => 'New Customer',
        'email' => $email,
        'password' => 'Secret123',
        'password_confirmation' => 'Secret123',
        'role' => 'customer',
    ])
        ->assertCreated()
        ->assertJsonPath('user.email', $email)
        ->assertJsonPath('user.role', 'customer')
        ->assertJsonPath('user.status', 'active');

    $this->assertDatabaseHas('users', [
        'email' => $email,
        'role' => 'customer',
        'status' => 'active',
    ]);
});

it('rejects a customer trying to register as admin', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Sneaky',
        'email' => 'sneaky-'.Str::uuid().'@example.com',
        'password' => 'Secret123',
        'password_confirmation' => 'Secret123',
        'role' => 'admin',
    ])
        ->assertStatus(422)
        ->assertJsonPath('error_code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['role']]);
});

it('rejects weak passwords during registration', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Weak Pass',
        'email' => 'weak-'.Str::uuid().'@example.com',
        'password' => '123456',
        'password_confirmation' => '123456',
        'role' => 'customer',
    ])
        ->assertStatus(422)
        ->assertJsonPath('error_code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['password']]);
});

it('rejects a duplicate email during registration', function () {
    $existing = User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Duplicate',
        'email' => $existing->email,
        'password' => 'Secret123',
        'password_confirmation' => 'Secret123',
        'role' => 'customer',
    ])
        ->assertStatus(422)
        ->assertJsonPath('error_code', 'VALIDATION_ERROR');
});

it('logs a user in and lets them read their profile', function () {
    $password = 'Secret123';
    $user = User::factory()->create([
        'role' => 'customer',
        'status' => 'active',
        'password' => Hash::make($password),
    ]);

    $login = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => $password,
    ]);

    $login->assertOk()->assertJsonPath('user.id', $user->id);
    $this->assertNotEmpty($login->json('access_token'));

    $this->app['auth']->forgetGuards();

    $this->withToken($login->json('access_token'))->getJson('/api/v1/auth/profile')
        ->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.email', $user->email);
});

it('rejects login with invalid credentials', function () {
    $user = User::factory()->create(['role' => 'customer', 'status' => 'active']);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'WrongPass123',
    ])
        ->assertStatus(401)
        ->assertJsonPath('error_code', 'INVALID_CREDENTIALS');
});

it('blocks a suspended user from logging in', function () {
    $user = User::factory()->create([
        'role' => 'customer',
        'status' => 'suspended',
        'password' => Hash::make('Secret123'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'Secret123',
    ])
        ->assertStatus(403)
        ->assertJsonPath('error_code', 'USER_SUSPENDED');
});

it('rejects unauthenticated access to the profile route', function () {
    $this->getJson('/api/v1/auth/profile')
        ->assertStatus(401)
        ->assertJsonPath('error_code', 'UNAUTHENTICATED');
});

it('lets a user update their profile name', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $token = authApiToken($user);

    $this->app['auth']->forgetGuards();

    $this->withToken($token)->putJson('/api/v1/auth/profile', [
        'name' => 'Updated Name',
    ])
        ->assertOk()
        ->assertJsonPath('user.name', 'Updated Name');
});

it('revokes the current token on logout', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $token = authApiToken($user);

    $this->app['auth']->forgetGuards();

    $this->withToken($token)->postJson('/api/v1/auth/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logged out successfully');

    $this->app['auth']->forgetGuards();

    $this->withToken($token)->getJson('/api/v1/auth/profile')
        ->assertStatus(401);
});

it('provides a csrf token for authenticated state-changing requests', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $token = authApiToken($user);

    $this->app['auth']->forgetGuards();

    $response = $this->withToken($token)->getJson('/api/v1/csrf-token');

    $response->assertOk()->assertJsonPath('success', true);
    $this->assertNotEmpty($response->json('data.csrf_token'));
});