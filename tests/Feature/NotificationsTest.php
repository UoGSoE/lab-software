<?php

use App\Mail\SystemClosing;
use App\Mail\SystemOpen;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Scopes\AcademicSessionScope;
use App\Models\Setting;
use App\Models\Software;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->academicSession = AcademicSession::factory()->create([
        'name' => '2024/2025',
        'is_default' => true,
    ]);
});

describe('system open notification', function () {
    it('sends the initial message on the opening date to all users in the current academic session', function () {
        Mail::fake();
        Setting::factory()->create([
            'key' => 'notifications_system_open_date',
            'value' => now()->format('Y-m-d'),
        ]);

        $user1 = User::factory()->create([
            'academic_session_id' => $this->academicSession->id,
        ]);

        $user2 = User::factory()->create([
            'academic_session_id' => $this->academicSession->id,
        ]);

        $userWhoIsntInThisYear = User::factory()->create([
            'academic_session_id' => AcademicSession::factory()->create([
                'name' => '2020/2021',
                'is_default' => false,
            ]),
        ]);

        $this->artisan('labsoftware:notify-system-open');

        Mail::assertQueued(SystemOpen::class, 2);
        Mail::assertQueued(SystemOpen::class, fn (SystemOpen $mail) => $mail->hasTo($user1->email));
        Mail::assertQueued(SystemOpen::class, fn (SystemOpen $mail) => $mail->hasTo($user2->email));
    });

    it('does not send the initial message if the date is not today', function () {
        Mail::fake();
        Setting::factory()->create([
            'key' => 'notifications_system_open_date',
            'value' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $user = User::factory()->create([
            'academic_session_id' => $this->academicSession->id,
        ]);

        $this->artisan('labsoftware:notify-system-open');

        Mail::assertNotQueued(SystemOpen::class);
    });

    it('exits with status 1 if the date setting is invalid', function () {
        Mail::fake();
        $user = User::factory()->create([
            'academic_session_id' => $this->academicSession->id,
        ]);
        $setting = Setting::factory()->create([
            'key' => 'notifications_system_open_date',
            'value' => 'invalid-date',
        ]);

        $this->artisan('labsoftware:notify-system-open')->assertExitCode(1);

        Mail::assertNotQueued(SystemOpen::class);

        $setting->delete();

        $this->artisan('labsoftware:notify-system-open')->assertExitCode(1);

        Mail::assertNotQueued(SystemOpen::class);
    });

    it('has the right contents in the mail', function () {
        Mail::fake();
        Setting::factory()->create([
            'key' => 'notifications_system_open_date',
            'value' => now()->format('Y-m-d'),
        ]);
        $oldSession = AcademicSession::factory()->create([
            'name' => '2020/2021',
            'is_default' => false,
            'created_at' => now()->subYear(),
            'updated_at' => now()->subYear(),
        ]);

        $user = User::factory()->create([
            'academic_session_id' => $oldSession->id,
        ]);

        $software1 = Software::factory()->create([
            'academic_session_id' => $oldSession->id,
            'name' => 'Software 1',
        ]);
        $software2 = Software::factory()->create([
            'academic_session_id' => $oldSession->id,
            'name' => 'Software 2',
        ]);
        $software3 = Software::factory()->create([
            'academic_session_id' => $oldSession->id,
            'name' => 'Software 3',
        ]);
        $course1 = Course::factory()->create([
            'academic_session_id' => $oldSession->id,
            'code' => 'CRS1234',
        ]);
        $course2 = Course::factory()->create([
            'academic_session_id' => $oldSession->id,
            'code' => 'CRS5678',
        ]);
        $course3 = Course::factory()->create([
            'academic_session_id' => $oldSession->id,
            'code' => 'CRS9012',
        ]);
        $software1->course_id = $course1->id;
        $software1->save();
        $software2->course_id = $course1->id;
        $software2->save();
        $software3->course_id = $course2->id;
        $software3->save();
        $user->courses()->withoutGlobalScope(AcademicSessionScope::class)->attach($course1);
        $user->courses()->withoutGlobalScope(AcademicSessionScope::class)->attach($course2);

        $mailable = new SystemOpen($user);
        $mailable->assertSeeInText('Lab Software System');
        $mailable->assertSeeInText('The Lab Software Team');
        $mailable->assertSeeInText($software1->name);
        $mailable->assertSeeInText($software2->name);
        $mailable->assertSeeInText($software3->name);
        $mailable->assertSeeInText($course1->code);
        $mailable->assertSeeInText($course2->code);
        $mailable->assertDontSeeInText($course3->code);
        $mailable->assertSeeInText('Looks good to me');
        $mailable->assertSeeInText('Otherwise, please log into the Lab Software System to indicate the software you will be using for teaching this year.');
        $mailable->assertSeeInText(route('signed-off', $user));

        $mailable->assertDontSeeInText('Please log into the Lab Software System to indicate the software you will be using for teaching this year.');
    });

    it('has the right contents in the mail when the user has no courses with software in the old academic session', function () {
        Mail::fake();
        Setting::factory()->create([
            'key' => 'notifications_system_open_date',
            'value' => now()->format('Y-m-d'),
        ]);
        $oldSession = AcademicSession::factory()->create([
            'name' => '2020/2021',
            'is_default' => false,
            'created_at' => now()->subYear(),
            'updated_at' => now()->subYear(),
        ]);

        $user = User::factory()->create([
            'academic_session_id' => $oldSession->id,
        ]);

        $software1 = Software::factory()->create([
            'academic_session_id' => $oldSession->id,
            'name' => 'Software 1',
        ]);
        $course1 = Course::factory()->create([
            'academic_session_id' => $oldSession->id,
            'code' => 'CRS1234',
        ]);
        $software1->course_id = $course1->id;
        $software1->save();
        $user->load('courses.software');

        $mailable = new SystemOpen($user);
        $mailable->assertSeeInText('Lab Software System');
        $mailable->assertSeeInText('The Lab Software Team');
        $mailable->assertDontSeeInText($software1->name);
        $mailable->assertDontSeeInText($course1->code);
        $mailable->assertDontSeeInText(route('signed-off', $user));
        $mailable->assertSeeInText(route('home'));
        $mailable->assertDontSeeInText('Looks good to me');
        $mailable->assertDontSeeInText('Otherwise, please log into the Lab Software System to indicate the software you will be using for teaching this year.
');
        $mailable->assertSeeInText('Please log into the Lab Software System to indicate the software you will be using for teaching this year.');
    });

    test('the magic sign off link does the right thing', function () {
        $oldSession = AcademicSession::factory()->create([
            'name' => '2020/2021',
            'is_default' => false,
            'created_at' => now()->subYear(),
            'updated_at' => now()->subYear(),
        ]);
        $user = User::factory()->create([
            'academic_session_id' => $oldSession->id,
        ]);
        $otherUser = User::factory()->create([
            'academic_session_id' => $oldSession->id,
            'email' => 'otheruser@example.com',
        ]);
        $course1 = Course::factory()->create([
            'academic_session_id' => $oldSession->id,
            'code' => 'CRS1234',
        ]);
        $course2 = Course::factory()->create([
            'academic_session_id' => $oldSession->id,
            'code' => 'CRS5678',
        ]);
        $software1 = Software::factory()->create([
            'academic_session_id' => $oldSession->id,
            'name' => 'Software 1',
            'created_by' => $otherUser->id,
            'course_id' => $course1->id,
        ]);
        $software2 = Software::factory()->create([
            'academic_session_id' => $oldSession->id,
            'name' => 'Software 2',
            'created_by' => $otherUser->id,
            'course_id' => $course2->id,
        ]);
        $user->courses()->withoutGlobalScope(AcademicSessionScope::class)->attach($course1);
        $user->courses()->withoutGlobalScope(AcademicSessionScope::class)->attach($course2);
        $oldSession->copyForwardTo($this->academicSession);
        $currentUser = User::where('username', '=', $user->username)->first();

        $this->get($currentUser->getSignoffLink())->assertOk()->assertSee('Your software requests from last year will be used again this year');

        $courses = Course::get();
        $this->assertCount(2, $courses);
        $this->assertTrue($courses->contains('code', 'CRS1234'));
        $this->assertTrue($courses->contains('code', 'CRS5678'));
        $userCourses = $currentUser->fresh()->courses;
        $this->assertTrue($userCourses->contains('code', 'CRS1234'));
        $this->assertTrue($userCourses->contains('code', 'CRS5678'));
    });
});

describe('deadline notification', function () {
    it('sends a second nag message before the closing date for anyone who has not signed stuff off', function () {
        Mail::fake();
        Setting::factory()->create([
            'key' => 'notifications_closing_date',
            'value' => now()->addDays(7)->format('Y-m-d'),  // we send the notification 7 days before the closing date
        ]);

        $user1 = User::factory()->create([
            'academic_session_id' => $this->academicSession->id,
        ]);

        $user2 = User::factory()->create([
            'academic_session_id' => $this->academicSession->id,
        ]);

        $userWhoIsntInThisYear = User::factory()->create([
            'academic_session_id' => AcademicSession::factory()->create([
                'name' => '2020/2021',
                'is_default' => false,
            ]),
        ]);

        $this->artisan('labsoftware:notify-closing-deadline');

        Mail::assertQueued(SystemClosing::class, 2);
        Mail::assertQueued(SystemClosing::class, fn (SystemClosing $mail) => $mail->hasTo($user1->email));
        Mail::assertQueued(SystemClosing::class, fn (SystemClosing $mail) => $mail->hasTo($user2->email));
    });

    it('has the right contents in the closing mail', function () {
        Setting::factory()->create([
            'key' => 'notifications_closing_date',
            'value' => now()->addDays(7)->format('Y-m-d'),  // we send the notification 7 days before the closing date
        ]);

        $user1 = User::factory()->create([
            'academic_session_id' => $this->academicSession->id,
        ]);

        $mailable = new SystemClosing($user1);
        $mailable->assertSeeInText('Lab Software System closing soon');
        $mailable->assertSeeInText("The system is closing on " . now()->addDays(7)->format('d/m/Y'));
        $mailable->assertSeeInText(route('home'));
    });

});
