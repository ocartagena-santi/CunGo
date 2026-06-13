<?php

namespace App\Http\Controllers;

use App\Data\RouteData;
use App\Data\RouteDetailData;
use App\Data\RoutePolylineData;
use App\Data\StopData;
use App\Models\Route;
use App\Models\Stop;
use Inertia\Inertia;
use Inertia\Response;

class TransitController extends Controller
{
    /**
     * The trip search home page.
     */
    public function home(): Response
    {
        return Inertia::render('transit/Search');
    }

    /**
     * The list of active routes.
     */
    public function routes(): Response
    {
        $routes = Route::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return Inertia::render('transit/Routes', [
            'routes' => RouteData::collect($routes),
        ]);
    }

    /**
     * The detail of a single route.
     */
    public function showRoute(Route $route): Response
    {
        return Inertia::render('transit/RouteShow', [
            'route' => RouteDetailData::fromModel($route),
        ]);
    }

    /**
     * The explore map with every stop and route drawn.
     */
    public function map(): Response
    {
        $routes = Route::query()
            ->with('paths')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return Inertia::render('transit/Map', [
            'stops' => StopData::collect(Stop::all()),
            'routes' => RoutePolylineData::collect($routes),
        ]);
    }
}
