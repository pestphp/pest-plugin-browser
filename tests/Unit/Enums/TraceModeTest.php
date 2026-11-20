<?php

declare(strict_types=1);

use Pest\Browser\Enums\TraceMode;

it('keeps every outcome when the mode is on', function (): void {
    expect(TraceMode::On->shouldSave(failed: true))->toBeTrue()
        ->and(TraceMode::On->shouldSave(failed: false))->toBeTrue();
});

it('keeps only failures when the mode is retain-on-failure', function (): void {
    expect(TraceMode::RetainOnFailure->shouldSave(failed: true))->toBeTrue()
        ->and(TraceMode::RetainOnFailure->shouldSave(failed: false))->toBeFalse();
});
