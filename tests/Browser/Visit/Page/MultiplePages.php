<?php

declare(strict_types=1);

use Pest\Browser\Page;

it('may visit multiple URLs and object pages', function (): void {
    Route::get('/page1', fn (): string => '<div>
        <h1>Page 1</h1>
    </div>');

    Route::get('/page2', fn (): string => '<div>
        <h1>Page 2</h1>
    </div>');

    Route::get('/page3', fn (): string => '<div>
        <h1>Page 3</h1>
    </div>');

    $pages = visit(['/page1', new Page2, new Page3]);

    $pages->assertSee('Page');
});

it('may array destructure multiple URLs and object pages', function (): void {
    Route::get('/page1', fn (): string => '<div>
        <h1>Page 1</h1>
    </div>');

    Route::get('/page2', fn (): string => '<div>
        <h1>Page 2</h1>
    </div>');

    Route::get('/page3', fn (): string => '<div>
        <h1>Page 3</h1>
    </div>');

    [$page1, $page2, $page3] = visit(['/page1', new Page2, new Page3]);

    $page1->assertSee('Page 1');
    $page2->assertSee('Page 2');
    $page3->assertSee('Page 3');
});

final class Page2 extends Page
{
    public function url(): string
    {
        return '/page2';
    }
}

final class Page3 extends Page
{
    public function url(): string
    {
        return '/page3';
    }
}
