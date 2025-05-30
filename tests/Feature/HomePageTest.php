<?php

use App\Livewire\HomePage;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Software;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->session = AcademicSession::factory()->create();
    $this->user = User::factory()->create(['academic_session_id' => $this->session->id]);
});

it('can be rendered', function () {
    $course = Course::factory()->create(['academic_session_id' => $this->session->id]);
    $software = Software::factory()->create(['name' => 'Test Software', 'academic_session_id' => $this->session->id]);
    $software->course_id = $course->id;
    $software->save();

    actingAs($this->user);
    livewire(HomePage::class)
        ->assertSee(config('app.name'))
        ->assertSee($this->session->name)
        ->assertSee('Test Software');
});

describe('we can filter in various ways', function () {
    beforeEach(function () {
        $this->course1 = Course::factory()->create(['academic_session_id' => $this->session->id, 'code' => 'TEST1234']);
        $this->course2 = Course::factory()->create(['academic_session_id' => $this->session->id, 'code' => 'BEST2345']);
        $this->software1 = Software::factory()->create(['name' => 'Test Software QQQQQQ', 'academic_session_id' => $this->session->id]);
        $this->software2 = Software::factory()->create(['name' => 'Test Software ZZZZZZ', 'academic_session_id' => $this->session->id]);
        $this->software1->course_id = $this->course1->id;
        $this->software1->save();
        $this->software2->course_id = $this->course2->id;
        $this->software2->save();
    });

    it('can filter by course', function () {
        actingAs($this->user);
        livewire(HomePage::class)
            ->assertSee($this->course1->code)
            ->assertSee($this->course2->code)
            ->assertSee('Test Software QQQQQQ')
            ->assertSee('Test Software ZZZZZZ')
            ->set('filters.course', $this->course1->code)
            ->assertSee('Test Software QQQQQQ')
            ->assertDontSee('Test Software ZZZZZZ')
            ->set('filters.course', '')
            ->assertSee('Test Software QQQQQQ')
            ->assertSee('Test Software ZZZZZZ');
    });

    it('can filter by software', function () {
        actingAs($this->user);
        livewire(HomePage::class)
            ->set('filters.software', $this->software1->name)
            ->assertSee($this->course1->code)
            ->assertDontSee($this->course2->code)
            ->assertSee($this->software1->name)
            ->assertDontSee($this->software2->name)
            ->set('filters.software', '')
            ->assertSee($this->course1->code)
            ->assertSee($this->course2->code)
            ->assertSee($this->software1->name)
            ->assertSee($this->software2->name);
    });

    it('can filter by school', function () {
        actingAs($this->user);
        livewire(HomePage::class)
            ->set('filters.school', 'TEST')
            ->assertSee($this->course1->code)
            ->assertDontSee($this->course2->code)
            ->set('filters.school', 'BEST')
            ->assertDontSee($this->course1->code)
            ->assertSee($this->course2->code)
            ->set('filters.school', '')
            ->assertSee($this->course1->code)
            ->assertSee($this->course2->code);
    });
});

describe('requesting new software', function () {
    beforeEach(function () {
        $this->course = Course::factory()->create(['academic_session_id' => $this->session->id, 'code' => 'TEST1234']);
        $this->software = Software::factory()->create(['name' => 'Test Software', 'academic_session_id' => $this->session->id]);
        $this->software->course_id = $this->course->id;
        $this->software->save();
    });

    it('works for the happy path', function () {
        actingAs($this->user);
        livewire(HomePage::class)
            ->set('newSoftware.name', 'Test Software 2')
            ->set('newSoftware.course_code', $this->course->code)
            ->call('addSoftware')
            ->assertHasNoErrors();

        expect(Software::where('name', 'Test Software')->exists())->toBeTrue();
        expect(Software::where('name', 'Test Software 2')->exists())->toBeTrue();
        expect(Software::where('name', 'Test Software 2')->first()->course_id)->toBe($this->course->id);
    });

    it('flags missing required fields', function () {
        actingAs($this->user);
        livewire(HomePage::class)
            ->set('newSoftware.name', '')
            ->set('newSoftware.course_code', '')
            ->call('addSoftware')
            ->assertHasErrors(['newSoftware.name', 'newSoftware.course_code']);

        expect(Software::where('name', '')->exists())->toBeFalse();
    });

    it('allows the user to create a new course', function () {
        actingAs($this->user);
        livewire(HomePage::class)
            ->set('newSoftware.name', 'Test Software 3')
            ->set('newSoftware.course_code', 'TEST9999')
            ->call('addSoftware')
            ->assertHasNoErrors();

        expect(Course::where('code', 'TEST9999')->exists())->toBeTrue();
    });
});

