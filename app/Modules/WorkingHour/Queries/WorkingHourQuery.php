<?php
namespace App\Modules\WorkingHour\Queries;

use App\Models\User;
use App\Modules\WorkingHour\Models\WorkingHour;

class WorkingHourQuery
{
    public function index(User $user)
    {
        return WorkingHour::query()
            ->with('location')
            ->when(
                $user->role !== 'admin',
                fn ($query) => $query->whereHas(
                    'location',
                    fn ($q) => $q->where('provider_id', $user->id)
                )
            )
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }
    public function forLocation(int $locationId)
    {
        return WorkingHour::query()
            ->where('location_id', $locationId)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }
    public function forService(int $serviceId)
{
    return WorkingHour::query()
        ->with('location')
        ->whereHas('location', function ($query) use ($serviceId) {
            $query->whereHas('services', function ($query) use ($serviceId) {
                $query->where('services.id', $serviceId);
            });
        })
        ->where('is_active', true)
        ->orderBy('day_of_week')
        ->orderBy('start_time')
        ->get();
}
}

