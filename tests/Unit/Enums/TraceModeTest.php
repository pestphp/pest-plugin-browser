<?php

declare(strict_types=1);

use Pest\Browser\Enums\TraceMode;

it('disables tracing and never saves when the mode is off', function (): void {
    expect(TraceMode::OFF->isEnabled())->toBeFalse()
        ->and(TraceMode::OFF->shouldSave(failed: true))->toBeFalse()
        ->and(TraceMode::OFF->shouldSave(failed: false))->toBeFalse();
});

it('keeps every outcome when the mode is on', function (): void {
    expect(TraceMode::ON->isEnabled())->toBeTrue()
        ->and(TraceMode::ON->shouldSave(failed: true))->toBeTrue()
        ->and(TraceMode::ON->shouldSave(failed: false))->toBeTrue();
});

it('keeps only failures when the mode is retain-on-failure', function (): void {
    expect(TraceMode::RETAIN_ON_FAILURE->isEnabled())->toBeTrue()
        ->and(TraceMode::RETAIN_ON_FAILURE->shouldSave(failed: true))->toBeTrue()
        ->and(TraceMode::RETAIN_ON_FAILURE->shouldSave(failed: false))->toBeFalse();
});
