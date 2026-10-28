<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Api\PendingAwaitablePage;
use Pest\Browser\Api\Webpage;
use Pest\Browser\Support\Screenshot;
use Pest\TestSuite;
use PHPUnit\Framework\ExpectationFailedException;

beforeEach(function (): void {
    Route::get('/', fn (): string => '
        <body style="margin: 0">
            <div id="box" style="width: 200px; height: 200px; background: rgb(255, 0, 0)"></div>
        </body>
    ');
});

afterEach(function (): void {
    $snapshot = TestSuite::getInstance()->snapshots->current();

    if ($snapshot->exists()) {
        unlink(TestSuite::getInstance()->rootPath.'/'.$snapshot->path());
    }
});

/**
 * Stores the current screenshot of the page as the snapshot of the running test.
 */
function storeScreenshotAsSnapshot(PendingAwaitablePage $page, string $extraChunk = ''): void
{
    $page->screenshot(filename: 'tolerance-baseline.png');

    $png = (string) file_get_contents(Screenshot::path('tolerance-baseline.png'));

    if ($extraChunk !== '') {
        // Insert a tEXt chunk right after IHDR: the pixels stay the same, the bytes do not.
        $chunk = pack('N', mb_strlen($extraChunk, '8bit')).'tEXt'.$extraChunk.pack('N', crc32('tEXt'.$extraChunk));
        $png = mb_substr($png, 0, 33, '8bit').$chunk.mb_substr($png, 33, null, '8bit');
    }

    TestSuite::getInstance()->snapshots->current()->write(base64_encode($png));
}

it('passes when the snapshot has the same pixels but different bytes', function (): void {
    $page = visit('/');

    storeScreenshotAsSnapshot($page, "Comment\0re-encoded");

    $page->assertScreenshotMatches();
});

it('fails with the pixel difference when the screenshot really changed', function (): void {
    $page = visit('/');

    storeScreenshotAsSnapshot($page);

    $page->script("document.getElementById('box').style.background = 'rgb(0, 0, 255)'");

    expect(fn (): Webpage => $page->assertScreenshotMatches())
        ->toThrow(ExpectationFailedException::class, 'pixels (ratio');
});
