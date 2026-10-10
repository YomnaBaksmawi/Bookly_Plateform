<?php

namespace App\Modules\BookingSlot\Services;

use App\Modules\BookingSlot\Models\BookingSlot;
use App\Modules\ServiceLocation\Models\ServiceLocation;
use Carbon\Carbon;

class BookingSlotGenerator
{
    public function generate(ServiceLocation $serviceLocation, string $date): array
    {
        $service = $serviceLocation->service;
        $location = $serviceLocation->location;
        $timezone = $location->timezone;

        $exception = $location->scheduleExceptions()
            ->whereDate('date', $date)
            ->first();

        if ($exception?->type === 'holiday') {
            return [];
        }

        if (! $exception && $this->isPublicHoliday($location, $date)) {
            return [];
        }

        $intervals = $this->getWorkingIntervals($location, $date, $exception);
        $slots = [];

        foreach ($intervals as $interval) {
            $start = Carbon::parse("$date {$interval['start_time']}", $timezone);
            $end = Carbon::parse("$date {$interval['end_time']}", $timezone);

            while ($start->copy()->addMinutes($service->duration)->lte($end)) {
                $slotEnd = $start->copy()->addMinutes($service->duration);

                $slot = BookingSlot::firstOrCreate(
                    [
                        'service_location_id' => $serviceLocation->id,
                        'date' => $date,
                        'start_time' => $start->format('H:i:s'),
                    ],
                    [
                        'end_time' => $slotEnd->format('H:i:s'),
                        'status' => 'available',
                    ]
                );

                $slots[] = $slot;
                $start = $slotEnd;
            }
        }

        return $slots;
    }

    private function getWorkingIntervals($location, string $date, $exception): array
    {
        if ($exception?->type === 'custom_hours') {
            return [[
                'start_time' => $exception->start_time,
                'end_time' => $exception->end_time,
            ]];
        }

        $dayOfWeek = strtolower(Carbon::parse($date, $location->timezone)->format('l'));

        return $location->workingHours()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get()
            ->map(fn ($workingHour) => [
                'start_time' => $workingHour->start_time,
                'end_time' => $workingHour->end_time,
            ])
            ->toArray();
    }

    private function isPublicHoliday($location, string $date): bool
    {
        return \App\Modules\PublicHoliday\Models\PublicHoliday::query()
            ->whereDate('date', $date)
            ->where(function ($query) use ($location) {
                $query
                    ->where(function ($query) use ($location) {
                        $query->whereNull('location_id')
                            ->where('country', $location->country);
                    })
                    ->orWhere('location_id', $location->id);
            })
            ->exists();
    }
}