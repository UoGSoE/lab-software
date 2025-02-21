<?php

it('has importexport page', function () {
    $response = $this->get('/importexport');

    $response->assertStatus(200);
});
