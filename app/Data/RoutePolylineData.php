<?php

namespace App\Data;

use App\Enums\RouteDirection;
use App\Models\Route;
use App\Models\RoutePath;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class RoutePolylineData extends Data
{
    /**
     * @param  array<int, LatLngData>  $path
     */
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public string $color,
        #[DataCollectionOf(LatLngData::class)]
        public array $path,
    ) {
    }

    public static function fromModel(Route $route): self
    {
        $route->loadMissing('paths');

        $path = $route->paths->firstWhere('direction', RouteDirection::Ida);

        $coordinates = $path instanceof RoutePath
            ? $path->geometry->getGeometries()
                ->map(fn (Point $point): LatLngData => new LatLngData($point->latitude, $point->longitude))
                ->all()
            : [];

        return new self(
            id: $route->id,
            code: $route->code,
            name: $route->name,
            color: $route->color,
            path: $coordinates,
        );
    }
}
