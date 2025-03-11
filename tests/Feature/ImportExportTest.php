<?php

use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'is_admin' => true,
    ]);
});

it('has importexport page', function () {
    $response = $this->actingAs($this->admin)->get('/importexport');

    $response->assertStatus(200);
});
