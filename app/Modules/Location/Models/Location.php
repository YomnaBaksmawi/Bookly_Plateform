<?php

namespace App\Modules\Location\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Service\Models\Service;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Modules\WorkingHour\Models\WorkingHour;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\PublicHoliday\Models\PublicHoliday;
use App\Modules\ScheduleException\Models\ScheduleException;
use App\Modules\Location\Policies\LocationPolicy;
class Location extends Model
{
    protected static string $policy = LocationPolicy::class;
    use SoftDeletes;

    protected $fillable = [
        'provider_id',
        'name',
        'address',
        'city',
        'country',
         'timezone',
        'lat',
        'lng',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
    public function services(): BelongsToMany
{
    return $this->belongsToMany(  Service::class,  'service_locations', 'location_id', 'service_id'  );
}
public function workingHours(): HasMany
{
    return $this->hasMany(WorkingHour::class);
}
public function scheduleExceptions(): HasMany
{
    return $this->hasMany(ScheduleException::class);
}
public function publicHolidays(): HasMany
{
    return $this->hasMany(PublicHoliday::class);
}
}