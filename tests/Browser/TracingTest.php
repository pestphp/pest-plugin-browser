<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Tracing;
use PHPUnit\Framework\ExpectationFailedException;

it('saves a trace with the steps and the test source', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $tracing = $page->page()->context()->tracing();
    $tracing?->start('tracing-saved');

    expect(fn (): Pest\Browser\Api\Webpage => $page->assertSee('Hello Universe'))
        ->toThrow(ExpectationFailedException::class, 'A trace of the test has been saved to [tests/Browser/Traces/tracing-saved.zip]');

    $paths = Tracing::stopAll(save: true);

    expect($paths)->toHaveCount(1)
        ->and($paths[0])->toEndWith('tests/Browser/Traces/tracing-saved.zip')
        ->and($paths[0])->toBeFile();

    $zip = new ZipArchive();
    $zip->open($paths[0]);

    $trace = (string) $zip->getFromName('trace.trace');
    $stacks = json_decode((string) $zip->getFromName('trace.stacks'), true);

    expect($trace)->toContain("assertSee('Hello Universe')")
        ->and($trace)->toContain('"type":"frame-snapshot"')
        ->and($trace)->toContain('"type":"screencast-frame"')
        ->and($stacks['files'])->toBe([__FILE__])
        ->and($zip->getFromName('resources/src@'.sha1(__FILE__).'.txt'))->toBe(file_get_contents(__FILE__));

    $zip->close();

    unlink($paths[0]);
});

it('discards the trace when it is not needed', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->page()->context()->tracing()?->start('tracing-discarded');

    $page->assertSee('Hello World');

    expect(Tracing::stopAll(save: false))->toBe([])
        ->and(Tracing::isRecording())->toBeFalse()
        ->and(__DIR__.'/Traces/tracing-discarded.zip')->not->toBeFile();
});
