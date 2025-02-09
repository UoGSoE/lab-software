<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\School;
use App\Models\Software;
use App\Models\AcademicSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cache::forget('total_user_count');
        $thisYear = now()->year;
        $nextYear = $thisYear + 1;
        $session = AcademicSession::create([
            'name' => $thisYear . '-' . $nextYear,
            'is_default' => true,
        ]);
        $admin = User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('secret'),
            'is_admin' => true,
            'is_staff' => true,
            'academic_session_id' => $session->id,
        ]);
        User::factory()->count(1000)->create([
            'academic_session_id' => $session->id,
        ]);
        User::take(300)->inRandomOrder()->get()->each(function ($user) use ($session) {
            Software::factory()->count(rand(1, 3))->create([
                'created_by' => $user->id,
                'academic_session' => $session->id,
            ]);
        });

        Course::factory()->count(1000)->create(['academic_session' => $session->id]);
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
