<?php

declare(strict_types=1);

use Pest\Browser\Enums\TraceMode;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Plugin;

beforeEach(function (): void {
    Playwright::resetTracing();
});

afterEach(function (): void {
    Playwright::resetTracing();
});

it('leaves tracing off when the argument is absent', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php']);

    expect(Playwright::shouldTrace())->toBeFalse()
        ->and(Playwright::traceMode())->toBe(TraceMode::RetainOnFailure)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('retains traces on failure when passed bare', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--trace']);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::RetainOnFailure)
        ->and(Playwright::shouldSaveTrace(failed: true))->toBeTrue()
        ->and(Playwright::shouldSaveTrace(failed: false))->toBeFalse()
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('keeps a following argument that is not a mode', function (): void {
    $arguments = new Plugin()->handleArguments(['--trace', 'tests/Unit/ExampleTest.php']);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::RetainOnFailure)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('keeps every trace when the mode is on', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--trace=on']);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::On)
        ->and(Playwright::shouldSaveTrace(failed: true))->toBeTrue()
        ->and(Playwright::shouldSaveTrace(failed: false))->toBeTrue()
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('accepts an explicit retain-on-failure mode', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--trace=retain-on-failure']);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::RetainOnFailure)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('rejects an unknown trace mode', function (): void {
    expect(fn (): array => new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--trace=always']))
        ->toThrow(InvalidArgumentException::class, 'The specified trace mode is not supported. Supported modes are: on, retain-on-failure.');
});

it('keeps every trace when retainOnFailure is disabled in the configuration', function (): void {
    new Pest\Browser\Configuration()->trace(retainOnFailure: false);

    expect(Playwright::shouldTrace())->toBeTrue()
        ->and(Playwright::traceMode())->toBe(TraceMode::On)
        ->and(Playwright::shouldSaveTrace(failed: false))->toBeTrue();
});
