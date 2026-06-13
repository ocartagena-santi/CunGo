<?php

namespace Database\Seeders;

use App\Enums\RouteDirection;
use App\Enums\VehicleType;
use App\Models\Route;
use App\Models\RoutePath;
use App\Models\Stop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use RuntimeException;

/**
 * Seeds invented-but-plausible transit data for Cancún (city + hotel zone).
 *
 * Coordinates are approximate and for development only.
 */
class CancunTransitSeeder extends Seeder
{
    /**
     * Stops keyed by name, created on demand.
     *
     * @var Collection<string, Stop>
     */
    protected Collection $stops;

    public function run(): void
    {
        $this->stops = collect();

        $this->seedStops();
        $this->seedRoutes();
    }

    /**
     * Master list of stops: [name, lat, lng, colonia, landmark, isLandmark].
     */
    protected function seedStops(): void
    {
        $stops = [
            // Centro / ciudad
            ['Palacio Municipal', 21.1619, -86.8515, 'Centro', 'Palacio Municipal', true],
            ['Mercado 28', 21.1605, -86.8290, 'Centro', 'Mercado 28', true],
            ['Terminal ADO', 21.1610, -86.8260, 'Centro', 'Terminal de Autobuses ADO', true],
            ['Crucero', 21.1758, -86.8470, 'SM 64', 'Av. Tulum y López Portillo', true],
            ['Plaza Las Américas', 21.1490, -86.8230, 'SM 21', 'Plaza Las Américas', true],
            ['Walmart Cancún', 21.1450, -86.8200, 'SM 21', 'Walmart', false],
            ['Puerto Juárez', 21.1860, -86.8090, 'Puerto Juárez', 'Ferry a Isla Mujeres', true],
            ['Bonfil', 21.0470, -86.8470, 'Alfredo V. Bonfil', null, false],
            ['Región 510', 21.0690, -86.8590, 'Región 510', null, false],

            // Zona hotelera
            ['Playa Tortugas', 21.1330, -86.7480, 'Zona Hotelera', 'Playa Tortugas', true],
            ['Forum / Coco Bongo', 21.1340, -86.7460, 'Zona Hotelera', 'Forum by the Sea', true],
            ['Plaza La Isla', 21.0890, -86.7710, 'Zona Hotelera', 'La Isla Shopping Village', true],
            ['Playa Delfines', 21.0490, -86.7770, 'Zona Hotelera', 'Mirador Playa Delfines', true],
        ];

        foreach ($stops as [$name, $lat, $lng, $colonia, $landmark, $isLandmark]) {
            $this->stops[$name] = Stop::create([
                'name' => $name,
                'location' => new Point($lat, $lng, Srid::WGS84->value),
                'colonia' => $colonia,
                'landmark' => $landmark,
                'is_landmark' => $isLandmark,
            ]);
        }
    }

    protected function seedRoutes(): void
    {
        $this->createRoute(
            code: 'R-1',
            name: 'Centro – Zona Hotelera',
            color: '#2563eb',
            vehicleType: VehicleType::Camion,
            fare: 12.00,
            frequency: 10,
            stopNames: [
                'Palacio Municipal', 'Mercado 28', 'Terminal ADO',
                'Playa Tortugas', 'Forum / Coco Bongo', 'Plaza La Isla', 'Playa Delfines',
            ],
        );

        $this->createRoute(
            code: 'R-2',
            name: 'Puerto Juárez – Centro',
            color: '#16a34a',
            vehicleType: VehicleType::Urban,
            fare: 12.00,
            frequency: 15,
            stopNames: [
                'Puerto Juárez', 'Crucero', 'Mercado 28', 'Palacio Municipal', 'Plaza Las Américas',
            ],
        );

        $this->createRoute(
            code: 'R-5',
            name: 'Centro – Bonfil',
            color: '#dc2626',
            vehicleType: VehicleType::Combi,
            fare: 13.50,
            frequency: 20,
            stopNames: [
                'Palacio Municipal', 'Walmart Cancún', 'Plaza Las Américas', 'Región 510', 'Bonfil',
            ],
        );
    }

    /**
     * Create a route, attach its stops in both directions, and build its paths.
     *
     * @param  list<string>  $stopNames
     */
    protected function createRoute(
        string $code,
        string $name,
        string $color,
        VehicleType $vehicleType,
        float $fare,
        int $frequency,
        array $stopNames,
    ): void {
        $route = Route::create([
            'code' => $code,
            'name' => $name,
            'color' => $color,
            'vehicle_type' => $vehicleType,
            'fare' => $fare,
            'frequency_minutes' => $frequency,
            'operating_hours' => '05:00–23:00',
            'is_active' => true,
        ]);

        $stops = collect($stopNames)->map(
            fn (string $stopName): Stop => $this->stops->get($stopName)
                ?? throw new RuntimeException("Unknown stop: {$stopName}"),
        );

        $this->attachStops($route, $stops, RouteDirection::Ida);
        $this->attachStops($route, $stops->reverse()->values(), RouteDirection::Vuelta);

        $this->buildPath($route, $stops, RouteDirection::Ida);
        $this->buildPath($route, $stops->reverse()->values(), RouteDirection::Vuelta);
    }

    /**
     * @param  Collection<int, Stop>  $stops
     */
    protected function attachStops(Route $route, Collection $stops, RouteDirection $direction): void
    {
        $route->stops()->attach(
            $stops->mapWithKeys(fn (Stop $stop, int $index): array => [
                $stop->id => ['sequence' => $index + 1, 'direction' => $direction->value],
            ])->all(),
        );
    }

    /**
     * @param  Collection<int, Stop>  $stops
     */
    protected function buildPath(Route $route, Collection $stops, RouteDirection $direction): void
    {
        RoutePath::create([
            'route_id' => $route->id,
            'direction' => $direction,
            'geometry' => new LineString(
                $stops->map(fn (Stop $stop): Point => $stop->location)->all(),
                Srid::WGS84->value,
            ),
        ]);
    }
}
