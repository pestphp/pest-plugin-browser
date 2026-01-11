<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Api\Webpage;
use PHPUnit\Framework\ExpectationFailedException;

beforeEach(function (): void {
    $this->tempFiles = [];
});

afterEach(function (): void {
    collect($this->tempFiles)->each(fn ($path): bool => @unlink($path));
});

it('captures a download with fluent assertions', function (): void {
    Route::get('/', fn (): string => '<a href="/file" download="report.pdf">Download</a>');
    Route::get('/file', fn (): Response => downloadResponse('Hello!', 'report.pdf'));

    visit('/')
        ->expectDownload(fn ($page): Webpage => $page->click('a[download]'))
        ->assertFilename('report.pdf')
        ->assertUrlContains('/file')
        ->assertSuccessful();
});

it('saves a download to disk', function (): void {
    Route::get('/', fn (): string => '<a href="/file" download="data.txt">Download</a>');
    Route::get('/file', fn (): Response => downloadResponse('File contents here', 'data.txt'));

    $path = tempPath($this, '.txt');

    visit('/')
        ->expectDownload(fn ($page): Webpage => $page->click('a[download]'))
        ->saveAs($path)
        ->assertSuccessful();

    expect(file_get_contents($path))->toBe('File contents here');
});

it('retrieves the temporary download path', function (): void {
    Route::get('/', fn (): string => '<a href="/file" download="temp.bin">Download</a>');
    Route::get('/file', fn (): Response => downloadResponse('content', 'temp.bin'));

    $download = visit('/')
        ->expectDownload(fn ($page): Webpage => $page->click('a[download]'));

    expect(file_exists($download->path()))->toBeTrue();
});

it('handles JavaScript-triggered downloads', function (): void {
    Route::get('/', fn (): string => '
        <button id="btn">Download</button>
        <script>
            document.getElementById("btn").onclick = () => {
                Object.assign(document.createElement("a"), {
                    href: "/file", download: "dynamic.txt"
                }).click();
            };
        </script>
    ');
    Route::get('/file', fn (): Response => downloadResponse('JS Download!', 'dynamic.txt'));

    visit('/')
        ->expectDownload(fn ($page): Webpage => $page->click('#btn'))
        ->assertFilename('dynamic.txt')
        ->assertSuccessful();
});

it('handles binary file downloads', function (): void {
    Route::get('/', fn (): string => '<a href="/image" download="pixel.png">Download</a>');
    Route::get('/image', function () {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        return response($png)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="pixel.png"');
    });

    $path = tempPath($this, '.png');

    visit('/')
        ->expectDownload(fn ($page): Webpage => $page->click('a[download]'))
        ->assertFilename('pixel.png')
        ->saveAs($path);

    expect(filesize($path))->toBeGreaterThan(0);
});

it('reads download contents directly', function (): void {
    Route::get('/', fn (): string => '<a href="/file" download="data.txt">Download</a>');
    Route::get('/file', fn (): Response => downloadResponse('Hello, World!', 'data.txt'));

    $download = visit('/')
        ->expectDownload(fn ($page): Webpage => $page->click('a[download]'));

    expect($download->contents())->toBe('Hello, World!');
});

it('asserts filename contains', function (): void {
    Route::get('/', fn (): string => '<a href="/file" download="invoice-2024-001.pdf">Download</a>');
    Route::get('/file', fn (): Response => downloadResponse('PDF content', 'invoice-2024-001.pdf'));

    visit('/')
        ->expectDownload(fn ($page): Webpage => $page->click('a[download]'))
        ->assertFilenameContains('invoice')
        ->assertFilenameContains('2024');
});

it('asserts content contains', function (): void {
    Route::get('/', fn (): string => '<a href="/file" download="report.csv">Download</a>');
    Route::get('/file', fn (): Response => downloadResponse("Name,Email\nJohn,john@example.com", 'report.csv'));

    visit('/')
        ->expectDownload(fn ($page): Webpage => $page->click('a[download]'))
        ->assertContentContains('John')
        ->assertContentContains('john@example.com');
});

it('chains assertions fluidly', function (): void {
    Route::get('/', fn (): string => '<a href="/file" download="chain.txt">Download</a>');
    Route::get('/file', fn (): Response => downloadResponse('Fluent!', 'chain.txt'));

    $path = tempPath($this, '.txt');

    visit('/')
        ->expectDownload(fn ($page): Webpage => $page->click('a[download]'))
        ->assertSuccessful()
        ->assertFilename('chain.txt')
        ->assertUrlContains('/file')
        ->saveAs($path)
        ->assertSuccessful();
});

it('captures multiple downloads as a collection', function (): void {
    Route::get('/', fn (): string => '
        <button id="all">Download All</button>
        <script>
            document.getElementById("all").onclick = () => {
                ["a.txt", "b.txt", "c.txt"].forEach(f =>
                    Object.assign(document.createElement("a"), {
                        href: "/file/" + f, download: f
                    }).click()
                );
            };
        </script>
    ');
    Route::get('/file/{name}', fn (string $name): Response => downloadResponse("Content: {$name}", $name));

    $downloads = visit('/')->expectDownloads(
        fn ($page): Webpage => $page->click('#all'),
        count: 3
    );

    expect($downloads)->toHaveCount(3);

    // Collection higher-order magic
    $downloads->each->assertSuccessful();

    expect($downloads->map->suggestedFilename()->sort()->values()->all())
        ->toBe(['a.txt', 'b.txt', 'c.txt']);
});

it('fails when more downloads than expected', function (): void {
    Route::get('/', fn (): string => '
        <button id="all">Download All</button>
        <script>
            document.getElementById("all").onclick = () =>
                ["a.txt", "b.txt", "c.txt"].forEach(f =>
                    Object.assign(document.createElement("a"), {
                        href: "/file/" + f, download: f
                    }).click()
                );
        </script>
    ');
    Route::get('/file/{name}', fn (string $name): Response => downloadResponse("Content: {$name}", $name));

    visit('/')->expectDownloads(
        fn ($page): Webpage => $page->click('#all'),
        count: 2
    );
})->throws(ExpectationFailedException::class, 'Expected 2 downloads, but 3 received');

it('saves multiple downloads with collection methods', function (): void {
    Route::get('/', fn (): string => '
        <button id="all">Download All</button>
        <script>
            document.getElementById("all").onclick = () =>
                ["one.txt", "two.txt"].forEach(f =>
                    Object.assign(document.createElement("a"), {
                        href: "/file/" + f, download: f
                    }).click()
                );
        </script>
    ');
    Route::get('/file/{name}', fn (string $name): Response => downloadResponse("File: {$name}", $name));

    $test = $this;
    $downloads = visit('/')->expectDownloads(
        fn ($page): Webpage => $page->click('#all'),
        count: 2
    );

    $paths = $downloads->map(fn ($d) => tap(tempPath($test, '.txt'), fn (string $p): Pest\Browser\Api\PendingDownload => $d->saveAs($p)));

    $paths->each(fn ($path): Pest\Mixins\Expectation => expect(file_exists($path))->toBeTrue());
});

// Helpers

function downloadResponse(string $content, string $filename): Response
{
    return response($content)
        ->header('Content-Type', 'application/octet-stream')
        ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
}

function tempPath(object $test, string $extension): string
{
    $path = sys_get_temp_dir().'/pest-download-'.uniqid().$extension;
    $test->tempFiles[] = $path;

    return $path;
}
