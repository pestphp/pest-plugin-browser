<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Playwright\Playwright;

beforeEach(function (): void {
    // Reflect into private Playwright state and reset video-related fields
    $ref = new ReflectionClass(Playwright::class);

    $flag = $ref->getProperty('recordVideoOnFailure');
    $flag->setValue(null, false);

    $dir = $ref->getProperty('pendingVideoDir');
    $dir->setValue(null, null);

    $name = $ref->getProperty('pendingVideoDestName');
    $name->setValue(null, null);
});

it('returns false for recordVideoOnFailure by default', function (): void {
    expect(Playwright::shouldRecordVideoOnFailure())->toBeFalse();
});

it('sets recordVideoOnFailure flag via Playwright', function (): void {
    Playwright::setRecordVideoOnFailure();

    expect(Playwright::shouldRecordVideoOnFailure())->toBeTrue();
});

it('enables video recording via Configuration fluent API', function (): void {
    $config = new Configuration();

    $result = $config->recordVideoOnFailure();

    expect($result)->toBeInstanceOf(Configuration::class);
    expect(Playwright::shouldRecordVideoOnFailure())->toBeTrue();
});

it('registers a video recording with a directory and destination name', function (): void {
    expect(Playwright::pendingVideoDir())->toBeNull();
    expect(Playwright::pendingVideoDestName())->toBeNull();

    Playwright::registerVideoRecording('/tmp/test-dir', 'my_test');

    expect(Playwright::pendingVideoDir())->toBe('/tmp/test-dir');
    expect(Playwright::pendingVideoDestName())->toBe('my_test');
});

it('clears video recording state', function (): void {
    Playwright::registerVideoRecording('/tmp/test-dir', 'my_test');

    Playwright::clearVideoRecording();

    expect(Playwright::pendingVideoDir())->toBeNull();
    expect(Playwright::pendingVideoDestName())->toBeNull();
});
