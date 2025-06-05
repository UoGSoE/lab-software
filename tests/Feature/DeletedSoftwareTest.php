<?php

use App\Livewire\DeletedSoftware;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Software;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->session = AcademicSession::factory()->create();
    $this->admin = User::factory()->create(['is_admin' => true, 'academic_session_id' => $this->session->id]);
    $this->course = Course::factory()->create(['academic_session_id' => $this->session->id]);
});

it('has deleted software page', function () {
    $response = $this->actingAs($this->admin)->get('/deleted-software');

    $response->assertStatus(200);
    $response->assertSeeLivewire('deleted-software');
});

it('shows page headings', function () {
    actingAs($this->admin);
    livewire(DeletedSoftware::class)
        ->assertSee('Software Pending Deletion')
        ->assertSee('Package')
        ->assertSee('Version')
        ->assertSee('O/S');
});

it('displays correct software', function () {
    $software1 = Software::factory()->create([
        'name' => 'Regular Software',
        'academic_session_id' => $this->session->id,
        'course_id' => $this->course->id,
        'removed_at' => null,
        'removed_by' => null,
    ]);

    $software2 = Software::factory()->create([
        'name' => 'Removed Software',
        'academic_session_id' => $this->session->id,
        'course_id' => $this->course->id,
        'removed_at' => now(),
        'removed_by' => $this->admin->id,
    ]);

    actingAs($this->admin);
    livewire(DeletedSoftware::class)
        ->assertSee('Removed Software')
        ->assertDontSee('Regular Software')
        ->assertSee('Restore');
});

describe('unmarking software for removal', function () {
    beforeEach(function () {
        $this->removedSoftware = Software::factory()->create([
            'name' => 'Test Software',
            'academic_session_id' => $this->session->id,
            'course_id' => $this->course->id,
            'removed_at' => now(),
            'removed_by' => $this->admin->id,
        ]);
    });

    it('unmarks software for removal', function () {
        actingAs($this->admin);
        livewire(DeletedSoftware::class)
            ->call('unmarkForRemoval', $this->removedSoftware->id);

        $this->removedSoftware->refresh();
        expect($this->removedSoftware->removed_at)->toBeNull();
        expect($this->removedSoftware->removed_by)->toBeNull();
        
    });

    // Need to test that software reappears on home page/global software page
    it('removes software from the deleted list after unmarking', function () {
        actingAs($this->admin);
        livewire(DeletedSoftware::class)->assertSee('Test Software');
        livewire(DeletedSoftware::class)->call('unmarkForRemoval', $this->removedSoftware->id);
        livewire(DeletedSoftware::class)->assertDontSee('Test Software');
    });
});

describe('permanently deleting software', function () {
    beforeEach(function () {
        $this->removedSoftware = Software::factory()->create([
            'name' => 'Test Software',
            'academic_session_id' => $this->session->id,
            'course_id' => $this->course->id,
            'removed_at' => now(),
            'removed_by' => $this->admin->id,
        ]);
    });

    it('successfully deletes software permanently', function () {
        actingAs($this->admin);
        livewire(DeletedSoftware::class)
            ->call('deleteSoftware', $this->removedSoftware->id)
            ->assertHasNoErrors();

        expect(Software::where('id', '=', $this->removedSoftware->id)->exists())->toBeFalse();
    });

    it('removes software from the list after deletion', function () {
        actingAs($this->admin);
        livewire(DeletedSoftware::class)->assertSee('Test Software');
        livewire(DeletedSoftware::class)->call('deleteSoftware', $this->removedSoftware->id);
        livewire(DeletedSoftware::class)->assertDontSee('Test Software');
    });
});