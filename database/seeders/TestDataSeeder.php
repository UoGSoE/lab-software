<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\School;
use App\Models\Scopes\AcademicSessionScope;
use App\Models\Setting;
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
            'created_at' => now()->subYear(),
            'updated_at' => now()->subYear(),
        ]);
        $newSession = AcademicSession::create([
            'name' => $thisYear.'-'.$thisYear + 1,
            'is_default' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin = User::factory()->admin()->create([
            'username' => 'admin2x',
            'password' => bcrypt('secret'),
            'is_staff' => true,
            'academic_session_id' => $oldSession->id,
        ]);
        $testUser = User::factory()->create([
            'username' => 'testuser2x',
            'password' => bcrypt('secret'),
            'is_staff' => true,
            'academic_session_id' => $oldSession->id,
        ]);
        $courses = Course::factory()->count(1000)->create(['academic_session_id' => $oldSession->id]);
        User::factory()->count(1000)->create([
            'academic_session_id' => $oldSession->id,
        ]);
        User::withoutGlobalScope(AcademicSessionScope::class)->take(300)->inRandomOrder()->get()->each(function ($user) use ($oldSession, $courses) {
            $randomCourse = $courses->shift();
            Software::factory()->count(rand(1, 3))->create([
                'created_by' => $user->id,
                'academic_session_id' => $oldSession->id,
                'course_id' => $randomCourse->id,
            ]);
            $user->courses()->attach($randomCourse);
            $randomCourse = $courses->shift();
            Software::factory()->count(rand(1, 3))->create([
                'created_by' => $user->id,
                'academic_session_id' => $oldSession->id,
                'course_id' => $randomCourse->id,
            ]);
            $user->courses()->attach($randomCourse);
        });

        $globalSoftware = Software::factory()->count(100)->create([
            'academic_session_id' => $oldSession->id,
            'course_id' => null,
            'created_by' => $admin->id,
        ]);

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

        Setting::setSetting('notifications_system_open_date', now()->format('Y-m-d'));
        Setting::setSetting('notifications_closing_date', now()->addDays(14)->format('Y-m-d'));
        Setting::setSetting('notifications_system_reminder_days', 0);
        $oldSession->copyForwardTo($newSession);
        $newSession->setAsDefault();
    }
}
