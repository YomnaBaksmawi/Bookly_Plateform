<?php

namespace App\Modules\BookingSlot\Models;

use App\Modules\Booking\Models\Booking;
use App\Modules\ServiceLocation\Models\ServiceLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class BookingSlot extends Model
{
    protected $fillable = [
        'service_location_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'booking_id',
        'locked_at',
        'expires_at',
    ];

    protected $casts = [
        'date' => 'date',
        'locked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function serviceLocation(): BelongsTo
    {
        return $this->belongsTo(ServiceLocation::class);
    }

   public function booking(): HasOne
{
    return $this->hasOne(Booking::class);
}
}