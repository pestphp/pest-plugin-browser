<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('assert an element is focused', function (): void {
    Route::get('/', fn (): string => <<<'HTML'
    <div>
        <input type="text" name="firstname" id="firstname" data-test="firstname" autofocus />
        <input type="text" name="lastname" id="lastname" data-test="lastname" />
    </div>
    HTML);

    $page = visit('/');

    $page->assertFocused('@firstname');
    $page->assertNotFocused('@lastname');
});

it('may fail when asserting an element is focused but it is not', function (): void {
    Route::get('/', fn (): string => <<<'HTML'
    <div>
        <input type="text" name="firstname" id="firstname" data-test="firstname" autofocus />
        <input type="text" name="lastname" id="lastname" data-test="lastname" />
    </div>
    HTML);

    $page = visit('/');

    $page->assertFocused('@lastname');
})->throws(ExpectationFailedException::class);

it('may fail when asserting an element is not focused but it is', function (): void {
    Route::get('/', fn (): string => <<<'HTML'
    <div>
        <input type="text" name="firstname" id="firstname" data-test="firstname" autofocus />
    </div>
    HTML);

    $page = visit('/');

    $page->assertNotFocused('@firstname');
})->throws(ExpectationFailedException::class);
