<?php

declare(strict_types=1);

use Pest\Browser\Page;

it('may navigate to a object page', function (): void {
    Route::get('/page-a', fn (): string => 'page 1');
    Route::get('/page-b', fn (): string => 'page 2');

    $page = visit('/page-a');
    $page->assertSee('page 1');

    $page->navigate(new PageB);
    $page->assertSee('page 2');
});

final class PageB extends Page
{
    public function url(): string
    {
        return '/page-b';
    }
}
