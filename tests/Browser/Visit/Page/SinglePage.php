<?php

declare(strict_types=1);

use Pest\Browser\Page;

it('may visit a object page with custom locale and timezone', function (): void {
    Route::get('/', fn (): string => '
        <html>
        <head></head>
        <body>
            <h1>Locale/Timezone Test</h1>
            <p id="info">Locale and timezone are set in browser context only.</p>
        </body>
        </html>
    ');

    $page = visit(new HomePage);

    $locale = $page->script('navigator.language');
    expect($locale)->toBe('fr-FR');

    $timezone = $page->script('Intl.DateTimeFormat().resolvedOptions().timeZone');
    expect($timezone)->toBe('Europe/Paris');
});

it('may visit external URLs with object page', function (): void {
    $page = visit(new ExternalPage);

    $page->assertSee('Example Domain');
});

final class ExternalPage extends Page
{
    public function url(): string
    {
        return 'https://example.com';
    }
}

final class HomePage extends Page
{
    public function url(): string
    {
        return '/';
    }

    #[Override]
    public function timezone(): string
    {
        return 'Europe/Paris';
    }

    #[Override]
    public function locale(): string
    {
        return 'fr-FR';
    }
}
