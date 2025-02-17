<?php

declare(strict_types=1);

test('assert host is', function () {
    $this->visit('http://localhost')
        ->assertHostIs('localhost');
});
