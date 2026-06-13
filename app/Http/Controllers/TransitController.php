<?php

namespace App\Http\Controllers;

use App\Data\RouteData;
use App\Data\RouteDetailData;
use App\Models\Route;
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
}
