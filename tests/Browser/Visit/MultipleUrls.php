<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('may visit multiple pages', function (): void {
    Route::get('/page1', fn (): string => '<div>
        <h1>Page 1</h1>
    </div>');

    Route::get('/page2', fn (): string => '<div>
        <h1>Page 2</h1>
    </div>');

    $pages = visit(['/page1', '/page2']);

    $pages->assertSee('Page');
});

it('may array destructure multiple pages', function (): void {
    Route::get('/page1', fn (): string => '<div>
        <h1>Page 1</h1>
    </div>');

    Route::get('/page2', fn (): string => '<div>
        <h1>Page 2</h1>
    </div>');

    [$page1, $page2] = visit(['/page1', '/page2']);

    $page1->assertSee('Page 1');
    $page2->assertSee('Page 2');
});
