<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Modules\ServiceLocation\Models\ServiceLocation;
use App\Modules\Payment\Models\Payment;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Modules\BookingSlot\Models\BookingSlot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\BookingStatusLog\Models\BookingStatusLog;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
        use SoftDeletes;

    protected $fillable = [
    'customer_id',
    'service_location_id',
    'booking_slot_id',
    'starts_at',
    'ends_at',
    'status',
    'price_at_booking',
    'notes',
];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
          'price_at_booking' => 'decimal:2',
];
    public function serviceLocation(): BelongsTo
{
    return $this->belongsTo(ServiceLocation::class);
}

    public function bookingSlot(): BelongsTo
    {
        return $this->belongsTo(BookingSlot::class);
    }
    public function statusLogs(): HasMany
{
    return $this->hasMany(BookingStatusLog::class);
}

public function customer(): BelongsTo
{
    return $this->belongsTo(User::class, 'customer_id');
}
public function payment(): HasOne
{
    return $this->hasOne(Payment::class);
}
}