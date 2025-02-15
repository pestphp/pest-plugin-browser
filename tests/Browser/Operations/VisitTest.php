<?php

declare(strict_types=1);

test('visit', function (): void {
    $url = 'https://laravel.com';

    $browser = $this->visit($url);

    $browser->assertUrlIs($url);
});
