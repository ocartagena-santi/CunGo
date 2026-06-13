<?php

namespace App\Data;

use App\Enums\RouteDirection;
use App\Models\Route;
use App\Models\Stop;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class DirectTripData extends Data
{
    /**
     * @param  array<int, StopData>  $segmentStops
     */
    public function __construct(
        public RouteData $route,
        public RouteDirection $direction,
        public StopData $boardStop,
        public StopData $alightStop,
        #[DataCollectionOf(StopData::class)]
        public array $segmentStops,
        public int $stopCount,
    ) {
    }

    /**
     * Build a trip from the ordered segment of stops between board and alight.
     *
     * @param  Collection<int, Stop>  $segment
     */
    public static function fromSegment(Route $route, RouteDirection $direction, Collection $segment): self
    {
        $stops = $segment->map(fn (Stop $stop): StopData => StopData::fromModel($stop))->values();

        $boardStop = $stops->first();
        $alightStop = $stops->last();

        if (! $boardStop instanceof StopData || ! $alightStop instanceof StopData) {
            throw new InvalidArgumentException('A trip segment must contain at least two stops.');
        }

        return new self(
            route: RouteData::fromModel($route),
            direction: $direction,
            boardStop: $boardStop,
            alightStop: $alightStop,
            segmentStops: $stops->all(),
            stopCount: $stops->count() - 1,
        );
    }
}
