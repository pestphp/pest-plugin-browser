<?php

declare(strict_types=1);

use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Enums\ColorScheme;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Plugin;

const WORKER_ARGUMENTS = ['vendor/bin/phpunit', '--no-output'];

beforeEach(function (): void {
    $this->originalBrowserType = Playwright::defaultBrowserType();
    $this->originalShouldTrace = Playwright::shouldTrace();
    $this->originalColorScheme = Playwright::defaultColorScheme();
    $this->originalServer = $_SERVER;
    $this->originalEnvironment = $_ENV;

    // This suite runs in parallel: the worker running this test already holds the globals of its own run.
    unset($_SERVER['PARATEST'], $_SERVER['PEST_PARALLEL_GLOBAL_BROWSER_ARGUMENTS']);
});

afterEach(function (): void {
    Playwright::setDefaultBrowserType($this->originalBrowserType);
    setShouldTrace($this->originalShouldTrace);
    Playwright::setColorScheme($this->originalColorScheme);

    $_SERVER = $this->originalServer;
    $_ENV = $this->originalEnvironment;
});

function setShouldTrace(bool $shouldTrace): void
{
    new ReflectionProperty(Playwright::class, 'shouldTrace')->setValue(null, $shouldTrace);
}

function runInWorker(): void
{
    Playwright::setDefaultBrowserType(BrowserType::CHROME);
    setShouldTrace(false);
    Playwright::setColorScheme(ColorScheme::LIGHT);

    $_SERVER['PARATEST'] = '1';

    new Plugin()->handleArguments(WORKER_ARGUMENTS);
}

it('runs the workers in the browser passed to the parallel run', function (): void {
    new Plugin()->handleArguments(['vendor/bin/pest', '--browser', 'safari', '--parallel']);

    runInWorker();

    expect(Playwright::defaultBrowserType())->toBe(BrowserType::SAFARI);
});

it('records traces in the workers when the parallel run records them', function (): void {
    new Plugin()->handleArguments(['vendor/bin/pest', '--trace', '--parallel']);

    runInWorker();

    expect(Playwright::shouldTrace())->toBeTrue();
});

it('renders the pages of the workers in the color scheme of the parallel run', function (): void {
    new Plugin()->handleArguments(['vendor/bin/pest', '--dark', '--parallel']);

    runInWorker();

    expect(Playwright::defaultColorScheme())->toBe(ColorScheme::DARK);
});
