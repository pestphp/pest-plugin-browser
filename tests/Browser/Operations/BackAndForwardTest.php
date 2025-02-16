<?php

declare(strict_types=1);

test('goes back and forward', function (): void {
    $initialUrl = 'https://laravel.com';
    $secondaryUrl = 'https://laravel-news.com';

    $browser = $this->visit($initialUrl);

    $browser->visit($secondaryUrl)
        ->back();

    $browser->assertUrlIs($initialUrl)
        ->forward();

    $browser->assertUrlIs($secondaryUrl);
});
