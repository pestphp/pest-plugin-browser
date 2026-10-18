<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\ExpectationFailedException;

it('asserts that there are no accessibility issues', function (): void {
    Route::get('/', fn (): string => '
        <!DOCTYPE html>
        <html lang="en">
        <head><title>Accessible</title></head>
        <body>
            <main>
                <h1>Accessible page</h1>
                <img src="data:image/gif;base64,R0lGODlhAQABAAAAACw=" alt="Logo">
            </main>
        </body>
        </html>
    ');

    $page = visit('/');

    $page->assertNoAccessibilityIssues();
});

it('asserts that there are accessibility issues', function (): void {
    Route::get('/', fn (): string => '
        <!DOCTYPE html>
        <html lang="en">
        <head><title>Inaccessible</title></head>
        <body>
            <main>
                <img src="data:image/gif;base64,R0lGODlhAQABAAAAACw=">
            </main>
        </body>
        </html>
    ');

    $page = visit('/');

    $page->assertNoAccessibilityIssues();
})->throws(ExpectationFailedException::class, 'image-alt');

it('asserts that there are accessibility issues inside an iframe', function (): void {
    Route::get('/', fn (): string => '
        <!DOCTYPE html>
        <html lang="en">
        <head><title>Main</title></head>
        <body>
            <main>
                <h1>Main page</h1>
                <iframe src="/frame" title="Embedded content"></iframe>
            </main>
        </body>
        </html>
    ');

    Route::get('/frame', fn (): string => '
        <!DOCTYPE html>
        <html lang="en">
        <head><title>Frame</title></head>
        <body>
            <img src="data:image/gif;base64,R0lGODlhAQABAAAAACw=">
        </body>
        </html>
    ');

    $page = visit('/');

    $page->assertNoAccessibilityIssues();
})->throws(ExpectationFailedException::class, 'image-alt');

it('asserts that there are accessibility issues after navigating to another page', function (): void {
    Route::get('/', fn (): string => '
        <!DOCTYPE html>
        <html lang="en">
        <head><title>First</title></head>
        <body>
            <main>
                <h1>First page</h1>
                <a href="/second">Second page</a>
            </main>
        </body>
        </html>
    ');

    Route::get('/second', fn (): string => '
        <!DOCTYPE html>
        <html lang="en">
        <head><title>Second</title></head>
        <body>
            <main>
                <img src="data:image/gif;base64,R0lGODlhAQABAAAAACw=">
            </main>
        </body>
        </html>
    ');

    $page = visit('/');

    $page->assertNoAccessibilityIssues()
        ->click('Second page')
        ->assertPathIs('/second')
        ->assertNoAccessibilityIssues();
})->throws(ExpectationFailedException::class, 'image-alt');
