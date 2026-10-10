<?php

namespace App\Modules\Booking\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $slot = $this->bookingSlot;
        $serviceLocation = $slot->serviceLocation;

        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'status' => $this->status,
            'price' => $this->price_at_booking,
            'date' => $slot->date->format('Y-m-d'),
            'start_time' => $slot->start_time,
            'end_time' => $slot->end_time,
            'service' => [
                'id' => $serviceLocation->service->id,
                'name' => $serviceLocation->service->name,
                'duration' => $serviceLocation->service->duration,
            ],
            'location' => [
                'id' => $serviceLocation->location->id,
                'name' => $serviceLocation->location->name,
                'address' => $serviceLocation->location->address,
                'city' => $serviceLocation->location->city,
                'timezone' => $serviceLocation->location->timezone,
            ],
        ];
    }
}