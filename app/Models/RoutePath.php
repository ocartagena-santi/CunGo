<?php

namespace App\Models;

use App\Enums\RouteDirection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

/**
 * @property LineString $geometry
 */
class RoutePath extends Model
{
    /** @use HasFactory<\Database\Factories\RoutePathFactory> */
    use HasFactory;
    use HasSpatial;

    protected $fillable = [
        'route_id',
        'direction',
        'geometry',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'direction' => RouteDirection::class,
            'geometry' => LineString::class,
        ];
    }

    /**
     * @return BelongsTo<Route, $this>
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }
}
