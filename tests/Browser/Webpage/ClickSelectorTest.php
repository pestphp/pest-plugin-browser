<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('can click on elements using the data-test selector', function (): void {
    Route::get('/', fn (): string => '
        <form>
            <div data-test="test-selector" onclick="document.getElementById(\'result\').textContent = \'Selector Clicked\'">
                Click Me
            </div>
            <div id="result"></div>
        </form>
    ');

    $page = visit('/');

    $page->clickSelector('@test-selector');

    expect($page->text('#result'))->toBe('Selector Clicked');
});
