<?php

namespace App\Modules\Availability\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class AvailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'service' => [
                'id' => $this['service']->id,
                'name' => $this['service']->name,
                'duration' => $this['service']->duration,
                'price' => $this['service']->price,
            ],

            'location' => [
                'id' => $this['location']->id,
                'name' => $this['location']->name,
                'address' => $this['location']->address,
                'city' => $this['location']->city,
                'timezone' => $this['location']->timezone,
            ],

            'date' => $this['date'],

    'slots' => collect($this['slots'])->map(fn ($slot) => [
    'id' => $slot['id'],
    'start_at' => Carbon::parse($slot['start_at'])->format('Y-m-d H:i'),
    'end_at' => Carbon::parse($slot['end_at'])->format('Y-m-d H:i'),
])
    ->values()
    ->all(),];
    }
}