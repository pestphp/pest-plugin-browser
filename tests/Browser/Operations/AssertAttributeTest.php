<?php

declare(strict_types=1);

test('assert attribute', function () {
    $this->visit('/')
        ->assertAttribute('main', 'id', 'app');
});
