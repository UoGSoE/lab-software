<?php

it('has academicsessions page', function () {
    $response = $this->get('/academicsessions');

    $response->assertStatus(200);
});
