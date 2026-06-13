<?php

namespace Database\Factories;

use App\Enums\RouteDirection;
use App\Models\Route;
use App\Models\RoutePath;
use Illuminate\Database\Eloquent\Factories\Factory;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @extends Factory<RoutePath>
 */
class RoutePathFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'route_id' => Route::factory(),
            'direction' => RouteDirection::Ida,
            'geometry' => new LineString([
                new Point(21.16, -86.85, Srid::WGS84->value),
                new Point(21.15, -86.82, Srid::WGS84->value),
                new Point(21.14, -86.80, Srid::WGS84->value),
            ], Srid::WGS84->value),
        ];
    }
}
