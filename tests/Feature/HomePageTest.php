<?php

use App\Models\User;
use App\Models\Course;
use App\Models\Software;
use App\Livewire\HomePage;
use App\Models\AcademicSession;
use function Pest\Livewire\livewire;
use function Pest\Laravel\{actingAs};

beforeEach(function () {
    $this->session = AcademicSession::factory()->create();
    $this->user = User::factory()->create(['academic_session_id' => $this->session->id]);
});

it('can be rendered', function () {
    $course = Course::factory()->create(['academic_session_id' => $this->session->id]);
    $software = Software::factory()->create(['name' => 'Test Software', 'academic_session_id' => $this->session->id]);
    $software->courses()->attach($course->id);

    $this->actingAs($this->user)->get('/')
                          ->assertStatus(200)
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
        $this->software1->courses()->attach($this->course1->id);
        $this->software2->courses()->attach($this->course2->id);
    });

    it('can filter by course', function () {
        actingAs($this->user)->livewire(HomePage::class)
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
        $this->software->courses()->attach($this->course->id);
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
        expect(Software::where('name', 'Test Software 2')->first()->courses()->count())->toBe(1);
        expect(Software::where('name', 'Test Software 2')->first()->courses()->first()->id)->toBe($this->course->id);
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
        $this->software->courses()->attach($this->course->id);
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
        $this->software->courses()->attach($this->course->id);
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
            ->assertSee($this->software->building)
            ->assertSee($this->software->lab)
            ->assertSee($this->software->version)
            ->assertSee($this->software->created_by?->name)
            ->assertSee($this->software->created_at->format('d/m/Y'));
    });
});
