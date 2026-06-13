<?php

use App\Http\Controllers\RouteController;
use App\Http\Controllers\StopController;
use App\Http\Controllers\TripController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Transit query API (Estándar — public). Consumed by the search UI via Inertia's HTTP client.
Route::prefix('api')->name('api.')->group(function () {
    Route::get('routes', [RouteController::class, 'index'])->name('routes.index');
    Route::get('routes/{route}', [RouteController::class, 'show'])->name('routes.show');
    Route::get('stops/search', [StopController::class, 'search'])->name('stops.search');
    Route::get('stops/nearest', [StopController::class, 'nearest'])->name('stops.nearest');
    Route::get('trips/search', [TripController::class, 'search'])->name('trips.search');
});

require __DIR__ . '/settings.php';
