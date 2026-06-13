<?php

use App\Data\RouteData;
use App\Data\StopData;
use App\Enums\RouteDirection;
use App\Enums\VehicleType;
use App\Models\Route;
use App\Models\RoutePath;
use App\Models\Stop;
use Database\Seeders\CancunTransitSeeder;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\Point;

test('a stop stores and reads its geographic location', function () {
    $stop = Stop::factory()->create([
        'location' => new Point(21.1619, -86.8515, Srid::WGS84->value),
    ]);

    expect($stop->fresh()->location)
        ->latitude->toBe(21.1619)
        ->longitude->toBe(-86.8515);
});

test('a route exposes its stops ordered by sequence per direction', function () {
    $route = Route::factory()->create();
    $first = Stop::factory()->create();
    $second = Stop::factory()->create();

    $route->stops()->attach([
        $first->id => ['sequence' => 1, 'direction' => RouteDirection::Ida->value],
        $second->id => ['sequence' => 2, 'direction' => RouteDirection::Ida->value],
    ]);

    $ida = $route->stopsForDirection(RouteDirection::Ida)->get();

    expect($ida)->toHaveCount(2)
        ->and($ida->first()->id)->toBe($first->id)
        ->and($ida->last()->id)->toBe($second->id);
});

test('a route path belongs to a route and stores a line', function () {
    $path = RoutePath::factory()->create();

    expect($path->route)->toBeInstanceOf(Route::class)
        ->and($path->geometry->getGeometries())->toHaveCount(3)
        ->and($path->direction)->toBe(RouteDirection::Ida);
});

test('StopData exposes coordinates flattened for the frontend', function () {
    $stop = Stop::factory()->create([
        'location' => new Point(21.10, -86.80, Srid::WGS84->value),
        'name' => 'Palacio Municipal',
    ]);

    $data = StopData::fromModel($stop);

    expect($data->name)->toBe('Palacio Municipal')
        ->and($data->lat)->toBe(21.10)
        ->and($data->lng)->toBe(-86.80);
});

test('RouteData carries the vehicle type enum', function () {
    $route = Route::factory()->create(['vehicle_type' => VehicleType::Combi]);

    expect(RouteData::fromModel($route)->vehicleType)->toBe(VehicleType::Combi);
});

test('the Cancún seeder creates the expected routes, stops and paths', function () {
    $this->seed(CancunTransitSeeder::class);

    expect(Route::count())->toBe(3)
        ->and(Stop::count())->toBe(13)
        ->and(RoutePath::count())->toBe(6);

    $r1 = Route::where('code', 'R-1')->firstOrFail();

    expect($r1->stopsForDirection(RouteDirection::Ida)->pluck('name')->first())
        ->toBe('Palacio Municipal')
        ->and($r1->stopsForDirection(RouteDirection::Vuelta)->pluck('name')->first())
        ->toBe('Playa Delfines');
});
