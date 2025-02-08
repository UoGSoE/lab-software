<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\School;
use App\Models\Software;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('secret'),
            'is_admin' => true,
            'is_staff' => true,
        ]);
        User::factory()->count(1000)->create();
        User::take(300)->inRandomOrder()->get()->each(function ($user) {
            Software::factory()->count(rand(1, 3))->create([
                'created_by' => $user->id,
            ]);
        });
        Course::factory()->count(1000)->create();
        foreach (Course::all() as $course) {
            $course->software()->attach(Software::inRandomOrder()->limit(rand(1, 3))->pluck('id'));
        }
        $schools = [
            [
                'name' => 'Engineering',
                'course_prefix' => 'ENG',
            ],
            [
                'name' => 'Physics',
                'course_prefix' => 'PHAS',
            ],
            [
                'name' => 'Maths',
                'course_prefix' => 'MATH',
            ],
            [
                'name' => 'Chemistry',
                'course_prefix' => 'CHEM',
            ],
            [
                'name' => 'Geoscience',
                'course_prefix' => 'GEOS',
            ],
            [
                'name' => 'Computer Science',
                'course_prefix' => 'COMP',
            ],
        ];
        foreach ($schools as $school) {
            School::create($school);
        }
    }
}
