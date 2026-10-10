<?php

namespace App\Modules\Admin\Services;

use App\Modules\Booking\Models\Booking;
use Carbon\Carbon;

class BookingReportService
{
    public function get(array $filters): array
{
    $query = Booking::query();

    if (!empty($filters['from'])) {
        $query->where(
            'starts_at',
            '>=',
            Carbon::parse($filters['from'])->startOfDay()
        );
    }

    if (!empty($filters['to'])) {
        $query->where(
            'starts_at',
            '<=',
            Carbon::parse($filters['to'])->endOfDay()
        );
    }

    $report = $query
        ->selectRaw('COUNT(*) as total')
        ->selectRaw("COUNT(*) FILTER (WHERE status = 'pending') as pending")
        ->selectRaw("COUNT(*) FILTER (WHERE status = 'confirmed') as confirmed")
        ->selectRaw("COUNT(*) FILTER (WHERE status = 'completed') as completed")
        ->selectRaw("COUNT(*) FILTER (WHERE status = 'cancelled') as cancelled")
        ->selectRaw("COUNT(*) FILTER (WHERE status = 'no_show') as no_show")
        ->first();

    $total = (int) $report->total;
    $cancelled = (int) $report->cancelled;

    return [
        'total' => $total,
        'pending' => (int) $report->pending,
        'confirmed' => (int) $report->confirmed,
        'completed' => (int) $report->completed,
        'cancelled' => $cancelled,
        'no_show' => (int) $report->no_show,
        'cancellation_rate' => $total > 0
            ? round(($cancelled / $total) * 100, 2)
            : 0,
    ];
}
}