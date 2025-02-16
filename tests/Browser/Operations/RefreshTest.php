<?php

declare(strict_types=1);

test('refreshes', function (): void {
    $url = 'https://laravel.com';

    $browser = $this->visit($url)
        ->refresh();

    $browser->assertUrlIs($url);
});