describe('signing off on software', function () {
    beforeEach(function () {
        $this->software = Software::factory()->create(['academic_session_id' => $this->session->id]);
        $this->course = Course::factory()->create(['academic_session_id' => $this->session->id]);
        $this->software->course_id = $this->course->id;
        $this->software->save();
    });

    it('allows staff to sign off the software for a course', function () {
        actingAs($this->user);
        livewire(HomePage::class)
            ->call('signOff', $this->course->id)
            ->assertHasNoErrors();

        expect($this->course->users()->count())->toBe(1);
        expect($this->course->users()->first()->id)->toBe($this->user->id);
    });
});

describe('interacting with the existing software', function () {
    beforeEach(function () {
        $this->software = Software::factory()->create(['academic_session_id' => $this->session->id]);
        $this->course = Course::factory()->create(['academic_session_id' => $this->session->id]);
        $this->software->course_id = $this->course->id;
        $this->software->save();
    });

    it('allows staff to see more details about the software', function () {
        actingAs($this->user);
        livewire(HomePage::class)
            ->call('viewSoftwareDetails', $this->software->id)
            ->assertSee($this->software->name)
            ->assertSee($this->course->code)
            ->assertSee($this->software->config)
            ->assertSee($this->software->notes)
            ->assertSee($this->software->os)
            // ->assertSee($this->software->building) // See Software::getLocationAttribute()
            ->assertSee($this->software->lab)
            ->assertSee($this->software->version);
    });

    it('allows staff to edit existing software', function () {
        actingAs($this->user);
        livewire(HomePage::class)
            ->call('editSoftware', $this->software->id)
            ->assertHasNoErrors()
            ->set('newSoftware.name', 'Test Software 2')
            ->set('newSoftware.course_code', $this->course->code)
            ->call('addSoftware')
            ->assertHasNoErrors();

        expect(Software::where('name', 'Test Software 2')->exists())->toBeTrue();
    });

    it('allows staff to indicate that software is no longer needed', function () {
        actingAs($this->user);
        livewire(HomePage::class)
            ->assertSee($this->software->name)
            ->assertDontSee('Marked for removal')
            ->call('removeSoftware', $this->software->id)
            ->assertHasNoErrors()
            ->assertSee($this->software->name)
            ->assertSee('Marked for removal');

        expect(Software::where('id', $this->software->id)->first()->removed_at)->not->toBeNull();
        expect(Software::where('id', $this->software->id)->first()->removed_at->format('Y-m-d H:i'))->toBe(now()->format('Y-m-d H:i'));
        expect(Software::where('id', $this->software->id)->first()->removed_by)->toBe($this->user->id);
    });

    it('allows staff to unmark software for removal', function () {
        actingAs($this->user);
        $removedSoftware = Software::factory()->create(['academic_session_id' => $this->session->id, 'course_id' => $this->course->id, 'removed_at' => now(), 'removed_by' => $this->user->id]);

        livewire(HomePage::class)
            ->assertSee($removedSoftware->name)
            ->assertSee('Marked for removal')
            ->call('unmarkForRemoval', $removedSoftware->id)
            ->assertHasNoErrors()
            ->assertSee($removedSoftware->name)
            ->assertDontSee('Marked for removal');

        expect(Software::where('id', $removedSoftware->id)->first()->removed_at)->toBeNull();
        expect(Software::where('id', $removedSoftware->id)->first()->removed_by)->toBeNull();
    });
});
