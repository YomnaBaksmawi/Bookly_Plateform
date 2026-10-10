<?php

use App\Modules\BookingSlot\Services\BookingSlotGenerator;
use App\Modules\Location\Models\Location;
use App\Modules\PublicHoliday\Models\PublicHoliday;
use App\Modules\ScheduleException\Models\ScheduleException;
use App\Modules\WorkingHour\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Support\BookingFixtures;

uses(TestCase::class, RefreshDatabase::class);

function generatorDate(): string
{
    return now()->addDays(30)->toDateString();
}

function generatorDay(): string
{
    return strtolower(Carbon::parse(generatorDate(), 'UTC')->format('l'));
}

function workingHourFor(Location $location, string $start = '09:00', string $end = '17:00'): WorkingHour
{
    return WorkingHour::create([
        'location_id' => $location->id,
        'day_of_week' => generatorDay(),
        'start_time' => $start.':00',
        'end_time' => $end.':00',
        'is_active' => true,
    ]);
}

it('fills an open day with slots matching the service duration', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();
    workingHourFor($serviceLocation->location);

    $slots = (new BookingSlotGenerator)->generate($serviceLocation, generatorDate());

    expect(count($slots))->toBe(8);
    expect($slots[0]->start_time)->toBe('09:00:00');
    expect($slots[7]->end_time)->toBe('17:00:00');
    expect($slots[7]->status)->toBe('available');
});

it('respects multiple working intervals for the same day', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();
    workingHourFor($serviceLocation->location, '09:00', '11:00');
    workingHourFor($serviceLocation->location, '13:00', '15:00');

    $slots = (new BookingSlotGenerator)->generate($serviceLocation, generatorDate());

    expect(count($slots))->toBe(4);
});

it('drops the trailing slot that does not fully fit', function () {
    [, , $location, $service, $serviceLocation] = BookingFixtures::stack();
    $service->update(['duration' => 90]);
    workingHourFor($location);

    $slots = (new BookingSlotGenerator)->generate($serviceLocation, generatorDate());

    expect(count($slots))->toBe(5);
});

it('produces no slots when the day has no working hours', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();

    $slots = (new BookingSlotGenerator)->generate($serviceLocation, generatorDate());

    expect($slots)->toBe([]);
});

it('ignores inactive working hours', function () {
    [, , $location, , $serviceLocation] = BookingFixtures::stack();
    WorkingHour::create([
        'location_id' => $location->id,
        'day_of_week' => generatorDay(),
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'is_active' => false,
    ]);

    $slots = (new BookingSlotGenerator)->generate($serviceLocation, generatorDate());

    expect($slots)->toBe([]);
});

it('returns no slots on a schedule exception holiday', function () {
    [, , $location, , $serviceLocation] = BookingFixtures::stack();
    ScheduleException::create([
        'location_id' => $location->id,
        'date' => generatorDate(),
        'type' => 'holiday',
    ]);

    $slots = (new BookingSlotGenerator)->generate($serviceLocation, generatorDate());

    expect($slots)->toBe([]);
});

it('uses custom hours from a schedule exception instead of working hours', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();
    ScheduleException::create([
        'location_id' => $serviceLocation->location_id,
        'date' => generatorDate(),
        'type' => 'custom_hours',
        'start_time' => '12:00',
        'end_time' => '14:00',
    ]);

    $slots = (new BookingSlotGenerator)->generate($serviceLocation, generatorDate());

    expect(count($slots))->toBe(2);
    expect($slots[0]->start_time)->toBe('12:00:00');
});

it('respects national public holidays for the location country', function () {
    [, , $location, , $serviceLocation] = BookingFixtures::stack();
    workingHourFor($location);
    PublicHoliday::create([
        'name' => 'National Day',
        'date' => generatorDate(),
        'country' => 'EG',
    ]);

    $slots = (new BookingSlotGenerator)->generate($serviceLocation, generatorDate());

    expect($slots)->toBe([]);
});

it('ignores public holidays of an unrelated country', function () {
    [, , $location, , $serviceLocation] = BookingFixtures::stack();
    workingHourFor($location);
    PublicHoliday::create([
        'name' => 'Independence Day',
        'date' => generatorDate(),
        'country' => 'US',
    ]);

    $slots = (new BookingSlotGenerator)->generate($serviceLocation, generatorDate());

    expect(count($slots))->toBe(8);
});

it('respects public holidays tied to the specific location', function () {
    [, , $location, , $serviceLocation] = BookingFixtures::stack();
    workingHourFor($location);
    PublicHoliday::create([
        'name' => 'Studio Holiday',
        'date' => generatorDate(),
        'country' => 'EG',
        'location_id' => $location->id,
    ]);

    $slots = (new BookingSlotGenerator)->generate($serviceLocation, generatorDate());

    expect($slots)->toBe([]);
});

it('keeps existing booked slots when regenerating a day', function () {
    [, , , $service, $serviceLocation] = BookingFixtures::stack();
    workingHourFor($serviceLocation->location);
    $generator = new BookingSlotGenerator;

    $first = $generator->generate($serviceLocation, generatorDate());
    expect(count($first))->toBe(8);

    $first[3]->update(['status' => 'booked']);

    $regenerated = $generator->generate($serviceLocation, generatorDate());

    expect(count($regenerated))->toBe(8);
    expect($regenerated[3]->status)->toBe('booked');
    expect($regenerated[3]->id)->toBe($first[3]->id);
    expect(collect($regenerated)->where('status', 'booked')->count())->toBe(1);
});