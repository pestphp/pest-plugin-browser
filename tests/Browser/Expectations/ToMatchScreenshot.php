<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\ExpectationFailedException;

it('does match a screenshot', function (): void {
    $page = page()->goto('/');

    expect($page)->toMatchScreenshot(showDiff: true);
})->skip();

it('does not match a screenshot', function (): void {
    Route::get('screenshot-mismatch', fn (): string => 'pest');

    $page = page()->goto('screenshot-mismatch');

    expect($page)->toMatchScreenshot(showDiff: true);
})->throws(ExpectationFailedException::class);
