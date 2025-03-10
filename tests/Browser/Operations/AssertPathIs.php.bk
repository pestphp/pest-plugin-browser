<?php

declare(strict_types=1);

test('assert path is', function () {
    $url = 'https://laravel.com/docs/11.x';

    $this->visit($url)
        ->assertPathIs('/docs/11.x');
});

test('assert path is with wildcard', function () {
    $url = 'https://laravel.com/docs/11.x';

    $this->visit($url)
        ->assertPathIs('/docs/*.x');
});
