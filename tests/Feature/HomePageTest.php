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

test('we can filter in various ways', function () {
    $course1 = Course::factory()->create(['academic_session_id' => $this->session->id, 'code' => 'TEST1234']);
    $course2 = Course::factory()->create(['academic_session_id' => $this->session->id, 'code' => 'TEST2345']);
    $software1 = Software::factory()->create(['name' => 'Test Software QQQQQQ', 'academic_session_id' => $this->session->id]);
    $software2 = Software::factory()->create(['name' => 'Test Software ZZZZZZ', 'academic_session_id' => $this->session->id]);
    $software1->courses()->attach($course1->id);
    $software2->courses()->attach($course2->id);

    actingAs($this->user)->livewire(HomePage::class)
        ->assertSee($course1->code)
        ->assertSee($course2->code)
        ->assertSee('Test Software QQQQQQ')
        ->assertSee('Test Software ZZZZZZ')
        ->set('filters.course', $course1->code)
        ->assertSee('Test Software QQQQQQ')
        ->assertDontSee('Test Software ZZZZZZ')
        ->assertSee($course1->code)
        ->assertDontSee($course2->code)
        ->set('filters.course', '')
        ->set('filters.software', $software1->name)
        ->assertSee($course1->code)
        ->assertDontSee($course2->code)
        ->assertSee($software1->name)
        ->assertDontSee($software2->name);
});

describe('requesting new software', function () {

    it('works for the happy path', function () {
        $course = Course::factory()->create(['academic_session_id' => $this->session->id]);
        $software = Software::factory()->create(['name' => 'Test Software', 'academic_session_id' => $this->session->id]);
        $software->courses()->attach($course->id);
        actingAs($this->user);
            livewire(HomePage::class)
            ->set('newSoftware.name', 'Test Software')
            ->set('newSoftware.course_code', $course->code)
            ->call('addSoftware')
            ->assertHasNoErrors();

        expect(Software::where('name', 'Test Software')->exists())->toBeTrue();
    });

    it('flags missing required fields', function () {
        $course = Course::factory()->create(['academic_session_id' => $this->session->id]);
        $software = Software::factory()->create(['name' => 'Test Software', 'academic_session_id' => $this->session->id]);
        $software->courses()->attach($course->id);
        actingAs($this->user);
            livewire(HomePage::class)
            ->set('newSoftware.name', '')
            ->set('newSoftware.course_code', '')
            ->call('addSoftware')
            ->assertHasErrors(['newSoftware.name', 'newSoftware.course_code']);
    });
});
