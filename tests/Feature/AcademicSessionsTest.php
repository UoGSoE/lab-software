<?php

use App\Models\User;
use App\Models\Course;
use App\Models\Software;
use App\Jobs\CopyForward;
use App\Models\AcademicSession;
use Illuminate\Support\Facades\DB;
use App\Models\Scopes\AcademicSessionScope;

it('we can create a new academic session with all data copied forward', function () {
    $previousSession = AcademicSession::create([
        'name' => '2024-2025',
        'is_default' => false,
    ]);

    $user1 = User::factory()->create([
        'academic_session_id' => $previousSession->id,
    ]);

    $user2 = User::factory()->create([
        'academic_session_id' => $previousSession->id,
    ]);

    $course1 = Course::factory()->create([
        'title' => 'Course 1',
        'academic_session_id' => $previousSession->id,
    ]);

    $course2 = Course::factory()->create([
        'title' => 'Course 2',
        'academic_session_id' => $previousSession->id,
    ]);

    $software1 = Software::factory()->create([
        'name' => 'Software 1',
        'academic_session_id' => $previousSession->id,
        'created_by' => $user1->id,
    ]);

    $software2 = Software::factory()->create([
        'name' => 'Software 2',
        'academic_session_id' => $previousSession->id,
        'created_by' => $user1->id,
    ]);

    $user1->courses()->withoutGlobalScope(AcademicSessionScope::class)->attach($course1);
    $user1->courses()->withoutGlobalScope(AcademicSessionScope::class)->attach($course2);
    $user2->courses()->withoutGlobalScope(AcademicSessionScope::class)->attach($course1);

    $course1->software()->withoutGlobalScope(AcademicSessionScope::class)->attach($software1);
    $course1->software()->withoutGlobalScope(AcademicSessionScope::class)->attach($software2);
    $course2->software()->withoutGlobalScope(AcademicSessionScope::class)->attach($software1);

    $newSession = AcademicSession::create([
        'name' => '2025-2026',
        'is_default' => true,
    ]);

    expect($newSession->courses()->withoutGlobalScope(AcademicSessionScope::class)->count())->toBe(0);
    expect($newSession->users()->withoutGlobalScope(AcademicSessionScope::class)->count())->toBe(0);
    expect($newSession->software()->withoutGlobalScope(AcademicSessionScope::class)->count())->toBe(0);

    CopyForward::dispatchSync($previousSession, $newSession);

    expect($newSession->courses()->count())->toEqual(2);
    expect($newSession->users()->count())->toEqual(2);
    expect($newSession->software()->count())->toEqual(2);

    // also test course <-> software relationship is synced
    expect($newSession->courses()->where('code', $course1->code)->first()->software()->count())->toEqual(2);
    expect($newSession->courses()->where('code', $course2->code)->first()->software()->count())->toEqual(1);

    // and that the user signoffs are not copied forward
    expect($newSession->users()->where('email', $user1->email)->first()->courses()->count())->toEqual(0);
    expect($newSession->users()->where('email', $user2->email)->first()->courses()->count())->toEqual(0);
});

it('can get the default academic session', function () {
    $session1 = AcademicSession::create([
        'name' => '2024-2025',
        'is_default' => false,
    ]);
    $session2 = AcademicSession::create([
        'name' => '2025-2026',
        'is_default' => true,
    ]);
    $session3 = AcademicSession::create([
        'name' => '2026-2027',
        'is_default' => false,
    ]);
    expect(AcademicSession::getDefault()->id)->toEqual($session2->id);
});

it('sets the default academic session on the users http session if no session is set', function () {
    $session1 = AcademicSession::create([
        'name' => '2024-2025',
        'is_default' => false,
    ]);
    $session2 = AcademicSession::create([
        'name' => '2025-2026',
        'is_default' => true,
    ]);
    $user = User::factory()->create();
    $this->actingAs($user);
    expect(session()->missing('academic_session_id'))->toBeTrue();
    $this->get(route('home'));
    expect(session()->get('academic_session_id'))->toBe($session2->id);
});

it('doesnt change the users http academic session if it is already set', function () {
    $session1 = AcademicSession::create([
        'name' => '2024-2025',
        'is_default' => false,
    ]);
    $session2 = AcademicSession::create([
        'name' => '2025-2026',
        'is_default' => true,
    ]);
    $user = User::factory()->create(['academic_session_id' => $session1->id]);
    $this->actingAs($user);
    session()->put('academic_session_id', $session1->id);
    $this->get(route('home'));
    expect(session()->get('academic_session_id'))->toBe($session1->id);
});
