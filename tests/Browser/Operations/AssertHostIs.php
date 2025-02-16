<?php

declare(strict_types=1);

test('assert host is', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertHostIs('laravel.com');
});
