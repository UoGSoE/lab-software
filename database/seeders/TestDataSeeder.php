<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\School;
use App\Models\Software;
use App\Models\User;
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
        // try and create a plausible academic year
        $thisYear = now()->year;
        $thisMonth = now()->month;
        if ($thisMonth < 7) {
            $thisYear = $thisYear - 1;
        }

        $oldSession = AcademicSession::create([
            'name' => $thisYear - 1 .'-'.$thisYear,
            'is_default' => true,
        ]);
        $newSession = AcademicSession::create([
            'name' => $thisYear.'-'.$thisYear + 1,
            'is_default' => false,
        ]);
        $admin = User::factory()->admin()->create([
            'username' => 'admin2x',
            'password' => bcrypt('secret'),
            'is_staff' => true,
            'academic_session_id' => $oldSession->id,
        ]);
        User::factory()->count(1000)->create([
            'academic_session_id' => $oldSession->id,
        ]);
        User::take(300)->inRandomOrder()->get()->each(function ($user) use ($oldSession) {
            Software::factory()->count(rand(1, 3))->create([
                'created_by' => $user->id,
                'academic_session_id' => $oldSession->id,
            ]);
        });

        Course::factory()->count(1000)->create(['academic_session_id' => $oldSession->id]);
        $courses = Course::all();
        $softwares = Software::all();
        // Assign each course a single random software
        foreach ($courses as $course) {
            $software = Software::inRandomOrder()->whereNull('course_id')->first();
            if ($software) {
                $software->course_id = $course->id;
                $software->save();
            }
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
                'course_prefix' => 'GES',
            ],
            [
                'name' => 'Computer Science',
                'course_prefix' => 'COMP',
            ],
        ];
        foreach ($schools as $school) {
            School::create($school);
        }

        $oldSession->copyForwardTo($newSession);
        $newSession->setAsDefault();
    }
}
