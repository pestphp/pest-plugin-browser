<?php

declare(strict_types=1);

it('assert an element is focused', function (): void {
    Route::get('/', fn (): string => <<<'HTML'
    <div>
        <input type="text" name="firstname" id="firstname" data-test="firstname" autofocus />
        <input type="text" name="lastname" id="lastname" data-text="lastname" />
    </div>
    HTML);

    $page = visit('/');

    $page->assertFocused('@firstname');
    $page->assertNotFocused('@lastname');
});
