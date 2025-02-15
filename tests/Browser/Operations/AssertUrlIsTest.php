<?php

declare(strict_types=1);

test('assert url is', function (): void {
    $url = 'https://laravel.com';

    $browser = $this->visit($url);

    $browser->assertUrlIs($url);
});

