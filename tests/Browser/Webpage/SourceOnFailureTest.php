<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Support\Source;
use PHPUnit\Framework\ExpectationFailedException;

afterEach(function (): void {
    $property = new ReflectionProperty(Playwright::class, 'shouldSaveSourceOnFailedAssertions');
    $property->setValue(null, false);
});

it('may not save the page source when an assertion fails', function (): void {
    Route::get('/', fn (): string => '<div>Hello World</div>');

    $page = visit('/');

    try {
        $page->assertSee('Goodbye World');
    } catch (ExpectationFailedException $exception) {
        $filename = str_replace('__pest_evaluable_', '', test()->name());

        expect($exception->getMessage())->not->toContain('The source of the page has been saved to')
            ->and(file_exists(Source::path($filename)))->toBeFalse();

        return;
    }

    $this->fail('The assertion did not fail as expected.');
});

it('may save the page source when an assertion fails', function (): void {
    Playwright::setShouldSaveSourceOnFailedAssertions();

    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    try {
        $page->assertSee('Goodbye World');
    } catch (ExpectationFailedException $exception) {
        $filename = str_replace('__pest_evaluable_', '', test()->name());

        expect($exception->getMessage())->toContain("The source of the page has been saved to [Tests/Browser/Source/$filename].")
            ->and(file_exists(Source::path($filename)))->toBeTrue()
            ->and((string) file_get_contents(Source::path($filename)))->toContain('<div id="content">Hello World</div>');

        return;
    }

    $this->fail('The assertion did not fail as expected.');
});
