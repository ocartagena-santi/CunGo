<?php

use App\Enums\RouteDirection;
use App\Models\Route;
use App\Models\Stop;
use App\Services\TripPlanner;

/**
 * Create a route serving the given stops in order for ida, and reversed for vuelta.
 *
 * @param  array<int, Stop>  $stops
 */
function routeServing(array $stops): Route
{
    $route = Route::factory()->create();

    foreach ($stops as $index => $stop) {
        $route->stops()->attach($stop->id, [
            'sequence' => $index + 1,
            'direction' => RouteDirection::Ida->value,
        ]);
    }

    foreach (array_reverse($stops) as $index => $stop) {
        $route->stops()->attach($stop->id, [
            'sequence' => $index + 1,
            'direction' => RouteDirection::Vuelta->value,
        ]);
    }

    return $route;
}

beforeEach(function () {
    $this->planner = app(TripPlanner::class);
    [$this->a, $this->b, $this->c] = Stop::factory()->count(3)->create();
});

test('it finds a direct trip in the ida direction', function () {
    routeServing([$this->a, $this->b, $this->c]);

    $trips = $this->planner->findDirectTrips($this->a, $this->c);

    expect($trips)->toHaveCount(1);

    $trip = $trips->first();
    expect($trip->direction)->toBe(RouteDirection::Ida)
        ->and($trip->boardStop->id)->toBe($this->a->id)
        ->and($trip->alightStop->id)->toBe($this->c->id)
        ->and($trip->stopCount)->toBe(2)
        ->and($trip->segmentStops)->toHaveCount(3);
});

test('it finds the reverse leg in the vuelta direction', function () {
    routeServing([$this->a, $this->b, $this->c]);

    $trips = $this->planner->findDirectTrips($this->c, $this->a);

    expect($trips)->toHaveCount(1)
        ->and($trips->first()->direction)->toBe(RouteDirection::Vuelta);
});

test('it returns no trip when stops are not connected', function () {
    routeServing([$this->a, $this->b]);
    $lonely = Stop::factory()->create();

    expect($this->planner->findDirectTrips($this->a, $lonely))->toBeEmpty();
});

test('it ignores the same origin and destination', function () {
    routeServing([$this->a, $this->b, $this->c]);

    expect($this->planner->findDirectTrips($this->a, $this->a))->toBeEmpty();
});

test('it ignores inactive routes', function () {
    routeServing([$this->a, $this->b, $this->c])->update(['is_active' => false]);

    expect($this->planner->findDirectTrips($this->a, $this->c))->toBeEmpty();
});
