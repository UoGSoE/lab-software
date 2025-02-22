<?php

it('has useradmin page', function () {
    $response = $this->get('/useradmin');

    $response->assertStatus(200);
});

describe('Can add/move admin rights to users', function () {
    it('can add admin rights to a user', function () {
    });

    it('can remove admin rights from a user', function () {
    });

    it('makes a new admin have admin rights in all other academic sessions', function () {
    });

    it('removes admin rights from a user in all other academic sessions when they are demoted', function () {
    });
});
