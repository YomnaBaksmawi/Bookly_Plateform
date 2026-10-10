<?php

namespace App\Modules\Availability\Services;

use App\Modules\BookingSlot\Models\BookingSlot;
use App\Modules\BookingSlot\Services\BookingSlotGenerator;
use App\Modules\Location\Models\Location;
use App\Modules\Service\Models\Service;
use App\Modules\ServiceLocation\Models\ServiceLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AvailabilityService
{
    public function __construct(
        private BookingSlotGenerator $slotGenerator
    ) {
    }

    public function getSlots(Service $service, Location $location, string $date): array
    {
        $cacheKey = "availability:{$service->id}:{$location->id}:{$date}";

        return Cache::remember($cacheKey, 300, fn () => $this->calculateSlots($service, $location, $date));
    }

    private function calculateSlots(Service $service, Location $location, string $date): array
    {
        $serviceLocation = ServiceLocation::query()
            ->where('service_id', $service->id)
            ->where('location_id', $location->id)
            ->first();

        if (! $serviceLocation) {
            return [];
        }

        $this->slotGenerator->generate($serviceLocation, $date);

        return BookingSlot::query()
            ->where('service_location_id', $serviceLocation->id)
            ->whereDate('date', $date)
            ->where('status', 'available')
            ->orderBy('start_time')
            ->get()
            ->map(function (BookingSlot $slot) use ($date, $location) {
                $start = Carbon::parse("$date {$slot->start_time}", $location->timezone);
                $end = Carbon::parse("$date {$slot->end_time}", $location->timezone);

                return [
                    'id' => $slot->id,
                    'start_at' => $start->toISOString(),
                    'end_at' => $end->toISOString(),
                ];
            })
            ->toArray();
    }
}