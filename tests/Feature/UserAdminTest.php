<?php

it('has useradmin page', function () {
    $response = $this->get('/useradmin');

    $response->assertStatus(200);
});
