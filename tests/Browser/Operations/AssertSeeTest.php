<?php

declare(strict_types=1);

test('assert sees', function () {
    $this->visit('/')
        ->assertSee('Pest is a testing framework');
});

test('assert sees ignoring case', function () {
    $this->visit('/')
        ->assertSee('pest is a testing framework', true);
});
