<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Software>
 */
class SoftwareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $building = rand(1, 10) > 6 ? fake()->randomElement(['Engineering', 'Physics', 'Maths', 'Chemistry', 'Geoscience', 'Computer Science']) : null;
        $lab = $building ? (rand(1, 10) > 6 ? fake()->numberBetween(100, 500) : null) : null;
        return [
            'name' => fake()->sentence(),
            'version' => fake()->randomElement(['1.0', '2.0', '3.0', '4.0', '5.0', '6.0', '7.0', '8.0', '9.0', '10.0']),
            'os' => fake()->randomElement(['Windows', 'Mac', 'Linux']),
            'building' => $building,
            'lab' => $lab,
            'config' => rand(1, 10) == 9 ? fake()->sentence() : null,
            'notes' => rand(1, 10) == 9 ? fake()->sentence() : null,
            'is_new' => false,
            'is_free' => false,
        ];
    }
}
