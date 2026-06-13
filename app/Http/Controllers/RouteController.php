<?php

namespace App\Http\Controllers;

use App\Data\RouteData;
use App\Data\RouteDetailData;
use App\Models\Route;
use Illuminate\Http\JsonResponse;

class RouteController extends Controller
{
    /**
     * List the active routes.
     */
    public function index(): JsonResponse
    {
        $routes = Route::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return response()->json(RouteData::collect($routes));
    }

    /**
     * Show a route with its stops and path per direction.
     */
    public function show(Route $route): JsonResponse
    {
        return response()->json(RouteDetailData::fromModel($route));
    }
}
