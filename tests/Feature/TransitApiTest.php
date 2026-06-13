<?php

use App\Models\Route;
use App\Models\Stop;
use Database\Seeders\CancunTransitSeeder;

beforeEach(function () {
    $this->seed(CancunTransitSeeder::class);
});

test('it lists active routes', function () {
    $response = $this->getJson(route('api.routes.index'));

    $response->assertOk()->assertJsonCount(3);
});

test('it shows a route with stops and path per direction', function () {
    $route = Route::where('code', 'R-1')->firstOrFail();

    $response = $this->getJson(route('api.routes.show', $route));

    $response->assertOk()
        ->assertJsonPath('route.code', 'R-1')
        ->assertJsonCount(7, 'stopsIda')
        ->assertJsonCount(7, 'pathIda')
        ->assertJsonPath('stopsIda.0.name', 'Palacio Municipal');
});

test('stop search requires at least two characters', function () {
    $this->getJson(route('api.stops.search', ['q' => 'a']))
        ->assertOk()
        ->assertExactJson([]);
});

test('stop search matches name, landmark and colonia', function () {
    $this->getJson(route('api.stops.search', ['q' => 'Palacio']))
        ->assertOk()
        ->assertJsonFragment(['name' => 'Palacio Municipal']);

    $this->getJson(route('api.stops.search', ['q' => 'Hotelera']))
        ->assertOk()
        ->assertJsonFragment(['colonia' => 'Zona Hotelera']);
});

test('nearest returns the closest stop first', function () {
    $palacio = Stop::where('name', 'Palacio Municipal')->firstOrFail();

    $response = $this->getJson(route('api.stops.nearest', [
        'lat' => $palacio->location->latitude,
        'lng' => $palacio->location->longitude,
    ]));

    $response->assertOk()->assertJsonPath('0.name', 'Palacio Municipal');
});

test('nearest validates coordinates', function () {
    $this->getJson(route('api.stops.nearest', ['lat' => 200, 'lng' => 0]))
        ->assertStatus(422);
});

test('trip search returns a direct trip between two connected stops', function () {
    $origin = Stop::where('name', 'Palacio Municipal')->firstOrFail();
    $destination = Stop::where('name', 'Playa Delfines')->firstOrFail();

    $response = $this->getJson(route('api.trips.search', [
        'origin' => $origin->id,
        'destination' => $destination->id,
    ]));

    $response->assertOk()
        ->assertJsonPath('0.route.code', 'R-1')
        ->assertJsonPath('0.boardStop.name', 'Palacio Municipal')
        ->assertJsonPath('0.alightStop.name', 'Playa Delfines');
});

test('trip search rejects identical origin and destination', function () {
    $stop = Stop::where('name', 'Palacio Municipal')->firstOrFail();

    $this->getJson(route('api.trips.search', [
        'origin' => $stop->id,
        'destination' => $stop->id,
    ]))->assertStatus(422);
});
