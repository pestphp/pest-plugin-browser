<?php

declare(strict_types=1);

test('assert attribute', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertAttribute('html', 'data-theme', 'light');

});
