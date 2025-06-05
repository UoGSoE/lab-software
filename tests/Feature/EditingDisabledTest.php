<?php

use App\Models\User;
use App\Models\Course;
use App\Models\Setting;
use App\Models\Software;
use App\Livewire\HomePage;
use App\Models\AcademicSession;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;
use Illuminate\Support\Facades\Cache;

describe('editing window prevents or allows editing', function () {
    beforeEach(function () {
        Cache::delete('editingEnabled');
        $this->session = AcademicSession::factory()->create();
    });

    it('prevents editing if the date is outside the editing window', function () {
        Setting::factory()->create([
            'key' => 'notifications_system_open_date',
            'value' => now()->addDays(10),
        ]);

        Setting::factory()->create([
            'key' => 'notifications_system_close_date',
            'value' => now()->addDays(20),
        ]);

        $user = User::factory()->create(['academic_session_id' => $this->session->id]);
        $course = Course::factory()->create(['academic_session_id' => $this->session->id]);
        $software = Software::factory()->create([
            'course_id' => $course->id,
            'academic_session_id' => $this->session->id,
        ]);

        actingAs($user);

        livewire(HomePage::class)->assertDontSee('Request new software')->assertDontSee('Sign off')->assertDontSee("Details");

    });

    it('allows editing if the date is inside the editing window', function () {
        Setting::factory()->create([
            'key' => 'notifications_system_open_date',
            'value' => now()->subDays(10),
        ]);

        Setting::factory()->create([
            'key' => 'notifications_system_close_date',
            'value' => now()->addDays(10),
        ]);

        $user = User::factory()->create(['academic_session_id' => $this->session->id]);
        $course = Course::factory()->create(['academic_session_id' => $this->session->id]);
        $software = Software::factory()->create([
            'course_id' => $course->id,
            'academic_session_id' => $this->session->id,
        ]);

        actingAs($user);

        livewire(HomePage::class)->assertSee('Request new software')->assertSee('Sign off')->assertSee("Details");

    });
});