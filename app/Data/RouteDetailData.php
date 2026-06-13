<?php

namespace App\Data;

use App\Enums\RouteDirection;
use App\Models\Route;
use App\Models\RoutePath;
use App\Models\Stop;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class RouteDetailData extends Data
{
    /**
     * @param  array<int, StopData>  $stopsIda
     * @param  array<int, StopData>  $stopsVuelta
     * @param  array<int, LatLngData>  $pathIda
     * @param  array<int, LatLngData>  $pathVuelta
     */
    public function __construct(
        public RouteData $route,
        #[DataCollectionOf(StopData::class)]
        public array $stopsIda,
        #[DataCollectionOf(StopData::class)]
        public array $stopsVuelta,
        #[DataCollectionOf(LatLngData::class)]
        public array $pathIda,
        #[DataCollectionOf(LatLngData::class)]
        public array $pathVuelta,
    ) {
    }

    public static function fromModel(Route $route): self
    {
        $route->loadMissing(['stops', 'paths']);

        return new self(
            route: RouteData::fromModel($route),
            stopsIda: self::stopsForDirection($route, RouteDirection::Ida),
            stopsVuelta: self::stopsForDirection($route, RouteDirection::Vuelta),
            pathIda: self::pathForDirection($route, RouteDirection::Ida),
            pathVuelta: self::pathForDirection($route, RouteDirection::Vuelta),
        );
    }

    /**
     * @return array<int, StopData>
     */
    protected static function stopsForDirection(Route $route, RouteDirection $direction): array
    {
        return $route->stops
            ->filter(fn (Stop $stop): bool => $stop->pivot->direction === $direction->value)
            ->sortBy(fn (Stop $stop): int => $stop->pivot->sequence)
            ->map(fn (Stop $stop): StopData => StopData::fromModel($stop))
            ->values()
            ->all();
    }

    /**
     * @return array<int, LatLngData>
     */
    protected static function pathForDirection(Route $route, RouteDirection $direction): array
    {
        $path = $route->paths->firstWhere('direction', $direction);

        if (! $path instanceof RoutePath) {
            return [];
        }

        return $path->geometry->getGeometries()
            ->map(fn (Point $point): LatLngData => new LatLngData($point->latitude, $point->longitude))
            ->all();
    }
}
