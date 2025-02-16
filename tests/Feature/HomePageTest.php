<?php

use App\Models\User;
use App\Models\Course;
use App\Models\Software;
use App\Models\AcademicSession;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->session = AcademicSession::factory()->create();
});

it('can be rendered', function () {
    $course = Course::factory()->create(['academic_session_id' => $this->session->id]);
    $user = User::factory()->create(['academic_session_id' => $this->session->id]);
    $software = Software::factory()->create(['name' => 'Test Software', 'academic_session_id' => $this->session->id]);
    $software->courses()->attach($course->id);

    $this->actingAs($user)->get('/')
                          ->assertStatus(200)
                          ->assertSee(config('app.name'))
                          ->assertSee('Test Software');
});

// it('can be incremented', function () {
//     livewire(Counter::class)
//         ->call('increment')
//         ->assertSee(1);
// });
