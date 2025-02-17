<?php

declare(strict_types=1);

test('assert host is not', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertHostIsNot('cloud.laravel.com');
});
