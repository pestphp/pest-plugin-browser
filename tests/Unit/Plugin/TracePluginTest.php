<?php

declare(strict_types=1);

use Pest\Browser\Configuration;
use Pest\Browser\Enums\TraceMode;
use Pest\Browser\Exceptions\TraceModeNotSupportedException;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Plugin;

beforeEach(function (): void {
    $this->originalTraceMode = Playwright::traceMode();
    $this->originalShouldOpenTraces = Playwright::shouldOpenTraces();

    Playwright::setShouldTrace(open: true, mode: TraceMode::OFF);
});

afterEach(function (): void {
    Playwright::setShouldTrace(open: $this->originalShouldOpenTraces, mode: $this->originalTraceMode);
});

it('leaves tracing off when the argument is absent', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php']);

    expect(Playwright::shouldTrace())->toBeFalse()
        ->and(Playwright::traceMode())->toBe(TraceMode::OFF)
        ->and(Playwright::shouldSaveTrace(failed: true))->toBeFalse()
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('retains traces on failure when passed bare', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--trace']);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::RETAIN_ON_FAILURE)
        ->and(Playwright::shouldSaveTrace(failed: true))->toBeTrue()
        ->and(Playwright::shouldSaveTrace(failed: false))->toBeFalse()
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('keeps a following argument that is not a trace mode', function (): void {
    $arguments = new Plugin()->handleArguments(['--trace', 'tests/Unit/ExampleTest.php']);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::RETAIN_ON_FAILURE)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('keeps every trace when the mode is on', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--trace=on']);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::ON)
        ->and(Playwright::shouldSaveTrace(failed: true))->toBeTrue()
        ->and(Playwright::shouldSaveTrace(failed: false))->toBeTrue()
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('reads the trace mode from the following argument', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--trace', 'on']);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::ON)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('accepts an explicit retain-on-failure mode', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--trace=retain-on-failure']);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::RETAIN_ON_FAILURE)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('accepts an explicit off mode', function (): void {
    new Configuration()->trace(all: true);

    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--trace=off']);

    expect(Playwright::shouldTrace())->toBeFalse()
        ->and(Playwright::traceMode())->toBe(TraceMode::OFF)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('accepts an explicit space-separated off mode', function (): void {
    new Configuration()->trace(all: true);

    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--trace', 'off']);

    expect(Playwright::shouldTrace())->toBeFalse()
        ->and(Playwright::traceMode())->toBe(TraceMode::OFF)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('strips duplicate trace arguments and lets the last explicit mode win', function (): void {
    $arguments = new Plugin()->handleArguments([
        '--trace=retain-on-failure',
        'tests/Unit/ExampleTest.php',
        '--trace=on',
    ]);

    expect(Playwright::traceMode())->toBe(TraceMode::ON)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('re-enables tracing when bare --trace follows --trace=off', function (): void {
    $arguments = new Plugin()->handleArguments(['--trace=off', '--trace', 'tests/Unit/ExampleTest.php']);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::RETAIN_ON_FAILURE)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('preserves a configured mode when --trace is passed bare', function (): void {
    new Configuration()->trace(open: false, all: true);

    $arguments = new Plugin()->handleArguments(['--trace', 'tests/Unit/ExampleTest.php']);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::shouldOpenTraces())->toBeFalse()
        ->and(Playwright::traceMode())->toBe(TraceMode::ON)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('overrides a configured mode when an explicit mode is passed', function (): void {
    new Configuration()->trace(open: false, all: true);

    $arguments = new Plugin()->handleArguments(['--trace=retain-on-failure', 'tests/Unit/ExampleTest.php']);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::shouldOpenTraces())->toBeFalse()
        ->and(Playwright::traceMode())->toBe(TraceMode::RETAIN_ON_FAILURE)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('rejects an unknown trace mode without mutating state', function (): void {
    expect(fn (): array => new Plugin()->handleArguments(['--trace=on', '--trace=always']))
        ->toThrow(
            TraceModeNotSupportedException::class,
            'The specified trace mode is not supported. Supported modes are: off, on, retain-on-failure.',
        );

    expect(Playwright::shouldTrace())->toBeFalse()
        ->and(Playwright::traceMode())->toBe(TraceMode::OFF);
});

it('rejects an empty trace mode value', function (): void {
    expect(fn (): array => new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--trace=']))
        ->toThrow(
            TraceModeNotSupportedException::class,
            'The specified trace mode is not supported. Supported modes are: off, on, retain-on-failure.',
        );
});
