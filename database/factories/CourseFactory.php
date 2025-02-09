<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'code' => fake()->randomElement(['ENG', 'PHAS', 'MATH', 'CHEM', 'GES', 'COMP']) . fake()->numberBetween(1000, 9999),
            'academic_session_id' => AcademicSession::factory(),
        ];
    }
}
