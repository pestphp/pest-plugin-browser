<?php

declare(strict_types=1);

use Pest\Browser\Api\On;
use Pest\Browser\Api\Webpage;
use Pest\Browser\Enums\ColorScheme;

// grab all methods from PendingAwaitablePage
$reflection = new ReflectionClass(On::class);

$methods = array_map(
    fn (ReflectionMethod $method): string => $method->getName(),
    array_filter(
        $reflection->getMethods(ReflectionMethod::IS_PUBLIC),
        fn (ReflectionMethod $method): bool => $method->getName() !== '__construct' && $method->getName() !== '__call',
    ),
);

it('may visit a page', function (string $method): void {
    Route::get('/', fn (): string => file_get_contents(
        fixture('responsive.html'),
    ));

    /** @var Webpage $page */
    $page = visit('/')->on()->{$method}();

    $page->assertSee('Experience elegance across every device.')
        ->assertScreenshotMatches();
})->with($methods)->skipOnCI();

it('may visit a page in light/dark mode', function (ColorScheme $scheme): void {
    Route::get('/', fn (): string => '
        <html>
        <head>
            <style>
                body {
                    font-family: sans-serif;
                    padding: 2rem;
                }

                @media (prefers-color-scheme: dark) {
                    body {
                        background-color: #121212;
                        color: #f1f1f1;
                    }
                }

                @media (prefers-color-scheme: light) {
                    body {
                        background-color: #ffffff;
                        color: #000000;
                    }
                }
            </style>
        </head>
        <body>
            <div>
                <h1>Dark Mode Test</h1>
                <p>This is a test for dark mode.</p>
            </div>
        </body>
        </html>
    ');

    $page = visit('/');

    match ($scheme) {
        ColorScheme::DARK => $page->inDarkMode(),
        ColorScheme::LIGHT => $page->inLightMode(),
    };

    $page->assertScreenshotMatches();
})->with(ColorScheme::cases())->skipOnCI();

it('may visit a page with custom locale and timezone', function (): void {
    Route::get('/', fn (): string => '
        <html>
        <head></head>
        <body>
            <h1>Locale/Timezone Test</h1>
            <p id="info">Locale and timezone are set in browser context only.</p>
        </body>
        </html>
    ');

    $page = visit('/')
        ->withLocale('fr-FR')
        ->withTimezone('Europe/Paris');

    $locale = $page->script('navigator.language');
    expect($locale)->toBe('fr-FR');

    $timezone = $page->script('Intl.DateTimeFormat().resolvedOptions().timeZone');
    expect($timezone)->toBe('Europe/Paris');
});

it('may visit a page with a custom userAgent', function (): void {
    Route::get('/', fn (): string => '
        <html>
        <head></head>
        <body>
            <h1>User Agent Test</h1>
        </body>
        </html>
    ');

    $page = visit('/')
        ->withUserAgent('Pest-Browser');

    $userAgent = $page->script('navigator.userAgent');
    expect($userAgent)->toBe('Pest-Browser');
});

it('may visit external URLs', function (): void {
    $page = visit('https://example.com');

    $page->assertSee('Example Domain');
});
