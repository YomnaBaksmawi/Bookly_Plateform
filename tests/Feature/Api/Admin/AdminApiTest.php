<?php

use App\Http\Middleware\VerifyApiCsrfToken;
use App\Models\User;
use App\Modules\AuditLog\Models\AuditLog;
use App\Modules\Auth\Actions\GenerateTokenAction;
use App\Modules\Booking\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Support\BookingFixtures;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([VerifyApiCsrfToken::class]);
    Cache::flush();
});

function adminAs($test, string $token)
{
    \Illuminate\Support\Facades\Auth::forgetGuards();

    return $test->withToken($token);
}

function adminTokenFor(?User $user = null): string
{
    $user = $user ?? User::factory()->create(['role' => 'admin', 'status' => 'active']);

    return (new GenerateTokenAction)->execute($user, 'auth_token');
}

it('blocks non-admins from the admin user list', function () {
    $customer = BookingFixtures::customer();
    $token = adminTokenFor($customer);

    adminAs($this, $token)->getJson('/api/v1/admin/users')
        ->assertStatus(403);
});

it('lets an admin list and filter users', function () {
    $adminToken = adminTokenFor();
    BookingFixtures::customer();
    BookingFixtures::customer();

    adminAs($this, $adminToken)->getJson('/api/v1/admin/users?role=customer')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.total', 2);
});

it('lets an admin view a user', function () {
    $adminToken = adminTokenFor();
    $customer = BookingFixtures::customer();

    adminAs($this, $adminToken)->getJson("/api/v1/admin/users/{$customer->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $customer->id);
});

it('lets an admin suspend and reactivate a user', function () {
    $adminToken = adminTokenFor();
    $customer = BookingFixtures::customer();

    adminAs($this, $adminToken)->postJson("/api/v1/admin/users/{$customer->id}/suspend")
        ->assertOk()
        ->assertJsonPath('data.status', 'suspended');

    expect(User::findOrFail($customer->id)->status)->toBe('suspended');

    adminAs($this, $adminToken)->postJson("/api/v1/admin/users/{$customer->id}/activate")
        ->assertOk()
        ->assertJsonPath('data.status', 'active');
});

it('lets an admin delete a user', function () {
    $adminToken = adminTokenFor();
    $customer = BookingFixtures::customer();

    adminAs($this, $adminToken)->deleteJson("/api/v1/admin/users/{$customer->id}")
        ->assertOk()
        ->assertJsonPath('message', 'User deleted successfully.');

    $this->assertSoftDeleted('users', ['id' => $customer->id]);
});

it('lists audit logs for an admin', function () {
    $adminToken = adminTokenFor();
    $user = BookingFixtures::customer();

    AuditLog::create([
        'user_id' => $user->id,
        'action' => 'login.success',
        'auditable_type' => App\Models\User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => ['ip' => '127.0.0.1'],
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]);

    $response = adminAs($this, $adminToken)->getJson('/api/v1/admin/audit-logs');
    $response->assertOk()->assertJsonPath('success', true);
    expect($response->json('meta.total'))->toBe(1);

    adminAs($this, $adminToken)->getJson('/api/v1/admin/audit-logs/'.AuditLog::first()->id)
        ->assertOk()
        ->assertJsonPath('data.action', 'login.success');
});

it('run reports for an admin', function () {
    $adminToken = adminTokenFor();
    [$provider, , , $service, $serviceLocation] = BookingFixtures::stack();
    $date = now()->addDays(3)->toDateString();
    $slot = BookingFixtures::slot($serviceLocation, $date, '10:00', 60);
    $slotTwo = BookingFixtures::slot($serviceLocation, $date, '11:00', 60);

    $customer = BookingFixtures::customer();
    Booking::create([
        'customer_id' => $customer->id,
        'service_location_id' => $serviceLocation->id,
        'booking_slot_id' => $slot->id,
        'starts_at' => now()->addDays(3),
        'ends_at' => now()->addDays(3)->addHour(),
        'status' => 'confirmed',
        'price_at_booking' => $service->price,
    ]);
    Booking::create([
        'customer_id' => $customer->id,
        'service_location_id' => $serviceLocation->id,
        'booking_slot_id' => $slotTwo->id,
        'starts_at' => now()->addDays(4),
        'ends_at' => now()->addDays(4)->addHour(),
        'status' => 'confirmed',
        'price_at_booking' => $service->price,
    ]);

    adminAs($this, $adminToken)->getJson('/api/v1/admin/reports/bookings')
        ->assertOk()->assertJsonPath('success', true);

    adminAs($this, $adminToken)->getJson('/api/v1/admin/reports/revenue')
        ->assertOk()->assertJsonPath('success', true);

    adminAs($this, $adminToken)->getJson('/api/v1/admin/reports/providers')
        ->assertOk()->assertJsonPath('success', true);

    adminAs($this, $adminToken)->getJson('/api/v1/admin/reports/services')
        ->assertOk()->assertJsonPath('success', true);

    $report = adminAs($this, $adminToken)->getJson('/api/v1/admin/reports/most-booked-services');
    $report->assertOk()->assertJsonPath('success', true);
    expect($report->json('data.0.service_id'))->toBe($service->id);
    expect($report->json('data.0.bookings_count'))->toBe(2);
});

it('lists notifications and tracks unread count', function () {
    $adminToken = adminTokenFor();
    $admin = User::where('role', 'admin')->firstOrFail();

    DB::table('notifications')->insert([
        'id' => (string) Str::uuid(),
        'type' => 'App\Notifications\BookingReminder',
        'notifiable_type' => App\Models\User::class,
        'notifiable_id' => $admin->id,
        'data' => json_encode(['title' => 'Booking reminder']),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('notifications')->insert([
        'id' => (string) Str::uuid(),
        'type' => 'App\Notifications\BookingReminder',
        'notifiable_type' => App\Models\User::class,
        'notifiable_id' => $admin->id,
        'data' => json_encode(['title' => 'Read one']),
        'read_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $index = adminAs($this, $adminToken)->getJson('/api/v1/admin/notifications');
    $index->assertOk()->assertJsonPath('success', true);
    expect($index->json('data.total'))->toBe(2);

    adminAs($this, $adminToken)->getJson('/api/v1/admin/notifications/unread-count')
        ->assertOk()
        ->assertJsonPath('data.count', 1);
});

it('marks a notification as read', function () {
    $adminToken = adminTokenFor();
    $admin = User::where('role', 'admin')->firstOrFail();
    $id = (string) Str::uuid();

    DB::table('notifications')->insert([
        'id' => $id,
        'type' => 'App\Notifications\BookingReminder',
        'notifiable_type' => App\Models\User::class,
        'notifiable_id' => $admin->id,
        'data' => json_encode(['title' => 'Hi']),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    adminAs($this, $adminToken)->patchJson("/api/v1/admin/notifications/{$id}/read")
        ->assertOk()
        ->assertJsonPath('message', 'Notification marked as read.');

    adminAs($this, $adminToken)->getJson('/api/v1/admin/notifications/unread-count')
        ->assertJsonPath('data.count', 0);
});

it('registers a device token for push notifications', function () {
    $adminToken = adminTokenFor();

    adminAs($this, $adminToken)->postJson('/api/v1/admin/device-tokens', [
        'token' => 'exponent-push-token-abc',
        'device_type' => 'android',
    ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.device_type', 'android');
});