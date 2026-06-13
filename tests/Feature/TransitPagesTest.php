<?php

use App\Models\Route;
use Database\Seeders\CancunTransitSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(CancunTransitSeeder::class);
});

test('the search page renders', function () {
    $this->get(route('transit.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('transit/Search'));
});

test('the routes page lists active routes', function () {
    $this->get(route('transit.routes'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('transit/Routes')
            ->has('routes', 3)
            ->where('routes.0.code', 'R-1'));
});

test('the explore map page renders stops and route polylines', function () {
    $this->get(route('transit.map'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('transit/Map')
            ->has('stops', 13)
            ->has('routes', 3)
            ->has('routes.0.path'));
});

test('the route detail page renders stops per direction', function () {
    $route = Route::where('code', 'R-1')->firstOrFail();

    $this->get(route('transit.routes.show', $route))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('transit/RouteShow')
            ->where('route.route.code', 'R-1')
            ->has('route.stopsIda', 7)
            ->has('route.stopsVuelta', 7)
            ->where('route.stopsIda.0.name', 'Palacio Municipal'));
});
