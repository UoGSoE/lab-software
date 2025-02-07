<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Software;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Software::factory()->count(500)->create();
        Course::factory()->count(1000)->create();
        foreach (Course::all() as $course) {
            $course->software()->attach(Software::inRandomOrder()->limit(rand(1, 3))->pluck('id'));
        }
    }
}
