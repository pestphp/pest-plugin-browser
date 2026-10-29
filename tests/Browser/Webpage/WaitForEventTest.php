<?php

declare(strict_types=1);

it('waits for a load state', function (): void {
    Route::get('/', fn (): string => 'Hello World');

    $page = visit('/');

    $page->waitForEvent('load')->assertSee('Hello World');
});
