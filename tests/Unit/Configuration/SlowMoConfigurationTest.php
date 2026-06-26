<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Playwright\Playwright;

beforeEach(function (): void {
    // Reset Playwright state before each test
    Playwright::setSlowMo(0);
});

it('defaults slow motion to zero', function (): void {
    expect(Playwright::slowMo())->toBe(0);
});

it('can set slow motion via configuration', function (): void {
    $config = new Configuration();

    $result = $config->slowMo(250);

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::slowMo())->toBe(250);
});

it('uses a sensible default when no value is given', function (): void {
    $config = new Configuration();

    $config->slowMo();

    expect(Playwright::slowMo())->toBe(Playwright::DEFAULT_SLOW_MO);
});

it('follows fluent interface pattern', function (): void {
    $config = new Configuration();

    $result = $config
        ->slowMo(500)
        ->timeout(10000);

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::slowMo())->toBe(500);
});

it('stores slow motion in Playwright global state', function (): void {
    expect(Playwright::slowMo())->toBe(0);

    Playwright::setSlowMo(1000);

    expect(Playwright::slowMo())->toBe(1000);
});
