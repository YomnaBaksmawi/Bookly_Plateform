<?php

namespace App\Modules\Service\Queries;

use App\Modules\Service\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchServicesQuery
{
    public function execute(  string $search, ?int $locationId = null ): LengthAwarePaginator {
        $query = Service::query()
            ->where('status', 'active')
            ->where(function ($query) use ($search) {
                $query->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%");
            });

        if ($locationId !== null) {
            $query->whereHas('locations', function ($query) use ($locationId) {
                $query->where('locations.id', $locationId);
            });
        }

        return $query
            ->with('provider')
            ->paginate(20);
    }
}