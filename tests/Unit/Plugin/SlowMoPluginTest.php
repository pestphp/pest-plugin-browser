<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Plugin;

beforeEach(function (): void {
    Playwright::setSlowMo(0);
});

it('leaves slow motion off when the argument is absent', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php']);

    expect(Playwright::slowMo())->toBe(0)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('uses the default when passed bare', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--slow-mo']);

    expect(Playwright::slowMo())->toBe(Playwright::DEFAULT_SLOW_MO)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('reads the value from the following argument', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--slow-mo', '250']);

    expect(Playwright::slowMo())->toBe(250)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('reads the value from the same argument', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--slow-mo=250']);

    expect(Playwright::slowMo())->toBe(250)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('keeps a following argument that is not a number', function (): void {
    $arguments = new Plugin()->handleArguments(['--slow-mo', 'tests/Unit/ExampleTest.php']);

    expect(Playwright::slowMo())->toBe(Playwright::DEFAULT_SLOW_MO)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});

it('falls back to the default when the same-argument value is not a number', function (): void {
    $arguments = new Plugin()->handleArguments(['tests/Unit/ExampleTest.php', '--slow-mo=fast']);

    expect(Playwright::slowMo())->toBe(Playwright::DEFAULT_SLOW_MO)
        ->and($arguments)->toBe(['tests/Unit/ExampleTest.php']);
});
