<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\ExpectationFailedException;

it('does not replay a withinFrame callback after its clicks have run', function (): void {
    Route::get('/', fn (): string => '<script>window.clicks = 0;</script><iframe class="frame-box" src="/frame"></iframe>');
    Route::get('/frame', fn (): string => '<button id="save">Save</button><script>document.getElementById("save").addEventListener("click", () => parent.clicks++);</script>');

    $attempts = 0;
    $page = visit('/');

    expect(function () use ($page, &$attempts): void {
        $page->withinFrame('.frame-box', function ($frame) use (&$attempts): void {
            $attempts++;
            $frame->click('Save');

            expect(false)->toBeTrue('the callback fails after the click');
        });
    })->toThrow(ExpectationFailedException::class);

    expect($attempts)->toBe(1);
    $page->assertScript('window.clicks', 1);
});
