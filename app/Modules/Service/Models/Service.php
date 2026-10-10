<?php

namespace App\Modules\Service\Models;

use App\Models\User;
use App\Modules\Category\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Location\Models\Location;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'provider_id',
        'category_id',
        'name',
        'description',
        'duration',
        'price',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function locations(): BelongsToMany
{
    return $this->belongsToMany(  Location::class, 'service_locations', 'service_id', 'location_id'  );
}
}