<?php

namespace App\Http\Controllers;

use App\Models\Stop;
use App\Services\TripPlanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function __construct(private readonly TripPlanner $planner)
    {
    }

    /**
     * Search direct trips between an origin and a destination stop.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'origin' => ['required', 'integer', 'exists:stops,id'],
            'destination' => ['required', 'integer', 'different:origin', 'exists:stops,id'],
        ]);

        $trips = $this->planner->findDirectTrips(
            Stop::findOrFail((int) $validated['origin']),
            Stop::findOrFail((int) $validated['destination']),
        );

        return response()->json($trips);
    }
}
