<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Playwright\Playwright;

beforeEach(function () {
    // Reset Playwright state before each test
    Playwright::setLaunchArgs([]);
    Playwright::setHeadedLaunchArgs([]);
    Playwright::setHeadlessLaunchArgs([]);
});

test('can set custom launch arguments for all modes', function () {
    $args = ['--custom-flag', '--another-flag'];

    Playwright::setLaunchArgs($args);

    expect(Playwright::getEffectiveLaunchArgs())
        ->toBe($args);
});

test('can set custom launch arguments for headed mode only', function () {
    $headedArgs = ['--headed-flag'];

    Playwright::setHeadedLaunchArgs($headedArgs);

    // In headless mode, should not include headed args
    expect(Playwright::getEffectiveLaunchArgs())
        ->toBe([]);

    // Switch to headed mode
    Playwright::headed();

    expect(Playwright::getEffectiveLaunchArgs())
        ->toBe($headedArgs);
});

test('can set custom launch arguments for headless mode only', function () {
    $headlessArgs = ['--headless-flag'];

    Playwright::setHeadlessLaunchArgs($headlessArgs);

    // In headless mode (default), should include headless args
    expect(Playwright::getEffectiveLaunchArgs())
        ->toBe($headlessArgs);

    // Switch to headed mode
    Playwright::headed();

    expect(Playwright::getEffectiveLaunchArgs())
        ->toBe([]);
});

test('merges global and mode-specific arguments correctly', function () {
    $globalArgs = ['--global-flag'];
    $headedArgs = ['--headed-flag'];
    $headlessArgs = ['--headless-flag'];

    Playwright::setLaunchArgs($globalArgs);
    Playwright::setHeadedLaunchArgs($headedArgs);
    Playwright::setHeadlessLaunchArgs($headlessArgs);

    // In headless mode (default)
    expect(Playwright::getEffectiveLaunchArgs())
        ->toBe(['--global-flag', '--headless-flag']);

    // Switch to headed mode
    Playwright::headed();

    expect(Playwright::getEffectiveLaunchArgs())
        ->toBe(['--global-flag', '--headed-flag']);
});

test('configuration withArgs method sets launch arguments', function () {
    $args = ['--config-flag'];

    $config = new Configuration();
    $result = $config->withArgs($args);

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::getEffectiveLaunchArgs())->toBe($args);
});

test('configuration withHeadedArgs method sets headed launch arguments', function () {
    $args = ['--headed-config-flag'];

    $config = new Configuration();
    $result = $config->withHeadedArgs($args);

    expect($result)->toBeInstanceOf(Configuration::class);

    // Should not be active in headless mode
    expect(Playwright::getEffectiveLaunchArgs())->toBe([]);

    // Should be active in headed mode
    Playwright::headed();
    expect(Playwright::getEffectiveLaunchArgs())->toBe($args);
});

test('configuration withHeadlessArgs method sets headless launch arguments', function () {
    $args = ['--headless-config-flag'];

    $config = new Configuration();
    $result = $config->withHeadlessArgs($args);

    expect($result)->toBeInstanceOf(Configuration::class);

    // Should be active in headless mode (default)
    expect(Playwright::getEffectiveLaunchArgs())->toBe($args);

    // Should not be active in headed mode
    Playwright::headed();
    expect(Playwright::getEffectiveLaunchArgs())->toBe([]);
});

test('configuration methods can be chained', function () {
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
