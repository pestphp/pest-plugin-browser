<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Playwright\Playwright;

beforeEach(function (): void {
    $ref = new ReflectionClass(Playwright::class);

    $flag = $ref->getProperty('recordVideoOnFailure');
    $flag->setValue(null, false);

    $page = $ref->getProperty('pendingVideoPage');
    $page->setValue(null, null);

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

it('returns null for pendingVideoPage and pendingVideoDestName by default', function (): void {
    expect(Playwright::pendingVideoPage())->toBeNull();
    expect(Playwright::pendingVideoDestName())->toBeNull();
});

it('clears video recording state', function (): void {
    Playwright::clearVideoRecording();

    expect(Playwright::pendingVideoPage())->toBeNull();
    expect(Playwright::pendingVideoDestName())->toBeNull();
});
