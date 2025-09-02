<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Playwright\Playwright;

beforeEach(function (): void {
    Playwright::setLaunchArgs([]);
    Playwright::setHeadedLaunchArgs([]);
    Playwright::setHeadlessLaunchArgs([]);

    $reflection = new ReflectionClass(Playwright::class);
    $headlessProperty = $reflection->getProperty('headless');
    $headlessProperty->setValue(null, true);
});

it('can set custom launch arguments for all modes', function (): void {
    $args = ['--custom-flag', '--another-flag'];

    Playwright::setLaunchArgs($args);

    expect(Playwright::getEffectiveLaunchArgs())
        ->toBe($args);
});

it('can set custom launch arguments for headed mode only', function (): void {
    $headedArgs = ['--headed-flag'];

    Playwright::setHeadedLaunchArgs($headedArgs);

    expect(Playwright::getEffectiveLaunchArgs())->toBe([]);

    Playwright::headed();

    expect(Playwright::getEffectiveLaunchArgs())->toBe($headedArgs);
});

it('can set custom launch arguments for headless mode only', function (): void {
    $headlessArgs = ['--headless-flag'];

    Playwright::setHeadlessLaunchArgs($headlessArgs);

    expect(Playwright::getEffectiveLaunchArgs())->toBe($headlessArgs);

    Playwright::headed();

    expect(Playwright::getEffectiveLaunchArgs())->toBe([]);
});

it('merges global and mode-specific arguments correctly', function (): void {
    $globalArgs = ['--global-flag'];
    $headedArgs = ['--headed-flag'];
    $headlessArgs = ['--headless-flag'];

    Playwright::setLaunchArgs($globalArgs);
    Playwright::setHeadedLaunchArgs($headedArgs);
    Playwright::setHeadlessLaunchArgs($headlessArgs);

    expect(Playwright::getEffectiveLaunchArgs())->toBe(['--global-flag', '--headless-flag']);

    Playwright::headed();

    expect(Playwright::getEffectiveLaunchArgs())->toBe(['--global-flag', '--headed-flag']);
});

it('configuration withArgs method sets launch arguments', function (): void {
    $args = ['--config-flag'];

    $config = new Configuration();
    $result = $config->withArgs($args);

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::getEffectiveLaunchArgs())->toBe($args);
});

it('configuration withHeadedArgs method sets headed launch arguments', function (): void {
    $args = ['--headed-config-flag'];

    $config = new Configuration();
    $result = $config->withHeadedArgs($args);

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::getEffectiveLaunchArgs())->toBe([]);

    Playwright::headed();
    expect(Playwright::getEffectiveLaunchArgs())->toBe($args);
});

it('configuration withHeadlessArgs method sets headless launch arguments', function (): void {
    $args = ['--headless-config-flag'];

    $config = new Configuration();
    $result = $config->withHeadlessArgs($args);

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::getEffectiveLaunchArgs())->toBe($args);

    Playwright::headed();
    expect(Playwright::getEffectiveLaunchArgs())->toBe([]);
});

it('configuration methods can be chained', function (): void {
    $config = new Configuration();

    $result = $config
        ->withArgs(['--global'])
        ->withHeadedArgs(['--headed'])
        ->withHeadlessArgs(['--headless'])
        ->headed();

    expect($result)->toBeInstanceOf(Configuration::class);

    $args = Playwright::getEffectiveLaunchArgs();

    expect($args)->toContain('--global')
        ->and($args)->toContain('--headed');
});
