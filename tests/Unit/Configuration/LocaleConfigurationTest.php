<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Playwright\Playwright;

beforeEach(function (): void {
    Playwright::setDefaultLocale('en-US');
});

it('can set locale via configuration', function (): void {
    $config = new Configuration();

    $result = $config->withLocale('fr-FR');

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::defaultLocale())->toBe('fr-FR');
});

it('follows fluent interface pattern', function (): void {
    $config = new Configuration();

    $result = $config
        ->withLocale('fr-FR')
        ->userAgent('Test Agent')
        ->timeout(10000);

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::defaultLocale())->toBe('fr-FR');
});

it('stores locale in Playwright global state', function (): void {
    expect(Playwright::defaultLocale())->toBe('en-US');

    Playwright::setDefaultLocale('ja-JP');

    expect(Playwright::defaultLocale())->toBe('ja-JP');
});

it('can override locale multiple times', function (): void {
    Playwright::setDefaultLocale('fr-FR');
    expect(Playwright::defaultLocale())->toBe('fr-FR');

    Playwright::setDefaultLocale('de-DE');
    expect(Playwright::defaultLocale())->toBe('de-DE');

    Playwright::setDefaultLocale('ja-JP');
    expect(Playwright::defaultLocale())->toBe('ja-JP');
});
