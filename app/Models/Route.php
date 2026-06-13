<?php

namespace App\Models;

use App\Enums\RouteDirection;
use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property VehicleType $vehicle_type
 * @property string $fare
 */
class Route extends Model
{
    /** @use HasFactory<\Database\Factories\RouteFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'color',
        'vehicle_type',
        'fare',
        'frequency_minutes',
        'operating_hours',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'fare' => 'decimal:2',
            'frequency_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The stops served by this route, ordered along the route.
     *
     * @return BelongsToMany<Stop, $this, RouteStop>
     */
    public function stops(): BelongsToMany
    {
        return $this->belongsToMany(Stop::class)
            ->using(RouteStop::class)
            ->withPivot(['sequence', 'direction'])
            ->withTimestamps()
            ->orderByPivot('sequence');
    }

    /**
     * Get the stops for a single direction of travel.
     *
     * @return BelongsToMany<Stop, $this, RouteStop>
     */
    public function stopsForDirection(RouteDirection $direction): BelongsToMany
    {
        return $this->stops()->wherePivot('direction', $direction->value);
    }

    /**
     * @return HasMany<RoutePath, $this>
     */
    public function paths(): HasMany
    {
        return $this->hasMany(RoutePath::class);
    }
}
