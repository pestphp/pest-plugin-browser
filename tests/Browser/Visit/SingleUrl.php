<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
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
})->with($methods)->onlyOnMac()->skipOnCI();

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
                        .light { display: none; }
                }

                @media (prefers-color-scheme: light) {
                    body {
                        background-color: #ffffff;
                        color: #000000;
                    }
                        .dark { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="dark">
                <h1>Dark Mode Test</h1>
                <p>This is a test for dark mode.</p>
            </div>
            <div class="light">
                <h1>Light Mode Test</h1>
                <p>This is a test for light mode.</p>
            </div>
        </body>
        </html>
    ');

    $page = visit('/');

    $page = match ($scheme) {
        ColorScheme::DARK => $page->inDarkMode()
            ->assertSee('Dark Mode Test')
            ->assertDontSee('Light Mode Test'),
        ColorScheme::LIGHT => $page->inLightMode()
            ->assertSee('Light Mode Test')
            ->assertDontSee('Dark Mode Test'),
    };

    $page->assertScreenshotMatches();
})->with(ColorScheme::cases())->onlyOnMac()->skipOnCI();

it('may visit a page in both light and dark modes', function (): void {
    Route::get('/', fn (): string => '
        <html lang="en">
        <head>
            <title>Example test page</title>
            <style>
                body {
                    font-family: sans-serif;
                    padding: 2rem;
                }
                .text-red-500 {
                    color: #fb2c36;
                }
                .text-red-600 {
                    color: #e7000b;
                }
                @media (prefers-color-scheme: dark) {
                    body {
                        background-color: #121212;
                        color: #f1f1f1;
                    }
                    .light {
                        display: none;
                    }
                }
                @media (prefers-color-scheme: light) {
                    body {
                        background-color: #ffffff;
                        color: #000000;
                    }
                    .dark {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <main>
                <h1>Light and Dark Mode Test</h1>
                <p>This is a test for both light and dark modes.</p>
                <div class="dark text-red-500">This is only visible in dark mode</div>
                <div class="light text-red-600">This is only visible in light mode</div>
            </main>
        </body>
        </html>
    ');

    $pages = visit('/')
        ->inLightAndDarkMode()
        ->assertNoAccessibilityIssues(level: 3);

    $lightModePage = $pages[0];
    $darkModePage = $pages[1];

    $lightModeColorScheme = $lightModePage->script('window.matchMedia("(prefers-color-scheme: light)").matches');
    $darkModeColorScheme = $darkModePage->script('window.matchMedia("(prefers-color-scheme: dark)").matches');

    expect($lightModeColorScheme)->toBeTrue()
        ->and($darkModeColorScheme)->toBeTrue();

    $lightModePage->assertSee('This is only visible in light mode')
        ->assertDontSee('This is only visible in dark mode');
    $darkModePage->assertSee('This is only visible in dark mode')
        ->assertDontSee('This is only visible in light mode');
});

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
