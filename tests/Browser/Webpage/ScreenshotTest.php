<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Support\Screenshot;

it('captures a full-page screenshot', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->screenshot(filename: 'full-page-screenshot.png');

    expect(file_exists(Screenshot::path('full-page-screenshot.png')))
        ->toBeTrue();
});

it('captures a full-page screenshot with default filename', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->screenshot();

    $defaultFilename = str_replace('__pest_evaluable_', '', test()->name());

    expect(file_exists(Screenshot::path($defaultFilename)))
        ->toBeTrue();
});

it('captures a screenshot of an specific element', function (): void {
    Route::get('/', fn (): string => '<div>
        <h1>Text outside of screenshot element</h1>
        <div id="screenshot-element">Text inside of screenshot element</div>
    </div>');

    $page = visit('/');

    $page->screenshotElement('#screenshot-element', 'element-screenshot.png');

    expect(file_exists(Screenshot::path('element-screenshot.png')))
        ->toBeTrue();
});

it('captures responsive screenshots', function (string $device): void {
    Route::get('/', fn (): string => '<div>
        <h1>Responsive Screenshot Test</h1>
        <p>This page will be captured at different screen sizes</p>
    </div>');

    $page = visit('/');

    $page->responsiveScreenshots(filename: 'responsive-test');

    expect(file_exists(Screenshot::path("responsive-test-{$device}.png")))
        ->toBeTrue();
})->with(['xs', 'sm', 'md', 'lg', 'xl', '2xl']);

it('captures responsive screenshots with custom screen sizes', function (): void {
    Route::get('/', fn (): string => '<div>
        <h1>Responsive Screenshot Test</h1>
        <p>This page will be captured at different screen sizes</p>
    </div>');

    $page = visit('/');

    $responsiveScreenSizes = [
        'xs' => ['width' => 360, 'height' => 640],
        'sm' => ['width' => 640, 'height' => 360],
    ];

    $page->responsiveScreenshots(filename: 'responsive-test', responsiveScreenSizes: $responsiveScreenSizes);

    $devices = array_keys($responsiveScreenSizes);

    foreach ($devices as $device) {
        expect(file_exists(Screenshot::path("responsive-test-{$device}.png")))
            ->toBeTrue();
    }
});
