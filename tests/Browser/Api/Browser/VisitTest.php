<?php

declare(strict_types=1);

it('may visit a page', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->assertSee('Hello World');
});
