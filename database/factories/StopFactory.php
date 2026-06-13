<?php

namespace Database\Factories;

use App\Models\Stop;
use Illuminate\Database\Eloquent\Factories\Factory;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @extends Factory<Stop>
 */
class StopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Coordinates roughly within the Cancún urban area.
        $latitude = fake()->latitude(21.10, 21.20);
        $longitude = fake()->longitude(-86.88, -86.74);

        return [
            'name' => 'Parada '.fake()->streetName(),
            'location' => new Point($latitude, $longitude, Srid::WGS84->value),
            'landmark' => fake()->optional()->company(),
            'colonia' => fake()->randomElement(['Centro', 'SM 21', 'SM 64', 'Bonfil', 'Región 510']),
            'is_landmark' => false,
        ];
    }

    /**
     * Indicate that the stop is a notable landmark.
     */
    public function landmark(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_landmark' => true,
        ]);
    }
}
