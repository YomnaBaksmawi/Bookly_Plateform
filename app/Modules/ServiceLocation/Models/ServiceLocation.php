<?php

namespace App\Modules\ServiceLocation\Models;

use App\Modules\Location\Models\Location;
use App\Modules\Service\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\BookingSlot\Models\BookingSlot;

class ServiceLocation extends Model
{
    protected $table = 'service_locations';

    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'location_id',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function bookingSlots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }
}