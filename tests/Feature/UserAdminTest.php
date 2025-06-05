<?php

use App\Livewire\UserList;
use App\Models\AcademicSession;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->academicSession = AcademicSession::factory()->create();
    $this->admin = User::factory()->create(['is_admin' => true, 'academic_session_id' => $this->academicSession->id]);
});

it('has useradmin page', function () {
    $response = $this->actingAs($this->admin)->get('/users');

    $response->assertStatus(200);
    $response->assertSeeLivewire('user-list');
});

it('shows all the current users', function () {
    $user1 = User::factory()->create(['academic_session_id' => $this->academicSession->id]);
    $user2 = User::factory()->create(['academic_session_id' => $this->academicSession->id]);

    actingAs($this->admin);
    livewire(UserList::class)->assertSee($user1->email)->assertSee($user2->email);
});

it('can show user details', function () {
    $user = User::factory()->create(['academic_session_id' => $this->academicSession->id]);

    actingAs($this->admin);
    livewire(UserList::class)
        ->assertDontSee($user->username)
        ->call('showUserDetails', $user->id)
        ->assertSee($user->username);
});

describe('Can add/move admin rights to users', function () {
    it('can add or remove admin rights for a user', function () {
        $user1 = User::factory()->create(['academic_session_id' => $this->academicSession->id]);
        $user2 = User::factory()->create(['academic_session_id' => $this->academicSession->id]);

        actingAs($this->admin);
        expect($user1->fresh()->is_admin)->toBeFalse();
        expect($user2->fresh()->is_admin)->toBeFalse();
        livewire(UserList::class)->call('toggleAdmin', $user1->id);
        expect($user1->fresh()->is_admin)->toBeTrue();
        expect($user2->fresh()->is_admin)->toBeFalse();
        livewire(UserList::class)->call('toggleAdmin', $user1->id);
        expect($user1->fresh()->is_admin)->toBeFalse();
        expect($user2->fresh()->is_admin)->toBeFalse();
    });

    it('makes a new admin have admin rights in all other academic sessions', function () {
        $academicSession2 = AcademicSession::factory()->create();
        $user1 = User::factory()->create(['academic_session_id' => $this->academicSession->id, 'username' => 'user1']);
        $user2 = User::factory()->create(['academic_session_id' => $this->academicSession->id, 'username' => 'user2']);
        $otherUser1 = User::factory()->create(['academic_session_id' => $academicSession2->id, 'username' => 'user1']);

        actingAs($this->admin);
        livewire(UserList::class)->call('toggleAdmin', $user1->id);
        expect($user1->fresh()->is_admin)->toBeTrue();
        expect($otherUser1->fresh()->is_admin)->toBeTrue();
        expect($user2->fresh()->is_admin)->toBeFalse();
        livewire(UserList::class)->call('toggleAdmin', $user1->id);
        expect($user1->fresh()->is_admin)->toBeFalse();
        expect($otherUser1->fresh()->is_admin)->toBeFalse();
        expect($user2->fresh()->is_admin)->toBeFalse();
    });

    
});
