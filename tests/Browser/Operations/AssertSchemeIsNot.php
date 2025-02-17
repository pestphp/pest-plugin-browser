<?php

declare(strict_types=1);

test('assert scheme is not', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertSchemeIsNot('http');
});
