<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

/**
 * @property Point $location
 * @property-read RouteStop $pivot
 */
class Stop extends Model
{
    /** @use HasFactory<\Database\Factories\StopFactory> */
    use HasFactory;
    use HasSpatial;

    protected $fillable = [
        'name',
        'location',
        'landmark',
        'colonia',
        'is_landmark',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'location' => Point::class,
            'is_landmark' => 'boolean',
        ];
    }

    /**
     * The routes that serve this stop.
     *
     * @return BelongsToMany<Route, $this, RouteStop>
     */
    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(Route::class)
            ->using(RouteStop::class)
            ->withPivot(['sequence', 'direction'])
            ->withTimestamps();
    }
}
