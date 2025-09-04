<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

pest()->uses(RefreshDatabase::class);

test('example', function (): void {
    Route::get('/', fn (): string => Blade::render(<<<'BLADE'
        hi nuno
        BLADE,
    ));

    visit('/')->assertSee('nuno');
});
