<?php

declare(strict_types=1);

use Pest\Browser\Support\Screenshot;
use Pest\TestSuite;

function resetScreenshotDir(): void
{
    $property = new ReflectionProperty(Screenshot::class, 'dir');
    $property->setValue(null, null);
}

function screenshotTestDir(string $name): string
{
    return sys_get_temp_dir().'/pest-browser-'.$name.'-'.uniqid('', true);
}

beforeEach(function (): void {
    resetScreenshotDir();
});

afterEach(function (): void {
    Screenshot::cleanup();
    resetScreenshotDir();
});

it('uses the default screenshots directory', function (): void {
    expect(Screenshot::dir())->toBe(TestSuite::getInstance()->rootPath.'/tests/Browser/Screenshots');
});

it('uses a configured screenshots directory', function (): void {
    $dir = screenshotTestDir('configured');

    Screenshot::useDirectory($dir);

    expect(Screenshot::dir())->toBe($dir);
});

it('builds screenshot paths from the configured directory', function (): void {
    $dir = screenshotTestDir('path');

    Screenshot::useDirectory($dir);

    expect(Screenshot::path('foo'))->toBe($dir.'/foo.png');
});

it('cleans up only the configured screenshots directory', function (): void {
    $parent = screenshotTestDir('cleanup');
    $dir = $parent.'/Screenshots';
    $sharedFile = $parent.'/shared.txt';

    mkdir($dir.'/Sliders', 0755, true);
    mkdir($dir.'/ImageDiffView', 0755, true);
    file_put_contents($dir.'/screenshot.png', 'screenshot');
    file_put_contents($dir.'/Sliders/slider.png', 'slider');
    file_put_contents($dir.'/ImageDiffView/diff.html', 'diff');
    file_put_contents($sharedFile, 'shared');

    Screenshot::useDirectory($dir);
    Screenshot::cleanup();

    expect(is_dir($dir))->toBeFalse()
        ->and(is_dir($parent))->toBeTrue()
        ->and(file_exists($sharedFile))->toBeTrue();

    @unlink($sharedFile);
    @rmdir($parent);
});

it('places screenshots under tests/Browser/screenshots', function (): void {
    Screenshot::save('asdf', 'test-screenshot.png');

    expect(file_exists(Screenshot::path('test-screenshot.png')))
        ->toBeTrue();
});

it('saves screenshots with .png extension', function (): void {
    Screenshot::save('asdf', 'test-screenshot');

    expect(file_exists(Screenshot::path('test-screenshot.png')))
        ->toBeTrue();
});

it('saves screenshots with .png extension when no extension is provided', function (): void {
    Screenshot::save('asdf', 'test-screenshot.png');

    expect(file_exists(Screenshot::path('test-screenshot.png')))
        ->toBeTrue();
});

it('saves screenshots with .png extension when no extension is provided and the filename starts with a slash', function (): void {
    Screenshot::save('asdf', '/test-screenshot');

    expect(file_exists(Screenshot::path('test-screenshot.png')))
        ->toBeTrue();
});
