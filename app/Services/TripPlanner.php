<?php

namespace App\Services;

use App\Data\DirectTripData;
use App\Enums\RouteDirection;
use App\Models\Route;
use App\Models\Stop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TripPlanner
{
    /**
     * Find direct (single-route) trips that connect the origin to the destination.
     *
     * A trip is valid when both stops belong to the same route and direction, and the
     * origin's sequence comes before the destination's. Transfers are out of scope for now.
     *
     * @return Collection<int, DirectTripData>
     */
    public function findDirectTrips(Stop $origin, Stop $destination): Collection
    {
        if ($origin->is($destination)) {
            return collect();
        }

        return Route::query()
            ->where('is_active', true)
            ->whereHas('stops', fn (Builder $query) => $query->whereKey($origin->getKey()))
            ->whereHas('stops', fn (Builder $query) => $query->whereKey($destination->getKey()))
            ->with('stops')
            ->get()
            ->flatMap(fn (Route $route): Collection => $this->tripsForRoute($route, $origin, $destination))
            ->values();
    }

    /**
     * Resolve any valid trips a single route offers in either direction.
     *
     * @return Collection<int, DirectTripData>
     */
    protected function tripsForRoute(Route $route, Stop $origin, Stop $destination): Collection
    {
        $trips = collect();

        foreach (RouteDirection::cases() as $direction) {
            $stops = $route->stops
                ->filter(fn (Stop $stop): bool => $stop->pivot->direction === $direction->value)
                ->sortBy(fn (Stop $stop): int => $stop->pivot->sequence)
                ->values();

            $originIndex = $stops->search(fn (Stop $stop): bool => $stop->is($origin));
            $destinationIndex = $stops->search(fn (Stop $stop): bool => $stop->is($destination));

            if ($originIndex === false || $destinationIndex === false || $originIndex >= $destinationIndex) {
                continue;
            }

            $segment = $stops->slice($originIndex, $destinationIndex - $originIndex + 1)->values();

            $trips->push(DirectTripData::fromSegment($route, $direction, $segment));
        }

        return $trips;
    }
}
