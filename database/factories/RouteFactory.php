<?php

namespace Database\Factories;

use App\Enums\VehicleType;
use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Route>
 */
class RouteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'R-'.fake()->unique()->numberBetween(1, 999),
            'name' => fake()->streetName().' – '.fake()->streetName(),
            'color' => fake()->hexColor(),
            'vehicle_type' => fake()->randomElement(VehicleType::cases()),
            'fare' => fake()->randomElement([12.00, 13.50, 15.00]),
            'frequency_minutes' => fake()->randomElement([10, 15, 20, 30]),
            'operating_hours' => '05:00–23:00',
            'description' => null,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the route is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
