<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('may set viewport size', function (): void {
    Route::get('/', fn (): string => '
        <style>
            .mobile-only-element {
                display: none;
            }
            @media screen and (max-width: 600px) {
                .mobile-only-element {
                    display: block;
                }
            }
        </style>

        <div class="mobile-only-element">
            Only visible on mobile
        </div>
    ');

    $page = visit('/');

    $page
        ->assertDontSee('Only visible on mobile')
        ->resize(600, 600)
        ->assertSee('Only visible on mobile');
});

it('may get viewport size', function (): void {
    Route::get('/', fn (): string => '<h1>Test Page</h1>');

    $page = visit('/');

    $page->resize(800, 1200);

    $viewportSize = $page->getViewportSize();

    expect($viewportSize)->toHaveKey('width', 800);
    expect($viewportSize)->toHaveKey('height', 1200);
});
