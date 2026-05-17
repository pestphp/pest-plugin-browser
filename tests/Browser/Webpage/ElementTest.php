<?php

declare(strict_types=1);

it('may assert text on all matching elements', function (): void {
    Route::get('/', fn (): string => '
        <a class="link">wow</a>
        <a class="link">wow</a>
        <a class="link">wow</a>
    ');

    $page = visit('/');

    foreach ($page->element('.link')->all() as $element) {
        expect($element->textContent())->toBe('wow');
    }
});

it('may assert text on the first matching element', function (): void {
    Route::get('/', fn (): string => '
        <a class="link">wow</a>
        <a class="link">other</a>
    ');

    $page = visit('/');

    expect($page->element('.link')->first()->textContent())->toBe('wow');
});
