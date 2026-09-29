<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Playwright;
use PHPUnit\Framework\ExpectationFailedException;

it('can open a page after initialization fails', function (): void {
    Route::get('/', fn (): string => '<div>Welcome</div>');

    $timeout = Client::instance()->timeout();

    Playwright::browser(BrowserType::CHROME);

    expect(Client::instance()->timeout())->toBe($timeout);

    (new ReflectionProperty(Playwright::class, 'browserTypes'))->setValue([]);

    expect(fn () => Playwright::browser(BrowserType::CHROME))
        ->toThrow(ExpectationFailedException::class, 'Assertion error');

    expect(Client::instance()->timeout())->toBe($timeout);

    $this->__markAsBrowserTest();

    visit('/')->assertSee('Welcome');
});
