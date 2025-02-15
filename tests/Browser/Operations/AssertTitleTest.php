<?php

declare(strict_types=1);

test('assert title', function (): void {
    $url = 'https://laravel.com';

    $browser = $this->visit($url);

    $browser->assertTitle('Laravel - The PHP Framework For Web Artisans');
});
