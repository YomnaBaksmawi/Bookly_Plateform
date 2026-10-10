<?php

namespace App\Modules\Admin\Services;

use App\Modules\Payment\Models\Payment;
use Carbon\Carbon;

class RevenueReportService
{
    public function get(array $filters): array
    {
        $query = Payment::query();

        if (!empty($filters['from'])) {
            $query->where(
                'created_at',
                '>=',
                Carbon::parse($filters['from'])->startOfDay()
            );
        }

        if (!empty($filters['to'])) {
            $query->where(
                'created_at',
                '<=',
                Carbon::parse($filters['to'])->endOfDay()
            );
        }

        return [
            'total_revenue' => (clone $query)
                ->where('status', 'paid')
                ->sum('amount'),

            'paid_payments' => (clone $query)
                ->where('status', 'paid')
                ->count(),

            'pending_payments' => (clone $query)
                ->where('status', 'pending')
                ->count(),

            'failed_payments' => (clone $query)
                ->where('status', 'failed')
                ->count(),

            'refunded_payments' => (clone $query)
                ->where('status', 'refunded')
                ->count(),
        ];
    }
}