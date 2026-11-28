<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Enums\TraceMode;
use Pest\Browser\Playwright\Playwright;

beforeEach(function (): void {
    $this->originalTraceMode = Playwright::traceMode();
    $this->originalShouldOpenTraces = Playwright::shouldOpenTraces();

    Playwright::setShouldTrace(open: true, mode: TraceMode::OFF);
});

afterEach(function (): void {
    Playwright::setShouldTrace(open: $this->originalShouldOpenTraces, mode: $this->originalTraceMode);
});

it('defaults tracing to off', function (): void {
    expect(Playwright::shouldTrace())->toBeFalse()
        ->and(Playwright::traceMode())->toBe(TraceMode::OFF)
        ->and(Playwright::shouldSaveTrace(failed: true))->toBeFalse()
        ->and(Playwright::shouldSaveTrace(failed: false))->toBeFalse();
});

it('retains traces on failure by default', function (): void {
    $result = new Configuration()->trace();

    expect($result)->toBeInstanceOf(Configuration::class)
        ->and(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::shouldOpenTraces())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::RETAIN_ON_FAILURE)
        ->and(Playwright::shouldSaveTrace(failed: true))->toBeTrue()
        ->and(Playwright::shouldSaveTrace(failed: false))->toBeFalse();
});

it('can disable opening the trace viewer', function (): void {
    new Configuration()->trace(open: false);

    expect(Playwright::shouldOpenTraces())->toBeFalse()
        ->and(Playwright::traceMode())->toBe(TraceMode::RETAIN_ON_FAILURE);
});

it('can keep the trace of every test', function (): void {
    new Configuration()->trace(all: true);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::shouldOpenTraces())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::ON)
        ->and(Playwright::shouldSaveTrace(failed: true))->toBeTrue()
        ->and(Playwright::shouldSaveTrace(failed: false))->toBeTrue();
});

it('can keep every trace without opening the viewer', function (): void {
    new Configuration()->trace(open: false, all: true);

    expect(Playwright::shouldOpenTraces())->toBeFalse()
        ->and(Playwright::traceMode())->toBe(TraceMode::ON);
});
