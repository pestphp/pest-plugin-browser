<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Enums\TraceMode;
use Pest\Browser\Playwright\Playwright;

beforeEach(function (): void {
    Playwright::resetTracing();
});

afterEach(function (): void {
    Playwright::resetTracing();
});

it('retains traces on failure by default', function (): void {
    $result = new Configuration()->trace();

    expect($result)->toBeInstanceOf(Configuration::class)
        ->and(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::shouldOpenTraces())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::RetainOnFailure)
        ->and(Playwright::shouldSaveTrace(failed: true))->toBeTrue()
        ->and(Playwright::shouldSaveTrace(failed: false))->toBeFalse();
});

it('can disable opening the trace viewer', function (): void {
    new Configuration()->trace(open: false);

    expect(Playwright::shouldOpenTraces())->toBeFalse()
        ->and(Playwright::traceMode())->toBe(TraceMode::RetainOnFailure);
});

it('can keep the trace of every test', function (): void {
    new Configuration()->trace(retainOnFailure: false);

    expect(Playwright::traceMode())->toBe(TraceMode::On)
        ->and(Playwright::shouldSaveTrace(failed: false))->toBeTrue();
});

it('can keep every trace without opening the viewer', function (): void {
    new Configuration()->trace(open: false, retainOnFailure: false);

    expect(Playwright::shouldOpenTraces())->toBeFalse()
        ->and(Playwright::traceMode())->toBe(TraceMode::On);
});
