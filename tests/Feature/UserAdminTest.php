<?php

use App\Models\AcademicSession;
use App\Models\User;

beforeEach(function () {
    $this->academicSession = AcademicSession::factory()->create();
    $this->admin = User::factory()->create(['is_admin' => true, 'academic_session_id' => $this->academicSession->id]);
});

it('has useradmin page', function () {
    $response = $this->actingAs($this->admin)->get('/users');

    $response->assertStatus(200);
});

describe('Can add/move admin rights to users', function () {
    it('can add admin rights to a user', function () {});

    it('can remove admin rights from a user', function () {});

    it('makes a new admin have admin rights in all other academic sessions', function () {
        $this->fail('TODO');
    });

    it('removes admin rights from a user in all other academic sessions when they are demoted', function () {});
});
