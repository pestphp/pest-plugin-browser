<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\ExpectationFailedException;

it('keeps the original failure as the previous exception', function (): void {
    Route::get('/', fn (): string => '<div>Hello</div>');

    $page = visit('/');

    $thrown = null;

    try {
        $page->assertSee('DefinitelyNotOnThePage');
    } catch (ExpectationFailedException $e) {
        $thrown = $e;
    }

    expect($thrown)->toBeInstanceOf(ExpectationFailedException::class)
        ->and($thrown?->getPrevious())->toBeInstanceOf(ExpectationFailedException::class)
        ->and($thrown?->getPrevious()?->getMessage())->toContain('DefinitelyNotOnThePage');
});
