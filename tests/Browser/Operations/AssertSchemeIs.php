<?php

declare(strict_types=1);

test('assert scheme', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertSchemeIs('https');
});
