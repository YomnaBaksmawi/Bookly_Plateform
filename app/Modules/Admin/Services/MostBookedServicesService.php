<?php

namespace App\Modules\Admin\Services;

use App\Modules\Service\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class MostBookedServicesService
{
  public function get(array $filters): array
{
    $cacheKey = 'admin:reports:most-booked-services:' . md5(
        json_encode($filters)
    );

    return Cache::tags([
        'admin-reports',
        'most-booked-services',
    ])->remember(
        $cacheKey,
        now()->addMinutes(5),
        fn () => $this->buildReport($filters)
    );
}
private function buildReport(array $filters): array
{
   $query = Service::query()
    ->select([
        'services.id',
        'services.name',
    ])
    ->selectRaw('COUNT(bookings.id) as bookings_count')
    ->join(
        'service_locations',
        'services.id',
        '=',
        'service_locations.service_id'
    )
    ->join(
        'bookings',
        'service_locations.id',
        '=',
        'bookings.service_location_id'
    )
    ->whereIn('bookings.status', [
        'confirmed',
        'completed',
    ])
    ->groupBy(
        'services.id',
        'services.name'
    )
    ->orderByDesc('bookings_count');

    if (!empty($filters['from'])) {
        $query->where(
            'bookings.starts_at',
            '>=',
            Carbon::parse($filters['from'])->startOfDay()
        );
    }

    if (!empty($filters['to'])) {
        $query->where(
            'bookings.starts_at',
            '<=',
            Carbon::parse($filters['to'])->endOfDay()
        );
    }

    return $query
        ->limit(10)
        ->get()
        ->map(fn ($service) => [
            'service_id' => $service->id,
            'service_name' => $service->name,
            'bookings_count' => (int) $service->bookings_count,
        ])
        ->all();
}
}