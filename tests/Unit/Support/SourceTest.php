<?php

declare(strict_types=1);

use Pest\Browser\Support\Source;

it('places sources under tests/Browser/Source', function (): void {
    Source::save('<html></html>', 'test-source.html');

    expect(file_exists(Source::path('test-source.html')))
        ->toBeTrue();
});

it('saves sources with .html extension when no extension is provided', function (): void {
    Source::save('<html></html>', 'test-source');

    expect(file_exists(Source::path('test-source.html')))
        ->toBeTrue();
});

it('saves sources with .html extension when no extension is provided and the filename starts with a slash', function (): void {
    Source::save('<html></html>', '/test-source');

    expect(file_exists(Source::path('test-source.html')))
        ->toBeTrue();
});

it('saves the given content as-is', function (): void {
    Source::save('<html><body>Hello World</body></html>', 'test-source-content');

    expect(file_get_contents(Source::path('test-source-content.html')))
        ->toBe('<html><body>Hello World</body></html>');
});

it('saves sources using the test name when no filename is given', function (): void {
    $filename = Source::save('<html></html>');

    expect($filename)->not->toContain('__pest_evaluable_')
        ->and(file_exists(Source::path($filename)))->toBeTrue();
});

it('cleans up the sources directory', function (): void {
    Source::save('<html></html>', 'test-source-cleanup');

    Source::cleanup();

    expect(file_exists(Source::path('test-source-cleanup.html')))
        ->toBeFalse();
});
