<?php

declare(strict_types=1);

test('when', function (): void {
    $url = 'https://laravel.com';

    $browser = $this->visit($url);

    $browser->when(
        new Pest\Browser\Conditions\See('Laravel - The PHP Framework For Web Artisans'),
        function (Pest\Browser\PendingTest $browser): void {
            $browser->clickLink('Get Started')
                ->assertSee('Installation');
        },
        function (Pest\Browser\PendingTest $browser): void {
            $browser->assertSee('Laravel');
        }
    );
});

test('when using only the then callback', function (): void {
    $url = 'https://laravel.com';

    $browser = $this->visit($url);

    $browser->when(
        new Pest\Browser\Conditions\See('Laravel - The PHP Framework For Web Artisans'),
        function (Pest\Browser\PendingTest $browser): void {
            $browser->clickLink('Get Started')
                ->assertSee('Installation');
        }
    );
});
