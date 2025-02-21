<?php

it('has settings page', function () {
    $response = $this->get('/settings');

    $response->assertStatus(200);
});
