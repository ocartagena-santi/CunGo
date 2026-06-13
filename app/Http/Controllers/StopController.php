<?php

namespace App\Http\Controllers;

use App\Data\StopData;
use App\Models\Stop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\Point;

class StopController extends Controller
{
    /**
     * Autocomplete search over stop names, landmarks and colonias.
     */
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $stops = Stop::query()
            ->where(function (Builder $query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('landmark', 'like', "%{$term}%")
                    ->orWhere('colonia', 'like', "%{$term}%");
            })
            ->orderByDesc('is_landmark')
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json(StopData::collect($stops));
    }

    /**
     * Return the stops closest to a given coordinate, nearest first.
     */
    public function nearest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $point = new Point((float) $validated['lat'], (float) $validated['lng'], Srid::WGS84->value);

        $stops = Stop::query()
            ->orderByDistanceSphere('location', $point)
            ->limit(5)
            ->get();

        return response()->json(StopData::collect($stops));
    }
}
