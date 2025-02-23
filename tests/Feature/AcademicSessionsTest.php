<?php

use App\Jobs\CopyForward;
use App\Models\User;
use App\Models\Course;
use App\Models\Software;
use App\Models\AcademicSession;

it('we can create a new academic session with all data copied forward', function () {
    $previousSession = AcademicSession::create([
        'name' => '2024-2025',
        'is_default' => false,
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
    ]);

    $software2 = Software::factory()->create([
        'name' => 'Software 2',
        'academic_session_id' => $previousSession->id,
    ]);

    $user1 = User::factory()->create([
        'academic_session_id' => $previousSession->id,
    ]);

    $user2 = User::factory()->create([
        'academic_session_id' => $previousSession->id,
    ]);

    $user1->courses()->attach($course1);
    $user1->courses()->attach($course2);
    $user2->courses()->attach($course1);

    $course1->software()->attach($software1);
    $course1->software()->attach($software2);
    $course2->software()->attach($software1);

    $newSession = AcademicSession::create([
        'name' => '2025-2026',
        'is_default' => true,
    ]);

    expect($newSession->courses()->count())->toBe(0);
    expect($newSession->users()->count())->toBe(0);
    expect($newSession->software()->count())->toBe(0);

    CopyForward::dispatchSync($previousSession, $newSession);

    expect($newSession->courses()->count())->toBe(2);
    expect($newSession->users()->count())->toBe(2);
    expect($newSession->software()->count())->toBe(2);

    // also test course <-> software relationship is synced
    expect($newSession->courses()->where('code', $course1->code)->first()->software()->count())->toBe(2);
    expect($newSession->courses()->where('code', $course2->code)->first()->software()->count())->toBe(1);

    // and that the user signoffs are not copied forward
    expect($newSession->users()->where('email', $user1->email)->first()->courses()->count())->toBe(0);
    expect($newSession->users()->where('email', $user2->email)->first()->courses()->count())->toBe(0);
});
