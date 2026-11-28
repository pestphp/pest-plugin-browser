<?php

declare(strict_types=1);

use Pest\Browser\Support\Trace;
use Pest\TestSuite;

beforeEach(function (): void {
    $this->originalRootPath = TestSuite::getInstance()->rootPath;
    $this->tempDir = sys_get_temp_dir().'/pest-browser-trace-test-'.uniqid('', true);
    mkdir($this->tempDir, 0755, true);

    TestSuite::getInstance()->rootPath = $this->tempDir;
});

afterEach(function (): void {
    Trace::cleanup();
    TestSuite::getInstance()->rootPath = $this->originalRootPath;

    @rmdir($this->tempDir.'/tests/Browser');
    @rmdir($this->tempDir.'/tests');
    @rmdir($this->tempDir);
});

function createMinimalTraceZip(): string
{
    $tempFile = tempnam(sys_get_temp_dir(), 'pest-trace-zip-');
    $zip = new ZipArchive();
    $zip->open($tempFile, ZipArchive::OVERWRITE);
    $zip->addFromString('trace.trace', '{}');
    $zip->close();

    $contents = (string) file_get_contents($tempFile);
    @unlink($tempFile);

    return $contents;
}

it('distinguishes failed and passing traces directly from saved archives', function (): void {
    $archive = createMinimalTraceZip();
    $passedPath = Trace::path('passing-test');
    $failedPath = Trace::path('failing-test');

    Trace::save($passedPath, $archive, [], failed: false);
    Trace::save($failedPath, $archive, [], failed: true);

    expect(Trace::isFailed($passedPath))->toBeFalse()
        ->and(Trace::isFailed($failedPath))->toBeTrue()
        ->and(Trace::saved())->toContain($passedPath, $failedPath)
        ->and(Trace::failed())->toBe([$failedPath]);
});

it('returns false when checking failure status on a missing or invalid archive', function (): void {
    $invalidPath = $this->tempDir.'/not-a-zip.zip';
    file_put_contents($invalidPath, 'not a zip archive');

    expect(Trace::isFailed($this->tempDir.'/missing.zip'))->toBeFalse()
        ->and(Trace::isFailed($invalidPath))->toBeFalse();

    @unlink($invalidPath);
});
