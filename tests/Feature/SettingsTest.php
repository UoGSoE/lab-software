<?php

use App\Jobs\CopyForward;
use App\Livewire\Settings;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

describe('The livewire settings page', function () {
    beforeEach(function () {
        $this->academicSession = AcademicSession::factory()->create(['name' => '2024-2025']);
        $this->admin = User::factory()->create([
            'academic_session_id' => $this->academicSession->id,
            'is_admin' => true,
        ]);
    });

    it('renders ok', function () {
        $this->actingAs($this->admin)->get(route('settings'))->assertOk()->assertSeeLivewire('settings');
    });

    it('doesnt let non-admins see the page', function () {
        $user = User::factory()->create(['academic_session_id' => $this->academicSession->id]);
        $this->actingAs($user)->get(route('settings'))->assertForbidden();
    });

    it('shows all the academic sessions', function () {
        $secondSession = AcademicSession::factory()->create();

        actingAs($this->admin);

        livewire(Settings::class)
            ->assertSee($this->academicSession->name)
            ->assertSee($secondSession->name);
    });

    it('lets admins create a new academic session', function () {
        // NOTE: see AcademicSessionsTest.php for the detailed tests for the underlying feature
        Queue::fake();
        actingAs($this->admin);

        $thisYear = date('Y');
        livewire(Settings::class)
            ->assertSee($this->academicSession->name)
            ->assertDontSee($thisYear.'-'.$thisYear + 1)
            ->set('newSessionNameStart', $thisYear)
            ->set('newSessionNameEnd', $thisYear + 1)
            ->set('newSessionIsDefault', true)
            ->call('createNewSession')
            ->assertHasNoErrors()
            ->assertSee($thisYear.'-'.$thisYear + 1);

        $this->assertDatabaseHas('academic_sessions', [
            'name' => $thisYear.'-'.$thisYear + 1,
            'is_default' => true,
        ]);
        $this->assertFalse($this->academicSession->fresh()->is_default);
        Queue::assertPushed(CopyForward::class);
    });

    it('lets admins change the default academic session', function () {
        $secondSession = AcademicSession::factory()->create(['name' => '2025-2026']);
        actingAs($this->admin);

        livewire(Settings::class)
            ->call('updateDefaultSession', $secondSession->id)
            ->assertHasNoErrors();

        $this->assertTrue($secondSession->fresh()->is_default);
        $this->assertFalse($this->academicSession->fresh()->is_default);
    });

    it('allows admins to create a new school', function () {
        actingAs($this->admin);

        livewire(Settings::class)
            ->set('newSchoolName', 'Test School')
            ->set('newSchoolCoursePrefix', 'TEST')
            ->call('createNewSchool')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('schools', [
            'name' => 'Test School',
            'course_prefix' => 'TEST',
        ]);
    });

    it('correctly capitalises the course prefix', function () {
        actingAs($this->admin);

        livewire(Settings::class)
            ->set('newSchoolName', 'Test School')
            ->set('newSchoolCoursePrefix', 'test')
            ->call('createNewSchool')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('schools', [
            'name' => 'Test School',
            'course_prefix' => 'TEST',
        ]);
    });

    it('allows admins to update a school', function () {
        $school = School::factory()->create([
            'name' => 'Test School',
            'course_prefix' => 'TEST',
        ]);
        actingAs($this->admin);

        livewire(Settings::class)
            ->set('editSchoolId', $school->id)
            ->set('editSchoolName', 'Updated School')
            ->set('editSchoolCoursePrefix', 'upd')
            ->call('updateSchool')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('schools', [
            'name' => 'Test School',
            'course_prefix' => 'TEST',
        ]);
        $this->assertDatabaseHas('schools', [
            'name' => 'Updated School',
            'course_prefix' => 'UPD',  // automatically capitalised
        ]);
    });

    it('allows admins to delete a school', function () {
        $school = School::factory()->create([
            'name' => 'Test School',
            'course_prefix' => 'TEST',
        ]);
        $secondSchool = School::factory()->create([
            'name' => 'Second School',
            'course_prefix' => 'SECOND',
        ]);
        actingAs($this->admin);

        livewire(Settings::class)
            ->call('deleteSchool', $school->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('schools', [
            'name' => 'Test School',
            'course_prefix' => 'TEST',
        ]);
        $this->assertDatabaseHas('schools', [
            'name' => 'Second School',
            'course_prefix' => 'SECOND',
        ]);
    });

    it('lets admins change the start and end dates of the software request period', function () {
        actingAs($this->admin);

        livewire(Settings::class)
            ->set('openDate', '2025-04-08')
            ->set('closeDate', '2025-04-25')
            ->set('reminderDays', 7)
            ->call('updateSoftwareRequestPeriod')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', [
            'notifications.system_open_date' => '2025-04-08',
            'notifications.closing_date' => '2025-04-25',
            'notifications.system_reminder_days' => 7,
        ]);
    });
});
